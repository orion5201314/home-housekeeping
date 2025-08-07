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

namespace app\staffapi\validate;


use app\common\enum\OrderEnum;
use app\common\enum\StaffEnum;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffBusytime;
use app\common\model\staff\StaffDeposit;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

class OrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'image_info' => 'require|array',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'image_info.require' => '请上传图片',
        'image_info.array' => '图片错误',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneGrab()
    {
        return $this->only(['id'])
            ->append('id','checkGrab');
    }

    public function sceneReceive()
    {
        return $this->only(['id'])
            ->append('id','checkReceive');
    }

    public function sceneSetout()
    {
        return $this->only(['id'])
            ->append('id','checkSetout');
    }

    public function sceneArrive()
    {
        return $this->only(['id','image_info'])
            ->append('id','checkArrive');
    }

    public function sceneStart()
    {
        return $this->only(['id'])
            ->append('id','checkStart');
    }

    public function sceneFinish()
    {
        return $this->only(['id','image_info'])
            ->append('id','checkFinish');
    }

    /**
     * @notes 校验抢单
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkGrab($value,$rule,$data)
    {
        $staff = Staff::where(['id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if ($staff['work_status'] === StaffEnum::WORK_STATUS_REST) {
            return '休息中无法抢单';
        }
        if ($staff['status'] === StaffEnum::STATUS_FROZEN) {
            return '您的账号已被冻结，无法抢单';
        }

        $order = Order::where(['id'=>$value])->with(['order_goods' => function($query){
            $query->field('id,order_id,goods_id');
        }])->findOrEmpty()->toArray();
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_sub_status'] !== OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE || !empty($order['staff_id'])) {
            return '抢单失败';
        }
        if (!in_array($order['order_goods'][0]['goods_id'],$staff['goods_id'])) {
            return '订单服务项目不在你的服务范围';
        }

        //判断师傅是否满足距离要求
        //获取师傅服务范围 单位：公里
        $serviceDistance = ConfigService::get('transaction', 'service_distance',100);
        $distance = getDistance($order['address_info']['longitude'],$order['address_info']['latitude'],$staff['last_address_info']['longitude'],$staff['last_address_info']['latitude']);
        if ($distance > $serviceDistance) {
            return '师傅当前定位距离不在服务范围内';
        }

        //是否已达到接单数量
        //可接单数量
        $ableReceiveNum = ConfigService::get('transaction', 'default_order_num',1);
        $deposit = StaffDeposit::where('amount','<',$staff['staff_deposit'])->order(['id'=>'desc'])->findOrEmpty()->toArray();
        if (!empty($deposit)) {
            $ableReceiveNum = $deposit['order_num'];
        }
        //当天已接订单数量
        $receiveOrderNum = Order::where(['staff_id'=>$staff['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
            ->whereDay('appoint_time_start', date('Y-m-d',$order['appoint_time_start']))
            ->count();
        if ($receiveOrderNum >= $ableReceiveNum) {
            return '已到达接单上限';
        }

        //判断预约时间是否满足要求
        //判断订单预约时间内是否有其他订单
        $conflictOrder = Order::where(['staff_id'=>$staff['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
            ->whereRaw('appoint_time_start between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_end between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_start <= '.$order['appoint_time_start'].' and appoint_time_end >= '.$order['appoint_time_end'])
            ->findOrEmpty();
        if (!$conflictOrder->isEmpty()) {
            return '订单预约时间内已接取其他订单';
        }
        //判断是否在师傅忙时时间
        $staffBusyTime = StaffBusytime::field('date,time')
            ->where(['staff_id'=>$staff['id']])
            ->whereTime('date', 'between', [strtotime(date("Y-m-d",$order['appoint_time_start'])), strtotime(date("Y-m-d 23:59:59",$order['appoint_time_end']))])
            ->json(['time'],true)
            ->select()
            ->toArray();
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
                    return '师傅服务时间不满足订单预约时间';
                }
            }
        }

        return true;
    }

    /**
     * @notes 校验接单
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkReceive($value,$rule,$data)
    {
        $staff = Staff::where(['id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if ($staff['work_status'] === StaffEnum::WORK_STATUS_REST) {
            return '休息中无法接单';
        }
        if ($staff['status'] === StaffEnum::STATUS_FROZEN) {
            return '您的账号已被冻结，无法接单';
        }

        $order = Order::where(['id'=>$value])->with(['order_goods' => function($query){
            $query->field('id,order_id,goods_id');
        }])->findOrEmpty()->toArray();
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_sub_status'] !== OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE || $order['staff_id'] != $data['staff_id']) {
            return '接单失败';
        }
        if (!in_array($order['order_goods'][0]['goods_id'],$staff['goods_id'])) {
            return '订单服务项目不在你的服务范围';
        }

        //判断师傅是否满足距离要求
        //获取师傅服务范围 单位：公里
        $serviceDistance = ConfigService::get('transaction', 'service_distance',100);
        $distance = getDistance($order['address_info']['longitude'],$order['address_info']['latitude'],$staff['last_address_info']['longitude'],$staff['last_address_info']['latitude']);
        if ($distance > $serviceDistance) {
            return '师傅当前定位距离不在服务范围内';
        }

        //是否已达到接单数量
        //可接单数量
        $ableReceiveNum = ConfigService::get('transaction', 'default_order_num',1);
        $deposit = StaffDeposit::where('amount','<',$staff['staff_deposit'])->order(['id'=>'desc'])->findOrEmpty()->toArray();
        if (!empty($deposit)) {
            $ableReceiveNum = $deposit['order_num'];
        }
        //当天已接订单数量
        $receiveOrderNum = Order::where(['staff_id'=>$staff['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
            ->whereDay('appoint_time_start', date('Y-m-d',$order['appoint_time_start']))
            ->count();
        if ($receiveOrderNum >= $ableReceiveNum) {
            return '已到达接单上限';
        }

        //判断预约时间是否满足要求
        //判断订单预约时间内是否有其他订单
        $conflictOrder = Order::where(['staff_id'=>$staff['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
            ->whereRaw('appoint_time_start between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_end between '.$order['appoint_time_start'].' and '.$order['appoint_time_end'].' or appoint_time_start <= '.$order['appoint_time_start'].' and appoint_time_end >= '.$order['appoint_time_end'])
            ->findOrEmpty();
        if (!$conflictOrder->isEmpty()) {
            return '订单预约时间内已接取其他订单';
        }
        //判断是否在师傅忙时时间
        $staffBusyTime = StaffBusytime::field('date,time')
            ->where(['staff_id'=>$staff['id']])
            ->whereTime('date', 'between', [strtotime(date("Y-m-d",$order['appoint_time_start'])), strtotime(date("Y-m-d 23:59:59",$order['appoint_time_end']))])
            ->json(['time'],true)
            ->select()
            ->toArray();
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
                    return '师傅服务时间不满足订单预约时间';
                }
            }
        }

        return true;
    }

    /**
     * @notes 校验出发
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkSetout($value,$rule,$data)
    {
        $order = Order::where(['id'=>$value,'staff_id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if (empty($order)) {
            return '订单错误';
        }
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_sub_status'] !== OrderEnum::ORDER_SUB_STATUS_RECEIVED) {
            return '订单状态错误';
        }

        return true;
    }

    /**
     * @notes 校验到达
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkArrive($value,$rule,$data)
    {
        $order = Order::where(['id'=>$value,'staff_id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if (empty($order)) {
            return '订单错误';
        }
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_sub_status'] !== OrderEnum::ORDER_SUB_STATUS_SET_OUT) {
            return '订单状态错误';
        }

        return true;
    }

    /**
     * @notes 校验开始服务
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkStart($value,$rule,$data)
    {
        $order = Order::where(['id'=>$value,'staff_id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if (empty($order)) {
            return '订单错误';
        }
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_sub_status'] !== OrderEnum::ORDER_SUB_STATUS_ARRIVE) {
            return '订单状态错误';
        }

        return true;
    }

    /**
     * @notes 校验完成服务
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/18 下午5:26
     */
    public function checkFinish($value,$rule,$data)
    {
        $order = Order::where(['id'=>$value,'staff_id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if (empty($order)) {
            return '订单错误';
        }
        if ($order['order_status'] !== OrderEnum::ORDER_STATUS_SERVICE) {
            return '订单状态错误';
        }

        return true;
    }
}