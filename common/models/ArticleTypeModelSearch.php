<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ArticleTypeModel;

/**
 * ArticleTypeModelSearch represents the model behind the search form of `common\models\ArticleTypeModel`.
 */
class ArticleTypeModelSearch extends ArticleTypeModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['at_id', 'subscription_num', 'weight', 'is_del'], 'integer'],
            [['at_name', 'topic', 'topic_des', 'image'], 'safe'],
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
        $query = ArticleTypeModel::find();

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
            'at_id' => $this->at_id,
            'subscription_num' => $this->subscription_num,
            'weight' => $this->weight,
            'is_del' => $this->is_del,
        ]);

        $query->andFilterWhere(['like', 'at_name', $this->at_name])
            ->andFilterWhere(['like', 'topic', $this->topic])
            ->andFilterWhere(['like', 'topic_des', $this->topic_des])
            ->andFilterWhere(['like', 'image', $this->image]);

        return $dataProvider;
    }
}
