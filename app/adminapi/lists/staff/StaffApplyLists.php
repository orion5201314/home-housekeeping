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
use app\common\enum\StaffEnum;
use app\common\lists\ListsExtendInterface;
use app\common\model\staff\Staff;

class StaffApplyLists extends BaseAdminDataLists implements ListsExtendInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/9/4 下午5:19
     */
    public function where()
    {
        $where = [];
        if (isset($this->params['apply_status']) && $this->params['apply_status'] != '') {
            $where[] = ['sa.apply_status','=',$this->params['apply_status']];
        }
        if (isset($this->params['staff_info']) && $this->params['staff_info'] != '') {
            $where[] = ['sa.name|s.sn','like','%'.$this->params['staff_info'].'%'];
        }
        if (isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $where[] = ['sa.create_time','>=',strtotime($this->params['start_time'])];
        }
        if (isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $where[] = ['sa.create_time','<=',strtotime($this->params['end_time'])];
        }
        return $where;
    }

    /**
     * @notes 列表
     * @return array
     * @author ljj
     * @date 2024/9/4 下午5:35
     */
    public function lists(): array
    {
        $lists = (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->field('IFNULL(sa.name,s.name) as name,s.mobile,IFNULL(sa.work_image,s.work_image) as work_image,IFNULL(sa.province_id,s.province_id) as province_id,IFNULL(sa.city_id,s.city_id) as city_id,IFNULL(sa.district_id,s.district_id) as district_id,IFNULL(sa.skill_id,s.skill_id) as skill_id,IFNULL(sa.goods_id,s.goods_id) as goods_id,sa.id,sa.apply_status,sa.create_time')
            ->order(['sa.id'=>'desc'])
            ->where($this->where())
            ->append(['apply_status_desc','skill_name','province','city','district'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/9/4 下午5:35
     */
    public function count(): int
    {
        return (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->where($this->where())
            ->count();
    }

    /**
     * @notes 数据统计
     * @return array
     * @author ljj
     * @date 2024/9/4 下午5:41
     */
    public function extend(): array
    {
        $where = $this->where();
        foreach ($where as $key => $item) {
            if ($item[0] == 'sa.apply_status') {
                unset($where[$key]);
            }
        }
        $where = array_values($where);

        $data['all'] =  (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->where($where)
            ->count();
        $data['wait'] = (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->where($where)
            ->where(['sa.apply_status'=>StaffEnum::APPLY_STATUS_WAIT])
            ->count();
        $data['success'] = (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->where($where)
            ->where(['sa.apply_status'=>StaffEnum::APPLY_STATUS_SUCCESS])
            ->count();
        $data['fail'] = (new Staff())->alias('s')
            ->join('staff_apply sa','sa.staff_id = s.id')
            ->where($where)
            ->where(['sa.apply_status'=>StaffEnum::APPLY_STATUS_FAIL])
            ->count();

        return $data;
    }
}