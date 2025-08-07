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

namespace app\common\enum;


class OrderEnum
{
    //订单类型
    const ORDER_TYPE_HOME             = 1;  //上门服务

    //订单状态
    const ORDER_STATUS_WAIT_PAY       = 0;  //待付款
    const ORDER_STATUS_WAIT_SERVICE   = 1;  //待服务
    const ORDER_STATUS_SERVICE        = 2;  //服务中
    const ORDER_STATUS_FINISH         = 3;  //已完成
    const ORDER_STATUS_CLOSE          = 4;  //已关闭

    //订单子状态
    const ORDER_SUB_STATUS_WAIT_RECEIVE= 0;  //待接单
    const ORDER_SUB_STATUS_RECEIVED    = 1;  //已接单
    const ORDER_SUB_STATUS_SET_OUT     = 2;  //已出发
    const ORDER_SUB_STATUS_ARRIVE      = 3;  //已到达

    //退款状态
    const REFUND_STATUS_NOT = 0;//未退款
    const REFUND_STATUS_PART = 1;//部分退款
    const REFUND_STATUS_ALL = 2;//全部退款

    //结算状态
    const SETTLEMENT_STATUS_NOT = 0;//未结算
    const SETTLEMENT_STATUS_ALREADY = 1;//已结算


    /**
     * @notes 订单状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 11:03 上午
     */
    public static function getOrderStatusDesc($value = true)
    {
        $data = [
            self::ORDER_STATUS_WAIT_PAY => '待付款',
            self::ORDER_STATUS_WAIT_SERVICE => '待服务',
            self::ORDER_STATUS_SERVICE => '服务中',
            self::ORDER_STATUS_FINISH => '已完成',
            self::ORDER_STATUS_CLOSE => '已关闭',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value] ?? '-';
    }

    /**
     * @notes 订单子状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/12 下午3:14
     */
    public static function getOrderSubStatusDesc($value = true)
    {
        $data = [
            self::ORDER_SUB_STATUS_WAIT_RECEIVE => '待接单',
            self::ORDER_SUB_STATUS_RECEIVED => '已接单',
            self::ORDER_SUB_STATUS_SET_OUT => '已出发',
            self::ORDER_SUB_STATUS_ARRIVE => '已到达',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value] ?? '-';
    }

    /**
     * @notes 订单类型
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/12 下午2:56
     */
    public static function getOrderTypeDesc($value = true)
    {
        $data = [
            self::ORDER_TYPE_HOME => '上门服务',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value] ?? '-';
    }

    /**
     * @notes 退款状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/12 下午2:56
     */
    public static function getRefundStatusDesc($value = true)
    {
        $data = [
            self::REFUND_STATUS_NOT => '未退款',
            self::REFUND_STATUS_PART => '部分退款',
            self::REFUND_STATUS_ALL => '全部退款',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value] ?? '-';
    }

    /**
     * @notes 结算状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/13 上午11:23
     */
    public static function getSettlementStatusDesc($value = true)
    {
        $data = [
            self::SETTLEMENT_STATUS_NOT => '待结算',
            self::SETTLEMENT_STATUS_ALREADY => '已结算',
        ];
        if (true === $value) {
            return $data;
        }
        return $data[$value] ?? '-';
    }
}