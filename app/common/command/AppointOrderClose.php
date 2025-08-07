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


use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\OrderLogLogic;
use app\common\logic\RefundLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\order\OrderLog;
use app\common\service\ConfigService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

/**
 * 关闭超过预约时间的订单
 * Class AppointOrderClose
 * @package app\common\command
 */
class AppointOrderClose extends Command
{
    protected function configure()
    {
        $this->setName('appoint_order_close')
            ->setDescription('关闭超过预约时间的订单');
    }

    protected function execute(Input $input, Output $output)
    {
        $time = time();

        $orders = Order::where(['order_status' => OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status' => OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,'pay_status' => PayEnum::ISPAID])
            ->where('appoint_time_start','<',$time)
            ->select()
            ->toArray();

        if (empty($orders)) {
            return true;
        }

        Db::startTrans();
        try{
            foreach ($orders as $order) {
                //处理基础订单
                //更新订单状态
                Order::update([
                    'order_status' => OrderEnum::ORDER_STATUS_CLOSE,
                    'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                    'cancel_time' => time(),
                ],['id'=>$order['id']]);
                //添加订单日志
                (new OrderLogLogic())->record(OrderLogEnum::TYPE_SYSTEM,OrderLogEnum::SYSTEM_CANCEL_APPOINT_ORDER,$order['id'],0);
                //基础订单退款
                $orderCopy = $order;
                $orderCopy['order_amount'] = $order['goods_price'];
                (new RefundLogic())->refund($orderCopy,$order['goods_price'],OrderRefundEnum::TYPE_SYSTEM,0);

                //处理加项订单
                $orderAdditional = OrderAdditional::where(['order_id'=>$order['id']])->select()->toArray();
                foreach ($orderAdditional as $item) {
                    $item['order_amount'] = $item['amount'];
                    $item['order_terminal'] = $item['terminal'];
                    //更新加项订单状态
                    OrderAdditional::update([
                        'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                    ],['id'=>$item['id']]);

                    //加项订单退款
                    (new RefundLogic())->refund($item,$item['order_amount'],OrderRefundEnum::TYPE_SYSTEM,$order['user_id'],1,OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL);
                }

                //处理补差价订单
                $orderDifference = OrderDifferencePrice::where(['order_id'=>$order['id']])->select()->toArray();
                foreach ($orderDifference as $item) {
                    $item['order_amount'] = $item['amount'];
                    $item['order_terminal'] = $item['terminal'];
                    //更新补差价订单状态
                    OrderDifferencePrice::update([
                        'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                    ],['id'=>$item['id']]);

                    //加项订单退款
                    (new RefundLogic())->refund($item,$item['order_amount'],OrderRefundEnum::TYPE_SYSTEM,$order['user_id'],1,OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE);
                }


                // 取消订单通知 - 通知用户
                event('Notice', [
                    'scene_id' =>  NoticeEnum::ORDER_CANCEL_NOTICE,
                    'params' => [
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                    ]
                ]);
                // 订单退款通知 - 通知用户
                if($order['pay_status'] == PayEnum::ISPAID) {
                    event('Notice', [
                        'scene_id' =>  NoticeEnum::ORDER_REFUND_NOTICE,
                        'params' => [
                            'order_id' => $order['id'],
                            'user_id' => $order['user_id'],
                            'refund_amount' => $order['order_amount']
                        ]
                    ]);
                }
            }

            Db::commit();
        } catch(\Exception $e) {
            Db::rollback();
            Log::write('关闭超过预约时间的订单失败,失败原因:' . $e->getMessage());
        }
    }

}