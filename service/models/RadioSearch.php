<?php

namespace service\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use service\models\Radio;
use common\models\RadioLabelModel;

/**
 * RadioSearch represents the model behind the search form of `service\models\Radio`.
 */
class RadioSearch extends Radio
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
        $query = Radio::find()->orderBy('id desc');

        $label_id = $params['RadioSearch']['label_id'] ?? '';
        if ($label_id) {
            $radio_id_list = RadioLabelModel::find()
                ->select('radio_id')
                ->where(['label_id' => $label_id])
                ->asArray()
                ->all();

            $radio_id_in = $radio_id_list ? array_column($radio_id_list, 'radio_id') : [];
            $query->andWhere(['in', 'id', $radio_id_in]);
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
