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

namespace app\common\model\order;


use app\common\enum\OrderLogEnum;
use app\common\model\auth\Admin;
use app\common\model\BaseModel;
use app\common\model\staff\Staff;
use app\common\model\user\User;

class OrderLog extends BaseModel
{
    /**
     * @notes 操作事件
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 2:21 下午
     */
    public function getChannelDescAttr($value,$data)
    {
        return OrderLogEnum::getRecordDesc($data['channel']);
    }


    public function getOperatorAttr($value,$data)
    {
        $operator = '未知';
        if ($data['type'] == OrderLogEnum::TYPE_SYSTEM) {
            $operator = '系统';
        }
        if ($data['type'] == OrderLogEnum::TYPE_ADMIN) {
            $operator = Admin::where('id',$data['operator_id'])->value('name');
        }
        if ($data['type'] == OrderLogEnum::TYPE_USER) {
            $operator = User::where('id',$data['operator_id'])->value('nickname');
        }
        if ($data['type'] == OrderLogEnum::TYPE_STAFF) {
            $operator = Staff::where('id',$data['operator_id'])->value('name');
        }
        return $operator;
    }
}