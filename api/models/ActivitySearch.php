<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ActivityItemSignUpModel;
use common\models\ActivityItemModel;
use common\models\ActivityModel;

/**
 * ActivitySearch represents the model behind the search form of `api\models\Activity`.
 */
class ActivitySearch extends Activity
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
        $user_id = \Yii::$app->params['__web']['user_id'];

        $query = Activity::find();
//        var_dump($user_id);exit;
        if(isset($params['ActivitySearch']['my_activity'])){
            $data = ActivityItemSignUp::find()->select('activity_id')->where(['user_id' => $user_id])->asArray()->all();
            $activity_id_arr = array_column($data, 'activity_id');
            $query->andWhere(['in','id',$activity_id_arr]);
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
