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

namespace app\adminapi\lists\order;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\order\OrderRefund;

class OrderOperationRefundLists extends BaseAdminDataLists
{
    public $count = 0;

    /**
     * @notes 退款操作列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/11/1 上午10:07
     */
    public function lists(): array
    {
        $orderId = $this->params['id'];

        //获取订单列表
        $order = Order::where(['id'=>$orderId])
            ->field('id,sn,goods_price as actual_price,pay_time,IFNULL(null,"基础") as order_category_desc,IFNULL(null,1) as order_category')
            ->append(['refund_amount'])
            ->select()
            ->toArray();

        //获取加项列表
        $orderAdditional = OrderAdditional::where(['order_id'=>$orderId,'pay_status'=>PayEnum::ISPAID])
            ->field('id,sn,amount as actual_price,pay_time,IFNULL(null,"加项") as order_category_desc,IFNULL(null,2) as order_category')
            ->append(['refund_amount'])
            ->select()
            ->toArray();

        //获取补差价列表
        $orderDifference = OrderDifferencePrice::where(['order_id'=>$orderId,'pay_status'=>PayEnum::ISPAID])
            ->field('id,sn,amount as actual_price,pay_time,IFNULL(null,"补差价") as order_category_desc,IFNULL(null,3) as order_category')
            ->append(['refund_amount'])
            ->select()
            ->toArray();

        $lists = array_merge($order,$orderAdditional,$orderDifference);
        $this->count = count($lists);

        //排序
        $sort = array_column($lists,'pay_time');
        array_multisort($sort,SORT_ASC,$lists);

        //分页
        $lists = array_slice($lists, $this->limitOffset, $this->limitLength);

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/11/1 上午10:06
     */
    public function count(): int
    {
        return $this->count;
    }
}