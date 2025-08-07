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


namespace app\common\cache;



use app\common\model\staff\Staff;
use app\common\model\staff\StaffSession;

class StaffTokenCache extends BaseCache
{

    private $prefix = 'token_staff_';

    /**
     * @notes 通过token获取缓存用户信息
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 上午11:46
     */
    public function getStaffInfo($token)
    {
        //直接从缓存获取
        $staffInfo = $this->get($this->prefix . $token);
        if ($staffInfo) {
            return $staffInfo;
        }

        //从数据获取信息被设置缓存(可能后台清除缓存）
        $staffInfo = $this->setStaffInfo($token);
        if ($staffInfo) {
            return $staffInfo;
        }

        return false;
    }

    /**
     * @notes 通过有效token设置用户信息缓存
     * @param $token
     * @return array|false|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/10 上午11:46
     */
    public function setStaffInfo($token)
    {
        $staffSession = StaffSession::where([['token', '=', $token], ['expire_time', '>', time()]])->find();

        if (empty($staffSession)) {
            return [];
        }
        $staff = Staff::where('id', '=', $staffSession->staff_id)
            ->find();

        $staffInfo = [
            'staff_id' => $staff->id,
            'name' => $staff->name,
            'token' => $token,
            'sn' => $staff->sn,
            'mobile' => $staff->mobile,
            'avatar' => $staff->work_image,
            'terminal' => $staffSession->terminal,
            'expire_time' => $staffSession->expire_time,
        ];

        $this->set($this->prefix . $token, $staffInfo, new \DateTime(Date('Y-m-d H:i:s', $staffSession->expire_time)));
        return $this->getStaffInfo($token);
    }

    /**
     * @notes 删除token缓存
     * @param $token
     * @return bool
     * @author ljj
     * @date 2024/10/10 上午11:47
     */
    public function deleteStaffInfo($token)
    {
        return $this->delete($this->prefix . $token);
    }


}