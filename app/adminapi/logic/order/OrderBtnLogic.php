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

namespace app\adminapi\logic\order;


use app\common\enum\OrderEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;

class OrderBtnLogic extends BaseLogic
{
    /**
     * @notes 后台订单按钮
     * @param $order
     * @return array
     * @author ljj
     * @date 2024/9/12 下午3:49
     */
    public static function getAdminOrderBtn($order)
    {
        return [
            'cancel_btn'                => self::getCancelBtn($order),
            'remark_btn'                => self::getRemarkBtn($order),
            'assign_staff_btn'          => self::getAssignStaffBtn($order),
            'replace_staff_btn'         => self::getReplaceStaffBtn($order),
            'accept_orders_btn'         => self::getAcceptOrdersBtn($order),
            'staff_setout_btn'          => self::getStaffSetoutBtn($order),
            'staff_arrive_btn'          => self::getStaffArriveBtn($order),
            'start_service_btn'         => self::getStartServiceBtn($order),
            'finish_btn'                => self::getFinishBtn($order),
            'refund_btn'                => self::getRefundBtn($order),
        ];
    }

    /**
     * @notes 取消订单按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getCancelBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] < OrderEnum::ORDER_STATUS_FINISH) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 备注按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:51
     */
    public static function getRemarkBtn($order)
    {
        return YesNoEnum::YES;
    }

    /**
     * @notes 分配师傅按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getAssignStaffBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && empty($order['staff_id'])) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 更换师傅按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getReplaceStaffBtn($order)
    {
        $btn = YesNoEnum::NO;
        if (!empty($order['staff_id']) && $order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE &&  in_array($order['order_sub_status'],[OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,OrderEnum::ORDER_SUB_STATUS_RECEIVED])) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 订单接取按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getAcceptOrdersBtn($order)
    {
        $btn = YesNoEnum::NO;
        if (!empty($order['staff_id']) && $order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE &&  $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 师傅出发按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getStaffSetoutBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE &&  $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_RECEIVED) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 师傅到达按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getStaffArriveBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE &&  $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_SET_OUT) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 开始服务按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getStartServiceBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE &&  $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_ARRIVE) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 服务完成按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getFinishBtn($order)
    {
        $btn = YesNoEnum::NO;
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_SERVICE) {
            $btn = YesNoEnum::YES;
        }

        return $btn;
    }

    /**
     * @notes 退款按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/12 下午3:50
     */
    public static function getRefundBtn($order)
    {
        $btn = YesNoEnum::NO;
        if (in_array($order['order_status'],[OrderEnum::ORDER_STATUS_FINISH,OrderEnum::ORDER_STATUS_CLOSE]) && $order['pay_status'] == PayEnum::ISPAID) {
            //基础订单
            $basicsOrder = Order::where(['id'=>$order['id'],'refund_status'=>[OrderEnum::REFUND_STATUS_NOT,OrderEnum::REFUND_STATUS_PART]])->findOrEmpty();
            //获取加项订单
            $additionalOrderCount = OrderAdditional::where(['order_id'=>$order['id'],'pay_status'=>PayEnum::ISPAID,'refund_status'=>[OrderEnum::REFUND_STATUS_NOT,OrderEnum::REFUND_STATUS_PART]])->count();
            //获取补差价订单
            $differenceOrder = OrderDifferencePrice::where(['order_id'=>$order['id'],'pay_status'=>PayEnum::ISPAID,'refund_status'=>[OrderEnum::REFUND_STATUS_NOT,OrderEnum::REFUND_STATUS_PART]])->count();
            if (!$basicsOrder->isEmpty() || $additionalOrderCount > 0 || $differenceOrder > 0) {
                $btn = YesNoEnum::YES;
            }
        }

        return $btn;
    }
}