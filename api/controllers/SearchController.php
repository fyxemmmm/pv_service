<?php

namespace api\controllers;

use api\models\Business;
use common\models\UserModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use common\models\RadioModel;
use common\models\BusinessType;
use common\models\BusinessBackgroundModel;
use common\models\ProvinceModel;
use common\models\CityModel;
use common\models\ArticleModel;
use common\models\FocusModel;
use common\models\RadioParticipantModel;
use common\models\Common;
use common\models\ArticleCommentModel;
use common\models\MakeMoneyGroupModel;
use common\models\MakeMoneyGroupApplyModel;
use common\models\JmbCategoryModel;
use common\models\JmbModel;
use api\models\MakeMoneyGroupUser;
use common\Helper;
use Yii;


class SearchController extends CommonController
{

    public $modelClass = '';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    /*
     * 综合搜索
     * */
    public function actionIndex()
    {
        $keyword = $this->get('keyword') ?? '';
        $user_id = Yii::$app->params['__web']['user_id'];
        /*
         * 电台
         * */
        $radio_data = RadioModel::find()->select('id,title,preview_image')
                                        ->where(['like','title',$keyword])
                                        ->andWhere(['status'=>1])
                                        ->orderBy('id desc')
                                        ->limit(3)
                                        ->asArray()
                                        ->all();
        /*
         * 创业邦
         * */
        $business_query = Business::find()->select('id,b_id,c_id,t_id,area,description')->where(['status' => 1, 'is_del' => 0]);
        if ($user_id) {
            $business_query->andWhere(['or', ['is_pri' => 0], ['u_id' => $user_id]]);
        } else {
            $business_query->andWhere(['is_pri' => 0]);
        }
        $business_query->andWhere(['like', 'description', $keyword]);
        $business_data = $business_query->orderBy('id desc')->limit(3)->asArray()->all();
        foreach($business_data as $k=>&$v){
            $type_name_logo = BusinessType::find()
                ->select('name,index_logo')
                ->where(['id' => $v['t_id']])
                ->one();
            $type_name = $type_name_logo['name'] ?? '';
            $type_logo = $type_name_logo['index_logo'] ?? '';
            $background_image = BusinessBackgroundModel::find()
                ->select('img_url')
                ->where(['id' => $v['b_id']])
                ->scalar() ? : '';

            if ('000' == substr($v['c_id'], -3)) {
                $model = new ProvinceModel();
            } else {
                $model = new CityModel();
            }
            $city_name = $model::find()
                ->select('name')
                ->where(['id' => $v['c_id']])
                ->scalar() ? : '';
            $v['type_name'] = $type_name;
            $v['background_image'] = $background_image;
            $v['city_name'] = $city_name;
            $v['type_logo'] = $type_logo;
            unset($v['b_id']);
            unset($v['c_id']);
            unset($v['t_id']);
        }
        unset($v);

        /*
         * 文章
         * */
        $article_data = ArticleModel::find()->select('id,title,preview_image,type,like_num,t.at_name type_name')
                                            ->leftJoin('article_type as t','t.at_id=article.type')
                                            ->where(['like','title',$keyword])
                                            ->andWhere(['status' => 1])
                                            ->orderBy('id desc')
                                            ->limit(6)
                                            ->asArray()
                                            ->all();
        foreach ($article_data as $k=>&$v){
            $all_comment_num = ArticleCommentModel::find()->where(['article_id' => $v['id'], 'pid'=>0])->andWhere(['del' => 0])->count();
            $private_num = ArticleCommentModel::find()->where(['article_id' => $v['id'], 'is_private' => 1, 'pid'=>0])->andWhere(['<>','user_id',$user_id])->count();
            $v['comment_num'] = $all_comment_num - $private_num;
            unset($v['type']);
        }
        unset($v);

        /*
         * 犀客
         * */
        $user_data = UserModel::find()->select('id,nick_name,avatar_image')
                                      ->where(['like', 'nick_name',$keyword])
                                      ->andWhere(['status' => 1])
                                      ->limit(6)
                                      ->asArray()
                                      ->all();
        foreach ($user_data as $k=>&$v){
            $v['focus_num'] = FocusModel::find()->where(['focus_user_id' => $v['id']])->count();
            $article_own = ArticleModel::find()->where(['creater' => $v['id']])->count();
            $radio_own = RadioParticipantModel::find()->where(['user_id' => $v['id']])->count();
            $v['product_num'] = $article_own + $radio_own;
        }
        unset($v);

        /*
         * 群聊
         * */
        $group_data = MakeMoneyGroupModel::find()->select('name,description,logo,nums,upvote_nums,im_group_id,create_time')
                                                 ->where(['like','name',$keyword])
                                                 ->andWhere(['or', ['status' => 1], ['u_id' => $this->userId]])
                                                 ->orderBy('id desc')
                                                 ->limit(3)
                                                 ->asArray()
                                                 ->all();
        foreach ($group_data as $k=>&$v){
            $v['is_new'] =  strtotime("-1 months") > strtotime($v['create_time'])  ? 0 : 1;
            $join_status = 0;
            $apply_exists = MakeMoneyGroupApplyModel::findOne(['u_id' => $this->userId, 'im_group_id' => $v['im_group_id'], 'status' => 0]);
            if ($apply_exists) {
                $join_status = 2; // 申请中
            } else {
                $join_exists = MakeMoneyGroupUser::findOne(['u_id' => $this->userId, 'im_group_id' => $v['im_group_id']]);
                if ($join_exists) {
                    $join_status = 1; // 已加入
                }
            }
            $v['join_status'] = $join_status;
        }
        unset($v);


        /*
         * 加盟
         * */
        $jmb_data = JmbModel::find()->select('jmb.id as id,jmb.name as name,apply_num,direct_store_num,jmb.image_url,register_time,est_init_investment,j.pid')
            ->andFilterWhere(['like','jmb.name',$keyword])
            ->orFilterWhere(['and',
                ['like','j.name',$keyword],
                ['<>', 'j.pid', 0]
            ])
            ->leftJoin('jmb_category as j','j.id=jmb.jmb_category_id')
            ->limit(5)
            ->orderBy('jmb.id desc')
            ->asArray()
            ->all();

        foreach ($jmb_data as $k=>&$v){
            // 几年品牌
            preg_match('/\d+/',$v['register_time'],$match);
            if(empty($match)) $v['brand_year'] = 0;
            $brand_year = $match[0];
            $today_year = date('Y');
            $v['brand_year'] =($today_year - $brand_year);
            unset($v['register_time']);
            $v['category_name'] = JmbCategoryModel::find()->select('name')->where(['id' => $v['pid']])->scalar();
            unset($v['pid']);
        }
        unset($v);


        $data = [
            'radio_data' => $radio_data,
            'business_data' => $business_data,
            'article_data' => $article_data,
            'user_data' => $user_data,
            'group_data' => $group_data,
            'jmb_data' => $jmb_data
        ];
        Helper::formatData($data);
        return Common::response(1, 'success', $data);
    }


}
