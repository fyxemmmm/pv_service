<?php

namespace api\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use api\models\RadioComment;
use Yii;

/**
 * RadioCommentSearch represents the model behind the search form of `api\models\RadioComment`.
 */
class RadioCommentSearch extends RadioComment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'radio_id', 'user_id', 'pid', 'reply_pid', 'like_num', 'child_count', 'del', 'is_private'], 'integer'],
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
        /*
         * radio-comments?radio_id=1&type=no_child&per-page=3   列表内页嵌入 不需要child的列表
         * radio-comments?radio_id=1&type=new&pid=0 最新列表 时间排序
         * radio-comments?radio_id=1&type=hot&pid=0 热门列表  10条以下不进行展示
         * */
        $user_id = Yii::$app->params['__web']['user_id'] ?? '';
        $type = $params['RadioCommentSearch']['type'] ?? '';
        RadioComment::$type = $type;
        $radio_id = $params['RadioCommentSearch']['radio_id'] ?? '';

        if($type === 'hot'){  // 最热评论 如果没达到10条评论,那么隐藏列表
            $count = RadioComment::find()->where(['radio_id' => $radio_id])->count();
            if($count < 10){ //  $count < 10
                $query = RadioComment::find()->where(['id' => 0]); // 隐藏
            }else{
                $query = RadioComment::find()->where(['del' => 0, 'is_private' => 0, 'pid' => 0])->orWhere(['del' => 0, 'user_id' => $user_id])->orderBy('child_count desc,id desc');
            }
        }else if($type === 'new'){  // 最新评论
            $query = RadioComment::find()->where(['del' => 0, 'is_private' => 0, 'pid' => 0])->orWhere(['del' => 0, 'user_id' => $user_id])->orderBy('id desc');
        }else if($type === 'no_child'){
            // 此时此刻 type = no_child 的热门评论 列表内页详情嵌入
            $query = RadioComment::find()->where(['del' => 0, 'is_private' => 0, 'pid' => 0])->orWhere(['del' => 0, 'user_id' => $user_id])->orderBy('child_count desc,id desc');
        }else{
            $query = RadioComment::find()->where(['del' => 0, 'is_private' => 0])->orWhere(['del' => 0, 'user_id' => $user_id]);
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
            'radio_id' => $this->radio_id,
            'user_id' => $this->user_id,
            'pid' => $this->pid,
            'reply_pid' => $this->reply_pid,
            'like_num' => $this->like_num,
            'child_count' => $this->child_count,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'del' => $this->del,
            'is_private' => $this->is_private,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
