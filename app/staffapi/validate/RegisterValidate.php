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
namespace app\staffapi\validate;

use app\common\enum\notice\NoticeEnum;
use app\common\model\staff\Staff;
use app\common\service\sms\SmsDriver;
use app\common\validate\BaseValidate;

/**
 * 注册验证器
 * Class RegisterValidate
 * @package app\staffapi\validate
 */
class RegisterValidate extends BaseValidate
{
    protected $rule = [
        'channel' => 'require',
        'mobile' => 'require|mobile|unique:' . Staff::class,
        'password' => 'require|length:6,20|alphaNum',
        'code' => 'require|checkCode'
    ];

    protected $message = [
        'channel.require' => '注册来源参数缺失',
        'mobile.require' => '请输入手机号',
        'mobile.mobile' => '手机号错误',
        'mobile.unique' => '手机号已存在',
        'password.require' => '请输入密码',
        'password.length' => '密码须在6-20位之间',
        'password.alphaNum' => '密码须为字母数字组合',
        'code.require' => '请输入验证码',
    ];


    /**
     * @notes 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/24 上午10:38
     */
    public function checkCode($code, $rule, $data)
    {
        $smsDriver = new SmsDriver();
        $result = $smsDriver->verify($data['mobile'], $code, NoticeEnum::REGISTER_CAPTCHA_STAFF);
        if ($result) {
            return true;
        }
        return '验证码错误';
    }
}