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


use app\common\enum\OrderRefundEnum;
use app\common\model\BaseModel;

class OrderAdditional extends BaseModel
{

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
     * @notes 退款金额
     * @param $value
     * @param $data
     * @return float
     * @author ljj
     * @date 2024/11/1 上午9:52
     */
    public function getRefundAmountAttr($value, $data)
    {
        $refundAmount = OrderRefund::where(['order_id'=>$data['id'],'refund_status'=>OrderRefundEnum::STATUS_SUCCESS,'order_category'=>OrderRefundEnum::ORDER_CATEGORY_ADDITIONAL])->sum('refund_amount');
        return $refundAmount;
    }
}