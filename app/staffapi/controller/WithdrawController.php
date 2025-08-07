<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\staffapi\controller;


use app\staffapi\lists\WithdrawLists;
use app\staffapi\logic\WithdrawLogic;
use app\staffapi\validate\WithdrawValidate;

class WithdrawController extends BaseStaffController
{
    /**
     * @notes 获取提现配置
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/17 上午10:34
     */
    public function getConfig()
    {
        $result = WithdrawLogic::getConfig($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 提现申请
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/17 上午11:13
     */
    public function apply()
    {
        $params = (new WithdrawValidate())->post()->goCheck('apply', ['staff_id' => $this->staffId]);
        $result = WithdrawLogic::apply($params);
        if($result !== false) {
            return $this->success('操作成功', ['id' => $result]);
        }
        return $this->fail(WithdrawLogic::getError());
    }

    /**
     * @notes 提现列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/17 上午11:20
     */
    public function lists()
    {
        return $this->dataLists(new WithdrawLists());
    }

    /**
     * @notes 提现详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/17 上午11:22
     */
    public function detail()
    {
        $params = (new WithdrawValidate())->goCheck('detail');
        $result = WithdrawLogic::detail($params);
        return $this->data($result);
    }
}