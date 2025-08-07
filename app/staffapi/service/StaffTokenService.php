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


namespace app\staffapi\service;

use app\common\cache\StaffTokenCache;
use app\common\model\staff\StaffSession;
use think\facade\Config;

class StaffTokenService
{
    /**
     * @notes 设置token
     * @param $staffId
     * @param $terminal
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 上午11:39
     */
    public static function setToken($staffId, $terminal)
    {
        $time = time();
        $staffSession = StaffSession::where([['staff_id', '=', $staffId], ['terminal', '=', $terminal]])->find();

        //获取token延长过期的时间
        $expireTime = $time + Config::get('project.staff_token.expire_duration');
        $staffTokenCache = new StaffTokenCache();

        //token处理
        if ($staffSession) {

            //清空缓存
            $staffTokenCache->deleteStaffInfo($staffSession->token);
            //重新获取token
            $staffSession->token = create_token($staffId);
            $staffSession->expire_time = $expireTime;
            $staffSession->update_time = $time;
            $staffSession->save();
        } else {
            //找不到在该终端的token记录，创建token记录
            $staffSession = StaffSession::create([
                'staff_id' => $staffId,
                'terminal' => $terminal,
                'token' => create_token($staffId),
                'expire_time' => $expireTime
            ]);

        }
        return $staffTokenCache->setStaffInfo($staffSession->token);
    }

    /**
     * @notes 延长token过期时间
     * @param $token
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 上午11:40
     */
    public static function overtimeToken($token)
    {
        $time = time();
        $staffSession = StaffSession::where('token', '=', $token)->find();
        //延长token过期时间
        $staffSession->expire_time = $time + Config::get('project.staff_token.expire_duration');
        $staffSession->update_time = $time;
        $staffSession->save();
        return (new StaffTokenCache())->setStaffInfo($staffSession->token);
    }

    /**
     * @notes 设置token为过期
     * @param $token
     * @return false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 上午11:42
     */
    public static function expireToken($token)
    {
        $staffSession = StaffSession::where('token', '=', $token)
            ->find();
        if (empty($staffSession)) {
            return false;
        }

        $time = time();
        $staffSession->expire_time = $time;
        $staffSession->update_time = $time;
        $staffSession->save();

        return (new  StaffTokenCache())->deleteStaffInfo($token);

    }

}