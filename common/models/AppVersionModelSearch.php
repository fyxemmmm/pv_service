<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AppVersionModel;

/**
 * AppVersionModelSearch represents the model behind the search form of `common\models\AppVersionModel`.
 */
class AppVersionModelSearch extends AppVersionModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'os', 'status', 'auditing', 'force'], 'integer'],
            [['name', 'channel', 'version', 'app_url'], 'safe'],
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
        $query = AppVersionModel::find();

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
            'os' => $this->os,
            'status' => $this->status,
            'auditing' => $this->auditing,
            'force' => $this->force,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'channel', $this->channel])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'app_url', $this->app_url]);

        return $dataProvider;
    }
}
