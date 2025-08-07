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

namespace app\staffapi\logic;


use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;

class OrderBtnLogic extends BaseLogic
{
    /**
     * @notes 师傅端订单按钮
     * @param $order
     * @return array
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getStaffOrderBtn($order)
    {
        return [
            'grab_btn'                   => self::getGrabBtn($order),
            'receive_btn'                => self::getReceiveBtn($order),
            'setout_btn'                 => self::getSetoutBtn($order),
            'arrive_btn'                 => self::getArriveBtn($order),
            'start_btn'                  => self::getStartBtn($order),
            'finish_btn'                 => self::getFinishBtn($order),
        ];
    }

    /**
     * @notes 抢单按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午4:59
     */
    public static function getGrabBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE && empty($order['staff_id'])) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 接单按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午5:01
     */
    public static function getReceiveBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE && !empty($order['staff_id'])) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 出发按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午5:01
     */
    public static function getSetoutBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_RECEIVED) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 到达按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午5:02
     */
    public static function getArriveBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_SET_OUT) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 开始服务按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午5:02
     */
    public static function getStartBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_ARRIVE) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 服务完成按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/18 下午5:03
     */
    public static function getFinishBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_SERVICE) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }
}