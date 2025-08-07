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

namespace app\adminapi\validate\setting;


use app\common\validate\BaseValidate;

class TransactionSettingsValidate extends BaseValidate
{
    protected $rule = [
        'cancel_unpaid_orders' => 'require|in:0,1',
        'cancel_unpaid_orders_times' => 'requireIf:cancel_unpaid_orders,1|integer|gt:0',
        'auto_highopinion_times' => 'require|number',
        'auto_highopinion_content' => 'require',
        'settlement_type' => 'require|in:1,2',
        'settlement_status_value' => 'requireIf:settlement_type,1|float|gt:0',
        'settlement_period_value1' => 'requireIf:settlement_type,2|in:1,2',
        'settlement_period_value2' => 'requireIf:settlement_type,2|between:1,28',
        'advance_reservation_time' => 'require|number',
        'default_order_num' => 'require|number',
        'service_distance' => 'require|float|egt:1',
//        'verification_orders' => 'require|in:0,1',
//        'verification_orders_times' => 'requireIf:verification_orders,1|integer|gt:0',
//        'is_auth_dispatch' => 'require|in:0,1',
    ];

    protected $message = [
        'cancel_unpaid_orders.require' => '请选择系统取消待付款订单方式',
        'cancel_unpaid_orders.in' => '系统取消待付款订单状态值有误',
        'cancel_unpaid_orders_times.requireIf' => '系统取消待付款订单时间未填写',
        'cancel_unpaid_orders_times.integer' => '系统取消待付款订单时间须为整型',
        'cancel_unpaid_orders_times.gt' => '系统取消待付款订单时间须大于0',
        'auto_highopinion_times.require' => '请输入超时未评价好评时间',
        'auto_highopinion_times.number' => '超时未评价好评时间值错误',
        'auto_highopinion_content.require' => '请输入超时未评价好评内容',
        'settlement_type.require' => '请选择订单结算周期',
        'settlement_type.in' => '订单结算周期值错误',
        'settlement_status_value.requireIf' => '请选择订单结算周期状态值',
        'settlement_status_value.float' => '订单结算周期状态值错误',
        'settlement_status_value.gt' => '订单结算周期状态值需大于0',
        'settlement_period_value1.requireIf' => '请选择订单结算周期时间',
        'settlement_period_value1.in' => '订单结算周期时间错误',
        'settlement_period_value2.requireIf' => '请选择订单结算周期时间',
        'settlement_period_value2.in' => '订单结算周期时间错误',
        'advance_reservation_time.require' => '请输入用户可提前预约时间',
        'advance_reservation_time.number' => '用户可提前预约时间值错误',
        'default_order_num.require' => '请输入默认技师接单数量',
        'default_order_num.number' => '默认技师接单数量值错误',
        'service_distance.require' => '请输入技师服务范围',
        'service_distance.float' => '技师服务范围值错误',
        'service_distance.egt' => '技师服务范围必须大于等于1',
//        'verification_orders.require' => '请选择系统自动核销订单方式',
//        'verification_orders.in' => '系统自动核销订单状态值有误',
//        'verification_orders_times.requireIf' => '系统自动核销订单时间未填写',
//        'verification_orders_times.integer' => '系统自动核销订单时间须为整型',
//        'verification_orders_times.gt' => '系统自动核销订单时间须大于0',
//        'is_auth_dispatch.require' => '请选择是否系统随机派单',
//        'is_auth_dispatch.in' => '系统随机派单状态值有误',
    ];

    public function sceneSetConfig()
    {
        return $this->only(['cancel_unpaid_orders','cancel_unpaid_orders_times','auto_highopinion_times','auto_highopinion_content','settlement_type','settlement_status_value','settlement_period_value1','settlement_period_value2','advance_reservation_time','default_order_num','service_distance']);
    }
}