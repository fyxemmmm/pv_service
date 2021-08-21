<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\RadioModel;

/**
 * RadioModelSearch represents the model behind the search form of `common\models\RadioModel`.
 */
class RadioModelSearch extends RadioModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'comment_num', 'like_num', 'admin_id', 'status'], 'integer'],
            [['radio_url', 'origin', 'title', 'desc', 'content', 'preview_image', 'create_time', 'update_time'], 'safe'],
            [['size'], 'number'],
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
        $query = RadioModel::find();

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
            'size' => $this->size,
            'type' => $this->type,
            'comment_num' => $this->comment_num,
            'like_num' => $this->like_num,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'admin_id' => $this->admin_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'radio_url', $this->radio_url])
            ->andFilterWhere(['like', 'origin', $this->origin])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'preview_image', $this->preview_image]);

        return $dataProvider;
    }
}
