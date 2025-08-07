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

namespace app\common\command;


use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\StaffAccountLogEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\OrderLogLogic;
use app\common\logic\StaffAccountLogLogic;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;


class OrderSettlement extends Command
{

    protected function configure()
    {
        $this->setName('order_settlement')
            ->setDescription('订单结算');
    }

    protected function execute(Input $input, Output $output)
    {
        //当前时间
        $currentTime = time();
        //订单结算周期：1-按状态；2-按周期；
        $settlementType = ConfigService::get('transaction', 'settlement_type',1);
        if ($settlementType == 1) {
            //订单结束(n)天后结算；
            $settlementTime = ConfigService::get('transaction', 'settlement_status_value',1) * 86400;

            $whereRaw = "cancel_time+$settlementTime < $currentTime or finish_time+$settlementTime < $currentTime";
        } else {
            //按周期结算值1：1-每周；2-每月；
            $settlementPeriodValue1 = ConfigService::get('transaction', 'settlement_period_value1',1);
            //按周期结算值2：数值对应的是每周或每月的第几天
            $settlementPeriodValue2 = ConfigService::get('transaction', 'settlement_period_value1',1);
            if ($settlementPeriodValue1 == 1) {
                if ($settlementPeriodValue2 > 7) {
                    $settlementPeriodValue2 = 7;
                }

                //获取当前星期几
                $currentWeekDay = date("N", $currentTime); //1（表示星期一）到 7（表示星期天）
                if ($currentWeekDay < $settlementPeriodValue2) {
                    return true;
                }

                $lastPeriod = date("Y-m-d", $currentTime - ($currentWeekDay - 3) * 86400 - 7 * 86400);
                $settlementTime = strtotime($lastPeriod.' 00:00:00');
            } else {
                if ($settlementPeriodValue2 > 28) {
                    $settlementPeriodValue2 = 28;
                }

                //获取当前几号
                $currentMonthDay = date("j", $currentTime); //1 到 31
                if ($currentMonthDay < $settlementPeriodValue2) {
                    return true;
                }

                $lastMonth = strtotime('last month');//上个月最后一天
                $lastMonthDayCount = date("j", $lastMonth);//上个月总天数
                $settlementTime = strtotime(date('Y-m-d',$lastMonth).($settlementPeriodValue2 - $lastMonthDayCount).' day');
            }

            $whereRaw = "cancel_time < $settlementTime or finish_time < $settlementTime";
        }

        $orders = Order::where(['order_status'=>[OrderEnum::ORDER_STATUS_FINISH,OrderEnum::ORDER_STATUS_CLOSE],'settlement_status'=>OrderEnum::SETTLEMENT_STATUS_NOT,'pay_status'=>PayEnum::ISPAID])
            ->where('refund_status','<>',OrderEnum::REFUND_STATUS_ALL)
            ->whereRaw($whereRaw)
            ->whereNotNull('staff_id')
            ->field('id,order_amount,earnings_ratio,settlement_status,settlement_amount,staff_id,sn')
            ->append(['earnings'])
            ->select()
            ->toArray();

        if (empty($orders)) {
            return true;
        }

        Db::startTrans();
        try{
            foreach ($orders as $order) {
                //更新订单状态
                Order::update(['settlement_status'=>OrderEnum::SETTLEMENT_STATUS_ALREADY,'settlement_amount'=>$order['earnings']], ['id' => $order['id']]);

                //添加订单日志
                (new OrderLogLogic())->record(OrderLogEnum::TYPE_SYSTEM,OrderLogEnum::SYSTEM_SETTLEMENT_ORDER,$order['id']);

                if ($order['earnings'] > 0) {
                    // 增加师傅保证金
                    Staff::update([
                        'staff_earnings' => ['inc',$order['earnings']],
                    ],['id'=>$order['staff_id']]);

                    // 记录账户流水
                    StaffAccountLogLogic::add($order['staff_id'], StaffAccountLogEnum::EARNINGS,StaffAccountLogEnum::ORDER_SETTLEMENT_INC_EARNINGS,StaffAccountLogEnum::INC, $order['earnings'], $order['sn']);
                }
            }

            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            Log::write('订单结算失败,失败原因:' . $e->getMessage());
        }
    }

}