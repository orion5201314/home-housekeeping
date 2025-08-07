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

namespace app\staffapi\controller;


use app\common\enum\StaffEnum;
use app\staffapi\lists\StaffIncomeLists;
use app\staffapi\logic\StaffLogic;
use app\staffapi\validate\StaffValidate;

class StaffController extends BaseStaffController
{
    public array $notNeedLogin = ['center','forgetPassword'];

    /**
     * @notes 师傅中心
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午4:21
     */
    public function center()
    {
        $result = (new StaffLogic())->center($this->staffInfo);
        return $this->success('',$result);
    }

    /**
     * @notes 师傅信息
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午4:23
     */
    public function info()
    {
        $result = StaffLogic::info($this->staffId);
        return $this->data($result);
    }


    public function setInfo()
    {
        $params = (new StaffValidate())->post()->goCheck('setInfo', ['staff_id' => $this->staffId]);
        (new StaffLogic)->setInfo($params);
        return $this->success('操作成功', [],1,1);
    }

    /**
     * @notes 忘记密码
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午4:29
     */
    public function forgetPassword()
    {
        $params = (new StaffValidate())->post()->goCheck('forgetPassword');
        $result = StaffLogic::forgetPassword($params);
        if (false === $result) {
            return $this->fail(StaffLogic::getError());
        }
        return $this->success('',[],1,1);
    }

    /**
     * @notes 修改密码
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午4:29
     */
    public function changePassword()
    {
        $params = (new StaffValidate())->post()->goCheck('changePassword',['staff_id'=>$this->staffId]);
        $result = StaffLogic::changePassword($params);
        if (false === $result) {
            return $this->fail(StaffLogic::getError());
        }
        return $this->success('',[],1,1);
    }

    /**
     * @notes 更新最后一次定位地址
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/10 下午5:51
     */
    public function updateLastAddress()
    {
        $params = $this->request->post();
        $params['staff_id'] = $this->staffId;
        StaffLogic::updateLastAddress($params);
        return $this->success('',[]);
    }

    /**
     * @notes 切换工作状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/11 下午1:58
     */
    public function changeWorkStatus()
    {
        StaffLogic::changeWorkStatus($this->staffId);
        return $this->success('操作成功',[]);
    }

    /**
     * @notes 技能列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/11 下午4:35
     */
    public function skillLists()
    {
        $result = (new StaffLogic())->skillLists();
        return $this->data($result);
    }

    /**
     * @notes 枚举列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/12 上午11:26
     */
    public function enumLists()
    {
        $result['education'] = (array)StaffEnum::getEducationDesc();
        $result['nation'] = StaffEnum::getNationDesc();
        return $this->success('',$result);
    }

    /**
     * @notes 申请入驻
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/12 下午2:53
     */
    public function apply()
    {
        $params = (new StaffValidate())->post()->goCheck('apply',['staff_id'=>$this->staffId]);
        $result = (new StaffLogic())->apply($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('申请成功，等待审核',[],1,1);
    }

    /**
     * @notes 申请详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/11 下午3:39
     */
    public function applyDetail()
    {
        $result = (new StaffLogic())->applyDetail($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 服务时间
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/14 下午12:12
     */
    public function serviceTime()
    {
        $result = (new StaffLogic())->serviceTime($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 设置忙时
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/14 下午3:02
     */
    public function setBusytime()
    {
        $params = (new StaffValidate())->post()->goCheck('setBusytime',['staff_id'=>$this->staffId]);
        (new StaffLogic())->setBusytime($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 获取完善资料信息
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/15 上午11:52
     */
    public function getImproveInfo()
    {
        $result = StaffLogic::getImproveInfo($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 完善资料
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/15 上午11:52
     */
    public function setImproveInfo()
    {
        $params = (new StaffValidate())->post()->goCheck('setImproveInfo',['staff_id'=>$this->staffId]);
        (new StaffLogic())->setImproveInfo($params);
        return $this->success('提交成功，等待审核',[],1,1);
    }

    /**
     * @notes 获取提现账户
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/16 下午5:00
     */
    public function getWithdrawAccount()
    {
        $result = (new StaffLogic())->getWithdrawAccount($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 设置提现账户
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/16 下午5:08、
     */
    public function setWithdrawAccount()
    {
        $params = $this->request->post();
        $params['staff_id'] = $this->staffId;
        (new StaffLogic())->setWithdrawAccount($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 收入列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午2:12
     */
    public function incomeLists()
    {
        return $this->dataLists(new StaffIncomeLists());
    }
}