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

namespace app\staffapi\logic;


use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\logic\BaseLogic;
use app\common\logic\OrderLogLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderCheckin;
use app\common\model\staff\Staff;
use think\facade\Db;

class OrderLogic extends BaseLogic
{
    /**
     * @notes 师傅订单列表
     * @param $staffId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/14 下午4:15
     */
    public function staffOrderLists($staffId)
    {
        $where[] = ['staff_id','=',$staffId];
        $where[] = ['order_status','in',[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE]];

        $lists = Order::field('id,order_status,order_sub_status,order_amount,appoint_time_start,create_time,order_type')
            ->append(['appoint_time_day','appoint_time_slot','order_status_desc','order_type_desc'])
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            }])
            ->where($where)
            ->whereTime('appoint_time_start', 'between', [strtotime(date("Y-m-d")), strtotime(date("Y-m-d 23:59:59",strtotime("+2 day")))])
            ->order('appoint_time_start','asc')
            ->select()
            ->toArray();

        //统计数据
        $extend = [
            'today_num' => Order::where($where)->whereDay('appoint_time_start')->count(),
            'tomorrow_num' => Order::where($where)->whereDay('appoint_time_start', date("Y-m-d",strtotime("+1 day")))->count(),
            'after_tomorrow_num' => Order::where($where)->whereDay('appoint_time_start', date("Y-m-d",strtotime("+2 day")))->count(),
        ];

        return ['lists'=>$lists,'extend'=>$extend];
    }

    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/10/18 下午5:23
     */
    public function detail($id,$staffId)
    {
        $result = Order::where('id',$id)
            ->append(['appoint_time_day','appoint_time_slot','order_status_desc','pay_way_desc','address_info','refund_amount','total_refund_amount','staff_order_btn','settlement_status_desc'])
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            },'order_additional' => function($query){
                $query->field('id,order_id,additional_snap')->json(['additional_snap'],true);
            },'order_checkin' => function($query){
                $query->field('order_id,image_info,order_status,order_sub_status,address_info,create_time')->append(['order_status_desc']);
            }])
            ->findOrEmpty()
            ->toArray();

        if(!empty($result)) {
            //手机号脱敏
            if ($result['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE) {
                $result['mobile'] = substr_replace($result['mobile'], '****', 3, 6);
            }

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

            //师傅端处理订单步骤
            $result['order_steps_index'] = 0;
            $result['order_steps_desc'] = '用户支付订单';
            switch ($result['order_status']) {
                case OrderEnum::ORDER_STATUS_WAIT_SERVICE:
                    switch ($result['order_sub_status']) {
                        case OrderEnum::ORDER_SUB_STATUS_RECEIVED:
                            $result['order_steps_index'] = 1;
                            $result['order_steps_desc'] = '订单已被师傅接取';
                            break;
                        case OrderEnum::ORDER_SUB_STATUS_SET_OUT:
                            $result['order_steps_index'] = 2;
                            $result['order_steps_desc'] = '师傅已出发';
                            break;
                        case OrderEnum::ORDER_SUB_STATUS_ARRIVE:
                            $result['order_steps_index'] = 3;
                            $result['order_steps_desc'] = '师傅已到达';
                            break;
                    }
                    break;
                case OrderEnum::ORDER_STATUS_SERVICE:
                    $result['order_steps_index'] = 4;
                    $result['order_steps_desc'] = '师傅已开始服务';
                    break;
                case OrderEnum::ORDER_STATUS_FINISH:
                    $result['order_steps_index'] = 5;
                    $result['order_steps_desc'] = '师傅已完成服务';
                    break;
                case OrderEnum::ORDER_STATUS_CLOSE:
                    $result['order_steps_index'] = 0;
                    $result['order_steps_desc'] = '订单已关闭';
                    break;
            }

            //获取订单距离
            $staff = Staff::where(['id'=>$staffId])->findOrEmpty()->toArray();
            $result['distance'] = getDistance($result['address_info']['longitude'],$result['address_info']['latitude'],$staff['last_address_info']['longitude'],$staff['last_address_info']['latitude'],1);
            if ($result['distance'] >= 1000) {
                $result['distance'] = round($result['distance'] / 1000,2).'km';
            } else {
                $result['distance'] = $result['distance'].'m';
            }

            //处理签到数据
            $orderCheckinInfo = [];
            foreach ($result['order_checkin'] as $checkin) {
                $checkin['time_desc'] = date('H:i',strtotime($checkin['create_time']));
                $orderCheckinInfo[date('Y年m月d日',strtotime($checkin['create_time']))][] = $checkin;
            }
            $result['order_checkin'] = $orderCheckinInfo;
        }

        return $result;
    }

    /**
     * @notes 抢单
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function grab($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            Order::update(['order_sub_status' => OrderEnum::ORDER_SUB_STATUS_RECEIVED,'staff_id'=>$params['staff_id']],['id'=>$params['id']]);

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_GRAB_ORDER,$params['id'],$params['staff_id']);

            // 订单接单通知 - 通知用户
            $order = order::findOrEmpty($params['id']);
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
     * @notes 接单
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function receive($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            Order::update(['order_sub_status' => OrderEnum::ORDER_SUB_STATUS_RECEIVED],['id'=>$params['id']]);

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_RECEIVE_ORDER,$params['id'],$params['staff_id']);

            // 订单接单通知 - 通知用户
            $order = order::findOrEmpty($params['id']);
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
     * @notes 出发
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function setout($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            Order::update(['order_sub_status' => OrderEnum::ORDER_SUB_STATUS_SET_OUT],['id'=>$params['id']]);

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_SETOUT_ORDER,$params['id'],$params['staff_id']);

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
     * @notes 到达
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function arrive($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            $order = Order::find($params['id']);
            $order->order_sub_status = OrderEnum::ORDER_SUB_STATUS_ARRIVE;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_ARRIVE_ORDER,$params['id'],$params['staff_id']);

            //添加签到记录
            $staff = Staff::where(['id'=>$params['staff_id']])->findOrEmpty()->toArray();
            OrderCheckin::create([
                'order_id' => $params['id'],
                'staff_id' => $params['staff_id'],
                'image_info' => $params['image_info'],
                'order_status' => $order->order_status,
                'order_sub_status' => OrderEnum::ORDER_SUB_STATUS_ARRIVE,
                'address_info' => json_encode($staff['last_address_info']),
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
     * @notes 开始服务
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function start($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            $order = Order::find($params['id']);
            $order->order_status = OrderEnum::ORDER_STATUS_SERVICE;
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_START_ORDER,$params['id'],$params['staff_id']);

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
     * @date 2024/10/18 下午5:31
     */
    public function finish($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //更新订单状态
            $order = Order::find($params['id']);
            $order->order_status = OrderEnum::ORDER_STATUS_FINISH;
            $order->finish_time = time();
            $order->save();

            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_STAFF,OrderLogEnum::STAFF_FINISH_ORDER,$params['id'],$params['staff_id']);

            //添加签到记录
            $staff = Staff::where(['id'=>$params['staff_id']])->findOrEmpty()->toArray();
            OrderCheckin::create([
                'order_id' => $params['id'],
                'staff_id' => $params['staff_id'],
                'image_info' => $params['image_info'],
                'order_status' => $order->order_status,
                'order_sub_status' => OrderEnum::ORDER_SUB_STATUS_ARRIVE,
                'address_info' => json_encode($staff['last_address_info']),
            ]);

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