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

namespace app\api\lists;


use app\common\enum\OrderEnum;
use app\common\model\order\Order;

class OrderLists extends BaseShopDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/2/28 9:31 上午
     */
    public function where()
    {
        $where = [];
        $where[] = ['user_id','=',$this->userId];
        if (isset($this->params['order_status']) && $this->params['order_status'] != '') {
            switch ($this->params['order_status']) {
                case 1:
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_WAIT_PAY];
                    break;
                case 2:
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    break;
                case 3:
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_SERVICE];
                    break;
                case 4:
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_FINISH];
                    break;
                case 5:
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_CLOSE];
                    break;
            }
        }

        return $where;
    }

    /**
     * @notes 订单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/28 9:31 上午
     */
    public function lists(): array
    {
        $lists = Order::field('id,sn,order_status,pay_status,order_amount,appoint_time_start,staff_id,order_sub_status,create_time,refund_status')
            ->order('id','desc')
            ->append(['appoint_time_day','appoint_time_slot','order_status_desc','refund_amount','total_refund_amount','user_order_btn','order_cancel_time'])
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            },'staff' => function($query){
                $query->field('id,name,work_image');
            }])
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 订单数量
     * @return int
     * @author ljj
     * @date 2022/2/28 9:32 上午
     */
    public function count(): int
    {
        return Order::where($this->where())->count();
    }
}