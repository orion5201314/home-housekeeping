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

namespace app\adminapi\logic\order;


use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\StaffEnum;
use app\common\logic\BaseLogic;
use app\common\logic\OrderLogLogic;
use app\common\logic\RefundLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\order\OrderGoods;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffBusytime;
use app\common\service\ConfigService;
use think\facade\Db;

class OrderLogic extends BaseLogic
{
    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/2/11 3:01 下午
     */
    public function detail($id)
    {
        $result = Order::where(['id'=>$id])
            ->append(['appoint_time_desc','pay_status_desc','order_type_desc','order_status_desc','pay_way_desc','admin_order_btn','order_terminal_desc','address_info','earnings','refund_amount','total_refund_amount','settlement_status_desc'])
            ->with(['order_goods' => function($query){
                $query->field('goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,goods_sku')->append(['goods_image'])->hidden(['goods_snap']);
            },'order_log' => function($query){
                $query->field('id,order_id,type,channel,operator_id,create_time')->append(['channel_desc','operator']);
            },'user' => function($query){
                $query->field('id,sn,nickname,avatar,mobile');
            },'staff' => function($query){
                $query->field('id,sn,name,work_image,mobile');
            },'order_checkin' => function($query){
                $query->field('order_id,image_info,order_status,order_sub_status,address_info,create_time')->append(['order_status_desc']);
            },'order_additional' => function($query){
                $query->field('id,order_id,additional_snap')->json(['additional_snap'],true);
            }])
            ->findOrEmpty()
            ->toArray();

        $result['settlement_info'] = [];
        if (!empty($result)) {
            //组装结算信息
            $result['settlement_info'][] = [
                'order_amount' => $result['order_amount'],
                'refund_amount' => $result['total_refund_amount'],
                'earnings' => $result['earnings'],
                'settlement_status_desc' => $result['settlement_status_desc']
            ];

            //处理加项数据
            $additionalSnap = array_column($result['order_additional'],'additional_snap');
            $additionalInfo = [];
            foreach ($additionalSnap as $additional) {
                foreach ($additional as $item) {
                    $additionalInfo[$item['id']]['id'] = $item['id'];
                    $additionalInfo[$item['id']]['name'] = $item['name'];
                    $additionalInfo[$item['id']]['duration'] = $item['duration'];
                    $additionalInfo[$item['id']]['price'] = ($additionalInfo[$item['id']]['price'] ?? 0) + ($item['price'] * $item['num']);
                    $additionalInfo[$item['id']]['num'] = ($additionalInfo[$item['id']]['num'] ?? 0) + $item['num'];
                }
            }
            $result['order_additional'] = array_values($additionalInfo);
        }

        return $result;
    }

    /**
     * @notes 取消订单
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/2/11 4:10 下午
     */
    public function cancel($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::where(['id'=>$params['id']])->append(['refund_amount'])->findOrEmpty();

            //更新订单信息
            $order->order_status = OrderEnum::ORDER_STATUS_CLOSE;
            $order->cancel_time = time();
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_CANCEL_ORDER,$params['id'],$params['admin_id']);

            // 取消订单通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::ORDER_CANCEL_NOTICE,
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'mobile' => $order['mobile']
                ]
            ]);
            // 取消订单通知 - 通知师傅
            if(!empty($order['staff_id'])) {
                event('Notice', [
                    'scene_id' =>  NoticeEnum::ORDER_CANCEL_NOTICE_STAFF,
                    'params' => [
                        'order_id' => $order['id'],
                        'staff_id' => $order['staff_id'],
                    ]
                ]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除订单
     * @param $params
     * @author ljj
     * @date 2022/2/11 4:27 下午
     */
    public function del($params)
    {
        Order::destroy($params['id']);
        return true;
    }

    /**
     * @notes 商家备注
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/2/11 4:45 下午
     */
    public function remark($params)
    {
        Order::update(['order_remarks'=>$params['remark'] ?? ''],['id'=>$params['id']]);
        return true;
    }

    /**
     * @notes 商家备注详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/2/11 4:56 下午
     */
    public function remarkDetail($id)
    {
        return Order::where('id',$id)->field('order_remarks')->findOrEmpty()->toArray();
    }


    /**
     * @notes 指派师傅
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/8/29 5:26 下午
     */
    public function dispatchStaff($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = Order::where('id',$params['id'])->findOrEmpty()->toArray();

            //为订单指派师傅
            Order::update(['staff_id'=>$params['staff_id']],['id'=>$params['id']]);

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_DISPATCH_STAFF,$params['id'],$params['admin_id']);

            // 接单通知 - 通知师傅
            event('Notice', [
                'scene_id' =>  NoticeEnum::ACCEPT_ORDER_NOTICE_STAFF,
                'params' => [
                    'order_id' => $params['id'],
                    'staff_id' => $params['staff_id'],
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 师傅列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/8/29 6:10 下午
     */
    public function staffLists($params)
    {
        //获取订单信息
        $order = Order::where(['id'=>$params['id']])
            ->field('address_info,appoint_time_start,appoint_time_end,staff_id')
            ->findOrEmpty()
            ->toArray();

        //师傅列表
        $lists = Staff::field('id,name,goods_id,last_address_info')
            ->where(['status'=>StaffEnum::STATUS_NORMAL,'work_status'=>StaffEnum::WORK_STATUS_AFOOT,'is_staff'=>1])
            ->order('id desc')
            ->select()
            ->toArray();

        //订单服务ID
        $goodsId = OrderGoods::where(['order_id'=>$params['id']])->value('goods_id');

        foreach ($lists as $key=>$item) {
            //判断师傅是否有该服务
            if (!in_array($goodsId,$item['goods_id'])) {
                unset($lists[$key]);
            }

            //判断师傅是否满足距离要求
            //获取师傅服务范围 单位：公里
            $serviceDistance = ConfigService::get('transaction', 'service_distance',100);
            $distance = getDistance($order['address_info']['longitude'],$order['address_info']['latitude'],$item['last_address_info']['longitude'],$item['last_address_info']['latitude']);
            if ($distance > $serviceDistance) {
                unset($lists[$key]);
            }

            //判断预约时间是否满足要求
            //判断订单预约时间内是否有其他订单
            $conflictOrder = Order::where(['staff_id'=>$item['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
                ->whereRaw('appoint_time_start between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_end between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_start <= '.$order['appoint_time_start'].' and appoint_time_end >= '.$order['appoint_time_end'])
                ->findOrEmpty();
            if (!$conflictOrder->isEmpty()) {
                unset($lists[$key]);
            }
            //判断是否在师傅忙时时间
            $staffBusyTime = StaffBusytime::field('date,time')
                ->where(['staff_id'=>$item['id']])
                ->whereTime('date', 'between', [strtotime(date("Y-m-d",$order['appoint_time_start'])), strtotime(date("Y-m-d 23:59:59",$order['appoint_time_end']))])
                ->json(['time'],true)
                ->select()
                ->toArray();
            if (!empty($staffBusyTime)) {
                $staffBusyTimeArr = [];
                foreach ($staffBusyTime as $timeItem) {
                    if (empty($timeItem['time'])) {
                        continue;
                    }
                    $staffBusyTimeArr[strtotime(date("Y-m-d",$timeItem['date']))] = $timeItem['time'];
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
                        unset($lists[$key]);
                    }
                }
            }
        }

        //如果订单已选择师傅
        $staff_ids = array_column($lists,'id');
        if (!empty($order['staff_id']) && !in_array($order['staff_id'],$staff_ids)) {
            $staff = Staff::field('id,name,goods_id,last_address_info')
                ->where(['id'=>$order['staff_id']])
                ->findOrEmpty()
                ->toArray();
            $lists = array_merge([$staff],$lists);
        }

        return $lists;
    }

    /**
     * @notes 退款信息
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/9/13 下午3:08
     */
    public function refundInfo($params)
    {
        $orderInfo = json_decode($params['order_info'],true);
        $result = [
            'order_id' => 0,
            'order_amount' => 0,
            'refund_amount' => 0,
        ];
        foreach ($orderInfo as $info) {
            switch ($info['order_category']) {
                case OrderRefundEnum::ORDER_CATEGORY_BASICS:
                    $order = Order::where(['id'=>$info['id']])
                        ->field('id,goods_price as order_amount,id as order_id')
                        ->append(['refund_amount'])
                        ->findOrEmpty()
                        ->toArray();
                    $result['order_amount'] += $order['order_amount'];
                    $result['refund_amount'] += $order['refund_amount'];
                    $result['order_id'] = $order['order_id'];
                    break;
                case OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL:
                    $order = OrderAdditional::where(['id'=>$info['id']])
                        ->field('id,amount as order_amount,order_id')
                        ->append(['refund_amount'])
                        ->findOrEmpty()
                        ->toArray();
                    $result['order_amount'] += $order['order_amount'];
                    $result['refund_amount'] += $order['refund_amount'];
                    $result['order_id'] = $order['order_id'];
                    break;
                case OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE:
                    $order = OrderDifferencePrice::where(['id'=>$info['id']])
                        ->field('id,amount as order_amount,order_id')
                        ->append(['refund_amount'])
                        ->findOrEmpty()
                        ->toArray();
                    $result['order_amount'] += $order['order_amount'];
                    $result['refund_amount'] += $order['refund_amount'];
                    $result['order_id'] = $order['order_id'];
                    break;
            }
        }

        $result['settlement_status'] = Order::where(['id'=>$result['order_id']])->value('settlement_status');

        return $result;
    }

    /**
     * @notes 退款
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/13 下午5:09
     */
    public function refund($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $basicsOrderId = 0;
            $orderInfo = json_decode($params['order_info'],true);
            $refundAmount = $params['refund_amount'];
            foreach ($orderInfo as $info) {
                switch ($info['order_category']) {
                    case OrderRefundEnum::ORDER_CATEGORY_BASICS:
                        $order = order::where(['id'=>$info['id']])->append(['refund_amount'])->findOrEmpty();
                        $refundOrder = $order->toArray();
                        $refundOrder['order_amount'] = $refundOrder['goods_price'];
                        $basicsOrderId = $info['id'];
                        break;
                    case OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL:
                        $order = OrderAdditional::where(['id'=>$info['id']])->append(['refund_amount'])->findOrEmpty();
                        $refundOrder = $order->toArray();
                        $refundOrder['order_amount'] = $refundOrder['amount'];
                        $refundOrder['order_terminal'] = $refundOrder['terminal'];
                        $basicsOrderId = $refundOrder['order_id'];
                        break;
                    case OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE:
                        $order = OrderDifferencePrice::where(['id'=>$info['id']])->append(['refund_amount'])->findOrEmpty();
                        $refundOrder = $order->toArray();
                        $refundOrder['order_amount'] = $refundOrder['amount'];
                        $refundOrder['order_terminal'] = $refundOrder['terminal'];
                        $basicsOrderId = $refundOrder['order_id'];
                        break;
                }
                $refundAmount = !empty($params['is_all_return']) ? $refundOrder['order_amount'] - $refundOrder['refund_amount'] : $refundAmount;
                $refundStatus = ($refundOrder['refund_amount'] + $refundAmount) >= $refundOrder['order_amount'] ? OrderEnum::REFUND_STATUS_ALL : OrderEnum::REFUND_STATUS_PART;

                //已支付订单 退款
                (new RefundLogic())->refund($refundOrder,$refundAmount,OrderRefundEnum::TYPE_ADMIN,$params['admin_id'],$params['refund_way'],$info['order_category']);

                //更新订单信息
                $order->refund_status = $refundStatus;
                $order->save();
            }

            //获取基础订单信息
            $basicsOrder = order::findOrEmpty($basicsOrderId);
            //取消订单
            if (!empty($params['is_cancel_order'])) {
                self::cancel(['id'=>$basicsOrder->id,'admin_id'=>$params['admin_id']]);
            }

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_ORDER_REFUND,$basicsOrder->id,$params['admin_id']);

            // 服务退款通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::ORDER_REFUND_NOTICE,
                'params' => [
                    'order_id' => $basicsOrder->id,
                    'user_id' => $basicsOrder->user_id,
                    'refund_amount' => $params['refund_amount'],
                    'mobile' => $basicsOrder->mobile
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 接取订单
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:25
     */
    public function acceptOrder($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::findOrEmpty($params['id']);
            //更新订单信息
            $order->order_sub_status = OrderEnum::ORDER_SUB_STATUS_RECEIVED;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_ACCEPT_ORDER,$params['id'],$params['admin_id']);

            // 订单接单通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::ACCEPT_ORDER_NOTICE,
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'mobile' => $order['mobile']
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 师傅出发
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:25
     */
    public function staffSetout($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::findOrEmpty($params['id']);
            //更新订单信息
            $order->order_sub_status = OrderEnum::ORDER_SUB_STATUS_SET_OUT;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_STAFF_SETOUT,$params['id'],$params['admin_id']);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 师傅到达
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:25
     */
    public function staffArrive($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::findOrEmpty($params['id']);
            //更新订单信息
            $order->order_sub_status = OrderEnum::ORDER_SUB_STATUS_ARRIVE;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_STAFF_ARRIVE,$params['id'],$params['admin_id']);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 开始服务
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:25
     */
    public function startService($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::findOrEmpty($params['id']);
            //更新订单信息
            $order->order_status = OrderEnum::ORDER_STATUS_SERVICE;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_STAFF_SERVICE,$params['id'],$params['admin_id']);

            // 开始服务通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::START_SERVICE_NOTICE,
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'mobile' => $order['mobile']
                ]
            ]);

            // 开始服务通知 - 通知师傅
            event('Notice', [
                'scene_id' =>  NoticeEnum::START_SERVICE_NOTICE_STAFF,
                'params' => [
                    'order_id' => $order['id'],
                    'staff_id' => $order['staff_id'],
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 服务完成
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:25
     */
    public function finish($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $order = order::findOrEmpty($params['id']);
            //更新订单信息
            $order->order_status = OrderEnum::ORDER_STATUS_FINISH;
            $order->finish_time = time();
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_ADMIN,OrderLogEnum::SHOP_ORDER_FINISH,$params['id'],$params['admin_id']);

            // 完成服务通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::FINISH_SERVICE_NOTICE,
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'mobile' => $order['mobile']
                ]
            ]);

            // 完成服务通知 - 通知师傅
            event('Notice', [
                'scene_id' =>  NoticeEnum::END_SERVICE_NOTICE_STAFF,
                'params' => [
                    'order_id' => $order['id'],
                    'staff_id' => $order['staff_id'],
                ]
            ]);

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }
}