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


use app\adminapi\logic\order\OrderBtnLogic;
use app\common\enum\OrderEnum;
use app\common\enum\OrderRefundEnum;
use app\common\enum\PayEnum;
use app\common\enum\user\UserTerminalEnum;
use app\common\model\BaseModel;
use app\common\model\Region;
use app\common\model\staff\Staff;
use app\common\model\user\User;
use app\common\service\ConfigService;
use think\model\concern\SoftDelete;

class Order extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    /**
     * @notes 关联用户模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2022/2/10 6:36 下午
     */
    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    /**
     * @notes 关联订单服务模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2022/2/10 6:52 下午
     */
    public function orderGoods()
    {
        return $this->hasMany(OrderGoods::class,'order_id','id');
    }

    /**
     * @notes 关联订单日志模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2022/2/10 6:53 下午
     */
    public function orderLog()
    {
        return $this->hasMany(OrderLog::class,'order_id','id');
    }

    /**
     * @notes 关联师傅模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2022/2/11 12:11 下午
     */
    public function staff()
    {
        return $this->hasOne(Staff::class,'id','staff_id');
    }

    /**
     * @notes 关联订单签到模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2024/9/14 下午5:24
     */
    public function orderCheckin()
    {
        return $this->hasMany(OrderCheckin::class,'order_id','id');
    }

    /**
     * @notes 关联订单附加项目模型
     * @return \think\model\relation\HasMany
     * @author ljj
     * @date 2024/10/9 下午2:08
     */
    public function orderAdditional()
    {
        return $this->hasMany(OrderAdditional::class,'order_id','id');
    }

    /**
     * @notes 预约时间
     * @param $value
     * @param $data
     * @return false|string
     * @author ljj
     * @date 2022/2/11 10:11 上午
     */
    public function getAppointTimeDescAttr($value,$data)
    {
        return date('m-d H:i',$data['appoint_time_start']);
    }

    /**
     * @notes 预约时间日期
     * @param $value
     * @param $data
     * @return false|string
     * @author ljj
     * @date 2024/9/29 下午4:44
     */
    public function getAppointTimeDayAttr($value,$data)
    {
        return date('m-d',$data['appoint_time_start']);
    }

    /**
     * @notes 预约时间段
     * @param $value
     * @param $data
     * @return false|string
     * @author ljj
     * @date 2024/9/29 下午4:44
     */
    public function getAppointTimeSlotAttr($value,$data)
    {
        return date('H:i',$data['appoint_time_start']);
    }

    /**
     * @notes 支付状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 11:08 上午
     */
    public function getPayStatusDescAttr($value,$data)
    {
        return PayEnum::getPayStatusDesc($data['pay_status']);
    }

    /**
     * @notes 支付方式
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 12:01 下午
     */
    public function getPayWayDescAttr($value,$data)
    {
        return PayEnum::getPayTypeDesc($data['pay_way']);
    }

    /**
     * @notes 订单状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 11:59 上午
     */
    public function getOrderStatusDescAttr($value,$data)
    {
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE) {
            return OrderEnum::getOrderSubStatusDesc($data['order_sub_status']);
        } else {
            return OrderEnum::getOrderStatusDesc($data['order_status']);
        }
    }

    /**
     * @notes 订单类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/12 下午3:31
     */
    public function getOrderTypeDescAttr($value,$data)
    {
        return OrderEnum::getOrderTypeDesc($data['order_type']);
    }

    /**
     * @notes 支付时间
     * @param $value
     * @param $data
     * @author ljj
     * @date 2022/2/28 11:02 上午
     */
    public function getPayTimeAttr($value,$data)
    {
        return $value ? date('Y-m-d H:i:s',$value) : '-';
    }

    /**
     * @notes 完成时间
     * @param $value
     * @param $data
     * @author ljj
     * @date 2022/2/28 11:02 上午
     */
    public function getFinishTimeAttr($value,$data)
    {
        return $value ? date('Y-m-d H:i:s',$value) : '-';
    }

    /**
     * @notes 未支付订单自动取消时间
     * @param $value
     * @param $data
     * @return float|int|string
     * @author ljj
     * @date 2022/3/15 4:28 下午
     */
    public function getOrderCancelTimeAttr($value, $data)
    {
        $end_time = 0;
        $is_cancel = ConfigService::get('transaction', 'cancel_unpaid_orders',1);
        if ($data['order_status'] == 0 && $data['pay_status'] == 0 && $is_cancel == 1) {
            $order_cancel_time = ConfigService::get('transaction', 'cancel_unpaid_orders_times',30);
            $end_time = $data['create_time'] + $order_cancel_time * 60;
        }
        return $end_time;
    }

    /**
     * @notes 地址信息
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2024/9/14 上午10:10
     */
    public function getAddressInfoAttr($value,$data)
    {
        $result = json_decode($data['address_info'],true);
        $result['province'] = Region::where(['id'=>$result['province_id']])->value('name');
        $result['city'] = Region::where(['id'=>$result['city_id']])->value('name');
        $result['district'] = Region::where(['id'=>$result['district_id']])->value('name');
        $result['sex_desc'] = isset($result['sex']) ? ($result['sex'] == 1 ? '先生' : '女士') : '先生';
        return $result;
    }

    /**
     * @notes 后台订单操作按钮
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2024/9/12 下午5:53
     */
    public function getAdminOrderBtnAttr($value, $data)
    {
        return OrderBtnLogic::getAdminOrderBtn($data);
    }

    /**
     * @notes 订单来源
     * @param $value
     * @param $data
     * @return array|mixed|string|string[]
     * @author ljj
     * @date 2024/9/12 下午5:54
     */
    public function getOrderTerminalDescAttr($value, $data)
    {
        return UserTerminalEnum::getTermInalDesc($data['order_terminal']);
    }

    /**
     * @notes 订单佣金
     * @param $value
     * @param $data
     * @return float
     * @author ljj
     * @date 2024/9/13 上午11:14
     */
    public function getEarningsAttr($value, $data)
    {
        if ($data['settlement_status'] == 1) {
            $result = $data['settlement_amount'];
        } else {
            $refundAmount = self::getTotalRefundAmountAttr($value, $data);
            $result = round(($data['order_amount'] - $refundAmount) * ($data['earnings_ratio'] / 100),2);
        }

        return $result;
    }

    /**
     * @notes 订单退款金额
     * @param $value
     * @param $data
     * @return float
     * @author ljj
     * @date 2024/9/13 上午11:14
     */
    public function getRefundAmountAttr($value, $data)
    {
        $refundAmount = OrderRefund::where(['order_id'=>$data['id'],'refund_status'=>OrderRefundEnum::STATUS_SUCCESS,'order_category'=>OrderRefundEnum::ORDER_CATEGORY_BASICS])->sum('refund_amount');
        return $refundAmount;
    }

    /**
     * @notes 订单总退款金额 包含加项、补差价
     * @param $value
     * @param $data
     * @return float
     * @author ljj
     * @date 2024/9/13 上午11:14
     */
    public function getTotalRefundAmountAttr($value, $data)
    {
        $basicsRefundAmount = OrderRefund::where(['order_id'=>$data['id'],'refund_status'=>OrderRefundEnum::STATUS_SUCCESS,'order_category'=>OrderRefundEnum::ORDER_CATEGORY_BASICS])->sum('refund_amount');

        $orderAdditionalIds = OrderAdditional::where(['order_id'=>$data['id']])->column('id');
        $additionalRefundAmount = OrderRefund::where(['order_id'=>$orderAdditionalIds,'refund_status'=>OrderRefundEnum::STATUS_SUCCESS,'order_category'=>OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL])->sum('refund_amount');

        $orderDifferencePriceIds = OrderDifferencePrice::where(['order_id'=>$data['id']])->column('id');
        $differenceRefundAmount = OrderRefund::where(['order_id'=>$orderDifferencePriceIds,'refund_status'=>OrderRefundEnum::STATUS_SUCCESS,'order_category'=>OrderRefundEnum::ORDER_CATEGORY_DIFFERENCE])->sum('refund_amount');

        return $basicsRefundAmount + $additionalRefundAmount + $differenceRefundAmount;
    }

    /**
     * @notes 结算状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/13 上午11:23
     */
    public function getSettlementStatusDescAttr($value, $data)
    {
        return OrderEnum::getSettlementStatusDesc($data['settlement_status']);
    }

    /**
     * @notes 用户端订单操作按钮
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2024/9/29 下午5:19
     */
    public function getUserOrderBtnAttr($value, $data)
    {
        return \app\api\logic\OrderBtnLogic::getUserOrderBtn($data);
    }

    /**
     * @notes 师傅端订单操作按钮
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2024/10/18 下午4:17
     */
    public function getStaffOrderBtnAttr($value, $data)
    {
        return \app\staffapi\logic\OrderBtnLogic::getStaffOrderBtn($data);
    }
}