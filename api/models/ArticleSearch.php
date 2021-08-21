<?php

namespace api\models;

use common\models\Common;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SwitchModel;
use Yii;

/**
 * ArticleSearch represents the model behind the search form of `api\models\Article`.
 */
class ArticleSearch extends Article
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'premium', 'comment_num', 'like_num', 'creater', 'admin_id', 'status'], 'integer'],
            [['origin', 'author', 'title', 'desc', 'profile', 'content', 'preview_image', 'create_time', 'update_time'], 'safe'],
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
        // 1只安卓开启贷超文章 2只苹果开启贷超 3全开贷超 关闭文章 4 展示文章
        $switch_audit = $switch_model = SwitchModel::find()->select('status')->where(['type' => 1, 'id' => 1])->scalar() ?: 4;

        $headers = Yii::$app->request->headers;
        $os = $headers->get('os');

        $offset = $params['ArticleSearch']['lpop'] ?? '';
        // 状态为1的才显示
        $query = Article::find()->where(['status'=>1])->orderBy('id desc');
        if($offset){
            $max_id = Article::find()->select('id')->orderBy('id desc')->scalar();
            $query = $query->andWhere(['<=', 'id',$max_id - $offset]);
        }
        if($switch_audit == 3){  // 全开贷超
            $query = $query->andWhere(['is_dc' => 1]); // 展示贷超信息
        }elseif ($switch_audit == 4){  // 全部文章
            $query = $query->andWhere(['is_dc' => 0]); // 展示全部文章
        } else if ($switch_audit == 1){
            if($os == 'xijin_android'){
                $query = $query->andWhere(['is_dc' => 1]);
            }else{
                $query = $query->andWhere(['is_dc' => 0]);
            }
        }else if ($switch_audit == 2){
            if($os == 'xijin_ios'){
                $query = $query->andWhere(['is_dc' => 1]);
            }else{
                $query = $query->andWhere(['is_dc' => 0]);
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
            'type' => $this->type,
            'premium' => $this->premium,
            'comment_num' => $this->comment_num,
            'like_num' => $this->like_num,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'creater' => $this->creater,
            'admin_id' => $this->admin_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'origin', $this->origin])
            ->andFilterWhere(['like', 'author', $this->author])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'desc', $this->desc])
            ->andFilterWhere(['like', 'profile', $this->profile])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'preview_image', $this->preview_image]);

        return $dataProvider;
    }
}
