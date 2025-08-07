<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------
namespace app\adminapi\validate\setting;

use app\common\validate\BaseValidate;

/**
 * 用户设置验证
 * Class UserConfigValidate
 * @package app\adminapi\validate\setting
 */
class UserConfigValidate extends BaseValidate
{

    protected $rule = [
        'login_way' => 'requireIf:scene,register|array',
        'coerce_mobile' => 'requireIf:scene,register|in:0,1',
        'login_agreement' => 'in:0,1',
        'third_auth' => 'in:0,1',
        'wechat_auth' => 'in:0,1',
        'default_avatar' => 'require',
        'withdraw_way' => 'require|array|min:1',
        'min_money' => 'float|egt:0.01',
        'max_money' => 'float|egt:min_money',
        'service_ratio' => 'float|egt:0|elt:100',
        'default_staff_avatar' => 'require',
    ];


    protected $message = [
        'default_avatar.require' => '请上传用户默认头像',
        'login_way.requireIf' => '请选择登录方式',
        'login_way.array' => '登录方式值错误',
        'coerce_mobile.requireIf' => '请选择注册强制绑定手机',
        'coerce_mobile.in' => '注册强制绑定手机值错误',
        'wechat_auth.in' => '公众号微信授权登录值错误',
        'third_auth.in' => '第三方登录值错误',
        'login_agreement.in' => '政策协议值错误',
        'withdraw_way.require' => '请选择提现方法',
        'withdraw_way.array' => '提现方法值错误',
        'withdraw_way.min' => '至少选择一个提现方式',
        'min_money.float' => '最低提现金额值错误',
        'min_money.egt' => '最低提现金额大于等于0.01',
        'max_money.float' => '最高提现金额值错误',
        'max_money.egt' => '最高提现金额大于等于最低提现金额',
        'service_ratio.float' => '提现手续费值错误',
        'service_ratio.egt' => '提现手续费必须大于等于0',
        'service_ratio.elt' => '提现手续费必须小于等于100',
    ];

    //用户设置验证
    public function sceneUser()
    {
        return $this->only(['default_avatar','default_staff_avatar']);
    }

    //注册验证
    public function sceneRegister()
    {
        return $this->only(['login_way', 'coerce_mobile', 'third_auth', 'wechat_auth']);
    }

    //提现配置
    public function sceneSetWithdrawConfig()
    {
        return $this->only(['withdraw_way', 'min_money', 'max_money', 'service_ratio']);
    }
}