<?php
namespace common;
use common\models\ArticleTypeInsertModel;
use common\models\ArticleTypeModel;
use common\models\ArticleModel;
/*
 * 重新组装回调数据
 * */
class Extra{
    protected $method_name;
    protected $response_data;

    /*
     * 自定义
     * */
    private static $_article_count = 0;

    public function __construct($controller_name, $action_name, $response_data)
    {
        $action_name = $action_name = str_replace('-','', ucwords($action_name, '-'));
        $this->method_name = $controller_name.$action_name;
        $this->response_data = $response_data;
    }

    public function checkFinalData(){ // 检查是否需要改变数据并返回
//        var_dump($this->method_name);exit();
        $this->compatibility();
        if(method_exists($this,$this->method_name)){
            return call_user_func([$this, $this->method_name], $this->response_data);
        }
        return false;
    }

    /*
     * 兼容性
     * */
    protected function compatibility(){
        switch ($this->method_name){
            case 'jmb-commentIndex':
                $this->method_name = 'jmbCommentIndex';
                break;
            case 'jmb-commentView':
                $this->method_name = 'jmbCommentView';
                break;

        }
    }

    /*
     * 类型判断
     * */
    public function articleGetInfo($response_data){
        // 1 20   3 7
        // 2 20   33
        $page = (int)VarTmp::$page;
        $per_page = (int)VarTmp::$per_page;
        $insert_arr = ArticleTypeInsertModel::find()->select('offset,article_type_id')->asArray()->all(); // 专题插入到文章的哪里
        $offset_arr = array_column($insert_arr, 'offset');
        array_multisort($offset_arr,SORT_ASC,$insert_arr);
        $max_v = $page * $per_page; // 20
        $min_v = ($page-1) * $per_page; // 0
        $items = $response_data['items'];

        foreach ($insert_arr as $k=>$v){
            if($v['offset'] > $max_v || $v['offset'] < $min_v){
                continue;
            }
            $v['offset'] = $v['offset'] - $min_v;
            $this->reload_article_array($items, $v);
        }

        return $response_data['items'] = $items;
    }


    /*
     * 插入专题到其中去
     * */
    private function reload_article_array(&$items,$article_type_data){
        $result = array_splice($items,$article_type_data['offset'] + self::$_article_count); // result是右边部分  // items是左边部分
        $article_type_info = ArticleTypeModel::find()->where(['at_id' => $article_type_data['article_type_id']])->asArray()->all();
        /*
         * 修改数据
         * */
        $article_type_info[0]['id'] = (int)$article_type_info[0]['at_id'];
        $article_type_info[0]['title'] = $article_type_info[0]['at_name'];
        $article_type_info[0]['desc'] = $article_type_info[0]['topic_des'];
        $article_type_info[0]['img_url'] = $article_type_info[0]['image'];
        $article_type_info[0]['preview_image'] = $article_type_info[0]['image'];
        $article_type_info[0]['count'] = ArticleModel::find()->where(['type' =>  $article_type_info[0]['at_id']])->count();
        unset($article_type_info[0]['at_id']);
        unset($article_type_info[0]['at_name']);
        unset($article_type_info[0]['topic']);
        unset($article_type_info[0]['topic_des']);
        unset($article_type_info[0]['subscription_num']);
        unset($article_type_info[0]['weight']);
        unset($article_type_info[0]['is_del']);
        unset($article_type_info[0]['image']);

        $article_type_info[0]['type_status'] = 2; // 专题
        $items = array_merge($items,$article_type_info,$result);
        ++ self::$_article_count;
    }

    public function jmbIndex($response_data){
        Helper::formatData($response_data,2);
        return $response_data['items'];
    }

    public function jmbDetail($response_data){
        Helper::formatData($response_data,2);
        return $response_data['items'];
    }

    public function jmbCommentIndex($response_data){
        Helper::formatData($response_data,2);
        return $response_data['items'];
    }

    public function jmbCommentView($response_data){
        Helper::formatData($response_data,2);
        return $response_data['items'];
    }

}