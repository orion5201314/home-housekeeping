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

namespace app\staffapi\lists;


use app\common\enum\StaffAccountLogEnum;
use app\common\model\staff\StaffWithdraw;

class WithdrawLists extends BaseStaffDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/17 上午11:17
     */
    public function where()
    {
        $where[] = ['staff_id', '=', $this->staffId];
        if (isset($this->params['source_type']) && $this->params['source_type'] != '') {
            $where[] = ['source_type','=',$this->params['source_type']];
        }else {
            $where[] = ['source_type','=',StaffAccountLogEnum::DEPOSIT];
        }
        if (isset($this->params['status']) && $this->params['status']) {
            $where[] = ['status','=',$this->params['status']];
        }

        return $where;
    }

    /**
     * @notes 提现列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/17 上午11:20
     */
    public function lists(): array
    {
        $lists = StaffWithdraw::field('id,type,money,status,verify_remarks,create_time')
            ->append(['type_desc','status_desc'])
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/17 上午11:20
     */
    public function count(): int
    {
        return StaffWithdraw::where($this->where())->count();
    }
}