<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\InformDynamic;
use Yii;

/**
 * InformDynamicSearch represents the model behind the search form of `api\models\InformDynamic`.
 */
class InformDynamicSearch extends InformDynamic
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'inform_id'], 'integer'],
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
        $user_id = Yii::$app->params['__web']['user_id'];
        $query = InformDynamic::find()->where(['user_id' => $user_id]);

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
            'user_id' => $this->user_id,
            'inform_id' => $this->inform_id
        ]);

        return $dataProvider;
    }
}
