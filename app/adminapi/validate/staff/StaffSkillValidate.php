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


use app\common\model\staff\StaffSkill;
use app\common\validate\BaseValidate;

class StaffSkillValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'name' => 'require|checkName',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'name.require' => '请输入师傅姓名',
        'status.require' => '请选择状态',
        'status.in' => '状态选择范围在[0,1]',
    ];

    public function sceneAdd()
    {
        return $this->only(['name','status']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','name','status']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneStatus()
    {
        return $this->only(['id','status']);
    }

    /**
     * @notes 校验技能ID
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/8/21 上午10:19
     */
    public function checkId($value,$rule,$data)
    {
        $result = StaffSkill::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '技能不存在';
        }
        return true;
    }

    /**
     * @notes 校验技能名称
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
        $result = StaffSkill::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '技能名称已存在，请重新输入';
        }
        return true;
    }

    /**
     * @notes 校验删除
     * @param $value
     * @param $rule
     * @param $data
     * @return true
     * @author ljj
     * @date 2024/8/21 上午10:21
     */
    public function checkDel($value,$rule,$data)
    {
        return true;
    }
}