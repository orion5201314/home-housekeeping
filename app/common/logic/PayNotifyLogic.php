<?php
// +----------------------------------------------------------------------
// | LikeShop100%开源免费商用电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | Gitee下载：https://gitee.com/likeshop_gitee/likeshop
// | 访问官网：https://www.likemarket.net
// | 访问社区：https://home.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 微信公众号：好象科技
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------

// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\common\logic;


use app\common\enum\AccountLogEnum;
use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\PayEnum;
use app\common\enum\StaffAccountLogEnum;
use app\common\enum\StaffEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\RechargeOrder;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffBusytime;
use app\common\model\staff\StaffDepositRecharge;
use app\common\model\user\User;
use app\common\service\ConfigService;
use think\facade\Db;
use think\facade\Log;

/**
 * 支付成功后处理订单状态
 * Class PayNotifyLogic
 * @package app\api\logic
 */
class PayNotifyLogic extends BaseLogic
{
    public static function handle($action, $orderSn, $extra = [])
    {
        Db::startTrans();
        try {
            self::$action($orderSn, $extra);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            Log::write(implode('-', [
                __CLASS__,
                __FUNCTION__,
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ]));
            self::setError($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @notes 调用回调方法统一处理 更新订单支付状态
     * @param $orderSn
     * @param array $extra
     * @author ljj
     * @date 2022/3/1 11:35 上午
     */
    private static function order($orderSn, $extra = [])
    {
        $order = Order::with(['order_goods'])->where(['sn' => $orderSn])->findOrEmpty();

        //更新订单状态
        Order::update([
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
            'order_status' => OrderEnum::ORDER_STATUS_WAIT_SERVICE,
            'transaction_id' => $extra['transaction_id'] ?? ''
        ], ['id' => $order['id']]);

        //添加订单日志
        (new OrderLogLogic())->record(OrderLogEnum::TYPE_USER,OrderLogEnum::USER_PAID_ORDER,$order['id'],$order['user_id']);

        // 订单付款通知 - 通知买家
        event('Notice', [
            'scene_id' =>  NoticeEnum::ORDER_PAY_NOTICE,
            'params' => [
                'user_id' => $order['user_id'],
                'order_id' => $order['id'],
                'mobile' => $order['mobile']
            ]
        ]);

        // 订单付款通知 - 通知卖家
        $mobile = ConfigService::get('website', 'web_contact_mobile');
        if (!empty($mobile)) {
            event('Notice', [
                'scene_id' =>  NoticeEnum::ORDER_PAY_NOTICE_PLATFORM,
                'params' => [
                    'mobile' => $mobile,
                    'order_id' => $order['id']
                ]
            ]);
        }

        //抢单通知师傅
        $staffLists = Staff::where(['work_status'=>StaffEnum::WORK_STATUS_REST,'status'=>StaffEnum::STATUS_FROZEN])
            ->whereRaw('FIND_IN_SET('.$order['order_goods'][0]['goods_id'].',goods_id)')
            ->field('id,last_address_info')
            ->select()
            ->toArray();
        foreach ($staffLists as $staff) {
            //判断师傅是否满足距离要求
            //获取师傅服务范围 单位：公里
            $serviceDistance = ConfigService::get('transaction', 'service_distance',100);
            $distance = getDistance($order['address_info']['longitude'],$order['address_info']['latitude'],$staff['last_address_info']['longitude'],$staff['last_address_info']['latitude']);
            if ($distance > $serviceDistance) {
                continue;
            }

            //判断预约时间是否满足要求
            //判断订单预约时间内是否有其他订单
            $conflictOrder = Order::where(['staff_id'=>$staff['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
                ->whereRaw('appoint_time_start between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_end between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_start <= '.$order['appoint_time_start'].' and appoint_time_end >= '.$order['appoint_time_end'])
                ->findOrEmpty();
            if (!$conflictOrder->isEmpty()) {
                continue;
            }
            //判断是否在师傅忙时时间
            $staffBusyTime = StaffBusytime::field('date,time')
                ->where(['staff_id'=>$staff['id']])
                ->whereTime('date', 'between', [strtotime(date("Y-m-d",$order['appoint_time_start'])), strtotime(date("Y-m-d 23:59:59",$order['appoint_time_end']))])
                ->json(['time'],true)
                ->select()
                ->toArray();
            $isSuit = true;
            if (!empty($staffBusyTime)) {
                $staffBusyTimeArr = [];
                foreach ($staffBusyTime as $item) {
                    if (empty($item['time'])) {
                        continue;
                    }
                    $staffBusyTimeArr[strtotime(date("Y-m-d",$item['date']))] = $item['time'];
                }
                //订单预约时间拆分为30分时间段
                for ($i = $order['appoint_time_start']; $i < $order['appoint_time_end']; $i += (30 * 60)) {
                    $timeH = (int)date("H",$i);
                    $timeI = (int)date("i",$i);
                    if ($timeI < 30) {
                        $timeI = '00';
                    } else {
                        $timeI = '30';
                    }
                    $timeH = str_pad($timeH,2,'0',STR_PAD_LEFT);
                    $time = $timeH.':'.$timeI;

                    if (in_array($time,$staffBusyTimeArr[strtotime(date("Y-m-d",$i))])) {
                        $isSuit = false;
                    }
                }
            }
            if (!$isSuit) {
                continue;
            }

            // 抢单通知 - 通知师傅
            event('Notice', [
                'scene_id' =>  NoticeEnum::GRAB_ORDER_NOTICE_STAFF,
                'params' => [
                    'order_id' => $order['id'],
                    'staff_id' => $staff['id'],
                ]
            ]);
        }
    }


    /**
     * @notes 充值回调
     * @param $orderSn
     * @param array $extra
     * @author ljj
     * @date 2022/12/26 5:00 下午
     */
    public static function recharge($orderSn, $extra = [])
    {
        $order = RechargeOrder::where('sn', $orderSn)->findOrEmpty()->toArray();

        // 增加用户累计充值金额及用户余额
        User::update([
            'user_money' => ['inc',$order['order_amount']],
            'total_recharge_amount' => ['inc',$order['order_amount']],
        ],['id'=>$order['user_id']]);

        // 记录账户流水
        AccountLogLogic::add($order['user_id'], AccountLogEnum::MONEY,AccountLogEnum::USER_RECHARGE_ADD_MONEY,AccountLogEnum::INC, $order['order_amount'], $order['sn']);

        // 更新充值订单状态
        RechargeOrder::update([
            'transaction_id' => $extra['transaction_id'],
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
        ],['id'=>$order['id']]);
    }

    /**
     * @notes 补差价回调
     * @param $orderSn
     * @param $extra
     * @author ljj
     * @date 2024/10/8 下午5:44
     */
    private static function difference_price($orderSn, $extra = [])
    {
        $order = OrderDifferencePrice::where(['sn' => $orderSn])->findOrEmpty();

        //更新补差价订单状态
        OrderDifferencePrice::update([
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
            'transaction_id' => $extra['transaction_id'] ?? ''
        ], ['id' => $order['id']]);

        //更新订单价格
        Order::update([
            'difference_price' => ['inc',$order['amount']],
            'order_amount' => ['inc',$order['amount']],
            'total_amount' => ['inc',$order['amount']]
        ], ['id' => $order['order_id']]);

        //添加订单日志
        (new OrderLogLogic())->record(OrderLogEnum::TYPE_USER,OrderLogEnum::USER_DIFFERENCE_PRICE,$order['order_id'],$order['user_id']);
    }

    /**
     * @notes 加项回调
     * @param $orderSn
     * @param $extra
     * @author ljj
     * @date 2024/10/9 下午1:54
     */
    private static function additional($orderSn, $extra = [])
    {
        $order = OrderAdditional::where(['sn' => $orderSn])->findOrEmpty();
        $additionalSnap = json_decode($order['additional_snap'],true);

        //计算加项时长
        $additionalTime = 0;
        foreach ($additionalSnap as $additional) {
            $additionalTime += $additional['duration'] * $additional['num'] * 60;
        }

        //更新补差价订单状态
        OrderAdditional::update([
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
            'transaction_id' => $extra['transaction_id'] ?? ''
        ], ['id' => $order['id']]);

        //更新订单价格及服务时间
        Order::update([
            'additional_price' => ['inc',$order['amount']],
            'order_amount' => ['inc',$order['amount']],
            'total_amount' => ['inc',$order['amount']],
            'appoint_time_end' => ['inc',$additionalTime]
        ], ['id' => $order['order_id']]);

        //添加订单日志
        (new OrderLogLogic())->record(OrderLogEnum::TYPE_USER,OrderLogEnum::USER_ADDITIONAL,$order['order_id'],$order['user_id']);
    }

    /**
     * @notes 充值保证金
     * @param $orderSn
     * @param $extra
     * @author ljj
     * @date 2024/10/18 上午10:49
     */
    private static function deposit($orderSn, $extra = [])
    {
        $order = StaffDepositRecharge::where(['sn' => $orderSn])->findOrEmpty();

        //更新充值保证金订单状态
        StaffDepositRecharge::update([
            'pay_status' => PayEnum::ISPAID,
            'pay_time' => time(),
            'transaction_id' => $extra['transaction_id'] ?? ''
        ], ['id' => $order['id']]);

        // 增加师傅保证金
        Staff::update([
            'staff_deposit' => ['inc',$order['amount']],
        ],['id'=>$order['staff_id']]);

        // 记录账户流水
        StaffAccountLogLogic::add($order['staff_id'], StaffAccountLogEnum::DEPOSIT,StaffAccountLogEnum::STAFF_RECHARGE_ADD_DEPOSIT,StaffAccountLogEnum::INC, $order['amount'], $order['sn']);
    }
}