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

namespace app\staffapi\controller;

use app\staffapi\validate\{LoginAccountValidate, RegisterValidate, WechatLoginValidate};
use app\staffapi\logic\LoginLogic;

/**
 * 登录注册
 * Class LoginController
 * @package app\staffapi\controller
 */
class LoginController extends BaseStaffController
{

    public array $notNeedLogin = ['register', 'login'];


    /**
     * @notes 注册
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午12:11
     */
    public function register()
    {
        $params = (new RegisterValidate())->post()->goCheck('register');
        $result = LoginLogic::register($params);
        if (true === $result) {
            return $this->success('注册成功', [], 1, 1);
        }
        return $this->fail(LoginLogic::getError());
    }


    /**
     * @notes 登录
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午2:02
     */
    public function login()
    {
        $params = (new LoginAccountValidate())->post()->goCheck();
        $result = LoginLogic::login($params);
        if (false === $result) {
            return $this->fail(LoginLogic::getError());
        }
        return $this->data($result);
    }


    /**
     * @notes 退出登录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 下午2:06
     */
    public function logout()
    {
        LoginLogic::logout($this->staffInfo);
        return $this->success();
    }
}