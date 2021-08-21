<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\Radio;
use common\models\RadioParticipantModel;
use Yii;

/**
 * RadioSearch represents the model behind the search form of `api\models\Radio`.
 */
class RadioSearch extends Radio
{
    public static $hidden = false;  // 列表默认会隐藏最新一条作为banner图的开关

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
        if(isset($params['RadioSearch']['my'])){ // ta的电台
            $user_id = $params['RadioSearch']['my'];
            $radio_ids = RadioParticipantModel::find()->select('radio_id as id')->where(['user_id' => $user_id])->asArray()->all();
            $ids = array_column($radio_ids, 'id');
            $query = Radio::find()->where(['status'=>1])->andWhere(['in','id',$ids])->orderBy('id desc');
        }else{
            if(self::$hidden){
                // 首页列表
                $max_id = parent::find()->select('id')->where(['status' => 1])->orderBy('id desc')->scalar() ? : 0;
                $query = Radio::find()->where(['status'=>1])->andWhere(['<','id',$max_id])->orderBy('id desc');
            }else{ // 电台详情
                // 状态为1则显示 详情页
                $query = Radio::find()->where(['status'=>1])->orderBy('id desc');
            }
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
