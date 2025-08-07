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

namespace app\staffapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\staff\Staff;
use app\staffapi\service\StaffTokenService;
use app\common\service\{ConfigService, FileService};
use think\facade\Config;

/**
 * 登录逻辑
 * Class LoginLogic
 * @package app\staffapi\logic
 */
class LoginLogic extends BaseLogic
{
    /**
     * @notes 注册
     * @param array $params
     * @return bool
     * @author ljj
     * @date 2024/10/10 下午1:54
     */
    public static function register(array $params)
    {
        try {
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);

            Staff::create([
                'mobile' => $params['mobile'],
                'password' => $password,
                'channel' => $params['channel'],
            ]);

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 登录
     * @param $params
     * @return array|false
     * @author ljj
     * @date 2024/10/10 下午2:05
     */
    public static function login($params)
    {
        try {
            $staff = Staff::where(['mobile' => $params['mobile']])->findOrEmpty();

            //更新登录信息
            $staff->login_time = time();
            $staff->login_ip = request()->ip();
            $staff->save();

            //设置token
            $staffInfo = StaffTokenService::setToken($staff->id, $params['terminal']);

            //返回登录信息
            $avatar = $staff->work_image ?: FileService::getFileUrl(config('project.default_image.staff_avatar'));
            return [
                'name' => $staffInfo['name'],
                'sn' => $staffInfo['sn'],
                'mobile' => $staffInfo['mobile'],
                'avatar' => $avatar,
                'token' => $staffInfo['token'],
            ];
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 退出登录
     * @param $staffInfo
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 下午2:06
     */
    public static function logout($staffInfo)
    {
        //token不存在，不注销
        if (!isset($staffInfo['token'])) {
            return false;
        }

        //设置token过期
        return StaffTokenService::expireToken($staffInfo['token']);
    }
}