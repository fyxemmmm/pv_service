<?php

namespace api\controllers;

use common\Bridge;
use common\Helper;
use common\models\Common;
use common\models\GoodsModel;
use common\models\GoodsSpecificationModel;
use common\models\GoodsTypeModel;
use common\models\GoodsUserAddrModel;
use common\models\JushengVipModel;
use common\VarTmp;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use api\models\GoodsIndex;
use api\models\GoodsView;
use common\models\CouponUserModel;
use common\models\CouponModel;
use common\models\CheckOpenLogic;
use common\models\GoodsUserShareModel;
use Yii;

class GoodsController extends CommonController
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
            'except' => ['index','view','share', 'get-type', 'get-type-index']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        $type = $this->get('type'); // 商品类型
        $search = urldecode($this->get('search') ?? '');
        $fields = ['id','name','discount','image','create_time'];

        if($type == 1){  // 推荐的商品
            $query = GoodsIndex::find()->select($fields)
                                       ->andWhere(['is_recommand' => 1]);
            VarTmp::$extra = ['sub_title' => "夏季新品上市,火爆抢购中"];
        }else{
            $query = GoodsIndex::find()->select($fields)
                                       ->andFilterWhere(['goods_type_id' => $type]);
        }

        $query->andFilterWhere(['like', 'name', $search])->andWhere(['status' => 1])->orderBy('top_time desc, id desc');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }


    public function actionView($id)
    {
        $version = $this->get('version');
//        if($id>60 && empty($version))  return Common::response(0,'出错了!请更新到最新版本');  // 兼容老版本

        $query = GoodsView::find()->where(['id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    /*
     * 商品分类
     * */
    public function actionGetType()
    {
        $data = GoodsTypeModel::find()->orderBy('order asc')->asArray()->all();
        $baokuan_img = Common::getAppSwitchKey('APP_BAOKUAN_IMG');
        $data = array_merge([['id' => "1","name" => "爆款","image_url" => $baokuan_img]],$data);
        return $data;
    }

        /*
     * 商品分类
     * */
    public function actionGetTypeIndex()
    {
        $CheckOpenLogic = new CheckOpenLogic();
        $open = $CheckOpenLogic->check();

        if ($open) {
            $limit = 7;
        } else {
            $limit = 8;
        }

        $data = GoodsTypeModel::find()->limit($limit)->orderBy('order asc')->asArray()->all();
        foreach ($data as $key => $value) {
            $data[$key]['type'] = 0;
        }

        $baokuan_img = Common::getAppSwitchKey('APP_BAOKUAN_IMG');
        $data = array_merge([['id' => "1","name" => "爆款","type" => "0","image_url" => $baokuan_img]],$data);

        $loan_img = Common::getAppSwitchKey('APP_LOAN_IMG');

        $open && $data = array_merge($data, [['id' => "88888888","name" => "借钱","type" => "2","image_url" => $loan_img]]);

        $vip_img = Common::getAppSwitchKey('APP_VIP_IMG');
        $data = array_merge($data, [['id' => "88888888","name" => "会员","type" => "1","image_url" => $vip_img]]);

        return $data;
    }

    /*
     * 个人地址信息start
     * */
    public function actionGetAddr()
    {
        $userinfo = $this->getUserInfo();
        $user_id = $userinfo['id'];
        $addr_info = GoodsUserAddrModel::find()->where(['user_id' => $user_id])->orderBy('is_default desc')->asArray()->all();
        return $addr_info;
    }

    public function actionAddAddr()
    {
        $addr = $this->post('addr') ?? '';
        $province = $this->post('province') ?? '';
        $city = $this->post('city') ?? '';
        $district = $this->post('district') ?? '';
        $mobile = $this->post('mobile') ?? '';
        $user_name = $this->post('user_name') ?? '';
        $is_default = $this->post('is_default') ?? 0;
        $userinfo = $this->getUserInfo();
        $user_id = $userinfo['id'];
        if($is_default){
            GoodsUserAddrModel::updateAll(['is_default' => 0],['user_id' => $user_id]);
        }
        $model = new GoodsUserAddrModel();
        $model->user_id = $user_id;
        $model->addr = $addr;
        $model->province = $province;
        $model->city = $city;
        $model->district = $district;
        $model->mobile = $mobile;
        $model->user_name = $user_name;
        $model->is_default = $is_default;
        $model->create_time = date('Y-m-d H:i:s');
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

    public function actionEditAddr()
    {
        $id = $this->post('id');
        $addr = $this->post('addr') ?? '';
        $province = $this->post('province') ?? '';
        $city = $this->post('city') ?? '';
        $district = $this->post('district') ?? '';
        $mobile = $this->post('mobile') ?? '';
        $user_name = $this->post('user_name') ?? '';
        $is_default = $this->post('is_default') ?? 0;
        $userinfo = $this->getUserInfo();
        $user_id = $userinfo['id'];
        if($is_default){
            GoodsUserAddrModel::updateAll(['is_default' => 0],['user_id' => $user_id]);
        }
        $model = GoodsUserAddrModel::find()->where(['id' => $id])->one();
        $model->user_id = $user_id;
        $model->addr = $addr;
        $model->province = $province;
        $model->city = $city;
        $model->district = $district;
        $model->mobile = $mobile;
        $model->user_name = $user_name;
        $model->is_default = $is_default;
        if($model->save()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }

    public function actionDelAddr()
    {
        $id = $this->post('id');
        $model = GoodsUserAddrModel::find()->where(['id' => $id, 'user_id' => $this->userId])->one();
        if($model->delete()) return Common::response(1, 'success');
        return Common::response(0,'failure', $model->getErrors());
    }
    /*
     * 个人地址信息end
     * */

    /*
     * 微信分享之后回调绑定关系
     * */
    public function actionShare(){
        $api_salt = $this->post('api_salt');
        if($api_salt != Bridge::SALT) return Common::response(0, '秘钥错误');
        $user_id = $this->post('user_id');
        $goods_id = $this->post('goods_id');
        $model = new GoodsUserShareModel();
        $model->user_id = $user_id;
        $model->goods_id = $goods_id;
        $model->create_time = date('Y-m-d H:i:s');
        if($model->save()) return Common::response(1, '分享成功');
        return Common::response(0,'分享失败', $model->getErrors());
    }

    /*
     * 选好规格、立即购买  单规格的 已废弃了！！ 兼容之前
     * */
    public function actionBuy(){
        $type = $this->post('type');
        $specification_id = $this->post('specification_id');  // 规格id
        $goods_id = $this->post('goods_id');
        $number = $this->post('number') ?? "1"; // 购买数量

        $s_model = GoodsSpecificationModel::find()->where(['id' => $specification_id])->one();
        $logistics_fee = GoodsModel::find()->select('logistics_fee')->where(['id' => $goods_id])->scalar() ?: 0; // 物流费

        if($type == 'share_click'){ // 点击分享后价格

            $can_discount = Common::checkShopDiscount($this->userId, $goods_id);
            if(!$can_discount)  return Common::response(0, '您还不是会员或未分享');

            $price = $s_model->after_discount_cost; // 单价
        }elseif ($type == 'original_click'){ // 点击原价购买
            $price = $s_model->original_cost; // 单价
        }else{
            return Common::response(0, '类型错误');
        }

        $total_price = $price * $number;
        $total_price = sprintf("%.2f", $total_price);
        $specification = $s_model->name;
        $image_url = $s_model->image_url;
        $goods_name = GoodsModel::find()->select('name')->where(['id' => $goods_id])->scalar() ?: '';

        // 收货地址
        $addr_info = GoodsUserAddrModel::find()->where(['is_default' => 1, 'user_id' => $this->userId])->asArray()->one() ?? [];
        if(empty($addr_info)){
            $addr_info = GoodsUserAddrModel::find()->where(['user_id' => $this->userId])->orderBy('id desc')->asArray()->one() ?? [];
        }
        if(!empty($addr_info)){
            unset($addr_info['user_id']);
            unset($addr_info['create_time']);
        }

        $time = date('Y-m-d H:i:s');
        $jusheng_vip = JushengVipModel::find()->where(['user_id'=>$this->userId,'vip_type'=>2])->one();
        if($jusheng_vip){
            $is_vip = 0;
        }else{
            $is_vip = 1;
        }
        $coupon_count = CouponUserModel::find()
            ->select('coupon_user.*')
            ->leftJoin('coupon','coupon.coupon_code = coupon_user.coupon_code')
            ->where(['coupon_user.user_id' => $this->userId])
            ->andwhere(['coupon_user.coupon_status' => 0])
            ->andwhere(['<', 'coupon_user.start_time', $time])
            ->andWhere(['>', 'coupon_user.end_time', $time])
            ->andWhere(['<=', 'coupon.limit_amount', $total_price])
            ->count();

        $data = [
            'addr_info' => $addr_info,
            'price' => $price, // 单价
            'total_price' => $total_price, // 总价
            'specification_id' => $specification_id,
            'specification' => $specification, // 规格名
            'image_url' => $image_url,
            'goods_name' => $goods_name,
            'number' => $number,
            'logistics_fee' => $logistics_fee,
            'is_vip' => $is_vip,
            'coupon_count' => $coupon_count
        ];

        VarTmp::$json_force_object = true;
        Helper::formatData($data,2);

        return $data;
    }


    // 新版购买
    public function actionBuy2(){
        $cart_list = $this->post('cart_list');
        $is_ios = $this->get('ios');
        if($is_ios){
            $cart_list = json_decode($cart_list, true);
        }
        
        // 收货地址
        $addr_info = GoodsUserAddrModel::find()->where(['is_default' => 1, 'user_id' => $this->userId])->asArray()->one() ?? [];
        if(empty($addr_info)){
            $addr_info = GoodsUserAddrModel::find()->where(['user_id' => $this->userId])->orderBy('id desc')->asArray()->one() ?? (object)[];
        }

        $can_discount = Common::checkShopDiscount($this->userId, null);
        list($price_total, $logistics_fee) = $this->getOrderInfo($cart_list, $can_discount);

        foreach ($cart_list  as $k=>&$v){
            $s_model = GoodsSpecificationModel::find()->where(['id' => $v['specification_id']])->one();
            $goods_name = GoodsModel::find()->select('name')->where(['id' => $v['goods_id']])->scalar() ?: '';
            if($can_discount){
                $v['price'] = $s_model->after_discount_cost;
            }else{
                $v['price'] = $s_model->original_cost;
            }
            $v['image_url'] = $s_model->image_url;
            $v['goods_name'] = $goods_name;
            $v['specification'] = $s_model->name;
        }

        $time = date('Y-m-d H:i:s');
        $jusheng_vip = JushengVipModel::find()->where(['user_id'=>$this->userId,'vip_type'=>2])->one();
        if($jusheng_vip){
            $is_vip = 0;
        }else{
            $is_vip = 1;
        }
        $coupon_count = CouponUserModel::find()
            ->select('coupon_user.*')
            ->leftJoin('coupon','coupon.coupon_code = coupon_user.coupon_code')
            ->where(['coupon_user.user_id' => $this->userId])
            ->andwhere(['coupon_user.coupon_status' => 0])
            ->andwhere(['<', 'coupon_user.start_time', $time])
            ->andWhere(['>', 'coupon_user.end_time', $time])
            ->andWhere(['<=', 'coupon.limit_amount', $price_total])
            ->count();

        $res = [
            'total_price' => $price_total,
            'logistics_fee' => $logistics_fee,
            'addr_info' => $addr_info,
            'cart_list' => $cart_list,
            'is_vip' => $is_vip,
            'coupon_count' => $coupon_count
        ];

        Helper::formatData($res,2);

        return $res;

    }

    // 得到订单总价值和商品名
    private function getOrderInfo($shopping_goods, $can_discount){
        $price_total = 0;
        $logistics_fee_sum = 0;
        foreach ($shopping_goods as $k=>$v){
            $goods_info = GoodsModel::find()->select('name,logistics_fee')->where(['id' => $v['goods_id']])->one();  // 无语
            $logistics_fee = $goods_info['logistics_fee'] ?? 0;

            if($can_discount){
                $price = GoodsSpecificationModel::find()->select('after_discount_cost')->where(['id' => $v['specification_id']])->scalar() ?: 0;
            }else{
                $price = GoodsSpecificationModel::find()->select('original_cost')->where(['id' => $v['specification_id']])->scalar() ?: 0;
            }
            $here_price = $price * $v['number'];
            $price_total += $here_price;
            $logistics_fee_sum += $logistics_fee;
        }

        return [
            $price_total, // 商品总价
            $logistics_fee_sum // 运费 快递
        ];
    }





    /*
     * 邀请好友链接
     * */
    public function actionGetInviteUrl(){
        return Common::getInviteUrl($this->userId);
    }



}
