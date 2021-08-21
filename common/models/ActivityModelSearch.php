<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ActivityModel;

/**
 * ActivityModelSearch represents the model behind the search form of `common\models\ActivityModel`.
 */
class ActivityModelSearch extends ActivityModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'is_free'], 'integer'],
            [['title', 'location', 'preview_image', 'activity_time', 'activity_time_end', 'registration_deadline', 'content'], 'safe'],
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
        $query = ActivityModel::find();

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
            'price' => $this->price,
            'user_id' => $this->user_id,
            'activity_time' => $this->activity_time,
            'activity_time_end' => $this->activity_time_end,
            'registration_deadline' => $this->registration_deadline,
            'is_free' => $this->is_free,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'location', $this->location])
            ->andFilterWhere(['like', 'preview_image', $this->preview_image])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
