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

namespace app\api\logic;


use app\common\enum\OrderEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\GoodsAdditional;
use app\common\model\order\OrderGoods;

class OrderBtnLogic extends BaseLogic
{
    /**
     * @notes 用户端订单按钮
     * @param $order
     * @return array
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getUserOrderBtn($order)
    {
        return [
            'cancel_btn'                => self::getCancelBtn($order),
            'pay_btn'                   => self::getPayBtn($order),
            'difference_price_btn'      => self::getDifferencePriceBtn($order),
            'additional_btn'            => self::getAdditionalBtn($order),
            'comment_btn'               => self::getCommentBtn($order),
            'look_comment_btn'          => self::getLookCommentBtn($order),
        ];
    }

    /**
     * @notes 取消订单按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getCancelBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_PAY) {
            return YesNoEnum::YES;
        }
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && $order['order_sub_status'] == OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 支付按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getPayBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_PAY) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 补差价按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getDifferencePriceBtn($order)
    {
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE || $order['order_status'] == OrderEnum::ORDER_STATUS_SERVICE) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 加项按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getAdditionalBtn($order)
    {
        if (($order['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE && in_array($order['order_sub_status'],[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE])) || $order['order_status'] == OrderEnum::ORDER_STATUS_SERVICE) {
            $goodsId = OrderGoods::where(['id'=>$order['id']])->value('goods_id');
            $additional = GoodsAdditional::where(['goods_id'=>$goodsId,'status'=>1])->count();
            if ($additional <= 0) {
                return YesNoEnum::NO;
            }
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 去评价按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/9/29 下午5:25
     */
    public static function getCommentBtn($order)
    {
        $isComment = OrderGoods::where(['order_id'=>$order['id']])->value('is_comment');
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_FINISH && $isComment == YesNoEnum::NO) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }

    /**
     * @notes 查看评价按钮
     * @param $order
     * @return int
     * @author ljj
     * @date 2024/10/9 下午4:31
     */
    public static function getLookCommentBtn($order)
    {
        $isComment = OrderGoods::where(['order_id'=>$order['id']])->value('is_comment');
        if ($order['order_status'] == OrderEnum::ORDER_STATUS_FINISH && $isComment == YesNoEnum::YES) {
            return YesNoEnum::YES;
        }

        return YesNoEnum::NO;
    }
}