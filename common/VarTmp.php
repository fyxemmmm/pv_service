<?php
namespace common;
/*
 * 保存临时变量
 * */
class VarTmp{
    /*
     * 第一页
     * */
    public static $page = 1;
    /*
     * 二十条数据
     * */
    public static $per_page = 20;

    /*
     * restful响应参数
     * */
    public static $extra;

    /*
     *  []  --->  {}
     * */
    public static $json_force_object;


    public static $admin_id;

}