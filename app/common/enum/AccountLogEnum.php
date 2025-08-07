<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\common\enum;


class AccountLogEnum
{
    //变动对象
    const MONEY = 1;//余额
    const EARNINGS = 2;//佣金

    //动作
    const DEC = 1;//减少
    const INC = 2;//增加

    //余额变动类型
    const ADMIN_INC_MONEY = 100;//管理员增加余额
    const ADMIN_DEC_MONEY = 101;//管理员扣减余额
    const CANCEL_ORDER_ADD_MONEY = 102;//取消订单
//    const WITHDRAW_ADD_MONEY = 103;//佣金提现增加余额
    const USER_RECHARGE_ADD_MONEY = 104;//充值余额
    const ORDER_DEC_MONEY = 105;//支付订单
    const ADDITIONAL_DEC_MONEY = 106;//支付加项
    const DIFFERENCE_PRICE_DEC_MONEY = 107;//支付补差价
    const ORDER_RETURN_DEC_MONEY = 108;//订单退款退还余额

    //可提现余额变动类型
    const ADMIN_INC_EARNINGS = 200;//管理员增加佣金
    const ADMIN_DEC_EARNINGS = 201;//管理员扣减佣金
    const WITHDRAW_DEC_EARNINGS = 202;//佣金提现
    const WITHDRAW_FAIL_INC_EARNINGS = 203;//提现失败返还佣金
    const ORDER_SETTLEMENT_INC_EARNINGS = 204;//团长佣金结算
    const AFTER_SALE_DEC_EARNINGS = 205;//售后退款扣减佣金


    //余额（变动类型汇总）
    const MONEY_DESC = [
        self::ADMIN_INC_MONEY,
        self::ADMIN_DEC_MONEY,
        self::CANCEL_ORDER_ADD_MONEY,
//        self::WITHDRAW_ADD_MONEY,
        self::USER_RECHARGE_ADD_MONEY,
        self::ORDER_DEC_MONEY,
        self::ADDITIONAL_DEC_MONEY,
        self::DIFFERENCE_PRICE_DEC_MONEY,
        self::ORDER_RETURN_DEC_MONEY,
    ];

    //可提现余额（变动类型汇总）
    const EARNINGS_DESC = [
        self::ADMIN_INC_EARNINGS,
        self::ADMIN_DEC_EARNINGS,
        self::WITHDRAW_DEC_EARNINGS,
        self::WITHDRAW_FAIL_INC_EARNINGS,
        self::ORDER_SETTLEMENT_INC_EARNINGS,
        self::AFTER_SALE_DEC_EARNINGS,
    ];


    /**
     * @notes 动作描述
     * @param $action
     * @param false $flag
     * @return string|string[]
     * @author ljj
     * @date 2022/10/28 5:08 下午
     */
    public static function getActionDesc($action, $flag = false)
    {
        $desc = [
            self::DEC => '减少',
            self::INC => '增加',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$action] ?? '';
    }

    /**
     * @notes 变动类型描述
     * @param $changeType
     * @param false $flag
     * @return string|string[]
     * @author ljj
     * @date 2022/10/28 5:09 下午
     */
    public static function getChangeTypeDesc($changeType, $flag = false)
    {
        $desc = [
            self::ADMIN_INC_MONEY => '管理员增加余额',
            self::ADMIN_DEC_MONEY => '管理员扣减余额',
            self::CANCEL_ORDER_ADD_MONEY => '取消订单',
//            self::WITHDRAW_ADD_MONEY => '佣金提现增加余额',
            self::USER_RECHARGE_ADD_MONEY => '充值余额',
            self::ORDER_DEC_MONEY => '支付订单',
            self::ADDITIONAL_DEC_MONEY => '支付加项',
            self::DIFFERENCE_PRICE_DEC_MONEY => '支付补差价',
            self::ORDER_RETURN_DEC_MONEY => '订单退款退还余额',

            self::ADMIN_INC_EARNINGS => '管理员增加佣金',
            self::ADMIN_DEC_EARNINGS => '管理员扣减佣金',
            self::WITHDRAW_DEC_EARNINGS => '佣金提现',
            self::WITHDRAW_FAIL_INC_EARNINGS => '提现失败返还佣金',
            self::ORDER_SETTLEMENT_INC_EARNINGS => '团长佣金结算',
            self::AFTER_SALE_DEC_EARNINGS => '售后退款扣减佣金',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$changeType] ?? '';
    }


    /**
     * @notes 获取余额类型描述
     * @return string|string[]
     * @author ljj
     * @date 2022/12/2 5:42 下午
     */
    public static function getMoneyChangeTypeDesc()
    {
        $change_type = self::MONEY_DESC;
        $change_type_desc = self::getChangeTypeDesc('',true);
        $change_type_desc = array_filter($change_type_desc, function($key)  use ($change_type) {
            return in_array($key, $change_type);
        }, ARRAY_FILTER_USE_KEY);
        return $change_type_desc;
    }

    /**
     * @notes 获取可提现余额类型描述
     * @return string|string[]
     * @author ljj
     * @date 2022/12/2 5:42 下午
     */
    public static function getEarningsChangeTypeDesc()
    {
        $change_type = self::EARNINGS_DESC;
        $change_type_desc = self::getChangeTypeDesc('',true);
        $change_type_desc = array_filter($change_type_desc, function($key)  use ($change_type) {
            return in_array($key, $change_type);
        }, ARRAY_FILTER_USE_KEY);
        return $change_type_desc;
    }
}