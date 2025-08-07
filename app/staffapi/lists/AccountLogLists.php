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

namespace app\staffapi\lists;


use app\common\enum\StaffAccountLogEnum;
use app\common\model\staff\StaffAccountLog;

class AccountLogLists extends BaseStaffDataLists
{
    /**
     * @notes 搜索条件
     * @return array
     * @author ljj
     * @date 2024/10/17 上午9:56
     */
    public function where()
    {
        $where[] = ['staff_id','=',$this->staffId];
        if (isset($this->params['change_object']) && $this->params['change_object'] != '') {
            $where[] = ['change_object','=',$this->params['change_object']];
        }else {
            $where[] = ['change_object','=',StaffAccountLogEnum::DEPOSIT];
        }
        if (isset($this->params['action']) && $this->params['action'] != '') {
            $where[] = ['action','=',$this->params['action']];
        }

        return $where;
    }

    /**
     * @notes 明细列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/17 上午9:56
     */
    public function lists(): array
    {
        $lists = StaffAccountLog::field('id,change_amount,change_type,action,remark,create_time')
            ->append(['change_type_desc'])
            ->where(self::where())
            ->limit($this->limitOffset, $this->limitLength)
            ->order('id','desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 数量
     * @return int
     * @author ljj
     * @date 2024/10/17 上午9:57
     */
    public function count(): int
    {
        return StaffAccountLog::where(self::where())->count();
    }
}