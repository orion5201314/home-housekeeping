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

namespace app\api\validate;


use app\common\enum\OrderEnum;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\validate\BaseValidate;

class StaffOrderValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|checkId',
        'verification_code' => 'require',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'verification_code.require' => '核销码不能为空',
    ];

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneConfirmService()
    {
        return $this->only(['id'])
            ->append('id','checkConfirmService');
    }

    public function sceneVerification()
    {
        return $this->only(['verification_code'])
            ->append('verification_code','checkVerification');
    }


    /**
     * @notes 检验订单id
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/2/28 10:12 上午
     */
    public function checkId($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '订单不存在';
        }
        return true;
    }

    /**
     * @notes 检验是否能确认服务
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/1 3:28 下午
     */
    public function checkConfirmService($value,$rule,$data)
    {
        $result = Order::where(['id'=>$value])->findOrEmpty();
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_APPOINT) {
            return '订单状态不正确，无法确认服务';
        }
        $staff_id = Staff::where('user_id',$data['user_id'])->value('id');
        if ($result['staff_id'] != $staff_id) {
            return '订单错误，无法确认服务';
        }
        return true;
    }

    /**
     * @notes 检验是否能核销
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2022/3/1 3:56 下午
     */
    public function checkVerification($value,$rule,$data)
    {
        $result = Order::where(['verification_code'=>$value])->findOrEmpty();
        if ($result->isEmpty()) {
            return '核销码不正确';
        }
        if ($result['order_status'] != OrderEnum::ORDER_STATUS_SERVICE) {
            return '订单状态不正确，无法核销';
        }
        $staff_id = Staff::where('user_id',$data['user_id'])->value('id');
        if ($result['staff_id'] != $staff_id) {
            return '订单错误，无法核销';
        }
        if ($result['verification_status'] == OrderEnum::VERIFICATION) {
            return '订单已核销';
        }
        return true;
    }
}