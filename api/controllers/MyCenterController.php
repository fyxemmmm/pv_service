<?php

namespace api\controllers;

use common\Config;
use common\models\FocusModel;
use common\models\InterestCardModel;
use common\models\InterestPayserialModel;
use common\models\JushengVipModel;
use common\models\JushengVipOrderModel;
use common\models\UserCardModel;
use common\models\UserModel;
use common\models\CouponUserModel;
use common\models\Common;
use common\models\CityModel;
use common\models\ProvinceModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use common\Bridge;
use common\models\UserRelationshipModel;
use common\models\UserDcOrderModel;
use common\models\UserAttendanceModel;
use common\models\UserIncomeModel;
use api\models\UserRelationship;
use yii\data\ActiveDataProvider;
use common\models\UserIdCardNoModel;
use common\models\UserApplyPriceModel;
use common\Helper;
use common\models\GoodsOrderModel;
use common\models\GoodsOrderSubModel;
use common\models\Jusheng;
use common\models\JushengCouponModel;
use common\models\UserAttendancePriceModel;
use common\models\XiaoyingVipOrderModel;
use Yii;

class MyCenterController extends CommonController
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
            'except' => ['get-focus-info','gen-code-pic']
        ];
        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }


    public function actionGetFocusInfo(){

        $id = $this->get('id'); // 用户id

        $focus_num = (int)FocusModel::find()->where(['user_id' => $id])->count() ?? 0; // 已关注
        $focused_num = (int)FocusModel::find()->where(['focus_user_id' => $id])->count() ?? 0; // 被关注
        /*
        $focus_num = UserModel::find()->select('focus_num')->where(['id' => $id])->scalar();
        $focused_num = UserModel::find()->select('focused_num')->where(['id' => $id])->scalar();
        */
        // stop
        
        $user_info = UserModel::find()->select('nick_name,mobile,gender,city_id,avatar_image,register_time,huanxin_username')->where(['id' => $id])->asArray()->one();
        $register_time = $user_info['register_time'] ? date('Y-m-d',strtotime($user_info['register_time'])) : '';
        $is_focus = FocusModel::find()->where(['user_id'=>$this->userId])->andWhere(['focus_user_id'=>$id])->one();
        $focus = $is_focus ? 1 : 0;
        $mobile = $user_info['mobile'] ?  : '';
        $nick_name = $user_info['nick_name'] ? : '';
        $avatar_image = $user_info['avatar_image'] ? : '';
        $gender = $user_info['gender'];
        $city_id = $user_info['city_id'] ?? '';
        $huanxin_username = $user_info['huanxin_username'] ?? '';
        if($city_id){
            $city = CityModel::find()->select('name,pid')->where(['id'=>$city_id])->asArray()->one();
            $city_name = $city['name'];
            $province_name = ProvinceModel::find()->select('name')->where(['id'=>$city['pid']])->scalar();
            $city_name = $province_name . $city_name;
        }else{
            $city_name = '';
        }

        $response = [
            'id' => (int)$id, // 他人用户的id
            'register_time' => $register_time,
            'focus_num'=>$focus_num,
            'focused_num'=>$focused_num,
            'avatar_image'=>$avatar_image,
            'nick_name'=>$nick_name,
            'focus' => $focus, // 1 已关注
            'mobile' => $mobile,
            'gender' => $gender == 1 ? '男' : '女',
            'city_name' => $city_name,
            'huanxin_username' => $huanxin_username
        ];

        return Common::response(1, '操作成功', $response);
    }


    public function actionFocus(){
        $id = $this->post('id'); // 他人用户id
        $model = FocusModel::find()->where(['focus_user_id' => $id])->andWhere(['user_id'=>$this->userId])->one();
        $active = 0;
        if($model){
            UserModel::findOne($this->userId)->updateCounters(['focus_num' => -1]);
            UserModel::findOne($id)->updateCounters(['focused_num' => -1]);
            $model->delete();
        }else{
            UserModel::findOne($this->userId)->updateCounters(['focus_num' => 1]);
            UserModel::findOne($id)->updateCounters(['focused_num' => 1]);
            $model = new FocusModel();
            $model->user_id = $this->userId;
            $model->focus_user_id = $id;
            $model->save();
            $active = 1;
            Helper::pushMessage(4,['user_id'=>$this->userId, 'accept_user_id'=>$id]);
        }
        return Common::response(1, '操作成功',['active'=>$active]);
    }


    public function actionWallet(){
        $user_id = $this->userId;
        $data = UserModel::find()->select('id,nick_name,total_income,available_money,user_level as level')->where(['id' => $user_id])->asArray()->one();
        $level = $data['level'];

        // 检查是否已经是犀金会员,如果是会员,那么默认就让他是黄金级
        $is_vip = Common::checkVip($data['id']);
        if($level == Bridge::SILVER && $is_vip === true) $level = Bridge::GOLD;

        switch ($level){
            case Bridge::SILVER;
                $data['level'] = Bridge::LEVEL[Bridge::SILVER];
                break;
            case Bridge::GOLD;
                $data['level'] = Bridge::LEVEL[Bridge::GOLD];
                break;
            case Bridge::DIAMOND;
                $data['level'] = Bridge::LEVEL[Bridge::DIAMOND];
                break;
        }

        $invite_sign = Bridge::genSign($user_id); // 邀请码

        $my_users_count = UserRelationshipModel::find()->where(['leader_id' => $user_id])->count();  // 我的下线总数

        // 会员
        $date = date('Y-m-d H:i:s');
//        $vip_info = InterestPayserialModel::find()->where(['user_id' => $user_id])->andWhere(['>','finish_time', $date])->one();
        $vip_info = JushengVipModel::find()->where(['user_id' => $user_id])->andWhere(['>=','expire_time', $date])->one();

        if($vip_info){
            $is_vip = true;
//            $card_id = $vip_info['card_id'];
//            $vip_name = InterestCardModel::find()->select('name')->where(['id' =>$card_id])->scalar() ?: '';
            $vip_name = '黑金会员';
        }else{
            $is_vip = false;
            $vip_name = '';
        }

        $bridge = new Bridge();
        $share_url = $bridge->xijin_loan_wap_url . '/loan/2.html?' . "sign=".$invite_sign; // 落地页带token
        $loan_url = $bridge->lb_share_url . '?sign='.$invite_sign . '&callback=' .$bridge->xijin_loan_wap_url . '/loan/2.html?sign='.$invite_sign; //callback 用邀请码分享的落地页 h5

        $now =  date('Y-m-d H:i:s');
        $new_order_count = UserDcOrderModel::find()->where(['leader_id' => $user_id])->andWhere(['>','new_time',$now])->asArray()->count(); // 今日订单
        $db = \Yii::$app->db;
        $sql = "SELECT COUNT(distinct user_id) AS count from user_dc_order where leader_id = '$user_id'";
        $pay_user_count  = $db->createCommand($sql)->queryOne(); // 下款用户

        $attendance_price = UserAttendancePriceModel::find()->select('attendance_price')->scalar() ?: 0;

        /*
         * 签到数据
         * */
        $sign_in_data = UserAttendanceModel::find()->where(['type' => Bridge::ATTENDANCE_QD, 'user_id' =>$user_id])->orderBy('id desc')->asArray()->one();
        $sign_in_data  = $this->getUserAttendanceInfo($sign_in_data);
        $return_data = [
            'invite_sign' => $invite_sign,
            'is_vip' => $is_vip,
            'vip_name' => $vip_name,
            'share_url' => $share_url,
            'loan_url' => $loan_url,
            'attendance_price' => $attendance_price,
            'my_users_count' => $my_users_count, // 下线总数
            'pay_user_count' => $pay_user_count['count'], // 下款用户数
            'new_order_count' => $new_order_count, // 今日订单(最新)
            'is_sign' => $sign_in_data['is_sign'], // 今天是否已签到
            'sign_day' => $sign_in_data['sign_day'], // 签到天数
        ];

        return array_merge($data, $return_data);
    }

    /*
     * 获取用户的签到信息
     * */
    public function getUserAttendanceInfo($data){
        // data是最新的
        // 如果从没有签到过
        if(empty($data)){
            return [
                'is_sign' => false,
                'sign_day' => 0
            ];
        }

        // 如果存在今天的签到数据
        if(date('Y-m-d',strtotime($data['attendance_time'])) == date('Y-m-d')){
            return [
                'is_sign' => true,
                'sign_day' => $data['continue_days']
            ];
        }

        // 如果不存在今天签到的数据，那么存在过昨天的？
        if(date('Y-m-d',strtotime($data['attendance_time'])) == date('Y-m-d',strtotime('-1 day'))){
            return [
                'is_sign' => false,
                'sign_day' => $data['continue_days']
            ];
        }

        // 昨天也没签到过,签到中断了,从零开始
        return [
            'is_sign' => false,
            'sign_day' => 0
        ];
    }


    /*
     * 用户签到
     * */
    public function actionAttendance(){
        $redis = \Yii::$app->cache->redis;
        $is_going = $redis->set('attendance'.$this->userId, $this->userId, 'ex','10', 'nx');
        if(!$is_going){
            return Common::response(0, '您已经签到过啦,遇到问题请联系客服');
        }

        $sign_in_data = UserAttendanceModel::find()->where(['type' => Bridge::ATTENDANCE_QD, 'user_id' =>$this->userId])->orderBy('id desc')->asArray()->one();
        $sign_info = $this->getUserAttendanceInfo($sign_in_data);
        if($sign_info['is_sign'] == true){
            $redis->del('attendance'.$this->userId);
            return Common::response(0, '您今天已签到过');
        }

        $model = new UserAttendanceModel();
        $transaction = UserAttendanceModel::getDb()->beginTransaction();
        try {
            $model->user_id = $this->userId;
            $model->type = Bridge::ATTENDANCE_QD;
            if ($sign_info['sign_day'] == 0) {
                $model->continue_days = 1;
                $model->attendance_time = date('Y-m-d H:i:s');
                $model->save();
                $transaction->commit();
                $redis->del('attendance'.$this->userId);
                return Common::response(1, '签到成功!', ['sign_day' => 1]);
            } else {
                if ($sign_info['sign_day'] != 7) {
                    $continue_days = $sign_info['sign_day'] + 1;
                    $model->continue_days = $continue_days;
                    $model->attendance_time = date('Y-m-d H:i:s');
                    $model->save();
                    if ($sign_info['sign_day'] == 6) { // 签到6天,签到第七天会奖励指定金额
                        $money = UserAttendancePriceModel::find()->select('attendance_price')->scalar() ?: 0;
                        $income_model = new UserIncomeModel();
                        $income_model->user_id = $this->userId;
                        $income_model->type = Bridge::INCOME_TYPE_QD;
                        $income_model->income = $money;
                        $income_model->create_time = date('Y-m-d H:i:s');
                        $income_model->order_sign = Common::genOrderSign();
                        $income_model->save();
                        $user = UserModel::findOne($this->userId);
                        $user->available_money =  $user->available_money + $money;
                        $user->total_income =  $user->total_income + $money;
                        $user->save();
                        Helper::pushMessage(5,['accept_user_id' => $this->userId, 'content' => Config::INCOMEMSG]);
                    }

                    $transaction->commit();
                    $redis->del('attendance'.$this->userId);
                    return Common::response(1, '签到成功!', ['sign_day' => $continue_days]);
                } else {
                    $continue_days = 1;
                    $model->continue_days = $continue_days;
                    $model->attendance_time = date('Y-m-d H:i:s');
                    $model->save();
                    $transaction->commit();
                    $redis->del('attendance'.$this->userId);
                    return Common::response(1, '签到成功!', ['sign_day' => 1]);
                }
            }

        }catch(\Exception $e) {
            $transaction->rollBack();
            $redis->del('attendance'.$this->userId);
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            $redis->del('attendance'.$this->userId);
            return Common::response(0, '操作失败', $e->getMessage());
        }
    }


    /*
     * 我的推广等级1
     * */
    public function actionGeneralize(){
        $user_id = $this->userId;
        $zsxj = UserRelationshipModel::find()->select('user_id')->where(['leader_id' => $user_id])->asArray()->all();   // 直属下级
        $zsxj_ids = array_column($zsxj, 'user_id');// 直属下级id
        $zsxj_count = count($zsxj_ids); // 直属下级总数
        $fsxj_count = UserRelationshipModel::find()->where(['in', 'leader_id', $zsxj_ids])->count(); // 附属下级(下级的下级 总数)

        $zs_total_money = UserDcOrderModel::find()->select('pay_money')->where(['leader_id' => $user_id, 'status' => Bridge::AUDIT_PASS])->asArray()->all(); // 直属下级下款额度
        $zs_total_money = array_column($zs_total_money, 'pay_money');
        $zs_total_money = array_sum($zs_total_money);

        $user = UserModel::find()->select('user_level,total_income')->where(['id' => $user_id])->asArray()->one();
        $is_vip = Common::checkVip($this->userId);
        if($is_vip === true && $user['user_level'] == Bridge::SILVER){  // 如果是犀金VIP而且是白银级,那么默认就让他享受黄金级别的待遇
            $user['level'] = Bridge::LEVEL[Bridge::GOLD];
            $user['next_level'] = Bridge::LEVEL[Bridge::DIAMOND];
            $user['team_group_count'] = Bridge::DIAMOND_COUNT;
        }else{
            switch ($user['user_level']){
                case Bridge::SILVER:
                    $user['level'] = Bridge::LEVEL[Bridge::SILVER];
                    $user['next_level'] = Bridge::LEVEL[Bridge::DIAMOND];
                    $user['team_group_count'] = Bridge::DIAMOND_COUNT;
                    break;
//                case Bridge::GOLD:
//                    $user['level'] = Bridge::LEVEL[Bridge::GOLD];
//                    $user['next_level'] = Bridge::LEVEL[Bridge::DIAMOND];
//                    $user['team_group_count'] = Bridge::DIAMOND_COUNT;
//                    break;
                case Bridge::DIAMOND:
                    $user['level'] = Bridge::LEVEL[Bridge::DIAMOND];
                    $user['next_level'] = Bridge::LEVEL[Bridge::DIAMOND];
                    $user['team_group_count'] = Bridge::DIAMOND_COUNT;
                    break;
            }
        }


        $desc1 = ($zsxj_count + $fsxj_count) >= 100 ? '团队基数大' : '团队较小';
        if($zsxj_count !== 0){
            $desc2 = ($zs_total_money / $zsxj_count) >= 3000 ? '团队收益均衡' : '团队收益待提高';
            $desc3 = ($fsxj_count / $zsxj_count) >= 10 ? '团队发展健康' : '下级数量待扩充';
        }else{
            $desc2 = '团队收益有待提高';
            $desc3 = '下级数量待扩充';
        }

        $data = [
            'level' => $user['level'], // 等级
            'next_level' => $user['next_level'], // 下一等级
            'team_group_count' => $user['team_group_count'], // 团队等级
            'desc1' => $desc1,
            'desc2' => $desc2,
            'desc3' => $desc3,
            'zsxj_count' => $zsxj_count, // 直属下级总数
            'fsxj_count' => $fsxj_count, // 附属下级总数
            'zs_total_money' => $zs_total_money, // 直属下款
            'total_income' => $user['total_income'] //累计收益
        ];
        Helper::formatData($data, 2);
        return $data;
    }

    /*
     * 我的推广等级2
     * */
    public function actionGeneralizeSecond(){
        $query = UserRelationship::find()->where(['leader_id' => $this->userId]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }


    /*
     * 检查是否已经实名认证过
     * */
    public function actionCheckReal(){
        $is_check = Common::checkReal($this->userId);
        return ['is_check' => $is_check];
    }

    /*
     * 进行实名认证
     * */
    public function actionRegisterReal(){
        $real_name = $this->post('real_name');
        $identity_number =  $this->post('identity_number');
        $is_legal = preg_match("/^\d{15,18}$/",$identity_number);
        if(!$is_legal) return Common::response(0, '请输入正确的身份证号码');
        if(mb_strlen($real_name) > 6) return Common::response(0, '请输入正确的姓名信息');

        $model = UserIdCardNoModel::find()->where(['user_id' => $this->userId])->one();
        if(!$model){
            $model = new UserIdCardNoModel();
            $model->real_name = $real_name;
            $model->user_id = $this->userId;
            $model->id_card_no = $identity_number;
            $model->create_time = date('Y-m-d H:i:s');
            if($model->save()) return Common::response(1, '身份信息绑定成功');
            return Common::response(0, '绑定失败', $model->getErrors());

        }
        return Common::response(0, '您已绑定过身份信息');
    }

    /*
     * 我的钱包界面
     * */
    public function actionGetWalletInfo(){
        $user_id = $this->userId;
        $money_info =  UserModel::find()->select('total_income,available_money')->where(['id' => $user_id])->asArray()->one();
        $code_pic_url = Common::genCodePic($user_id); // 生成图片二维码
        $data = array_merge($money_info, ['code_pic_url' => $code_pic_url]);
        return $data;
    }

    /*
     * 刷新二维码信息
     * */
    public function actionGenCodePic(){
        $user_id = $this->get('id'); // 用户id
        $codePic_url = Common::genCodePic($user_id); // 生成图片二维码
        return ['code_pic_url' => $codePic_url];
    }


    /*
     * 申请提现   点击立即提现按钮
     * */
    public function actionApplyForPrice(){
        $redis = \Yii::$app->cache->redis;
        $is_going = $redis->set('apply_price'.$this->userId, $this->userId, 'ex','10', 'nx');
        if(!$is_going){
            return Common::response(0, '请不要重复提交');
        }

        $user_id = $this->userId;
        $redis->del('apply_price'.$this->userId);
        $apply_price = $this->post('apply_price'); // 申请提现的金额
        $code_pic = $this->post('code_pic');  // 图片二维码
        $sms_code = $this->post('sms_code');
        $user_card_id = $this->post('card_id');  // 银行卡的id
        if(!is_numeric($apply_price)){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '金额不正确');
        }

        if($apply_price <= 0){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '请输入正确的提现金额');
        }

        $cache = \Yii::$app->cache;
        $captcha = $cache->get('codePic' . $user_id); // 图形验证码
        if(!$captcha){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '图形验证码已过期,请刷新');
        }

        if(strtoupper($code_pic) !== $captcha){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '图形验证码输入有误');
        }

        $mobile = $this->getUserInfo()['mobile'];
        $redis_sms_code = $cache->get('sms_code_' . $mobile);
        if($redis_sms_code != $sms_code){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '手机验证码有误');
        }

        // 可用的提现的金额
        $available_money = UserModel::find()->select('available_money')->where(['id' => $user_id])->scalar();

        if(($available_money < $apply_price) || $available_money == 0){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '提现金额大于您的可用余额!');
        }

        if($apply_price < 20){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '可用余额满20元方可提现');
        }

        $check = UserCardModel::find()->where(['id' => $user_card_id, 'user_id' => $user_id])->one();
        if(!$check){
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '请核对提交的卡号信息, 并未找到');
        }

        // 验证通过, 开始提现
        $model = new UserApplyPriceModel();
        $transaction = UserApplyPriceModel::getDb()->beginTransaction();
        try {
            $model->apply_price = $apply_price;
            $model->user_id = $user_id;
            $model->user_card_id = $user_card_id;
            $model->order_sign = Common::genOrderSign();
            $model->status = Bridge::APPLY_DOING;
            $model->create_time = date('Y-m-d H:i:s');
            $model->save();

            $user = UserModel::findOne($this->userId);
            $user->available_money = $user->available_money - $apply_price;
            $user->save();
            $transaction->commit();
            $redis->del('apply_price'.$this->userId);
        } catch(\Exception $e) {
            $transaction->rollBack();
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '操作失败', $e->getMessage());
        } catch(\Throwable $e) {
            $transaction->rollBack();
            $redis->del('apply_price'.$this->userId);
            return Common::response(0, '操作失败', $e->getMessage());
        }
        return Common::response(1, '提现申请成功！');
    }


    public function actionSendSms(){
        $mobile = $this->getUserInfo()['mobile'];
        $smsCode = rand(1000, 9999);
        Common::toggleSmsCode($mobile, $smsCode);
        return Common::response(1, '验证码发送成功');
    }

    public function actionIndex()
    {
        $bridge = new Bridge();
        $response = [];

        $user_info = $this->getUserInfo();
        $invite_sign = Bridge::genSign($user_info['id']);
        $share_url = $bridge->xijin_loan_wap_url . '/loan/2.html?' . "sign=".$invite_sign; // 落地页带token
        $loan_url = $bridge->lb_share_url . '?sign='.$invite_sign . '&callback=' .$bridge->xijin_loan_wap_url . '/loan/2.html?sign='.$invite_sign; //callback 用邀请码分享的落地页 h5

        $response = [
            'id' => $user_info['id'],
            'mobile' => $user_info['mobile'],
            'nick_name' => $user_info['nick_name'],
            'avatar_image' => $user_info['avatar_image'],
            'invite_sign' => $invite_sign,
            'share_url' => $share_url,
            'loan_url' => $loan_url
        ];

        $js_vip = JushengVipModel::find()
            ->where(['user_id' => $user_info['id']])
            ->andWhere(['<>', 'vip_type', 10])
            ->andWhere(['>', 'expire_time', date('Y-m-d H:i:s')])
            ->orderBy('vip_type DESC')
            ->asArray()
            ->all();

        $vip_type = 0;
        $expire_time = '';
        if ($js_vip) {
            $vip_info = reset($js_vip);
            $vip_type = $vip_info['vip_type'];
            $expire_time = $vip_info['expire_time'];
        } else {
            $vip_type = 0;
        }

        $app_name = Yii::$app->request->headers['name'];
        if ('xijing' != $app_name) {
            $is_xiaoying_vip = XiaoyingVipOrderModel::find()->where(['user_id' => $user_info['id'], 'trade_status' => 1])->one();
            if ($is_xiaoying_vip) {
                $vip_type = 10;
            } else {
                $vip_type = 0;
            }
        }

        $conut = (int)JushengVipOrderModel::find()
            ->where(['user_id' => $user_info['id']])
            ->andWhere('update_time > 0')
            ->count();
        if($conut > 0){
            $newvip = 0;
        }else{
            $newvip = 1;
        }

        $gift_bag_num = JushengCouponModel::find()
            ->where([
                'user_id' => $this->userId,
                'coupon_status' => 0
                ])
            ->andWhere(['>', 'end_time', date('Y-m-d H:i:s')])
            ->count();
        $coupon = CouponUserModel::find()
            ->where(['user_id' => $this->userId])
            ->andwhere(['coupon_status' => 0])
            ->count();
        $gift_bag_num +=  $coupon;
        $Jusheng = new Jusheng();
        $vault = $Jusheng->getData("/appweb/data/ws/rest/vip/agent/my/vault",['userId' => (string)$this->userId]);

        if(2 == $vip_type){
            $money_saved = (string)($vault['totalAmt'] + 110);
        }else{
            $money_saved = $vault['totalAmt'];
        }

        $expire_time && $expire_time = date('Y-m-d', strtotime($expire_time));
        $response['vip_type'] = $vip_type;
        $response['expire_time'] = $expire_time;
        $response['gift_bag_num'] = $gift_bag_num;
        $response['money_saved'] = $money_saved;

        $new_order_id = 0;
        $new_order_image = '';
        $new_order_status_msg = '';
        $new_order_date = '';

        $order = GoodsOrderModel::find()
            ->where(['user_id' => $this->userId])
            ->andWhere(['in', 'status', [3,4]])
            ->orderBy('id DESC')
            ->asArray()
            ->one();

        if ($order) {
            $new_order_id = (int) $order['id'];
            $new_order_image = $order['goods_image'];
            $new_order_status_msg = 4 == $order['status'] ? '已完成' : '已发货';
            $new_order_date = date('m-d', strtotime($order['update_time']));

            if ('v2' == $order['version']) {
                $order_sub = GoodsOrderSubModel::find()
                    ->where(['goods_order_id' => $new_order_id])
                    ->asArray()
                    ->one();

                $new_order_image = $order_sub['goods_image'];
            }
        }

        $response['is_new_vip'] = $newvip;
        $response['new_order_id'] = $new_order_id;
        $response['new_order_image'] = $new_order_image;
        $response['new_order_status_msg'] = $new_order_status_msg;
        $response['new_order_date'] = $new_order_date;

        return $response;
    }

}