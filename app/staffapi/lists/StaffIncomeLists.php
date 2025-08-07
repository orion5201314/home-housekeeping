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
use app\common\enum\PayEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\order\Order;

class StaffIncomeLists extends BaseStaffDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/18 下午12:02
     */
    public function where()
    {
        $where[] = ['staff_id','=',$this->staffId];
        $where[] = ['pay_status','=',PayEnum::ISPAID];
        $where[] = ['order_status','in',[OrderEnum::ORDER_STATUS_FINISH,OrderEnum::ORDER_STATUS_CLOSE]];
        $where[] = ['refund_status','in',[OrderEnum::REFUND_STATUS_NOT,OrderEnum::REFUND_STATUS_PART]];
        $startTime = strtotime(date("Y-m-d", strtotime("-1 month")).' 00:00:00');
        $endTime = time();
        if (isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $startTime = strtotime($this->params['start_time']);
        }
        if (isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $endTime = strtotime($this->params['end_time']);
        }
        $where[] = ['create_time','>=',$startTime];
        $where[] = ['create_time','<=',$endTime];

        return $where;
    }

    /**
     * @notes 收入列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/18 下午12:02
     */
    public function lists(): array
    {
        $lists = Order::field('id,sn,order_amount,earnings_ratio,settlement_status,settlement_amount,create_time,finish_time')
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            }])
            ->append(['settlement_status_desc'])
            ->where(self::where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id','desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/18 下午12:02
     */
    public function count(): int
    {
        return Order::where(self::where())->count();
    }

    /**
     * @notes 扩展数据
     * @return array
     * @author ljj
     * @date 2024/10/18 下午2:05
     */
    public function extend()
    {
        $startTime = date("Y-m-d", strtotime("-1 month"));
        $endTime = date("Y-m-d", time());
        if (isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $startTime = $this->params['start_time'];
        }
        if (isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $endTime = $this->params['end_time'];
        }
        return [
            'startTime' => $startTime,
            'endTime' => $endTime,
            'total_settlement_amount' => Order::where(self::where())->sum('settlement_amount')
        ];
    }
}