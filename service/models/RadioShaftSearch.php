<?php

namespace service\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use service\models\RadioShaft;

/**
 * RadioShaftSearch represents the model behind the search form of `service\models\RadioShaft`.
 */
class RadioShaftSearch extends RadioShaft
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'radio_id', 'at'], 'integer'],
            [['title', 'image', 'content', 'quote_href'], 'safe'],
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
        $query = RadioShaft::find()->orderBy('at asc');
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
            'at' => $this->at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'quote_href', $this->quote_href]);

        return $dataProvider;
    }
}
