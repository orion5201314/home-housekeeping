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

namespace app\staffapi\logic;


use app\common\enum\PayEnum;
use app\common\enum\user\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\logic\PayNotifyLogic;
use app\common\model\order\Order;
use app\common\model\order\OrderAdditional;
use app\common\model\order\OrderDifferencePrice;
use app\common\model\pay\PayWay;
use app\common\model\RechargeOrder;
use app\common\model\staff\StaffDepositRecharge;
use app\common\service\AliPayService;
use app\common\service\BalancePayService;
use app\common\service\WeChatPayService;
use app\common\service\WeChatService;

class PayLogic extends BaseLogic
{
    /**
     * @notes 支付方式
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2024/10/18 上午9:47
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
            // 充值时去除余额支付
            if ($params['from'] == 'deposit' && $item['pay_way'] == PayEnum::BALANCE_PAY) {
                unset($pay_way[$k]);
            }

            //暂时去除微信支付
            if ($item['pay_way'] == PayEnum::WECHAT_PAY) {
                unset($pay_way[$k]);
            }
        }
        $pay_way = array_values($pay_way);

        return $pay_way;
    }


    /**
     * @notes 支付
     * @param $payWay // 支付方式
     * @param $from //订单来源(商品订单?充值订单?其他订单?)
     * @param $order_id //订单id
     * @param $terminal //终端
     * @return array|bool|string|void
     * @throws \Exception
     * @author 段誉
     * @date 2021/7/29 14:49
     */
    public static function pay($payWay, $from, $order_id, $terminal,$wechatCode = '')
    {
        $order = [];
        //更新支付方式
        switch ($from) {
            case 'deposit':
                StaffDepositRecharge::update(['pay_way' => $payWay], ['id' => $order_id]);
                $order = StaffDepositRecharge::where('id',$order_id)->findOrEmpty()->toArray();
                $order['order_amount'] = $order['amount'];
                break;
        }

        if (empty($order)) {
            self::setError('订单错误');
        }

        if($order['order_amount'] == 0) {
            PayNotifyLogic::handle($from, $order['sn']);
            return ['pay_way'=>$payWay];
        }

        switch ($payWay) {
            case PayEnum::WECHAT_PAY:
                if (isset($wechatCode) && $wechatCode != '') {
                    switch ($terminal) {
                        case UserTerminalEnum::WECHAT_MMP:
                            $response = (new WeChatService())->getMnpResByCode($wechatCode);
                            $order['openid'] = $response['openid'];
                            break;
                        case UserTerminalEnum::WECHAT_OA:
                            $response = (new WeChatService())->getOaResByCode($wechatCode);
                            $order['openid'] = $response['openid'];
                            break;
                    }
                }

                $payService = (new WeChatPayService($terminal, $order['user_id'] ?? null));
                $result = $payService->pay($from, $order);
                break;
            case PayEnum::BALANCE_PAY:
                //余额支付
                $payService = (new BalancePayService());
                $result = $payService->pay($from, $order);
                if (false !== $result) {
                    PayNotifyLogic::handle($from, $order['sn']);
                }
                break;
            case PayEnum::ALI_PAY:
                $payService = (new AliPayService($terminal));
                $result = $payService->pay($from, $order);
                break;
            default:
                self::$error = '订单异常';
                $result = false;
        }

        //支付成功, 执行支付回调
        if (false === $result && !self::hasError()) {
            self::setError($payService->getError());
        }
        return $result;
    }

    /**
     * @notes 获取支付结果
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/3/21 5:48 下午
     */
    public static function getPayResult($params)
    {
        switch ($params['from']) {
            case 'deposit' :
                $result = StaffDepositRecharge::where(['id' => $params['order_id']])
                    ->field(['id','sn','pay_time','pay_way','amount','pay_status'])
                    ->findOrEmpty()
                    ->toArray();
                $result['order_amount'] = $result['amount'];
                $result['total_amount'] = '￥' . $result['amount'];
                break;
            default :
                $result = [];
        }

        if (empty($result)) {
            self::$error = '订单信息不存在';
        }

        $result['pay_way_desc'] = PayEnum::getPayTypeDesc($result['pay_way']);
        $result['pay_time'] = empty($result['pay_time']) ? '' : date('Y-m-d H:i:s', $result['pay_time']);
        return $result;
    }
}