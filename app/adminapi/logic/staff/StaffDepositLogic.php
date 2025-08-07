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

namespace app\adminapi\logic\staff;


use app\common\logic\BaseLogic;
use app\common\model\staff\StaffDeposit;

class StaffDepositLogic extends BaseLogic
{
    /**
     * @notes 新增
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/21 上午10:22
     */
    public function add($params)
    {
        StaffDeposit::create([
            'name' => $params['name'],
            'amount' => $params['amount'],
            'order_num' => $params['order_num'],
        ]);

        return true;
    }

    /**
     * @notes 详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/8/21 上午10:24
     */
    public function detail($id)
    {
        $result = StaffDeposit::where(['id'=>$id])->findOrEmpty()->toArray();

        return $result;
    }

    /**
     * @notes 编辑
     * @param $params
     * @return true
     * @author ljj
     * @date 2024/8/21 上午10:24
     */
    public function edit($params)
    {
        StaffDeposit::update([
            'name' => $params['name'],
            'amount' => $params['amount'],
            'order_num' => $params['order_num'],
        ],['id'=>$params['id']]);

        return true;
    }

    /**
     * @notes 删除
     * @param $id
     * @return bool
     * @author ljj
     * @date 2024/8/21 上午10:25
     */
    public function del($id)
    {
        return StaffDeposit::destroy($id);
    }
}