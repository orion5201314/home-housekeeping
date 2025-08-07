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

namespace app\common\model\order;


use app\common\enum\OrderRefundEnum;
use app\common\model\auth\Admin;
use app\common\model\BaseModel;
use app\common\model\user\User;

class OrderRefundLog extends BaseModel
{
    /**
     * @notes 退款状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/9/9 4:59 下午
     */
    public function getRefundStatusDescAttr($value,$data)
    {
        return OrderRefundEnum::getStatusDesc($data['refund_status']);
    }

    /**
     * @notes 操作人
     * @param $value
     * @param $data
     * @return mixed|string
     * @author ljj
     * @date 2022/9/9 5:59 下午
     */
    public function getOperatorDescAttr($value,$data)
    {
        switch ($data['type']) {
            case 1:
                return '系统';
            case 2:
                return Admin::where('id',$data['operator_id'])->value('name');
            case 3:
                return User::where('id',$data['operator_id'])->value('nickname');
            default:
                return '';
        }
    }

    /**
     * @notes 订单来源
     * @param $value
     * @param $data
     * @return int|mixed
     * @author ljj
     * @date 2024/11/4 下午6:28
     */
    public function getOrderTerminalAttr($value,$data)
    {
        $orderTerminal = 1;
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_BASICS) {
            $orderTerminal = Order::where(['id'=>$data['order_id']])->value('order_terminal');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL) {
            $orderTerminal = OrderAdditional::where(['id'=>$data['order_id']])->value('terminal');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE) {
            $orderTerminal = OrderDifferencePrice::where(['id'=>$data['order_id']])->value('terminal');
        }

        return $orderTerminal;
    }

    /**
     * @notes 订单支付方式
     * @param $value
     * @param $data
     * @return int|mixed
     * @author ljj
     * @date 2024/11/4 下午6:28
     */
    public function getPayWayAttr($value,$data)
    {
        $PayWay = 1;
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_BASICS) {
            $PayWay = Order::where(['id'=>$data['order_id']])->value('pay_way');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL) {
            $PayWay = OrderAdditional::where(['id'=>$data['order_id']])->value('pay_way');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE) {
            $PayWay = OrderDifferencePrice::where(['id'=>$data['order_id']])->value('pay_way');
        }

        return $PayWay;
    }

    /**
     * @notes 订单编号
     * @param $value
     * @param $data
     * @return int|mixed
     * @author ljj
     * @date 2024/11/4 下午6:28
     */
    public function getOrderSnAttr($value,$data)
    {
        $OrderSn = '';
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_BASICS) {
            $OrderSn = Order::where(['id'=>$data['order_id']])->value('sn');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL) {
            $OrderSn = OrderAdditional::where(['id'=>$data['order_id']])->value('sn');
        }
        if ($data['order_category'] == OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE) {
            $OrderSn = OrderDifferencePrice::where(['id'=>$data['order_id']])->value('sn');
        }

        return $OrderSn;
    }
}