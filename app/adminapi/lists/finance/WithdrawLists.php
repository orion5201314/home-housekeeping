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

namespace app\adminapi\lists\finance;

use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\WithdrawEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\staff\StaffWithdraw;


class WithdrawLists extends BaseAdminDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/9/6 下午3:32
     */
    public function where(): array
    {
        $where = [];
        if (isset($this->params['staff_info']) && $this->params['staff_info'] != '') {
            $where[] = ['s.sn|s.mobile|s.name','like','%'.$this->params['staff_info'].'%'];
        }
        if (isset($this->params['source_type']) && $this->params['source_type'] != '') {
            $where[] = ['sw.source_type','=',$this->params['source_type']];
        }
        if (isset($this->params['type']) && $this->params['type'] != '') {
            $where[] = ['sw.type','=',$this->params['type']];
        }
        if(isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $where[] = ['sw.create_time', '>=', strtotime($this->params['start_time'])];
        }
        if(isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $where[] = ['sw.create_time', '<=', strtotime($this->params['end_time'])];
        }
        if (isset($this->params['status']) && !empty($this->params['status'])) {
            $where[] = ['sw.status','=',$this->params['status']];
        }

        return $where;
    }

    /**
     * @notes 列表
     * @return array
     * @author ljj
     * @date 2024/9/6 下午3:47
     */
    public function lists(): array
    {
        $lists = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->field('s.work_image,s.name,s.sn as staff_sn,sw.id,sw.type,sw.source_type,sw.money,sw.left_money,sw.service_ratio,sw.status,sw.create_time')
            ->append(['source_type_desc','type_desc','status_desc'])
            ->where($this->where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('sw.id', 'desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/9/6 下午3:47
     */
    public function count(): int
    {
        return StaffWithdraw::alias('sw')
                ->leftJoin('staff s', 's.id = sw.staff_id')
                ->where($this->where())
                ->count();
    }

    /**
     * @notes 数据统计
     * @return array
     * @author ljj
     * @date 2024/9/6 下午3:49
     */
    public function extend()
    {
        $where = $this->where();
        foreach ($where as $key => $item) {
            if ($item[0] == 'sw.status') {
                unset($where[$key]);
            }
        }
        $where = array_values($where);

        $all = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->where($where)
            ->count();
        $statusWait = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->where($where)
            ->where('sw.status', WithdrawEnum::STATUS_WAIT)
            ->count();
        $statusIng = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->where($where)
            ->where('sw.status', WithdrawEnum::STATUS_ING)
            ->count();
        $statusSuccess = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->where($where)
            ->where('sw.status', WithdrawEnum::STATUS_SUCCESS)
            ->count();
        $statusFail = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->where($where)
            ->where('sw.status', WithdrawEnum::STATUS_FAIL)
            ->count();
        return [
            'all' => $all,
            'status_wait' => $statusWait,
            'status_ing' => $statusIng,
            'status_success' => $statusSuccess,
            'status_fail' => $statusFail,
        ];
    }
}