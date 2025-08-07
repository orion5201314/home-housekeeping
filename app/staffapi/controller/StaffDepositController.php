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


use app\staffapi\logic\StaffDepositLogic;
use app\staffapi\validate\StaffDepositValidate;

class StaffDepositController extends BaseStaffController
{
    /**
     * @notes 保证金列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/17 下午4:45
     */
    public function lists()
    {
        $result = (new StaffDepositLogic())->lists();
        return $this->data($result);
    }

    /**
     * @notes 充值保证金
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 上午10:16
     */
    public function recharge()
    {
        $params = (new StaffDepositValidate())->post()->goCheck('recharge',['staff_id'=>$this->staffId,'terminal' => $this->staffInfo['terminal']]);
        $result = (new StaffDepositLogic())->recharge($params);
        return $this->success('',$result);
    }

    /**
     * @notes 充值详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 上午11:01
     */
    public function rechargeDetail()
    {
        $params = $this->request->get();
        $result = (new StaffDepositLogic())->rechargeDetail($params['id'] ?? 0);
        return $this->success('',$result);
    }
}