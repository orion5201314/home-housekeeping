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


use app\common\enum\notice\NoticeEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\logic\OrderLogLogic;
use app\common\logic\RefundLogic;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsAdditional;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\order\OrderGoods;
use app\common\model\pay\PayWay;
use app\common\model\user\User;
use app\common\model\user\UserAddress;
use think\facade\Db;

class OrderLogic extends BaseLogic
{
    /**
     * @notes 订单结算详情
     * @param $params
     * @return array|false
     * @author ljj
     * @date 2022/2/24 6:19 下午
     */
    public function settlement($params)
    {
        try {
            //获取用户信息
            $user = User::findOrEmpty($params['user_id'])->toArray();

            //设置用户地址
            $user_address = UserAddress::getUserAddress($params['user_id'], $params['address_id'] ?? 0);

            //获取服务信息
            $goods = (new Goods())->alias('g')
                ->join('goods_sku gs', 'gs.goods_id = g.id')
                ->field('g.*,gs.price,gs.duration,gs.sku_value_arr,gs.sku_value_ids,gs.line_price')
                ->json(['sku_value_arr'],true)
                ->where(['gs.id'=>$params['goods']['sku_id']])
                ->findOrEmpty()
                ->toArray();
            $goods['goods_num'] = $params['goods']['goods_num'];

            // 订单金额
            $total_amount = round($goods['price'] * $goods['goods_num'],2);

            //订单应付金额
            $order_amount = $total_amount;

            //订单服务总数量
            $total_num = $goods['goods_num'];

            //订单服务总价
            $total_goods_price = round($goods['price'] * $goods['goods_num'],2);

            $result = [
                'terminal'          => $params['terminal'],
                'total_num'         => $total_num,
                'total_goods_price' => $total_goods_price,
                'total_amount'      => $total_amount,
                'order_amount'      => $order_amount,
                'user_id'           => $user['id'],
                'user_remark'       => $params['user_remark'] ?? '',
                'appoint_time'      => $params['appoint_time'] ?? '',
                'address'           => $user_address,
                'goods'             => $goods,
                'pay_way'           => $params['pay_way'] ?? '',
            ];

            return $result;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 提交订单
     * @param $params
     * @return array|false
     * @author ljj
     * @date 2022/2/25 9:40 上午
     */
    public static function submitOrder($params)
    {
        Db::startTrans();
        try {
            //收货地址
            if (empty($params['address'])) {
                throw new \Exception('请选择收货地址');
            }
            $goods = Goods::where(['id'=>$params['goods']['id']])->json(['open_city_id'],true)->findOrEmpty()->toArray();
            if (!empty($goods['open_city_id']) && !in_array($params['address']['city_id'],$goods['open_city_id'])) {
                throw new \Exception('抱歉，商品暂不支持该服务地址');
            }
            //服务时间
            if (empty($params['appoint_time'])) {
                throw new \Exception('请选择服务时间');
            }
            //支付方式
            if (empty($params['pay_way'])) {
                throw new \Exception('请选择支付方式');
            }

            //创建订单信息
            $order = self::addOrder($params);

            //下单增加服务人数
            Goods::update(['sale_num'=>['inc',1]],['id'=>$params['goods']['id']]);

            //订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_USER,OrderLogEnum::USER_ADD_ORDER,$order['id'],$params['user_id']);

            //提交事务
            Db::commit();
            return ['order_id' => $order['id'], 'type' => 'order'];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 创建订单信息
     * @param $params
     * @return Order|\think\Model
     * @author ljj
     * @date 2022/2/25 9:40 上午
     */
    public static function addOrder($params)
    {
        $appointTimeStart = strtotime($params['appoint_time']);
        $totalDuration = $params['goods']['duration'] * $params['goods']['goods_num'] * 60;
        $appointTimeEnd = $appointTimeStart + $totalDuration;
        //创建订单信息
        $order = Order::create([
            'sn'                    => generate_sn((new Order()), 'sn'),
            'user_id'               => $params['user_id'],
            'order_terminal'        => $params['terminal'],
            'pay_way'               => $params['pay_way'],
            'goods_price'           => $params['total_goods_price'],
            'order_amount'          => $params['order_amount'],
            'total_amount'          => $params['total_amount'],
            'total_num'             => $params['total_num'],
            'earnings_ratio'        => $params['goods']['earnings_ratio'],
            'user_remark'           => $params['user_remark'],
            'contact'               => $params['address']['contact'],
            'mobile'                => $params['address']['mobile'],
            'address_info'          => json_encode([
                'province_id'           => $params['address']['province_id'],
                'city_id'               => $params['address']['city_id'],
                'district_id'           => $params['address']['district_id'],
                'address'               => $params['address']['address'],
                'sex'                   => $params['address']['sex'],
                'longitude'             => $params['address']['longitude'],
                'latitude'              => $params['address']['latitude'],
            ]),
            'appoint_time_start'    => $appointTimeStart,
            'appoint_time_end'      => $appointTimeEnd,
        ]);

        //创建订单服务信息
        OrderGoods::create([
            'order_id'          => $order->id,
            'goods_id'          => $params['goods']['id'],
            'goods_name'        => $params['goods']['name'],
            'goods_sku'         => implode(',',$params['goods']['sku_value_arr']),
            'goods_num'         => $params['goods']['goods_num'],
            'goods_price'       => $params['goods']['price'],
            'goods_duration'    => $params['goods']['duration'],
            'total_price'       => round($params['goods']['price'] * $params['goods']['goods_num'],2),
            'total_pay_price'   => round($params['goods']['price'] * $params['goods']['goods_num'],2),
            'earnings_ratio'    => $params['goods']['earnings_ratio'],
            'goods_snap'        => json_encode($params['goods']),
        ]);

        return $order;
    }

    /**
     * @notes 订单详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2022/2/28 11:23 上午
     */
    public function detail($id)
    {
        $result = Order::where('id',$id)
            ->append(['appoint_time_day','appoint_time_slot','order_status_desc','pay_way_desc','address_info','refund_amount','total_refund_amount','user_order_btn','order_cancel_time'])
            ->with(['order_goods' => function($query){
                $query->field('id,order_id,goods_id,goods_snap,goods_name,goods_price,goods_sku,goods_num')->append(['goods_image','goods_sku_arr'])->hidden(['goods_snap','goods_sku']);
            },'staff' => function($query){
                $query->field('id,name,work_image,sn,mobile');
            },'order_additional' => function($query){
                $query->field('id,order_id,additional_snap')->json(['additional_snap'],true);
            }])
            ->findOrEmpty()
            ->toArray();

        if(!empty($result)) {
            //处理加项数据
            $additionalSnap = array_column($result['order_additional'],'additional_snap');
            $additionalInfo = [];
            foreach ($additionalSnap as $additional) {
                foreach ($additional as $item) {
                    $additionalInfo[$item['id']]['id'] = $item['id'];
                    $additionalInfo[$item['id']]['name'] = $item['name'];
                    $additionalInfo[$item['id']]['duration'] = $item['duration'];
                    $additionalInfo[$item['id']]['price'] = ($additionalInfo[$item['id']]['price'] ?? 0) + ($item['price'] * $item['num']);
                    $additionalInfo[$item['id']]['num'] = ($additionalInfo[$item['id']]['num'] ?? 0) + $item['num'];
                }
            }
            $result['order_additional'] = array_values($additionalInfo);
        }

        return $result;
    }

    /**
     * @notes 取消订单
     * @param $params
     * @return bool|string
     * @author ljj
     * @date 2022/2/28 11:36 上午
     */
    public function cancel($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            //处理基础订单
            //更新基础订单状态
            Order::update([
                'order_status' => OrderEnum::ORDER_STATUS_CLOSE,
                'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                'cancel_time' => time(),
            ],['id'=>$params['id']]);
            //添加订单日志
            (new OrderLogLogic())->record(OrderLogEnum::TYPE_USER,OrderLogEnum::USER_CANCEL_ORDER,$params['id'],$params['user_id']);

            //TODO 已支付订单原路退回金额
            $order = Order::where('id',$params['id'])->findOrEmpty()->toArray();
            if($order['pay_status'] == PayEnum::ISPAID) {
                $orderCopy = $order;
                $orderCopy['order_amount'] = $order['goods_price'];
                //基础订单退款
                (new RefundLogic())->refund($orderCopy,$order['goods_price'],OrderRefundEnum::TYPE_USER,$params['user_id']);

                //处理加项订单
                $orderAdditional = OrderAdditional::where(['order_id'=>$params['id']])->select()->toArray();
                foreach ($orderAdditional as $item) {
                    $item['order_amount'] = $item['amount'];
                    $item['order_terminal'] = $item['terminal'];
                    //更新加项订单状态
                    OrderAdditional::update([
                        'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                    ],['id'=>$item['id']]);

                    //加项订单退款
                    (new RefundLogic())->refund($item,$item['order_amount'],OrderRefundEnum::TYPE_USER,$params['user_id'],1,OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL);
                }

                //处理补差价订单
                $orderDifference = OrderDifferencePrice::where(['order_id'=>$params['id']])->select()->toArray();
                foreach ($orderDifference as $item) {
                    $item['order_amount'] = $item['amount'];
                    $item['order_terminal'] = $item['terminal'];
                    //更新补差价订单状态
                    OrderDifferencePrice::update([
                        'refund_status' => OrderEnum::REFUND_STATUS_ALL,
                    ],['id'=>$item['id']]);

                    //加项订单退款
                    (new RefundLogic())->refund($item,$item['order_amount'],OrderRefundEnum::TYPE_USER,$params['user_id'],1,OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE);
                }
            }


            // 取消订单通知 - 通知用户
            event('Notice', [
                'scene_id' =>  NoticeEnum::ORDER_CANCEL_NOTICE,
                'params' => [
                    'order_id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'mobile' => $order['mobile']
                ]
            ]);
            // 订单退款通知 - 通知用户
            if($order['pay_status'] == PayEnum::ISPAID) {
                event('Notice', [
                    'scene_id' =>  NoticeEnum::ORDER_REFUND_NOTICE,
                    'params' => [
                        'order_id' => $order['id'],
                        'user_id' => $order['user_id'],
                        'refund_amount' => $order['order_amount']
                    ]
                ]);
            }
            // 取消订单通知 - 通知师傅
            if(!empty($order['staff_id'])) {
                event('Notice', [
                    'scene_id' =>  NoticeEnum::ORDER_CANCEL_NOTICE_STAFF,
                    'params' => [
                        'order_id' => $order['id'],
                        'staff_id' => $order['staff_id'],
                    ]
                ]);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除订单
     * @param $id
     * @return bool
     * @author ljj
     * @date 2022/2/28 11:50 上午
     */
    public function del($id)
    {
        Order::destroy($id);
        return true;
    }

    /**
     * @notes 支付方式
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2024/7/24 下午7:08
     */
    public static function payWay($params)
    {
        $pay_way = PayWay::alias('pw')
            ->join('dev_pay dp', 'pw.pay_id = dp.id')
            ->where(['pw.scene'=>$params['scene'],'pw.status'=>YesNoEnum::YES])
            ->field('dp.id,dp.name,dp.pay_way,dp.image,pw.is_default')
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select()
            ->toArray();
        foreach ($pay_way as $k=>&$item) {
            if ($item['pay_way'] == PayEnum::BALANCE_PAY) {
                $user_money = User::where(['id' => $params['user_id']])->value('user_money');
                $item['extra'] = '可用余额:'.$user_money;
            }
            // 充值时去除余额支付
            if ($params['from'] == 'recharge' && $item['pay_way'] == PayEnum::BALANCE_PAY) {
                unset($pay_way[$k]);
            }
        }

        return $pay_way;
    }

    /**
     * @notes 补差价
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/10/8 下午5:21
     */
    public function differencePrice($params)
    {
        $info = OrderDifferencePrice::create([
            'sn' => generate_sn((new OrderDifferencePrice()), 'sn'),
            'user_id' => $params['user_id'],
            'order_id' => $params['id'],
            'terminal' => $params['terminal'],
            'amount' => $params['difference_price'],
        ]);
        return ['order_id' => $info->id, 'type' => 'difference_price'];
    }

    /**
     * @notes 补差价详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/10/8 下午6:01
     */
    public function differencePriceDetail($id)
    {
        $info = OrderDifferencePrice::field(['id,user_id,sn,order_id,transaction_id,terminal,amount'])->findOrEmpty($id)->toArray();
        return $info;
    }

    /**
     * @notes 加项
     * @param $params
     * @return array|false
     * @author ljj
     * @date 2024/10/9 下午12:11
     */
    public function additional($params)
    {
        // 启动事务
        Db::startTrans();
        try {
            $additionalIds = array_column($params['additional_info'],'id');
            $numArr = array_column($params['additional_info'],'num','id');
            $additionalInfo = GoodsAdditional::where(['id'=>$additionalIds])->select()->toArray();
            $amount = 0;
            foreach ($additionalInfo as $key=>$item) {
                if (empty($numArr[$item['id']])) {
                    unset($additionalInfo[$key]);
                    continue;
                }
                $additionalInfo[$key]['num'] = $numArr[$item['id']];
                $amount += $item['price'] * $numArr[$item['id']];
            }
            $additionalInfo = array_values($additionalInfo);

            //添加订单加项记录
            $info = OrderAdditional::create([
                'sn' => generate_sn((new OrderDifferencePrice()), 'sn'),
                'user_id' => $params['user_id'],
                'order_id' => $params['id'],
                'additional_snap' => json_encode($additionalInfo),
                'terminal' => $params['terminal'],
                'amount' => $amount,
            ]);

            // 提交事务
            Db::commit();
            return ['order_id' => $info->id, 'type' => 'additional'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }


    }

    /**
     * @notes 加项详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/10/9 下午12:11
     */
    public function additionalDetail($id)
    {
        $info = OrderAdditional::field(['id,user_id,sn,order_id,transaction_id,terminal,amount'])->findOrEmpty($id)->toArray();
        return $info;
    }
}