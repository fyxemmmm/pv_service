<?php

namespace service\models;

use common\models\ArticleLabelModel;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use service\models\Article;

class ArticleSearch extends Article
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'creater', 'status'], 'integer'],
            [['origin', 'author', 'title', 'desc', 'profile', 'content', 'create_time', 'update_time'], 'safe'],
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
        $query = Article::find();

        $label_id = $params['ArticleSearch']['label_id'] ?? '';
        if ($label_id) {
            $article_id_list = ArticleLabelModel::find()
                ->select('article_id')
                ->where(['label_id' => $label_id])
                ->asArray()
                ->all();

            $article_id_in = $article_id_list ? array_column($article_id_list, 'article_id') : [];
            $query->andWhere(['in', 'id', $article_id_in]);
        }

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
            'type' => $this->type,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'creater' => $this->creater,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'origin', $this->origin])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'profile', $this->profile])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
