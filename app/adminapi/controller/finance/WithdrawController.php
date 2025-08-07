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

namespace app\adminapi\controller\finance;

use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\finance\WithdrawLists;
use app\adminapi\logic\finance\WithdrawLogic;
use app\adminapi\validate\financce\WithdrawValidate;
use app\common\enum\WithdrawEnum;

class WithdrawController extends BaseAdminController
{
    /**
     * @notes 列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/6 下午3:49
     */
    public function lists()
    {
        return $this->dataLists(new WithdrawLists());
    }

    /**
     * @notes 详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/6 下午4:02
     */
    public function detail()
    {
        $params = (new WithdrawValidate())->goCheck('detail');
        $result = WithdrawLogic::detail($params);
        return $this->data($result);
    }

    /**
     * @notes 审核
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/6 下午4:31
     */
    public function verify()
    {
        $params = (new WithdrawValidate())->post()->goCheck('verify');
        $result = WithdrawLogic::verify($params);
        if(true === $result) {
            return $this->success('操作成功',[],1,1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 转账
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/6 下午4:39
     */
    public function transfer()
    {
        $params = (new WithdrawValidate())->post()->goCheck('transfer');
        $result = WithdrawLogic::transfer($params);
        if(true === $result) {
            return $this->success('操作成功',[],1,1);
        }
        return $this->fail($result);
    }

    /**
     * @notes 枚举列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/6 下午4:52
     */
    public function enumLists()
    {
        $result['type'] = WithdrawEnum::getTypeDesc();
        $result['source_type'] = WithdrawEnum::getSourceTypeDesc();
        return $this->data($result);
    }
}
