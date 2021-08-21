<?php
namespace common;

class Config{
    /*
     * inform_read 表
     * */
    CONST DCINFORM = 0; // 贷超通知
    CONST ORDERINFORM = 1; // 订单通知
    CONST INCOMEINFORM = 2; // 收入通知
    CONST TEAMINFORM = 3; // 团队通知

    CONST ORDERMSG = '您有一条新的订单通知';
    CONST INCOMEMSG = '您有一条新的收入通知';
    CONST TEAMMSG = '您有一条新的团队通知';

    /*
     * 按钮点击的类型
     * */
    CONST CLICKSHARE = 1; // 点击分享按钮
    CONST CLICKINFO = [
        self::CLICKSHARE => 'click_share'
    ];

    CONST REPORT_DOING = 0; // 报备审核中
    CONST REPORT_SUCCESS = 1; // 报备审核成功
    CONST REPORT_FAIL = 2; // 报备失败

    /*
     * 商城 start
     * */
    CONST APPLY_FOR_REFUND_NORMAL = 0;  // 待发货状态 没有申请退款 正常状态
    CONST APPLY_FOR_REFUND_DOING = 1;  // 待发货状态 申请退款中

    CONST SHOP_ORDER_UNPAY = 1; // 订单待支付
    CONST SHOP_ORDER_HAS_PAY = 2; // 订单已经支付 -- 待发货
    CONST SHOP_ORDER_HAS_DELIVERED = 3; // 订单已经发货 -- 待收货
    CONST SHOP_ORDER_HAS_FINISHED = 4; // 订单已完成
    CONST SHOP_ORDER_HAS_CANCELED = 5; // 订单已取消




    /*
     * 商城end
     * */

    /*
     * 日志
     * */
    CONST LOG_ZFB_SHOP_NOTIFY = 1; // 支付宝异步通知
    CONST LOG_WX_SHOP_NOTIFY = 2; // 支付宝异步通知

}