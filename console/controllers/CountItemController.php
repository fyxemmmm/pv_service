<?php
namespace console\controllers;
use common\models\Common;
use yii\console\Controller;
use common\models\ClickStatisticsModel;


class CountItemController extends Controller
{
    public $param;
    protected $start_date;
    protected $end_date;

    public function options($actionID)
    {
        return ['param'];
    }

    public function optionAliases()
    {
        return ['param' => 'param'];
    }


    public function actionIndex()
    {
        $day = $this->param ?? 1;
        $this->start_date = Common::generateDatetime('Y-m-d 00:00:00', strtotime("-$day days"));
        $day --;
        $this->end_date = Common::generateDatetime('Y-m-d 00:00:00', strtotime("-$day days"));
        $db = \Yii::$app->db;
        $sql = "SELECT sum(IF(controller='article',1,0)) as article,sum(IF(controller='radio',1,0)) as radio,sum(IF(controller='business',1,0)) as business,sum(IF(controller='make-money-group',1,0)) as make_money_group  FROM service_api_log WHERE app_name = 'app-api' and (controller = 'article' or controller = 'radio' or controller='business' or controller='make-money-group') and action = 'index' and create_time BETWEEN '{$this->start_date}' and '{$this->end_date}' GROUP BY ip";
        $list = $db->createCommand($sql)->queryAll();
        $this->countAll($list);
        $this->countDistinct($list);
    }

    protected function countAll($list){
        $article_count = array_sum(array_column($list, 'article'));
        $radio_count = array_sum(array_column($list, 'radio'));
        $business_count = array_sum(array_column($list, 'business'));
        $make_money_group_count = array_sum(array_column($list, 'make_money_group'));
        $attributes = [
            'article' => $article_count,
            'radio' => $radio_count,
            'business' => $business_count,
            'make_money_group' => $make_money_group_count,
            'time' => $this->start_date,
            'type' => 0
        ];
        $is_exist = ClickStatisticsModel::find()->where(['time' => $this->start_date, 'type'=>0])->one();
        if(!$is_exist){
            $model = new ClickStatisticsModel();
            $model->setAttributes($attributes);
            $model->save();
        }
    }

    protected function countDistinct($list){
        $article_count = 0;
        $radio_count = 0;
        $business_count = 0;
        $make_money_group_count = 0;
        foreach ($list as $k=>$v){
            if($v['article'] != 0) $article_count ++;
            if($v['radio'] != 0) $radio_count ++;
            if($v['business'] != 0) $business_count ++;
            if($v['make_money_group'] != 0) $make_money_group_count ++;
        }
        $attributes = [
            'article' => $article_count,
            'radio' => $radio_count,
            'business' => $business_count,
            'make_money_group' => $make_money_group_count,
            'time' => $this->start_date,
            'type' => 1
        ];
        $is_exist = ClickStatisticsModel::find()->where(['time' => $this->start_date,'type'=>1])->one();
        if(!$is_exist){
            $model = new ClickStatisticsModel();
            $model->setAttributes($attributes);
            $model->save();
        }
    }


}