<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\ArticleBrowse;

/**
 * ArticleBrowseModelSearch represents the model behind the search form of `common\models\ArticleBrowseModel`.
 */
class ArticleBrowseSearch extends ArticleBrowse
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ip', 'article_id', 'user_id'], 'integer'],
            [['create_time'], 'safe'],
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
        $query = ArticleBrowse::find();

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
            'ip' => $this->ip,
            'article_id' => $this->article_id,
            'user_id' => $this->user_id,
            'create_time' => $this->create_time,
        ]);

        return $dataProvider;
    }
}
