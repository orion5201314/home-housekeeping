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
use app\common\lists\ListsSearchInterface;
use app\common\model\staff\Staff;

class StaffLists extends BaseAdminDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2022/2/10 11:23 上午
     */
    public function where(): array
    {
        $where[] = ['is_staff','=',1];
        if (isset($this->params['staff_info']) && $this->params['staff_info'] != '') {
            $where[] = ['name|sn','like','%'.$this->params['staff_info'].'%'];
        }
        if (isset($this->params['work_status']) && $this->params['work_status'] != '') {
            $where[] = ['work_status','=',$this->params['work_status']];
        }
        if (isset($this->params['status']) && $this->params['status'] != '') {
            $where[] = ['status','=',$this->params['status']];
        }
        if (isset($this->params['skill_id']) && $this->params['skill_id'] != '') {
            $where[] = ['skill_id','in',$this->params['skill_id']];
        }
        if (isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $where[] = ['create_time','>=',strtotime($this->params['start_time'])];
        }
        if (isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $where[] = ['create_time','<=',strtotime($this->params['end_time'])];
        }
        return $where;
    }

    /**
     * @notes 师傅列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/10 11:28 上午
     */
    public function lists(): array
    {
        $lists = (new Staff())->field('id,sn,name,staff_deposit,staff_earnings,work_image,status,work_status,sort,create_time')
            ->order(['sort'=>'desc','id'=>'desc'])
            ->where($this->where())
            ->append(['status_desc','work_status_desc','deposit_info','total_order_num'])
            ->limit($this->limitOffset, $this->limitLength)
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 师傅总数
     * @return int
     * @author ljj
     * @date 2022/2/10 11:29 上午
     */
    public function count(): int
    {
        return (new Staff())->where($this->where())->count();
    }
}