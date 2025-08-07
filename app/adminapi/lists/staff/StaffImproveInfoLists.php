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

namespace app\adminapi\lists\staff;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\model\staff\Staff;

class StaffImproveInfoLists extends BaseAdminDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/9/5 下午12:05
     */
    public function where()
    {
        $where = [];
        if (isset($this->params['name']) && $this->params['name'] != '') {
            $where[] = ['s.name','=',$this->params['name']];
        }
        if (isset($this->params['sn']) && $this->params['sn'] != '') {
            $where[] = ['s.sn','=',$this->params['sn']];
        }
        if (isset($this->params['sex']) && $this->params['sex'] != '') {
            $where[] = ['s.sex','=',$this->params['sex']];
        }
        return $where;
    }

    /**
     * @notes 列表
     * @return array
     * @author ljj
     * @date 2024/9/5 下午12:09
     */
    public function lists(): array
    {
        $lists = (new Staff())->alias('s')
            ->join('staff_improve_info sii','sii.staff_id = s.id')
            ->field('s.name,s.sn,s.work_image,sii.skill_id,sii.goods_id,sii.id,sii.verify_status,sii.verify_remarks,sii.create_time')
            ->order(['sii.id'=>'desc'])
            ->where($this->where())
            ->append(['verify_status_desc','skill_name'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/9/5 下午12:09
     */
    public function count(): int
    {
        return (new Staff())->alias('s')
            ->join('staff_improve_info sii','sii.staff_id = s.id')
            ->where($this->where())
            ->count();
    }
}