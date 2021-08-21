<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ArticleModel;

/**
 * ArticleModelSearch represents the model behind the search form of `common\models\ArticleModel`.
 */
class ArticleModelSearch extends ArticleModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'premium', 'comment_num', 'like_num', 'creater', 'admin_id', 'status'], 'integer'],
            [['origin', 'author', 'title', 'desc', 'profile', 'content', 'preview_image', 'create_time', 'update_time'], 'safe'],
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
        $query = ArticleModel::find();

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
            'premium' => $this->premium,
            'comment_num' => $this->comment_num,
            'like_num' => $this->like_num,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'creater' => $this->creater,
            'admin_id' => $this->admin_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'origin', $this->origin])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'profile', $this->profile])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'preview_image', $this->preview_image]);

        return $dataProvider;
    }
}
