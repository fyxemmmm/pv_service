<?php
namespace common;
use api\models\ArticleComment;
use common\models\UmengPush;
use common\models\UserModel;
use common\models\ArticleModel;
use yii\db\Exception;

/*
 * 帮助类
 * */
class Helper{

    /*
     * 友盟推送
     * type 1 -- 评论文章, 2 -- 回复评论, 3 -- 通知贷超  4 -- 关注他人   老版本已废弃
     *      5 订单通知 收入通知 团队通知  新版本在用
     * user_id 推送者的id
     * accept_user_id 被推送的人的id
     * user_id 给 accept_user_id 推送了一条信息
     * */
    public static function pushMessage($type, $info){

        // 贷超系统通知
        if($type == 7){
            return;
            $umeng_model = new UmengPush();
            $title = $info['title'];
            $text = $info['describe'];
            $message = ['type' => 7, 'title'=>$title, 'describe'=>$text,'create_time'=>date('Y-m-d H:i:s')];
            $umeng_model->sendAndroidBroadcast($title,$text,$message);
            $umeng_model->sendIOSBroadcast($text,$message);
            return true;
        }

        $device_info = UserModel::find()->select('os,device_id')->where(['id' => $info['accept_user_id']])->asArray()->one();
        $os = $device_info['os'] ?? '';
        $device_tokens = $device_info['device_id'] ?? '';
        if(!$os || !$device_tokens) return;  // os 1 安卓,  2 IOS

        $umeng_model = new UmengPush();

        /*
         * 通知栏 订单通知 收入通知 团队通知
         * 示例:
         * ['accept_user_id' => 253,'content' => '您有一条新的订单通知']
         * ['accept_user_id' => 253,'content' => '您有一条新的收入通知']
         * ['accept_user_id' => 253,'content' => '您有一条新的团队通知']
         * */

        if($type == 5){
            $data = [
                'type' => 5,
                'content' => $info['content']
            ];

            if($os == 1){  // 安卓
                return;
                $umeng_model->sendAndroidUnicast($device_tokens, $data);
            }

            if($os == 2){  // ios
                return;
                $umeng_model->sendIOSUnicast($device_tokens, $data);
            }
            return true;
        }

        /*
         * *********************** 以下的type只为兼容以前,已废弃
         * */

        $nick_name = UserModel::find()->select('nick_name')->where(['id'=> $info['user_id']])->scalar() ?: '';
        // 评论文章推送
        /*
         * array 以下字段必有
         * article_id 文章的id
         * content 内容
         * */
        if($type == 1){
            $article_name = ArticleModel::find()->select('title')->where(['id' => $info['article_id']])->scalar() ?: '';
            $data = [
                'type' => 1,
                'nick_name' => $nick_name,
                'article_comment_id' => $info['article_comment_id'],  // 评论的id
                'article_comment_info' => $article_name,  //  文章名
                'content' => $info['content']
            ];

            if($os == 1){  // 安卓
                $umeng_model->sendAndroidUnicast($device_tokens, $data);
            }

            if($os == 2){  // ios
                $umeng_model->sendIOSUnicast($device_tokens, $data);
            }
            return true;
        }

        // 回复评论推送
        /*
         * array 以下字段必有
         * comment_id 评论的id  xx 评论  xxxxx  的xx id
         * comment_id_after 评论的id  xx 评论  xxxxx  的xxxx id
         * content 内容
         * */
        if($type == 2){
            $comment_data = ArticleComment::find()->where(['id'=>$info['comment_id']])->select('content')->scalar() ? : '';
            $data = [
                'type' => 2,
                'nick_name' => $nick_name,
                'article_comment_id' => $info['comment_id_after'],  // xxxx 评论了 xx 中的 xx
                'article_comment_info' => $comment_data,  // nick_name 回复了 article_comment_info ： content
                'content' => $info['content']
            ];

            if($os == 1){  // 安卓
                $umeng_model->sendAndroidUnicast($device_tokens, $data);
            }

            if($os == 2){  // ios
                $umeng_model->sendIOSUnicast($device_tokens, $data);
            }
            return true;
        }


        // 关注别人推送信息
        /* user_id    关注人的id
         * nick_name  关注人的昵称
         * */
        if($type == 4){
            $data = [
                'user_id' => $info['user_id'],  // xx 关注了 xxx 中的 xx
                'nick_name' => $nick_name,
                'type' => 4
            ];
            if($os == 1){  // 安卓
                $umeng_model->sendAndroidUnicast($device_tokens, $data);
            }

            if($os == 2){  // ios
                $umeng_model->sendIOSUnicast($device_tokens, $data);
            }
            return true;
        }
        return true;

        /*
         * *********************** 以上的type只为兼容以前,已废弃
         * */
    }

    /*
     * 转化时间
     * $time Y-m-d H:i:s 格式
     * return xxx 天/小时/分钟 之前
     * */
    public static function transferTime($time){
        if(!empty($time)){
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
    }


    /*
     * 分页
     * */
    public static function usePage($model){
        $page = (int)VarTmp::$page;
        $per_page = (int)VarTmp::$per_page;
        if($per_page === 0) $per_page = 1;

        $data = $model->offset(($page-1)*$per_page)
            ->limit($per_page)
            ->asArray()
            ->all();

        $totalCount = (int)$model->count();
        $page_count = ceil($totalCount / $per_page);
        if($per_page * $page > $totalCount){
            $page = $page_count;
        }

        $response = [
            'items' => $data,
            '_meta' => [
                'totalCount'=>$totalCount,'pageCount'=>$page_count,'currentPage'=>$page,'perPage'=>$per_page
            ]
        ];
        return $response;
    }

    public function strToInt($item){
        if(is_numeric($item)) return intval($item);
        return $item;
    }

    /*
     * 生成马甲用户
     * $count 马甲用户的数量
     * */
    public static function genSuitUser($count){
        $model = new UserModel();
        $max_id = $model::find()->select('max(id) as m_id')->scalar();
        $mobile_template = '18888888888';
        // $i是id
        for($i = $max_id + 1; $i <= $max_id + $count; $i++){
            try{
                $u_model = new UserModel();
                $u_model->id = $i;
                $u_model->access_token = (string)$i;
                $u_model->mobile = substr($mobile_template,0, -strlen($i)) . $i;
                $u_model->save();
            }catch (Exception $e){
                // pass
            }
        }
        return true;
    }

    /*
     * 转化成如 01:24:42 、 53:22 的时间格式
     * 输入秒 14575秒 string
     * */
    public static function formatTime($second){
        $hour_flag = false;
        $minute_flag = false;
        $second = (int)$second;
        if($second / 3600 > 1){
            $hour_flag = true;
            $hour = intdiv($second, 3600);
            $remain_second = $second % 3600;
            if($remain_second / 60 > 1){
                $minute = intdiv($remain_second, 60);
                $second = $remain_second % 60;
            }else{
                $minute = 0;
                $second = $remain_second;
            }
        }else if ($second / 60 > 1){
            $minute_flag = true;
            $hour = 0;
            $minute = intdiv($second, 60);
            $second = $second % 60;
        }else{
            $hour = 0;
            $minute = 0;
            $second = $second;
        }

        /*
         * 格式化
         * */
        $second = sprintf("%02s",$second);
        if($hour_flag){
            $hour = sprintf("%02s",$hour);
            $minute = sprintf("%02s",$minute);
            $out_put = $hour . ':' . $minute . ':' . $second;
        }else if ($minute_flag){
            $minute = sprintf("%02s",$minute);
            $out_put = $minute . ':' . $second;
        }else{
            $out_put = $second;
        }

        return $out_put;
    }


    /*
     * 格式化输出
     * type 1  -- String --> Int
     * type 2  -- Int --> String
     * */
    public static function formatData(array &$data, $type = 1){
        foreach($data as $k=>&$v){
            if(!is_array($v)){
                if(is_numeric($v)){
                    if ($type == 1) $v = intval($v);
                    if ($type == 2) $v = strval($v);
                }
            }else{
                if ($type == 1) static::formatData($v,1);
                if ($type == 2) static::formatData($v,2);
            }
        }
    }


    /*
     * 切割数据
     * $data 原数据
     * $chunk_arr 将这些数据从原数组中分隔成另一个数组
     * */
    public static function chunkData(array $data, array $chunk_arr){
        $arr2 = [];
        foreach ($chunk_arr as $k=>$v){
            if(isset($data[$v])){
                $arr2[$v] = $data[$v];
                unset($data[$v]);
            }
        }

        return [$data, $arr2];
    }

    public static function hiddenMobile($mobile){
        return preg_replace('/(.{3}).{4}(.*)/','$1****$2',$mobile);
    }

}