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

namespace app\adminapi\validate\staff;


use app\common\model\staff\StaffDeposit;
use app\common\validate\BaseValidate;

class StaffDepositValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'amount' => 'require|float|gt:0',
        'order_num' => 'require|number',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'name.require' => '请输入师傅姓名',
        'amount.require' => '请输入保证金金额',
        'amount.float' => '保证金金额值错误',
        'amount.gt' => '保证金金额必须大于零',
        'order_num.require' => '请输入日接单数量',
        'order_num.number' => '日接单数量值错误',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','amount','order_num']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','amount','order_num']);
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验ID
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/21 上午10:19
     */
    public function checkId($value,$rule,$data)
    {
        $result = StaffDeposit::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '保证金不存在';
        }
        return true;
    }

    /**
     * @notes 校验名称
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/21 上午10:21
     */
    public function checkName($value,$rule,$data)
    {
        $where[] = ['name','=',$value];
        if (isset($data['id'])) {
            $where[] = ['id','<>',$data['id']];
        }
        $result = StaffDeposit::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '保证金名称已存在，请重新输入';
        }
        return true;
    }
}