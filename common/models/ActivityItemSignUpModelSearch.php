<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ActivityItemSignUpModel;

/**
 * ActivityItemSignUpModelSearch represents the model behind the search form of `common\models\ActivityItemSignUpModel`.
 */
class ActivityItemSignUpModelSearch extends ActivityItemSignUpModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_id', 'activity_item_id', 'user_id'], 'integer'],
            [['user_name', 'mobile', 'create_time'], 'safe'],
            [['price'], 'number'],
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
        $query = ActivityItemSignUpModel::find();

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
            'activity_id' => $this->activity_id,
            'activity_item_id' => $this->activity_item_id,
            'user_id' => $this->user_id,
            'price' => $this->price,
            'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'user_name', $this->user_name])
            ->andFilterWhere(['like', 'mobile', $this->mobile]);

        return $dataProvider;
    }
}
