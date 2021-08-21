<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserModel;

/**
 * UserModelSearch represents the model behind the search form of `common\models\UserModel`.
 */
class UserModelSearch extends UserModel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'gender', 'city_id', 'register_ip', 'last_login_ip', 'status', 'os', 'focus_num', 'focused_num', 'login_time', 'active', 'conceal_hide', 'huanxin_created', 'huanxin_modified', 'huanxin_activated'], 'integer'],
            [['mobile', 'password', 'access_token', 'nick_name', 'wechat_token', 'qq_token', 'avatar_image', 'register_time', 'last_login_time', 'device_id', 'active_time', 'cause_of_violation', 'huanxin_uuid', 'huanxin_type', 'huanxin_username', 'huanxin_password', 'huanxin_nickname'], 'safe'],
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
        $query = UserModel::find();

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
            'gender' => $this->gender,
            'city_id' => $this->city_id,
            'register_time' => $this->register_time,
            'last_login_time' => $this->last_login_time,
            'register_ip' => $this->register_ip,
            'last_login_ip' => $this->last_login_ip,
            'status' => $this->status,
            'os' => $this->os,
            'focus_num' => $this->focus_num,
            'focused_num' => $this->focused_num,
            'login_time' => $this->login_time,
            'active' => $this->active,
            'active_time' => $this->active_time,
            'conceal_hide' => $this->conceal_hide,
            'huanxin_created' => $this->huanxin_created,
            'huanxin_modified' => $this->huanxin_modified,
            'huanxin_activated' => $this->huanxin_activated,
        ]);

        $query->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'access_token', $this->access_token])
            ->andFilterWhere(['like', 'nick_name', $this->nick_name])
            ->andFilterWhere(['like', 'wechat_token', $this->wechat_token])
            ->andFilterWhere(['like', 'qq_token', $this->qq_token])
            ->andFilterWhere(['like', 'avatar_image', $this->avatar_image])
            ->andFilterWhere(['like', 'device_id', $this->device_id])
            ->andFilterWhere(['like', 'cause_of_violation', $this->cause_of_violation])
            ->andFilterWhere(['like', 'huanxin_uuid', $this->huanxin_uuid])
            ->andFilterWhere(['like', 'huanxin_type', $this->huanxin_type])
            ->andFilterWhere(['like', 'huanxin_username', $this->huanxin_username])
            ->andFilterWhere(['like', 'huanxin_password', $this->huanxin_password])
            ->andFilterWhere(['like', 'huanxin_nickname', $this->huanxin_nickname]);

        return $dataProvider;
    }
}
