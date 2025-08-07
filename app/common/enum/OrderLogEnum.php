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


class OrderLogEnum
{
    //操作人类型
    const TYPE_SYSTEM   = 1;//系统
    const TYPE_ADMIN    = 2;//后台
    const TYPE_USER     = 3;//用户
    const TYPE_STAFF    = 4;//师傅


    //订单动作
    const USER_ADD_ORDER        = 101;//提交订单
    const USER_CANCEL_ORDER     = 102;//取消订单
    const USER_CONFIRM_ORDER    = 103;//确认收货
    const USER_PAID_ORDER       = 104;//支付订单
    const USER_DIFFERENCE_PRICE = 105;//用户补差价
    const USER_ADDITIONAL       = 106;//用户加项

    const SHOP_CANCEL_ORDER     = 201;//商家取消订单
    const SHOP_ORDER_REMARKS    = 202;//商家备注
    const SHOP_ORDER_REFUND     = 203;//商家退款
    const SHOP_DISPATCH_STAFF   = 204;//商家指派师傅
    const SHOP_ACCEPT_ORDER     = 205;//商家帮师傅接取订单
    const SHOP_STAFF_SETOUT     = 206;//商家确定师傅已出发
    const SHOP_STAFF_ARRIVE     = 207;//商家确定师傅已到达
    const SHOP_STAFF_SERVICE    = 208;//商家确定师傅已开始服务
    const SHOP_ORDER_FINISH     = 209;//商家确定师傅已完成服务

    const SYSTEM_CANCEL_ORDER   = 301;//系统取消超时未付款订单
    const SYSTEM_CONFIRM_ORDER  = 302;//系统核销订单
    const SYSTEM_CANCEL_APPOINT_ORDER   = 303;//系统取消超过预约时间订单
    const SYSTEM_SETTLEMENT_ORDER   = 304;//系统结算订单

    const STAFF_GRAB_ORDER      = 401;//师傅抢单
    const STAFF_RECEIVE_ORDER   = 402;//师傅接单
    const STAFF_SETOUT_ORDER    = 403;//师傅已出发
    const STAFF_ARRIVE_ORDER    = 404;//师傅已到达
    const STAFF_START_ORDER     = 405;//师傅开始服务
    const STAFF_FINISH_ORDER    = 406;//师傅完成服务


    /**
     * @notes 操作人
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 2:17 下午
     */
    public static function getOperatorDesc($value = true)
    {
        $desc = [
            self::TYPE_SYSTEM           => '系统',
            self::TYPE_ADMIN            => '后台',
            self::TYPE_USER             => '用户',
            self::TYPE_STAFF            => '师傅',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }

    /**
     * @notes 订单日志
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 2:17 下午
     */
    public static function getRecordDesc($value = true)
    {
        $desc = [
            //系统
            self::SYSTEM_CANCEL_ORDER   => '系统取消超时未付款订单',
            self::SYSTEM_CONFIRM_ORDER  => '系统核销订单',
            self::SYSTEM_CANCEL_APPOINT_ORDER  => '系统取消超过预约时间订单',
            self::SYSTEM_SETTLEMENT_ORDER => '系统结算订单',

            //商家
            self::SHOP_CANCEL_ORDER     => '商家取消订单',
            self::SHOP_ORDER_REMARKS    => '商家备注',
            self::SHOP_ORDER_REFUND     => '商家退款',
            self::SHOP_DISPATCH_STAFF   => '商家指派师傅',
            self::SHOP_ACCEPT_ORDER     => '商家帮师傅接取订单',
            self::SHOP_STAFF_SETOUT     => '商家确定师傅已出发',
            self::SHOP_STAFF_ARRIVE     => '商家确定师傅已到达',
            self::SHOP_STAFF_SERVICE    => '商家确定师傅已开始服务',
            self::SHOP_ORDER_FINISH     => '商家确定师傅已完成服务',

            //会员
            self::USER_ADD_ORDER        => '会员提交订单',
            self::USER_CANCEL_ORDER     => '会员取消订单',
            self::USER_CONFIRM_ORDER    => '会员确认收货',
            self::USER_PAID_ORDER       => '会员支付订单',
            self::USER_DIFFERENCE_PRICE => '会员补差价',
            self::USER_ADDITIONAL       => '会员加项',

            //师傅
            self::STAFF_GRAB_ORDER      => '师傅抢单',
            self::STAFF_RECEIVE_ORDER   => '师傅接单',
            self::STAFF_SETOUT_ORDER    => '师傅已出发',
            self::STAFF_ARRIVE_ORDER    => '师傅已到达',
            self::STAFF_START_ORDER     => '师傅开始服务',
            self::STAFF_FINISH_ORDER    => '师傅完成服务',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }
}