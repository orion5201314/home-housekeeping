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
use app\common\enum\OrderEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\order\Order;

class OrderLists extends BaseAdminDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/2/10 6:14 下午
     */
    public function where()
    {
        $where = [];
        $params = $this->params;
        $timeType = 'create_time';
        if (isset($params['order_info']) && $params['order_info'] != '') {
            $where[] = ['o.sn','like','%'.$params['order_info'].'%'];
        }
        if (isset($params['user_info']) && $params['user_info'] != '') {
            $where[] = ['u.sn|u.nickname|u.mobile','like','%'.$params['user_info'].'%'];
        }
        if (isset($params['goods_info']) && $params['goods_info'] != '') {
            $where[] = ['g.name','like','%'.$params['goods_info'].'%'];
        }
        if (isset($params['staff_info']) && $params['staff_info'] != '') {
            $where[] = ['s.sn|s.name|s.mobile','like','%'.$params['staff_info'].'%'];
        }
        if (isset($params['order_type']) && $params['order_type'] != '') {
            $where[] = ['o.order_type','=',$params['order_type']];
        }
        if (isset($params['pay_way']) && $params['pay_way'] != '') {
            $where[] = ['o.pay_way','=',$params['pay_way']];
        }
        if (isset($params['time_type']) && $params['time_type'] != '') {
            switch ($params['order_status']) {
                case 1://下单时间
                    $timeType = 'create_time';
                    break;
                case 2://支付时间
                    $timeType = 'pay_time';
                    break;
                case 3://完成时间
                    $timeType = 'finish_time';
                    break;
            }
        }
        if (isset($params['start_time']) && $params['start_time'] != '') {
            $where[] = ['o.'.$timeType,'>=',strtotime($params['start_time'])];
        }
        if (isset($params['end_time']) && $params['end_time'] != '') {
            $where[] = ['o.'.$timeType,'<=',strtotime($params['end_time'])];
        }
        if (isset($params['order_status']) && $params['order_status'] != '') {
            switch ($params['order_status']) {
                case 1://待支付
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_WAIT_PAY];
                    break;
                case 2://待接单
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    $where[] = ['o.order_sub_status','=',OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE];
                    break;
                case 3://待出发
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    $where[] = ['o.order_sub_status','=',OrderEnum::ORDER_SUB_STATUS_RECEIVED];
                    break;
                case 4://已出发
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    $where[] = ['o.order_sub_status','=',OrderEnum::ORDER_SUB_STATUS_SET_OUT];
                    break;
                case 5://已到达
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_WAIT_SERVICE];
                    $where[] = ['o.order_sub_status','=',OrderEnum::ORDER_SUB_STATUS_ARRIVE];
                    break;
                case 6://服务中
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_SERVICE];
                    break;
                case 7://已完成
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_FINISH];
                    break;
                case 8://已关闭
                    $where[] = ['o.order_status','=',OrderEnum::ORDER_STATUS_CLOSE];
                    break;
            }
        }

        return $where;
    }

    /**
     * @notes 订单列表
     * @return array
     * @author ljj
     * @date 2022/2/10 6:19 下午
     */
    public function lists(): array
    {
        $lists = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->field('o.id,o.sn,o.user_id,o.staff_id,o.order_type,o.order_status,o.order_sub_status,o.order_amount,o.refund_status,o.appoint_time_start,o.settlement_status,o.pay_status')
            ->with(['order_goods' => function($query){
                $query->field('goods_id,order_id,goods_snap,goods_name,goods_price,goods_num,goods_sku')->append(['goods_image'])->hidden(['goods_snap']);
            },'user' => function($query){
                $query->field('id,sn,nickname,avatar,mobile');
            },'staff' => function($query){
                $query->field('id,sn,name,work_image,mobile');
            }])
            ->where($this->where())
            ->order(['o.id'=>'desc'])
            ->append(['order_type_desc','order_status_desc','admin_order_btn','appoint_time_desc'])
            ->limit($this->limitOffset, $this->limitLength)
            ->group('o.id')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 订单总数
     * @return int
     * @author ljj
     * @date 2022/2/10 6:19 下午
     */
    public function count(): int
    {
        return (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($this->where())
            ->group('o.id')
            ->count();
    }

    /**
     * @notes 订单数据统计
     * @return array
     * @author ljj
     * @date 2022/2/15 11:07 上午
     */
    public function extend(): array
    {
        $where = self::where();
        foreach ($where as $key=>$val) {
            if ($val[0] == 'o.order_status') {
                unset($where[$key]);
            }
            if ($val[0] == 'o.order_sub_status') {
                unset($where[$key]);
            }
        }

        $data['all_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->group('o.id')
            ->count();
        $data['wait_pay_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_PAY])
            ->group('o.id')
            ->count();
        $data['wait_receive_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_WAIT_RECEIVE])
            ->group('o.id')
            ->count();
        $data['received_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_RECEIVED])
            ->group('o.id')
            ->count();
        $data['setout_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_SET_OUT])
            ->group('o.id')
            ->count();
        $data['arrive_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE,'order_sub_status'=>OrderEnum::ORDER_SUB_STATUS_ARRIVE])
            ->group('o.id')
            ->count();
        $data['service_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_SERVICE])
            ->group('o.id')
            ->count();
        $data['finish_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_FINISH])
            ->group('o.id')
            ->count();
        $data['close_count'] = (new Order())->alias('o')
            ->leftjoin('user u', 'u.id = o.user_id')
            ->leftjoin('order_goods og', 'og.order_id = o.id')
            ->leftjoin('goods g', 'g.id = og.goods_id')
            ->leftjoin('staff s', 's.id = o.staff_id')
            ->where($where)
            ->where(['order_status'=>OrderEnum::ORDER_STATUS_CLOSE])
            ->group('o.id')
            ->count();

        return $data;
    }
}