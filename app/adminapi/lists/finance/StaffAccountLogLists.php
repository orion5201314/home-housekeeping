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

namespace app\adminapi\lists\finance;


use app\adminapi\lists\BaseAdminDataLists;
use app\common\enum\StaffAccountLogEnum;
use app\common\lists\ListsExcelInterface;
use app\common\model\staff\StaffAccountLog;

class StaffAccountLogLists extends BaseAdminDataLists implements ListsExcelInterface
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/9/6 下午2:03
     */
    public function where()
    {
        $where = [];
        if (isset($this->params['change_object']) && $this->params['change_object'] != '') {
            $where[] = ['sal.change_object','=',$this->params['change_object']];
        }else {
            $where[] = ['sal.change_object','=',StaffAccountLogEnum::DEPOSIT];
        }
        if (isset($this->params['staff_info']) && $this->params['staff_info'] != '') {
            $where[] = ['s.sn|s.mobile|s.name','like','%'.$this->params['staff_info'].'%'];
        }
        if (isset($this->params['change_type']) && $this->params['change_type'] != '') {
            $where[] = ['sal.change_type','=',$this->params['change_type']];
        }
        // 开始时间
        if(isset($this->params['start_time']) && $this->params['start_time'] != '') {
            $where[] = ['sal.create_time', '>=', strtotime($this->params['start_time'])];
        }
        // 结束时间
        if(isset($this->params['end_time']) && $this->params['end_time'] != '') {
            $where[] = ['sal.create_time', '<=', strtotime($this->params['end_time'])];
        }

        return $where;
    }

    /**
     * @notes 列表
     * @return array
     * @author ljj
     * @date 2024/9/6 下午2:21
     */
    public function lists(): array
    {
        $lists = StaffAccountLog::alias('sal')
            ->join('staff s', 's.id = sal.staff_id')
            ->field('sal.id,s.sn as staff_sn,s.work_image,s.name,s.mobile,sal.change_amount,sal.left_amount,sal.action,sal.change_type,sal.association_sn,sal.create_time')
            ->append(['change_type_desc'])
            ->where(self::where())
            ->limit($this->limitOffset,$this->limitLength)
            ->order('sal.id','desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/9/6 下午2:21
     */
    public function count(): int
    {
        return StaffAccountLog::alias('sal')
            ->join('staff s', 's.id = sal.staff_id')
            ->where(self::where())
            ->count();
    }

    /**
     * @notes 导出字段
     * @return string[]
     * @author ljj
     * @date 2024/9/12 下午2:17
     */
    public function setExcelFields(): array
    {
        return [
            // '数据库字段名(支持别名) => 'Excel表字段名'
            'name' => '师傅名称',
            'staff_sn' => '师傅工号',
            'mobile' => '手机号码',
            'change_amount' => '变动金额',
            'left_amount' => '剩余金额',
            'change_type_desc' => '变动类型',
            'association_sn' => '来源单号',
            'create_time' => '记录时间',
        ];
    }

    /**
     * @notes 到处表名
     * @return string
     * @author ljj
     * @date 2024/9/12 下午2:17
     */
    public function setFileName(): string
    {
        $name = '师傅保证金明细列表';
        if (isset($this->params['change_object']) && $this->params['change_object'] == StaffAccountLogEnum::EARNINGS) {
            $name = '师傅佣金明细列表';
        }

        return $name;
    }
}