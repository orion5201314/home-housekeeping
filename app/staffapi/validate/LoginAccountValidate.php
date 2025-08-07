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

use app\common\cache\StaffAccountSafeCache;
use app\common\enum\LoginEnum;
use app\common\enum\notice\NoticeEnum;
use app\common\enum\user\UserTerminalEnum;
use app\common\model\staff\Staff;
use app\common\service\sms\SmsDriver;
use app\common\validate\BaseValidate;
use think\facade\Config;


class LoginAccountValidate extends BaseValidate
{

    protected $rule = [
        'terminal' => 'require|in:' . UserTerminalEnum::WECHAT_MMP . ',' . UserTerminalEnum::WECHAT_OA . ','
            . UserTerminalEnum::H5 . ',' . UserTerminalEnum::PC . ',' . UserTerminalEnum::IOS .
            ',' . UserTerminalEnum::ANDROID,
        'scene' => 'require|in:' . LoginEnum::ACCOUNT_PASSWORD . ',' . LoginEnum::MOBILE_CAPTCHA . '|checkConfig',
        'mobile' => 'require|mobile',
    ];


    protected $message = [
        'terminal.require' => '终端参数缺失',
        'terminal.in' => '终端参数状态值不正确',
        'scene.require' => '场景不能为空',
        'scene.in' => '场景值错误',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '手机号码错误',
    ];


    /**
     * @notes 登录场景相关校验
     * @param $scene
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2024/10/10 下午1:57
     */
    public function checkConfig($scene, $rule, $data)
    {
        // 密码登录
        if (LoginEnum::ACCOUNT_PASSWORD == $scene) {
            if (!isset($data['password'])) {
                return '请输入密码';
            }
            return $this->checkPassword($data['password'], [], $data);
        }

        // 验证码登录
        if (LoginEnum::MOBILE_CAPTCHA == $scene) {
            if (!isset($data['code'])) {
                return '请输入手机验证码';
            }
            return $this->checkCode($data['code'], [], $data);
        }

        return true;
    }


    /**
     * @notes 登录密码校验
     * @param $password
     * @param $other
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/10 下午1:58
     */
    public function checkPassword($password, $other, $data)
    {
        //账号安全机制，连续输错后锁定，防止账号密码暴力破解
        $staffAccountSafeCache = new StaffAccountSafeCache();
        if (!$staffAccountSafeCache->isSafe()) {
            return '密码连续' . $staffAccountSafeCache->count . '次输入错误，请' . $staffAccountSafeCache->minute . '分钟后重试';
        }

        $where = [];
        if ($data['scene'] == LoginEnum::ACCOUNT_PASSWORD) {
            // 手机号密码登录
            $where = ['mobile' => $data['mobile']];
        }

        $staffInfo = Staff::where($where)
            ->field(['password'])
            ->findOrEmpty();

        if ($staffInfo->isEmpty()) {
            return '账户不存在';
        }
        if (empty($staffInfo['password'])) {
            $staffAccountSafeCache->record();
            return '密码不存在';
        }

        $passwordSalt = Config::get('project.unique_identification');
        if ($staffInfo['password'] !== create_password($password, $passwordSalt)) {
            $staffAccountSafeCache->record();
            return '密码错误';
        }

        $staffAccountSafeCache->relieve();

        return true;
    }

    /**
     * @notes 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/10 下午2:01
     */
    public function checkCode($code, $rule, $data)
    {
        $smsDriver = new SmsDriver();
        $result = $smsDriver->verify($data['mobile'], $code, NoticeEnum::LOGIN_CAPTCHA_STAFF);
        if ($result) {
            return true;
        }
        return '验证码错误';
    }
}