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

namespace app\api\validate;


use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

class RechargeValidate extends BaseValidate
{
    protected $rule = [
        'money' => 'require|float|gt:0',
        'pay_way' => 'require',
    ];

    protected $message = [
        'money.require' => '请输入充值金额',
        'money.float' => '充值金额错误',
        'money.gt' => '充值金额必须大于零',
        'pay_way.require' => '请选择支付方式',
    ];


    public function sceneRecharge()
    {
        return $this->only(['money'])
            ->append('money','checkRecharge');
    }


    /**
     * @notes 校验充值
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/12/16 16:02
     */
    public function checkRecharge($value,$rule,$data)
    {
        $result = ConfigService::get('recharge', 'recharge_open',1);
        if($result != 1) {
            return '充值功能已关闭';
        }

        $min_amount = ConfigService::get('recharge', 'min_recharge_amount',0);
        if($data['money'] < $min_amount) {
            return '最低充值金额:'.$min_amount.'元';
        }

        return true;
    }
}