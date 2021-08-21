<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\JmbModel;

/**
 * JmbModelSearch represents the model behind the search form of `common\models\JmbModel`.
 */
class JmbModelSearch extends JmbModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'jmb_category_id', 'direct_store_num', 'join_store_num', 'apply_num', 'is_hot_join', 'is_hot_recommend', 'status'], 'integer'],
            [['name', 'brand_name', 'desc', 'main_project', 'register_time', 'location', 'est_init_investment', 'est_customer_unit_price', 'est_customer_daily_flow', 'est_mothly_sale', 'est_gross_profit', 'est_payback_period', 'inital_fee', 'join_fee', 'deposit_fee', 'device_fee', 'other_fee', 'image_url', 'info', 'create_time', 'update_time'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = JmbModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'jmb_category_id' => $this->jmb_category_id,
            'direct_store_num' => $this->direct_store_num,
            'join_store_num' => $this->join_store_num,
            'apply_num' => $this->apply_num,
            'is_hot_join' => $this->is_hot_join,
            'is_hot_recommend' => $this->is_hot_recommend,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'brand_name', $this->brand_name])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'main_project', $this->main_project])
            ->andFilterWhere(['like', 'register_time', $this->register_time])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'est_init_investment', $this->est_init_investment])
            ->andFilterWhere(['like', 'est_customer_unit_price', $this->est_customer_unit_price])
            ->andFilterWhere(['like', 'est_customer_daily_flow', $this->est_customer_daily_flow])
            ->andFilterWhere(['like', 'est_mothly_sale', $this->est_mothly_sale])
            ->andFilterWhere(['like', 'est_gross_profit', $this->est_gross_profit])
            ->andFilterWhere(['like', 'est_payback_period', $this->est_payback_period])
            ->andFilterWhere(['like', 'inital_fee', $this->inital_fee])
            ->andFilterWhere(['like', 'join_fee', $this->join_fee])
            ->andFilterWhere(['like', 'deposit_fee', $this->deposit_fee])
            ->andFilterWhere(['like', 'device_fee', $this->device_fee])
            ->andFilterWhere(['like', 'other_fee', $this->other_fee])
            ->andFilterWhere(['like', 'image_url', $this->image_url])
            ->andFilterWhere(['like', 'info', $this->info]);

        return $dataProvider;
    }
}
