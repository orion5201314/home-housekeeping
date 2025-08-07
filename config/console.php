<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 定时任务
        'crontab' => 'app\common\command\Crontab',
        // 修改超级管理员密码
        'password' => 'app\common\command\Password',
        // 派遣师傅
//        'dispatch_staff' => 'app\common\command\DispatchStaff',
        // 关闭超时未付款订单
        'order_close' => 'app\common\command\OrderClose',
        // 系统自动核销服务中订单
//        'order_verification' => 'app\common\command\OrderVerification',
        // 关闭超过预约时间的订单
        'appoint_order_close' => 'app\common\command\AppointOrderClose',
        // 订单退款
        'order_refund' => 'app\common\command\OrderRefund',
        // 订单退款查询
        'order_refund_query' => 'app\common\command\OrderRefundQuery',
        // 订单结算
        'order_settlement' => 'app\common\command\OrderSettlement',
        // 订单自动好评
        'order_comment' => 'app\common\command\OrderComment',
        // 订单异常通知
        'order_abnormal_notice' => 'app\common\command\OrderAbnormalNotice',
        // 重置地图异常key
        'map_key_reset' => 'app\common\command\MapKeyReset',
    ],
];
