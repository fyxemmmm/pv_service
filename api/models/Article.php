<?php

namespace api\models;

use common\models\LabelModel;
use common\models\ArticleModel;
use common\models\UserModel;
use common\models\ArticleLikeModel;
use common\models\ArticleCommentModel;
use common\models\ArticleCollectModel;
use common\models\ArticleCommentLikeModel;
use common\models\FocusModel;
use common\models\ArticleLabelModel;
use common\models\AppSettingModel;
use common\models\ArticleTypeModel;
use common\models\CollectionsModel;
use Yii;

class Article extends ArticleModel
{
    public static $like = 1;  // 点赞该文章
    public static $collect = 2;  // 收藏该文章
    public static $focus = 3;  // 关注作者
    public static $userID;

    public function fields()
    {
        $fields = parent::fields();

        unset($fields['content']);
        $fields['type_status'] = function (){
            return 1;
        };


        $fields['avatar_image'] = function (){
            $data = UserModel::find()->select('avatar_image')->where(['id' => $this->creater])->scalar();
            if($data) return $data;
            return '';
        };

        $fields['type_name'] = function (){
            if($this->type == 0) return '';
            $data = ArticleTypeModel::find()->select('at_name')->where(['at_id' => $this->type])->scalar();
            return $data ? : '';
        };

        $fields['author'] = function (){
            $data = UserModel::find()->select('nick_name')->where(['id' => $this->creater])->scalar();
            if($data) return $data;
            return '';
        };

        $fields['preview_content'] = function(){
            $res = mb_convert_encoding(substr(htmlspecialchars_decode($this->content), 0, 300), 'UTF-8', 'UTF-8');
            $res = preg_replace('/<img src=.*?>/','',$res);
            return $res;
        };

        // 多少时间之前
        $fields['before_time'] = function (){
            if(!empty($this->create_time)){
                $time = $this->create_time;
                $now_diff = time() - strtotime($time);
                if($now_diff / 86400 > 1){
                    $day = $now_diff / 86400;
                    return floor($day).'天前';
                }elseif ($now_diff / 3600 > 1){
                    $hour = $now_diff / 3600;
                    return floor($hour).'小时前';
                }elseif ($now_diff / 60 > 1){
                    $minute = $now_diff / 60;
                    return floor($minute).'分钟前';
                }else{
                    return '刚刚';
                }
            }
            return '';
        };

        $fields['share_url'] = function (){
            $share_url = AppSettingModel::find()->select('share_url')->scalar();
            return $share_url ? : '';
        };

        $fields['like_num'] = function (){
            return (string) $this->like_num;
        };

        $fields['like'] = function (){
            $num = ArticleLikeModel::find()->where(['article_id' => $this->id,"user_id" => self::$userID])->count();
            return $num ? 1 : 0;
        };

        $fields['focus'] = function (){
            $author = ArticleModel::find()->where(['id' => $this->id])->one();
            $author_id = $author ? $author->creater : 0;
            $num = FocusModel::find()->where(['focus_user_id' => $author_id,"user_id" => self::$userID])->count();
            return $num ? 1 : 0;
        };

        $fields['comment_num'] = function (){
            // pid 0 评论文章的评论才显示评论数
            $all_comment_num = ArticleCommentModel::find()->where(['article_id' => $this->id, 'pid'=>0])->andWhere(['del' => 0])->count();
            $private_num = ArticleCommentModel::find()->where(['article_id' => $this->id, 'is_private' => 1, 'pid'=>0])->andWhere(['<>','user_id',self::$userID])->count();
            return strval($all_comment_num - $private_num);
        };

        $fields['collect_num'] = function (){
            $num = ArticleCollectModel::find()->where(['article_id' => $this->id])->count();
            return $num;
        };

        $fields['collect'] = function (){
//            $num = ArticleCollectModel::find()->where(['article_id' => $this->id, "user_id" => self::$userID])->count();
            $num = CollectionsModel::find()->where(['item_id' => $this->id,"type"=>0 ,"user_id" => self::$userID])->count();
            return $num ? 1 : 0;
        };

        $fields['img_url'] = function (){
            return $this->preview_image;
        };

        $fields['create_time'] = function (){
            return date('Y-m-d', strtotime($this->create_time));
        };

        return $fields;
    }

    public function extraFields()
    {
        $fields['content'] = function (){
            return mb_convert_encoding(htmlspecialchars_decode($this->content), 'UTF-8', 'UTF-8');
        };

        $fields['label'] = function (){
            $label_data = ArticleLabelModel::find()->where(['article_id' => $this->id])->asArray()->all();
            if($label_data){
                $label_id = array_column($label_data,'label_id');
                $label_name = LabelModel::find()->where(['id'=> $label_id])->asArray()->all();
                if($label_name) return $label_name;
            }
            return [];
        };

        $fields['recommend'] = function (){
            $data = ArticleLabelModel::find()->where(['article_id' => $this->id])->asArray()->all();
            if($data){
                $label_id = array_column($data,'label_id');
                $article_label_arr = ArticleLabelModel::find()->select('article_id')
                    ->leftJoin('article as a','a.id=article_label.article_id')
                    ->where("article_id!={$this->id}")
                    ->andWhere(['a.status' => 1])
                    ->andwhere(['label_id' => $label_id])
                    ->asArray()
                    ->all();
                if($article_label_arr){
                    $article_id = array_column($article_label_arr,'article_id');
                    if(count($article_id) > 3){
                        shuffle($article_id);
                        array_splice($article_id,3);  // 随机取3篇相关推荐
                    }

                    $article_info = [];
                    foreach ($article_id as $id){
                        $info = Article::find()->select('id,preview_image as images,title,type')->where(['id' => $id])->asArray()->one();
//                        $info['like_num'] = ArticleLikeModel::find()->where(['article_id' => $id])->count();
//                        $info['like_num'] = (string)$this->like_num;
                        $info['like_num'] = Article::find()->select('like_num')->where(['id' => $id])->scalar() ?? 0;
                        $info['comment_num'] = ArticleComment::find()->where(['article_id' => $id])->count();
                        $type_name = ArticleTypeModel::find()->select('at_name')->where(['at_id' => $info['type']])->scalar();
                        $info['type_name'] = $type_name ? $type_name : '';
                        $article_info[] = $info;
                    }
                    return $article_info;
                }

            }
            return [];
        };

        $fields['comment'] = function (){
            $data =  ArticleCommentModel::find()->select('id,user_id,content,create_time,child_count')->where(['article_id' => $this->id, 'pid' => 0, 'del' => 0])->orderBy('child_count desc,id desc')->limit(3)->asArray()->all();
            foreach ($data as $k=>&$v){
                $like_num = ArticleCommentLikeModel::find()->where(['comment_id' => $v['id']])->count();
                $v['like_num'] = $like_num;
                $like =  ArticleCommentLikeModel::find()->where(['comment_id' => $v['id'], 'user_id'=> self::$userID])->one();
                $v['like'] = $like ? 1 : 0;
                foreach ($v as $key=>&$value){
                    if($key == 'user_id'){
                        $user = UserModel::find()->where(['id' => $value])->one();
                        $v['user_name'] = $user ? $user->nick_name : '';
                        $v['avatar_image'] = $user ? $user->avatar_image : '';
                    }

                    if($key == 'create_time'){
                        if(!empty($value)){
                            $now_diff = time() - strtotime($value);
                            if($now_diff / 86400 > 1){
                                $day = $now_diff / 86400;
                                $time_info =  floor($day).'天前';
                            }elseif ($now_diff / 3600 > 1){
                                $hour = $now_diff / 3600;
                                $time_info =  floor($hour).'小时前';
                            }elseif ($now_diff / 60 > 1){
                                $minute = $now_diff / 60;
                                $time_info =  floor($minute).'分钟前';
                            }else{
                                $time_info =  '刚刚';
                            }
                        }
                        $v['time_before'] = $time_info;
                    }
                }
            }
            return $data;
        };

        return $fields;
    }

}
