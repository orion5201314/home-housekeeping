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

namespace app\staffapi\logic;

use app\common\logic\BaseLogic;
use app\common\model\staff\StaffDeposit;
use app\common\model\staff\StaffDepositRecharge;

class StaffDepositLogic extends BaseLogic
{
    /**
     * @notes 保证金列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/17 下午4:56
     */
    public function lists(): array
    {
        $lists = (new StaffDeposit())->field('id,amount,name,order_num,create_time')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 充值保证金
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/10/18 上午10:16
     */
    public function recharge($params)
    {
        $info = StaffDepositRecharge::create([
            'sn' => generate_sn((new StaffDepositRecharge()), 'sn'),
            'staff_id' => $params['staff_id'],
            'terminal' => $params['terminal'],
            'amount' => $params['amount'],
            'pay_way' => $params['pay_way'],
        ]);
        return ['order_id' => $info->id, 'type' => 'deposit'];
    }

    /**
     * @notes 充值详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/10/18 上午11:01
     */
    public function rechargeDetail($id)
    {
        $info = StaffDepositRecharge::field(['id,staff_id,sn,transaction_id,terminal,amount'])->findOrEmpty($id)->toArray();
        return $info;
    }
}