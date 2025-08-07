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

namespace app\staffapi\validate;


use app\common\enum\PayEnum;
use app\common\validate\BaseValidate;

class StaffDepositValidate extends BaseValidate
{
    protected $rule = [
        'pay_way' => 'require|in:' . PayEnum::WECHAT_PAY . ',' . PayEnum::ALI_PAY,
        'amount' => 'require|float|gt:0',
    ];

    protected $message = [
        'pay_way.require'   => '支付方式参数缺失',
        'pay_way.in'        => '支付方式参数错误',
        'amount.require' => '请输入缴纳金额',
        'amount.float' => '缴纳金额值错误',
        'amount.gt' => '缴纳金额必须大于零',
    ];

    public function sceneRecharge()
    {
        return $this->only(['pay_way','amount']);
    }
}