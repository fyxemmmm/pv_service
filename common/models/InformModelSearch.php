<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\InformModel;

/**
 * InformModelSearch represents the model behind the search form of `common\models\InformModel`.
 */
class InformModelSearch extends InformModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dc_id', 'radio_id', 'type'], 'integer'],
            [['url', 'title', 'icon', 'desc', 'create_time'], 'safe'],
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
        $query = InformModel::find();

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
            'dc_id' => $this->dc_id,
            'radio_id' => $this->radio_id,
            'type' => $this->type,
            'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'desc', $this->desc]);

        return $dataProvider;
    }
}
