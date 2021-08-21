<?php

namespace service\controllers;

use api\models\UserRelationship;
use common\Bridge;
use common\Config;
use common\Helper;
use common\models\UserDcOrderModel;
use common\models\Common;
use common\models\UserIncomeModel;
use common\models\UserModel;
use common\VarTmp;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\CompositeAuth;
use PhpOffice\PhpSpreadsheet\IOFactory;



/*
 * 导表之后的订单表  0未下款(审核未通过)  1甲方正在审核   2已打钱(审核通过之后 余额进入卖家的钱包)
 * */
class UserDcOrderController extends CommonController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['index'],$actions['view']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                QueryParamAuth::className(),
            ],
            'except' => ['download-template'],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        $status = $this->get('status');
        $order_sign = $this->get('order_sign');
        $date = $this->get('date');
        $product_id = $this->get('product_id');
        $start_money = $this->get('start_money');
        $end_money = $this->get('end_money');

        $query = UserDcOrderModel::find()->select('user_dc_order.*,user.mobile as leader_mobile')
                                         ->leftJoin('user', '`user`.`id` = `user_dc_order`.`leader_id`')
                                         ->andFilterWhere(['user_dc_order.status' => $status])
                                         ->andFilterWhere(['user_dc_order.product_id' => $product_id])
                                         ->andFilterWhere(['like','user_dc_order.order_sign',$order_sign])
                                         ->andFilterWhere(['like','user_dc_order.create_time',$date])
                                         ->andFilterWhere(['>=','user_dc_order.return_money',$start_money])
                                         ->andFilterWhere(['<=','user_dc_order.return_money',$end_money])
                                         ->orderBy('user_dc_order.id desc');

        $data = Helper::usePage($query);
        $sum = 0;
        foreach ($data['items'] as $k=>&$v){
            $v['user_mobile'] = UserModel::find()->select('mobile')->where(['id' => $v['user_id']])->scalar() ?: '';
            $sum += $v['return_money'];
        }

        VarTmp::$extra = ['total_money' => $sum];
        return $data;
    }

    /*
     * 将一些审核中的状态修改为已经下款   或者是审核未通过
     * */
    public function actionAudit(){
        $status = $this->post('status');
        $id = $this->post('id');
        $model = UserDcOrderModel::findOne($id);
        $bridge = new Bridge();
        $award = $bridge->fetchFirstAward($model->product_id);
        if(isset($award['message'])) return Common::response(0, $award['message']);
        if($status == Bridge::AUDIT_NOT_PASS){
            if($model->status != Bridge::AUDITING) return Common::response(0, '非法操作');
            $model->status = Bridge::AUDIT_NOT_PASS;
            if($model->save()) return Common::response(1, '操作成功');
            return Common::response(0, '操作失败', $model->getErrors());
        }

        if($status == Bridge::AUDIT_PASS){  // 通过审核 钱到账
            if($model->status != Bridge::AUDITING) return Common::response(0, '非法操作');
            $transaction = UserDcOrderModel::getDb()->beginTransaction();
            try {
                $user = UserModel::find()->where(['id' => $model->leader_id])->one();
                $user->total_income += $model->return_money;
                $user->available_money += $model->return_money;
                $user->save();

                $income_model = new UserIncomeModel();
                $income_model->user_id = $model->leader_id;
                $income_model->type = Bridge::INCOME_TYPE_DEFAULT;
                $income_model->order_sign = $model->order_sign;  // 这个和导表生成的用户订单的是同一个订单号
                $income_model->income = $model->return_money;
                $income_model->create_time = date('Y-m-d H:i:s'); // 这个和导表生成的用户订单的是同一个时间信息
                $income_model->save();

                // 判断用户是否是首次下款,如果是则返回相应的佣金
                $first_buy = UserDcOrderModel::find()->where(['leader_id' => $model->leader_id, 'user_id' => $model->user_id, 'status'=>Bridge::AUDIT_PASS])->one(); // 查询是否是第一次下款
                if(!$first_buy){ // 如果没有下过款,说明这是第一次下款
                    $income_model = new UserIncomeModel();
                    $income_model->user_id = $model->leader_id;
                    $income_model->type = Bridge::INCOME_TYPE_FIRST_AWARD;
                    $income_model->order_sign = Common::genOrderSign();  // 这个和导表生成的用户订单的是同一个订单号
                    $income_model->income = $award;
                    $income_model->create_time = date('Y-m-d H:i:s'); // 这个和导表生成的用户订单的是同一个时间信息
                    $income_model->save();

                    $user->available_money += $award;
                    $user->total_income += $award;
                    $user->save();
                    Helper::pushMessage(5,['accept_user_id' => $model->leader_id,'content' => Config::INCOMEMSG]);
                }
                $model->status = Bridge::AUDIT_PASS;
                $model->pass_audit = Bridge::PASS_AUDIT; // 审核状态由审核中变为已下款
                $model->save();
                $transaction->commit();
                Helper::pushMessage(5,['accept_user_id' => $model->leader_id,'content' => Config::ORDERMSG]);
                return Common::response(1, '操作成功');
            } catch(\Exception $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            } catch(\Throwable $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            }
        }
        return Common::response(0, '非法状态值');
    }

    /*
     * 下载模板excel
     * */
    public function actionDownloadTemplate(){
        $template_dir = dirname(__DIR__) . '/template/order_template.xlsx';
        Common::downfile($template_dir);
    }

    /*
     * 导入excel
     * */
    public function actionExportExcel(){
        $order_template = $_FILES['order_excel'];
        if(empty($order_template)) return Common::response(0, '请上传文件');
        $file_path = $order_template['tmp_name'];
        $spreadsheet  = IOFactory::load($file_path);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数

        $product_id = $product_id = $sheet->getCell( 'A4')->getValue();
        if(empty($product_id)) return Common::response(0, 'A4 请正确填写犀金后台产品ID');

        // 验证产品
        $bridge = new Bridge();
        $product_info = $bridge->fetchProductInfo($product_id);
        if(isset($product_info['message'])) return Common::response(0, $product_info['message']);
        $type = (int)$product_info['settlement_type']; // 类型

        $list_arr = [];
        for($i = 4; $i <= $highestRow; $i++){
            $here_product_id = $sheet->getCell( 'A' . $i)->getValue();  // 产品id
            $mobile = $sheet->getCell( 'B' . $i)->getValue();  // 上家手机号
            $pay_money = $sheet->getCell( 'C' . $i)->getValue();  // 下款额度
            $status = $sheet->getCell( 'D' . $i)->getValue();  // 下款额度

            /*
             * 验证 start
             * */
            if(empty($mobile) && empty($pay_money) && empty($status)) continue;
            if($here_product_id != $product_id) return Common::response(0, "您填写的产品id有不一致的情况,请检查");

            // 检查手机号的合法性
            $is_mobile = Common::verifiyMobile($mobile);
            if(!$is_mobile) return Common::response(0, "A{$i} 手机号格式填写有误,请检查");

            // 如果是按照固定金额返佣,那么用户导入表格的时候不需要指定下款额度
            if($type === Bridge::RETURN_AWARD){
                $pay_money = 0;
            }else{
                // 判断金额的合法性
                $is_legal = preg_match('/^\d+$/',$pay_money);
                if(!$is_legal || $pay_money <= 0) return Common::response(0, "C{$i} 金额填写有误,请检查");
            }

            // 判断订单状态的合法性
            if($status != Bridge::AUDIT_NOT_PASS && $status != Bridge::AUDITING && $status != Bridge::AUDIT_PASS){
                return Common::response(0, "D{$i} 订单状态填写有误,请检查");
            }

            /*
             * 验证 end
             * */
            $user_id = UserModel::find()->select('id')->where(['mobile' => $mobile])->scalar() ?: '';  // 下家id   需要找出上家并进行返现

            if(!empty($user_id)){
                // 如果下家有审核中或者已在这款产品中下款的记录,那么跳过该记录
                $has_ever = UserDcOrderModel::find()->where(['user_id' => $user_id, 'product_id' => $product_id])->andWhere(['in','status',[Bridge::AUDITING,Bridge::AUDIT_PASS]])->one();
                if($has_ever){
                    continue;
                }

                $leader_info = UserRelationship::find()->select('user.id as leader_id,user.user_level')->leftJoin('user', '`user`.`id` = `user_relationship`.`leader_id`')->where(['user_relationship.user_id' => $user_id])->asArray()->one();
                if(empty($leader_info)) continue;

                // 犀金会员默认白银级提升为黄金级别
                $level = $leader_info['user_level'];
                // $is_vip = Common::checkVip($leader_info['leader_id']);
                // if($is_vip === true && $level == Bridge::SILVER) $level = Bridge::GOLD;

                // 按照比例来返现
                if($type === Bridge::RETURN_RATE){
                    switch ($level){
                        case Bridge::SILVER:
                            preg_match('@^(.*)%$@',$product_info['silver_rate'],$matches);
                            if(empty($matches)) continue;
                            $silver_rate = $matches[1];
                            $return_money = ($pay_money * $silver_rate)/100;
                            break;
                        case Bridge::GOLD:
                            preg_match('@^(.*)%$@',$product_info['gold_rate'],$matches);
                            if(empty($matches)) continue;
                            $gold_rate = $matches[1];
                            $return_money = ($pay_money * $gold_rate)/100;
                            break;
                        case Bridge::DIAMOND:
                            preg_match('@^(.*)%$@',$product_info['diamond_rate'],$matches);
                            if(empty($matches)) continue;
                            $diamond_rate = $matches[1];
                            $return_money = ($pay_money * $diamond_rate)/100;
                            break;
                    }
                }
                elseif ($type === Bridge::RETURN_AWARD){ // 按照固定的金额来返现
                    switch ($level){
                        case Bridge::SILVER:
                            $return_money = $product_info['silver_award'];
                            break;
                        case Bridge::GOLD:
                            $return_money = $product_info['gold_award'];
                            break;
                        case Bridge::DIAMOND:
                            $return_money = $product_info['diamond_award'];
                            break;
                    }
                }

                if(empty($return_money)) return Common::response(0, '产品可能没有填写返佣比例或返佣金额,或填写不完整,请检查产品');

                $list['product_id'] = $product_id;
                $list['product_name'] = $product_info['name'];
                $list['product_image'] = $product_info['image'];
                $list['product_name'] = $product_info['name'];
                $list['leader_id'] = $leader_info['leader_id'];
                $list['user_id'] = $user_id;
                $list['pay_money'] = $pay_money;
                $list['return_money'] = $return_money;
                $list['status'] = $status;
                $list['order_sign'] = Common::genOrderSign();
                $list['create_time'] = date('Y-m-d H:i:s');
                $list['new_time'] = date('Y-m-d H:i:s', strtotime('+1 day'));
                $list_arr[] = $list;
            }
        }
        return $list_arr;
    }


    public function actionSaveExcel(){
        $bridge = new Bridge();
        $data = $this->post();
        foreach ($data as $k=>$v){
            $award = $bridge->fetchFirstAward($v['product_id']);  // 返现的金额
            if(isset($award['message'])) return Common::response(0, $award['message']);
            $model = new UserDcOrderModel();
            $transaction = UserDcOrderModel::getDb()->beginTransaction();
            try {
                if($v['status'] == Bridge::AUDIT_PASS){  // 只有已经下款的 才可以打钱给上家
                    $user = UserModel::findOne($v['leader_id']);
                    $user->available_money += $v['return_money'];
                    $user->total_income += $v['return_money'];
                    $user->save();

                    $income_model = new UserIncomeModel();
                    $income_model->user_id = $v['leader_id'];
                    $income_model->type = Bridge::INCOME_TYPE_DEFAULT;
                    $income_model->order_sign = $v['order_sign'];  // 这个和导表生成的用户订单的是同一个订单号
                    $income_model->income = $v['return_money'];
                    $income_model->create_time = $v['create_time']; // 这个和导表生成的用户订单的是同一个时间信息
                    $income_model->save();
                    Helper::pushMessage(5,['accept_user_id' => $v['leader_id'], 'content' => Config::INCOMEMSG]);


                    // 判断用户是否是首次下款,如果是则返回相应的佣金
                    $first_buy = UserDcOrderModel::find()->where(['leader_id' => $v['leader_id'], 'user_id' => $v['user_id'], 'status'=>Bridge::AUDIT_PASS])->one(); // 查询是否是第一次下款
                    if(!$first_buy){ // 如果没有下过款,说明这是第一次下款
                        $income_model = new UserIncomeModel();
                        $income_model->user_id = $v['leader_id'];
                        $income_model->type = Bridge::INCOME_TYPE_FIRST_AWARD;
                        $income_model->order_sign = Common::genOrderSign();  // 这个和导表生成的用户订单的是同一个订单号
                        $income_model->income = $award;
                        $income_model->create_time = date('Y-m-d H:i:s'); // 这个和导表生成的用户订单的是同一个时间信息
                        $income_model->save();

                        $user->available_money += $award;
                        $user->total_income += $award;
                        $user->save();
                        Helper::pushMessage(5,['accept_user_id' => $v['leader_id'],'content' => Config::INCOMEMSG]);
                    }
                }
                $model->setAttributes($v);
                $model->save();
                Helper::pushMessage(5,['accept_user_id' => $v['leader_id'],'content' => Config::ORDERMSG]);
                $transaction->commit();
            } catch(\Exception $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            } catch(\Throwable $e) {
                $transaction->rollBack();
                return Common::response(0, '操作失败', $e->getMessage());
            }
        }

        return Common::response(1, '操作成功!');
    }


}
