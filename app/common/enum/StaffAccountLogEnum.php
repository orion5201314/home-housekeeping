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


class StaffAccountLogEnum
{
    //变动对象
    const DEPOSIT = 1;//保证金
    const EARNINGS = 2;//佣金

    //动作
    const DEC = 1;//减少
    const INC = 2;//增加

    //保证金变动类型
    const ADMIN_INC_DEPOSIT = 100;//管理员增加保证金
    const ADMIN_DEC_DEPOSIT = 101;//管理员扣减保证金
    const STAFF_RECHARGE_ADD_DEPOSIT = 102;//师傅充值保证金
    const STAFF_WITHDRAW_DEC_DEPOSIT = 103;//保证金提现扣除
    const STAFF_WITHDRAW_FAIL_INC_DEPOSIT = 104;//提现失败退回

    //佣金变动类型
    const ADMIN_INC_EARNINGS = 200;//管理员增加佣金
    const ADMIN_DEC_EARNINGS = 201;//管理员扣减佣金
    const WITHDRAW_DEC_EARNINGS = 202;//佣金提现扣除
    const WITHDRAW_FAIL_INC_EARNINGS = 203;//提现失败退回
    const ORDER_SETTLEMENT_INC_EARNINGS = 204;//订单佣金结算


    //保证金（变动类型汇总）
    const DEPOSIT_DESC = [
        self::ADMIN_INC_DEPOSIT,
        self::ADMIN_DEC_DEPOSIT,
        self::STAFF_RECHARGE_ADD_DEPOSIT,
        self::STAFF_WITHDRAW_DEC_DEPOSIT,
        self::STAFF_WITHDRAW_FAIL_INC_DEPOSIT,
    ];

    //可提现余额（变动类型汇总）
    const EARNINGS_DESC = [
        self::ADMIN_INC_EARNINGS,
        self::ADMIN_DEC_EARNINGS,
        self::WITHDRAW_DEC_EARNINGS,
        self::WITHDRAW_FAIL_INC_EARNINGS,
        self::ORDER_SETTLEMENT_INC_EARNINGS,
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
            self::ADMIN_INC_DEPOSIT => '管理员增加保证金',
            self::ADMIN_DEC_DEPOSIT => '管理员扣减保证金',
            self::ADMIN_INC_EARNINGS => '管理员增加佣金',
            self::ADMIN_DEC_EARNINGS => '管理员扣减佣金',
            self::WITHDRAW_DEC_EARNINGS => '佣金提现扣除',
            self::WITHDRAW_FAIL_INC_EARNINGS => '提现失败退回',
            self::ORDER_SETTLEMENT_INC_EARNINGS => '订单佣金结算',
            self::STAFF_RECHARGE_ADD_DEPOSIT => '师傅充值保证金',
            self::STAFF_WITHDRAW_DEC_DEPOSIT => '保证金提现扣除',
            self::STAFF_WITHDRAW_FAIL_INC_DEPOSIT => '提现失败退回',
        ];
        if($flag) {
            return $desc;
        }
        return $desc[$changeType] ?? '';
    }


    /**
     * @notes 获取保证金类型描述
     * @return string|string[]
     * @author ljj
     * @date 2022/12/2 5:42 下午
     */
    public static function getDepositChangeTypeDesc()
    {
        $change_type = self::DEPOSIT_DESC;
        $change_type_desc = self::getChangeTypeDesc('',true);
        $change_type_desc = array_filter($change_type_desc, function($key)  use ($change_type) {
            return in_array($key, $change_type);
        }, ARRAY_FILTER_USE_KEY);
        return $change_type_desc;
    }

    /**
     * @notes 获取佣金类型描述
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