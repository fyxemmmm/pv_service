<?php

namespace service\controllers;
use yii\data\ActiveDataProvider;
use common\VarTmp;
use common\models\ClickStatisticsModel;

class ClickStatisticsController extends CommonController
{
    public $modelClass = 'common\models\ClickStatisticsModel';

    public function actionGetData()
    {
        $time = $this->get('time') ?? '';
        $type = $this->get('type') ?? 0;  // 默认不去重

        if(mb_strlen($time) == 10 || mb_strlen($time) == 0){
            $query = (new \yii\db\Query())
                ->from('click_statistics')
                ->where("time like :time")
                ->orderBy('time desc')
                ->addParams([':time'=> "$time%"]);

        }elseif (mb_strlen($time) == 7){
            $query = (new \yii\db\Query())
                ->from('click_statistics')
                ->select('sum(article) as article,sum(radio) as radio,sum(business) as business,sum(make_money_group) as make_money_group')
                ->where("time like :time")
                ->addParams([':time'=> "$time%"])
                ->groupBy(["(SUBSTRING_INDEX(time,'-',2))"]);
        }

        $query->andWhere(['type' => $type]); // 类型 0查没有去重过的  1查去重过的数据

        // 全加上
        $new_query = ClickStatisticsModel::find()->select('sum(article) article,sum(radio) radio,sum(business) business,sum(make_money_group) make_money_group')
            ->where(['type' => $type])
            ->asArray()->all();
        VarTmp::$extra = $new_query;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }

}
