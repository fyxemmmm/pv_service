<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CommentSearch represents the model behind the search form of `api\models\ArticleComment`.
 */
class CommentSearch extends ArticleComment
{
    public static $userId;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'article_id', 'user_id', 'pid', 'reply_pid', 'like_num', 'child_count', 'del', 'is_private'], 'integer'],
            [['content', 'create_time', 'update_time'], 'safe'],
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
        $param = $params['CommentSearch'];
        $sort = $param['sort'] ?? '';
        $article_id = $param['article_id'] ?? '';
        if($sort == '-child_count' && $article_id){
            $count = ArticleComment::find()->where(['article_id' => $article_id])->count();
            if($count < 10){ // 10条评论以下 隐藏热门评论 否则进行展示并按评论数最多的进行排序
                $query = ArticleComment::find()->where(['id' => 0]);
            }else{
                $query = ArticleComment::find()->where(['del' => 0, 'is_private' => 0])->orWhere(['del' => 0, 'user_id' => self::$userId]);
            }
        }else{
            $query = ArticleComment::find()->where(['del' => 0, 'is_private' => 0])->orWhere(['del' => 0, 'user_id' => self::$userId]);
        }

//        $query = ArticleComment::find();

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
            'article_id' => $this->article_id,
            'user_id' => $this->user_id,
            'pid' => $this->pid,
            'reply_pid' => $this->reply_pid,
            'like' => $this->like_num,
            'child_count' => $this->child_count,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'del' => $this->del,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
