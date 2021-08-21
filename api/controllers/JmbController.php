<?php
namespace api\controllers;
use api\models\Jmb;
use common\models\CollectionsModel;
use common\models\JmbModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\JmbSearch;
use common\models\JmbCategoryModel;
use api\models\JmbHotCategory;
use yii\data\ActiveDataProvider;
use common\models\Common;
use common\models\JmbImageModel;
use Yii;

class JmbController extends CommonController
{
    public $modelClass = '';

    CONST INVEST = [  // 投资搜索
        '1-10万',
        '10-20万',
        '20-30万',
        '30-40万',
        '40-50万',
        '50万以上',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['index', 'view','detail', 'get-home-category','get-category', 'get-hot-prefecture', 'get-first-cate','get-child-cate','get-banner', 'get-ad','get-invest-price']
        ];
        return $behaviors;
    }

    public function actions()
    {
        Jmb::$userID = $this->userId;
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $searchModel = new JmbSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['JmbSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    public function actionDetail()
    {
        $id = $this->get('id') ?? '';
        if(!$id) return Common::response(0,  '请传id');
        // 需要统计
        Jmb::findOne($id)->updateCounters(['apply_num' => 1]);
        $searchModel = new JmbSearch();
        $queryParams = Yii::$app->request->queryParams;
        $search['JmbSearch'] = $queryParams;
        $dataProvider = $searchModel->search($search);
        return $dataProvider;
    }

    /*
     * 获取首页1级分类
     * */
    public function actionGetHomeCategory(){
        $per_page = $this->get('per-page') ?? '';
        $model = JmbCategoryModel::find()->where(['pid' => 0]);
        if($per_page){
            $data = $model->limit($per_page)->asArray()->all();
        }else{
            $data = $model->asArray()->all();
        }

        return $data;
    }

    /*
     * 热门专区
     * */
    public function actionGetHotPrefecture(){
        $query = JmbHotCategory::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

   /*
    * 获取第一级分类
    * */
    public function actionGetFirstCate(){
        $category = JmbCategoryModel::find()->select('id,name')->where(['pid' => 0])->asArray()->all();
        array_unshift($category, ['id'=>"0", "name"=>"热门"]);
        return $category;
    }

    /*
     * 二级分类
     * */
    public function actionGetChildCate(){
        $pic =  JmbImageModel::find()->select('image_url,jmb_id,use_out_link,out_link_url')->where(['type' => 2])->asArray()->one();
        $pid = $this->get('id') ?? 0;
        if(0 == $pid){ // 热门加盟
            $hot = Jmb::find()->select('id,name,image_url')->where(['is_hot_recommend' => 1])->asArray()->all();
            $data =  [
                'category_child' => [],
                'category_brand' => $hot
            ];
            $data = array_merge(['first_cate_id'=>$pid], $data);
            return array_merge($pic, $data);
        }
        $child_cate = JmbCategoryModel::find()->select('id,name,image_url')->where(['pid' => $pid])->asArray()->all();

        $brand_ids = array_column($child_cate, 'id'); // 名品加盟
        $brand_data = JmbModel::find()->select('id,image_url,name')->where(['in','jmb_category_id',$brand_ids])->andWhere(['is_hot_join' => 1])->asArray()->all();
        $data = [
            'category_child' => $child_cate,
            'category_brand' =>$brand_data
        ];
        $data = array_merge(['first_cate_id'=>$pid], $data);
        return array_merge($pic, $data);
    }
    
    /*
     * banner
     * */
    public function actionGetBanner(){
        return JmbImageModel::find()->select('image_url,jmb_id,use_out_link,out_link_url')->where(['type' => 1])->asArray()->all();
    }

    /*
     * 收藏
     * */
    public function actionCollect(){
        $jmb_id = $this->post('id');
        $active = 0;
        $model = CollectionsModel::find()->where(['item_id' => $jmb_id,"type"=>1,"user_id" => $this->userId])->one();
        if($model){
            $model->delete();
        }else{
            $model = new CollectionsModel();
            $model->user_id = $this->userId;
            $model->item_id = $jmb_id;
            $model->type = 1;
            $model->save();
            $active = 1;
        }
        return Common::response(1, '操作成功', ['active' => $active]);
    }

    public function actionGetInvestPrice(){
        $list = [];
        foreach (self::INVEST as $v){
            $list[] = ['price' => $v];
        }
        return $list;
    }
}
