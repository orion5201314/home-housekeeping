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

namespace app\adminapi\validate\order;


use app\common\enum\OrderEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\StaffEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\order\OrderGoods;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffBusytime;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;
use think\facade\Validate;

class OrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'order_info' => 'require',
        'staff_id' => 'require',
        'refund_way' => 'require|in:1,2',
        'refund_amount' => 'require|float|gt:0|checkRefundAmount',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'order_info.require' => '参数缺失',
        'staff_id.require' => '请选择师傅',
        'refund_way.require' => '请选择退款方式',
        'refund_way.in' => '退款方式错误',
        'refund_amount.require' => '请输入退款金额',
        'refund_amount.float' => '退款金额值错误',
        'refund_amount.gt' => '退款金额必须大于0',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneCancel()
    {
        return $this->only(['id'])
            ->append('id','checkCancel');
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneRemark()
    {
        return $this->only(['id']);
    }

    public function sceneRemarkDetail()
    {
        return $this->only(['id']);
    }

    public function sceneDispatchStaff()
    {
        return $this->only(['id','staff_id'])
            ->append('id','checkDispatchStaff');
    }

    public function sceneRefund()
    {
        return $this->only(['order_info','refund_way','refund_amount'])
            ->append('order_info','checkRefund');
    }

    public function sceneAcceptOrder()
    {
        return $this->only(['id'])
            ->append('id','checkAcceptOrder');
    }

    public function sceneStaffSetout()
    {
        return $this->only(['id'])
            ->append('id','checkStaffSetout');
    }

    public function sceneStaffArrive()
    {
        return $this->only(['id'])
            ->append('id','checkStaffArrive');
    }

    public function sceneStartService()
    {
        return $this->only(['id'])
            ->append('id','checkStartService');
    }

    public function sceneFinish()
    {
        return $this->only(['id'])
            ->append('id','checkFinish');
    }


    /**
     * @notes 检验订单id
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/11 11:46 上午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检验订单能否取消
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/11 3:08 下午
     */
    public function checkCancel($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] > OrderEnum::ORDER_STATUS_SERVICE) {
            return '订单不允许取消';
        }
//        if ($result['pay_status'] == PayEnum::ISPAID) {
//            $validate = Validate::rule([
//                'refund_way|退款方式' => 'require|in:1,2',
//                'refund_amount|退款金额' => 'require|float|gt:0',
//            ]);
//            if (!$validate->check($data)) {
//                return $validate->getError();
//            }
//
//            $order = Order::where('id',$data['id'])->append(['refund_amount'])->findOrEmpty()->toArray();
//            if ($order['refund_amount'] + $data['refund_amount'] > $order['order_amount']) {
//                return '订单剩余可退款金额不足';
//            }
//        }
        return true;
    }

    /**
     * @notes 检验订单能否删除
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/11 4:19 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_CLOSE) {
            return '订单不允许删除';
        }
        return true;
    }

    /**
     * @notes 校验订单指派师傅
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/8/29 5:19 下午
     */
    public function checkDispatchStaff($value,$rule,$data)
    {
        //订单信息
        $order = Order::where('id',$data['id'])->findOrEmpty()->toArray();
        if ($order['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || !in_array($order['order_sub_status'],[OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE])) {
            return '订单状态错误';
        }

        //师傅信息
        $staff = Staff::where(['id'=>$data['staff_id']])->findOrEmpty()->toArray();

        //判断师傅是否有该服务
        $goodsId = OrderGoods::where(['order_id'=>$data['id']])->value('goods_id');
        if (!in_array($goodsId,$staff['goods_id'])) {
            return '师傅不存在该服务技能';
        }

        //判断师傅是否满足距离要求
        //获取师傅服务范围 单位：公里
        $serviceDistance = ConfigService::get('transaction', 'service_distance',100);
        $distance = getDistance($order['address_info']['longitude'],$order['address_info']['latitude'],$staff['last_address_info']['longitude'],$staff['last_address_info']['latitude']);
        if ($distance > $serviceDistance) {
            return '师傅当前定位距离不在服务范围内';
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
     * @notes 校验退款金额
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/13 下午4:27
     */
    public function checkRefundAmount($value,$rule,$data)
    {
        //全部退款不校验退款金额
        if (!empty($data['is_all_return'])) {
            return true;
        }
        $order = [];
        $orderInfo = json_decode($data['order_info'],true);
        $orderCategory = $orderInfo[0]['order_category'];
        $id = $orderInfo[0]['id'];
        switch ($orderCategory) {
            case OrderRefundEnum::ORDER_CATEGORY_BASICS:
                $order = Order::where('id',$id)->append(['refund_amount'])->findOrEmpty()->toArray();
                $order['order_amount'] = $order['goods_price'];
                break;
            case OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL:
                $order = OrderAdditional::where(['id'=>$id])->append(['refund_amount'])->findOrEmpty()->toArray();
                $order['order_amount'] = $order['amount'];
                break;
            case OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE:
                $order = OrderDifferencePrice::where(['id'=>$id])->append(['refund_amount'])->findOrEmpty()->toArray();
                $order['order_amount'] = $order['amount'];
                break;
        }
        if (empty($order)) {
            return '订单错误';
        }

        if ($order['refund_amount'] + $data['refund_amount'] > $order['order_amount']) {
            return '订单剩余可退款金额不足';
        }
        return true;
    }

    /**
     * @notes 校验退款
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/13 下午5:07
     */
    public function checkRefund($value,$rule,$data)
    {
        $orderInfo = json_decode($data['order_info'],true);
        //校验order_info参数
        if (empty($orderInfo)) {
            return '参数错误';
        }
        foreach ($orderInfo as $item) {
            if (empty($item['id']) || empty($item['order_category'])) {
                return '参数错误';
            }
        }

        $basiceOrderId = 0;
        switch ($orderInfo[0]['order_category']) {
            case OrderRefundEnum::ORDER_CATEGORY_BASICS:
                $basiceOrderId = $orderInfo[0]['id'];
                break;
            case OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL:
                $basiceOrderId = OrderAdditional::where(['id'=>$orderInfo[0]['id']])->value('order_id');
                break;
            case OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE:
                $basiceOrderId = OrderDifferencePrice::where(['id'=>$orderInfo[0]['id']])->value('order_id');
                break;
        }

        $result = Order::where('id',$basiceOrderId)->findOrEmpty()->toArray();
        if (empty($result)) {
            return '订单错误';
        }
        if ($result['pay_status'] != PayEnum::ISPAID) {
            return '订单未支付，无法退款';
        }
        if (empty($data['is_cancel_order']) && !in_array($result['order_status'],[OrderEnum::ORDER_STATUS_FINISH,OrderEnum::ORDER_STATUS_CLOSE])) {
            return '订单状态错误';
        }
        return true;
    }

    /**
     * @notes 校验接取订单
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:21
     */
    public function checkAcceptOrder($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if (empty($result['staff_id']) || $result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || $result['order_sub_status'] != OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE) {
            return '订单状态错误';
        }
        return true;
    }

    /**
     * @notes 校验师傅出发
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:21
     */
    public function checkStaffSetout($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || $result['order_sub_status'] != OrderEnum::ORDER_SUB_STATUS_RECEIVED) {
            return '订单状态错误';
        }
        return true;
    }

    /**
     * @notes 校验师傅到达
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:21
     */
    public function checkStaffArrive($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || $result['order_sub_status'] != OrderEnum::ORDER_SUB_STATUS_SET_OUT) {
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
     * @date 2024/9/14 下午4:21
     */
    public function checkStartService($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_WAIT_SERVICE || $result['order_sub_status'] != OrderEnum::ORDER_SUB_STATUS_ARRIVE) {
            return '订单状态错误';
        }
        return true;
    }

    /**
     * @notes 校验服务完成
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/14 下午4:21
     */
    public function checkFinish($value,$rule,$data)
    {
        $result = Order::where('id',$value)->findOrEmpty()->toArray();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_SERVICE) {
            return '订单状态错误';
        }
        return true;
    }
}
