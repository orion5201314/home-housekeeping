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

namespace app\api\logic;


use app\common\enum\DefaultEnum;
use app\common\enum\OrderEnum;
use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsComment;
use app\common\model\order\Order;
use app\common\model\order\OrderGoods;
use app\common\model\staff\Staff;

class StaffLogic extends BaseLogic
{
    /**
     * @notes 师傅详情
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 6:34 下午
     */
    public function detail($params)
    {
        $result = Staff::where(['id'=>$params['id'] ?? 0])
            ->field('id,skill_id,goods_id,name,sex,age,education,nation,identity_number,work_image,credentials_image,last_address_info')
            ->append(['sex_desc','education_name','nation_name','skill_name'])
            ->findOrEmpty()
            ->toArray();

        if (!empty($result)) {
            //身份证脱敏
            $result['identity_number'] = substr_replace($result['identity_number'], '***************', 3);

            //获取师傅距离
            if (!empty($params['longitude']) && !empty($params['latitude'])) {
                $result['distance'] = getDistance($result['last_address_info']['longitude'],$result['last_address_info']['latitude'],$params['longitude'],$params['latitude'],1);
                if ($result['distance'] >= 1000) {
                    $result['distance'] = round($result['distance'] / 1000,2).'km';
                } else {
                    $result['distance'] = $result['distance'].'m';
                }
            }

            //轮播图
            $result['carousel_image'] = array_merge([$result['work_image']],$result['credentials_image']);

            //最早可预约时间 默认为当前时间往后延一个小时
            $earliestBookingTime = time() + (60 *60);
            //获取当天师傅已接未完成订单
            $staffOrder = Order::field('appoint_time_start,appoint_time_end')
                ->where(['staff_id'=>$params['id'],'order_status'=>[OrderEnum::ORDER_STATUS_WAIT_SERVICE,OrderEnum::ORDER_STATUS_SERVICE],'order_sub_status'=>[OrderEnum::ORDER_SUB_STATUS_RECEIVED,OrderEnum::ORDER_SUB_STATUS_SET_OUT,OrderEnum::ORDER_SUB_STATUS_ARRIVE]])
                ->whereDay('appoint_time_start')
                ->select()
                ->toArray();
            foreach ($staffOrder as $order) {
                if ($order['appoint_time_start'] <= $earliestBookingTime && $order['appoint_time_end'] >= $earliestBookingTime) {
                    $earliestBookingTime = $order['appoint_time_end'];
                }
            }
            $earliestBookingTimeH = (int)date("H",$earliestBookingTime);
            $earliestBookingTimeI = (int)date("i",$earliestBookingTime);
            if ($earliestBookingTimeI <= 30) {
                if ($earliestBookingTimeI === 0) {
                    $earliestBookingTimeH -= 1;
                    $earliestBookingTimeI = '30';
                } else {
                    $earliestBookingTimeI = '00';
                }
            }
            $earliestBookingTimeH = str_pad($earliestBookingTimeH,2,'0',STR_PAD_LEFT);
            $earliestBookingTime = $earliestBookingTimeH.':'.$earliestBookingTimeI;
            $result['earliest_booking_time'] = $earliestBookingTime;

            //统计师傅订单数量
            $staffOrderIds = Order::where(['staff_id'=>$params['id'],'order_status'=>OrderEnum::ORDER_STATUS_FINISH])->column('id');
            $result['order_count'] = count($staffOrderIds);

            //好评率 100 - (差评 / 订单数) * 100
            if ($result['order_count'] > 0) {
                $staffOrderGoodsIds = OrderGoods::where(['order_id'=>$staffOrderIds])->column('id');
                $badComment = GoodsComment::where(['order_goods_id'=>$staffOrderGoodsIds])
                    ->where('service_comment','<=',3)
                    ->count();
                $result['good_rate'] = 100 - round(($badComment / $result['order_count']) * 100,2);
            }
        }

        return $result;
    }
}