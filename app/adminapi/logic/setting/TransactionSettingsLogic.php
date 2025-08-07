<?php
// +----------------------------------------------------------------------
// | likeshop开源商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  likeshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | likeshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshop.cn.team
// +----------------------------------------------------------------------

namespace app\adminapi\logic\setting;


use app\common\logic\BaseLogic;
use app\common\service\ConfigService;

class TransactionSettingsLogic extends BaseLogic
{
    /**
     * @notes 获取交易设置
     * @return array
     * @author ljj
     * @date 2022/2/15 11:40 上午
     */
    public static function getConfig()
    {
        $config = [
            //未付款订单自动取消开关：0-关闭；1-开启；
            'cancel_unpaid_orders' => ConfigService::get('transaction', 'cancel_unpaid_orders',1),
            //未付款订单自动取消时间（单位：分钟）
            'cancel_unpaid_orders_times' => ConfigService::get('transaction', 'cancel_unpaid_orders_times',30),
            //自动好评时间（单位：天）
            'auto_highopinion_times' => ConfigService::get('transaction', 'auto_highopinion_times',1),
            //自动好评内容
            'auto_highopinion_content' => ConfigService::get('transaction', 'auto_highopinion_content','此用户未填写评价内容'),
            //订单结算周期：1-按状态；2-按周期；
            'settlement_type' => ConfigService::get('transaction', 'settlement_type',1),
            //按状态结算值：订单结束(n)天后结算；
            'settlement_status_value' => ConfigService::get('transaction', 'settlement_status_value',1),
            //按周期结算值1：1-每周；2-每月；
            'settlement_period_value1' => ConfigService::get('transaction', 'settlement_period_value1',1),
            //按周期结算值2：数值对应的是每周或每月的第几天
            'settlement_period_value2' => ConfigService::get('transaction', 'settlement_period_value1',1),
            //提前预约时间（单位：天）
            'advance_reservation_time' => ConfigService::get('transaction', 'advance_reservation_time',7),
            //默认接单数量
            'default_order_num' => ConfigService::get('transaction', 'default_order_num',1),
            //技师服务范围（单位：公里）
            'service_distance' => ConfigService::get('transaction', 'service_distance',100),
//            //自动核销订单开关：0-关闭；1-开启；
//            'verification_orders' => ConfigService::get('transaction', 'verification_orders',1),
//            //自动核销订单时间（单位：小时）
//            'verification_orders_times' => ConfigService::get('transaction', 'verification_orders_times',24),
//            //自动派单开关：0-关闭；1-开启；
//            'is_auth_dispatch' => ConfigService::get('transaction', 'is_auth_dispatch',1),
        ];

        return $config;
    }

    /**
     * @notes 设置交易设置
     * @param $params
     * @author ljj
     * @date 2022/2/15 11:49 上午
     */
    public static function setConfig($params)
    {
        ConfigService::set('transaction', 'cancel_unpaid_orders', $params['cancel_unpaid_orders']);
        ConfigService::set('transaction', 'cancel_unpaid_orders_times', $params['cancel_unpaid_orders_times']);
        ConfigService::set('transaction', 'auto_highopinion_times', $params['auto_highopinion_times']);
        ConfigService::set('transaction', 'auto_highopinion_content', $params['auto_highopinion_content']);
        ConfigService::set('transaction', 'settlement_type', $params['settlement_type']);
        ConfigService::set('transaction', 'settlement_status_value', $params['settlement_status_value']);
        ConfigService::set('transaction', 'settlement_period_value1', $params['settlement_period_value1']);
        ConfigService::set('transaction', 'settlement_period_value2', $params['settlement_period_value2']);
        ConfigService::set('transaction', 'advance_reservation_time', $params['advance_reservation_time']);
        ConfigService::set('transaction', 'default_order_num', $params['default_order_num']);
        ConfigService::set('transaction', 'service_distance', $params['service_distance']);
//        ConfigService::set('transaction', 'verification_orders', $params['verification_orders']);
//        ConfigService::set('transaction', 'verification_orders_times', $params['verification_orders_times']);
//        ConfigService::set('transaction', 'is_auth_dispatch', $params['is_auth_dispatch']);
    }
}