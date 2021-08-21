<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\RadioCommentModel;

/**
 * RadioCommentModelSearch represents the model behind the search form of `common\models\RadioCommentModel`.
 */
class RadioCommentModelSearch extends RadioCommentModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'radio_id', 'user_id', 'pid', 'reply_pid', 'like_num', 'child_count', 'del', 'is_private'], 'integer'],
            [['content', 'create_time', 'update_time'], 'safe'],
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
        $query = RadioCommentModel::find();

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
            'radio_id' => $this->radio_id,
            'user_id' => $this->user_id,
            'pid' => $this->pid,
            'reply_pid' => $this->reply_pid,
            'like_num' => $this->like_num,
            'child_count' => $this->child_count,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'del' => $this->del,
            'is_private' => $this->is_private,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
