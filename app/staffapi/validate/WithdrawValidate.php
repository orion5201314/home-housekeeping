<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\staffapi\validate;

use app\common\enum\StaffAccountLogEnum;
use app\common\enum\StaffEnum;
use app\common\enum\WithdrawEnum;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffWithdrawAccount;
use app\common\service\ConfigService;
use app\common\validate\BaseValidate;

class WithdrawValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'type' => 'require|in:'.StaffAccountLogEnum::DEPOSIT.','.StaffAccountLogEnum::EARNINGS,
        'way' => 'require|in:'.WithdrawEnum::WAY_WECHAT.','.WithdrawEnum::WAY_ALI.','.WithdrawEnum::WAY_BANK,
        'money' => 'require|float|gt:0',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'type.require' => '参数错误',
        'type.in' => '参数错误',
        'way.require' => '请选择提现方式',
        'way.in' => '提现方式错误',
        'money.require' => '请输入提现金额',
        'money.float' => '提现金额错误',
        'money.gt' => '提现金额须大于0',
    ];

    public function sceneApply()
    {
        return $this->only(['type', 'way', 'money'])
            ->append('type','checkApply');
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验提现申请
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/17 上午10:46
     */
    protected function checkApply($value, $rule, $data)
    {
        $staff = Staff::where('id', $data['staff_id'])->findOrEmpty()->toArray();
        if ($staff['status'] === StaffEnum::STATUS_FROZEN) {
            return '您的账号已被冻结，无法申请提现';
        }

        $amount = 0;
        switch ($data['type']) {
            case StaffAccountLogEnum::DEPOSIT:
                $amount = $staff['staff_deposit'];
                break;
            case StaffAccountLogEnum::EARNINGS:
                $amount = $staff['staff_earnings'];
                break;
        }
        $amount = is_null($amount) ? 0 : $amount;
        if ($data['money'] > $amount){
            return '可提现金额不足';
        }

        // 最低提现金额
        $min_withdraw = ConfigService::get('withdraw_config', 'min_money', 10);
        if($data['money'] < $min_withdraw){
            return '最低提现'.$min_withdraw.'元';
        }

        // 最高提现金额
        $max_withdraw = ConfigService::get('withdraw_config', 'max_money', 100);
        if ($data['money'] > $max_withdraw){
            return '最高提现'.$max_withdraw.'元';
        }

        //提现账户
        $withdrawAccount = StaffWithdrawAccount::where(['staff_id'=>$data['staff_id']])->findOrEmpty()->toArray();
        switch ($data['way']) {
            case WithdrawEnum::WAY_WECHAT:
                if (empty($withdrawAccount['wechat_name']) || empty($withdrawAccount['wechat_mobile'])) {
                    return '请绑定微信账户';
                }
                break;
            case WithdrawEnum::WAY_ALI:
                if (empty($withdrawAccount['alipay_name']) || empty($withdrawAccount['alipay_account'])) {
                    return '请绑定支付宝账户';
                }
                break;
            case WithdrawEnum::WAY_BANK:
                if (empty($withdrawAccount['bank_holder_name']) || empty($withdrawAccount['bank_opening']) || empty($withdrawAccount['bank_number'])) {
                    return '请绑定银行卡账户';
                }
                break;
        }

        return true;
    }
}