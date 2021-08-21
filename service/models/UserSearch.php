<?php

namespace service\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use service\models\User;

/**
 * UserSearch represents the model behind the search form of `service\models\User`.
 */
class UserSearch extends User
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'register_ip', 'last_login_ip', 'status', 'os', 'login_time', 'active', 'conceal_hide'], 'integer'],
            [['mobile', 'password', 'access_token', 'nick_name', 'wechat_token', 'qq_token', 'avatar_image', 'register_time', 'last_login_time', 'device_id', 'active_time'], 'safe'],
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
//        var_dump($params);exit;
        $type = $params['UserSearch']['type'] ?? '';
        if($type){
            $query = User::find()->where('access_token = id');
        }else{
            $query = User::find();
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
            'register_time' => $this->register_time,
            'last_login_time' => $this->last_login_time,
            'register_ip' => $this->register_ip,
            'last_login_ip' => $this->last_login_ip,
            'status' => $this->status,
            'os' => $this->os,
            'login_time' => $this->login_time,
            'active' => $this->active,
            'active_time' => $this->active_time,
            'conceal_hide' => $this->conceal_hide,
        ]);

        $query->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'nick_name', $this->nick_name])
            ->andFilterWhere(['like', 'wechat_token', $this->wechat_token])
            ->andFilterWhere(['like', 'qq_token', $this->qq_token])
            ->andFilterWhere(['like', 'avatar_image', $this->avatar_image])
            ->andFilterWhere(['like', 'device_id', $this->device_id]);

        return $dataProvider;
    }
}
