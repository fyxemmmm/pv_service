<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\WorkModel;

/**
 * WorkModelSearch represents the model behind the search form of `common\models\WorkModel`.
 */
class WorkModelSearch extends WorkModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status', 'is_recommend', 'is_del'], 'integer'],
            [['company_name', 'position_name', 'position_type', 'work_address', 'salary_range', 'experience_requir', 'minimum_education', 'description', 'create_time', 'update_time'], 'safe'],
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
        $query = WorkModel::find();

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
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_recommend' => $this->is_recommend,
            'is_del' => $this->is_del,
        ]);

        $query->andFilterWhere(['like', 'company_name', $this->company_name])
            ->andFilterWhere(['like', 'position_name', $this->position_name])
            ->andFilterWhere(['like', 'position_type', $this->position_type])
            ->andFilterWhere(['like', 'work_address', $this->work_address])
            ->andFilterWhere(['like', 'salary_range', $this->salary_range])
            ->andFilterWhere(['like', 'experience_requir', $this->experience_requir])
            ->andFilterWhere(['like', 'minimum_education', $this->minimum_education])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
