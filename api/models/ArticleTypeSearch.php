<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\ArticleType;
use common\models\SwitchModel;

/**
 * ArticleTypeSearch represents the model behind the search form of `api\models\ArticleType`.
 */
class ArticleTypeSearch extends ArticleType
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['at_id', 'subscription_num', 'weight', 'is_del'], 'integer'],
            [['at_name', 'topic', 'topic_des', 'image'], 'safe'],
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
        if(isset($params['ArticleTypeSearch']['id'])){
            $params['ArticleTypeSearch']['at_id'] = $params['ArticleTypeSearch']['id'];
        }

        // pc端只需要4条专题且随机打乱
        if(isset($params['ArticleTypeSearch']['type']) && $params['ArticleTypeSearch']['type'] == 'pc') {
            $ids = parent::find()->select('at_id')->where(['<>','is_del',1])->asArray()->all();
            $ids = array_column($ids,'at_id');
            shuffle($ids);
            array_splice($ids,4);  // 随机取4个专题文章
            $query = ArticleType::find()->where(['is_del' => 0])->andWhere(['in','at_id',$ids]);
        }else{
            $query = ArticleType::find()->where(['is_del' => 0])->orderBy('weight desc');
        }

        $need_filter = $switch_model = SwitchModel::find()->where(['type' => 1, 'status' => 1])->one();
        if($need_filter){
            $query = $query->andWhere(['is_dc' => 0]);  // 筛选出非贷超的信息
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
            'at_id' => $this->at_id,
            'subscription_num' => $this->subscription_num,
            'weight' => $this->weight,
            'is_del' => $this->is_del,
        ]);


        $query->andFilterWhere(['like', 'at_name', $this->at_name])
            ->andFilterWhere(['like', 'topic', $this->topic])
            ->andFilterWhere(['like', 'topic_des', $this->topic_des])
            ->andFilterWhere(['like', 'image', $this->image]);

        return $dataProvider;
    }
}
