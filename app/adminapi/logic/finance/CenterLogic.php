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

namespace app\adminapi\logic\finance;

use app\common\enum\OrderEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\WithdrawEnum;
use app\common\logic\BaseLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderRefund;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffWithdraw;
use app\common\model\user\User;

class CenterLogic extends BaseLogic
{
    /**
     * @notes 财务中心
     * @return array
     * @author ljj
     * @date 2022/9/9 6:28 下午
     */
    public function center()
    {
        $settledEarnings = Order::where(['pay_status'=>PayEnum::ISPAID,'settlement_status'=>OrderEnum::SETTLEMENT_STATUS_ALREADY])->field('id,order_amount,earnings_ratio,settlement_status,settlement_amount')->append(['earnings'])->select()->toArray();
        $settledEarnings = array_sum(array_column($settledEarnings,'earnings'));
        $waitSettledEarnings = Order::where(['pay_status'=>PayEnum::ISPAID,'settlement_status'=>OrderEnum::SETTLEMENT_STATUS_NOT])->field('id,order_amount,earnings_ratio,settlement_status,settlement_amount')->append(['earnings'])->select()->toArray();
        $waitSettledEarnings = array_sum(array_column($waitSettledEarnings,'earnings'));

        return [
            //经营概况
            //累计营业额
            'total_amount' => Order::where(['pay_status'=>PayEnum::ISPAID])->sum('order_amount'),
            //累计成交订单
            'total_order' => Order::where(['pay_status'=>PayEnum::ISPAID])->count(),
            //退款成功金额
            'refund_success_amount' => OrderRefund::where(['refund_status'=>OrderRefundEnum::STATUS_SUCCESS])->sum('refund_amount'),
            //退款失败金额
            'refund_fail_amount' => OrderRefund::where(['refund_status'=>[OrderRefundEnum::STATUS_FAIL]])->sum('refund_amount'),

            //用户概况
            //用户充值金额
            'user_recharge_amount' => User::sum('total_recharge_amount'),
            //用户可用余额
            'user_money' => User::sum('user_money'),

            //师傅概况
            //已结算佣金
            'settled_earnings' => round($settledEarnings,2),
            //已提现佣金
            'withdrawn_earnings' => StaffWithdraw::where(['source_type'=>WithdrawEnum::SOURCCE_TYPE_EARNINGS,'status'=>WithdrawEnum::STATUS_SUCCESS])->sum('money'),
            //可提现佣金
            'wait_withdrawn_earnings' => Staff::sum('staff_earnings'),
            //待结算佣金
            'wait_settled_earnings' => round($waitSettledEarnings,2),

            //保证金概况
            //师傅保证金
            'staff_deposit' => Staff::sum('staff_deposit'),
        ];
    }
}