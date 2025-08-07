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

namespace app\staffapi\lists;


use app\common\enum\OrderEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\order\Order;
use app\common\model\staff\Staff;

class OrderLists extends BaseStaffDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/18 下午3:06
     */
    public function where()
    {
        $where = [];
        if (isset($this->params['type']) && $this->params['type'] != '') {
            switch ($this->params['type']) {
                case 1://接单池
                    $goodsIds = Staff::where(['id'=>$this->staffId])->value('goods_id');
                    $orderIds = Order::alias('o')
                        ->join('order_goods og', 'o.id = og.order_id')
                        ->where(['o.order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'o.order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,'o.staff_id'=>null,'og.goods_id'=>explode(',',$goodsIds)])
                        ->column('o.id');
                    $where[] = ['id', 'in', $orderIds];
                    break;
                case 2://待接单
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    $where[] = ['order_sub_status','=',OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE];
                    $where[] = ['staff_id','=',$this->staffId];
                    break;
                case 3://进行中
                    $where[] = ['order_status','in',[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE]];
                    $where[] = ['order_sub_status','>',OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE];
                    $where[] = ['staff_id','=',$this->staffId];
                    break;
                case 4://已完成
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_FINISH];
                    $where[] = ['staff_id','=',$this->staffId];
                    break;
                case 5://已关闭
                    $where[] = ['order_status','=',OrderEnum::ORDER_STATUS_CLOSE];
                    $where[] = ['staff_id','=',$this->staffId];
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
     * @date 2024/10/18 下午3:07
     */
    public function lists(): array
    {
        $staff = Staff::where(['id'=>$this->staffId])->findOrEmpty()->toArray();

        //用st_distance_sphere函数计算两点记录，单位米
        $field = 'id,sn,order_status,pay_status,order_amount,appoint_time_start,staff_id,order_sub_status,address_info,user_remark,create_time,st_distance_sphere(point('.($staff['last_address_info']['longitude'] ?? 0).','.($staff['last_address_info']['latitude'] ?? 0).'),point(JSON_EXTRACT(address_info, "$.longitude"), JSON_EXTRACT(address_info, "$.latitude"))) as distance';
        $lists = Order::field($field)
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            }])
            ->where($this->where())
            ->append(['appoint_time_day','appoint_time_slot','order_status_desc','staff_order_btn'])
            ->limit($this->limitOffset, $this->limitLength)
            ->order(['distance'=>'asc','id'=>'desc'])
            ->select()
            ->toArray();
        foreach ($lists as &$item) {
            $item['distance'] = $item['distance'] >= 1000 ? round($item['distance']/1000,2).'km' : round($item['distance'],2).'m';
        }

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/18 下午3:07
     */
    public function count(): int
    {
        return Order::where($this->where())->count();
    }

    /**
     * @notes 统计数据
     * @return array
     * @author ljj
     * @date 2024/11/8 上午10:52
     */
    public function extend()
    {
        $goodsIds = Staff::where(['id'=>$this->staffId])->value('goods_id');
        $orderIds = Order::alias('o')
            ->join('order_goods og', 'o.id = og.order_id')
            ->where(['o.order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'o.order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,'o.staff_id'=>null,'og.goods_id'=>explode(',',$goodsIds)])
            ->column('o.id');

        return [
            'grab_num' => Order::where(['id'=>$orderIds])->count(),
            'receive_num' => Order::where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE,'staff_id'=>$this->staffId])->count(),
            'service_num' => Order::where(['order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE],'staff_id'=>$this->staffId])->count(),
        ];
    }
}