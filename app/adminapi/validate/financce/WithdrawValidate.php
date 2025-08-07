<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------

namespace app\adminapi\validate\financce;

use app\common\enum\WithdrawEnum;
use app\common\model\staff\StaffWithdraw;
use app\common\validate\BaseValidate;

class WithdrawValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'verify_status' => 'require|in:1,2',
        'verify_remarks' => 'requireIf:verify_status,2',
        'transfer_status' => 'require|in:1,2',
        'transfer_voucher' => 'requireIf:transfer_status,1',
        'transfer_remark' => 'requireIf:transfer_status,2',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'verify_status.require' => '参数缺失',
        'verify_status.in' => '参数错误',
        'verify_remarks.requireIf' => '请输入拒绝原因',
        'transfer_status.require' => '参数缺失',
        'transfer_status.in' => '参数错误',
        'transfer_voucher.requireIf' => '请上传转账凭证',
        'transfer_remark.requireIf' => '请输入失败原因',
    ];


    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneVerify()
    {
        return $this->only(['id','verify_status','verify_remarks'])
            ->append('id', 'checkVerify');
    }

    public function sceneTransfer()
    {
        return $this->only(['id','transfer_status','transfer_voucher','transfer_remark'])
            ->append('id', 'checkTransfer');
    }

    /**
     * @notes 校验审核
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/6 下午4:11
     */
    public function checkVerify($value, $rule, $data)
    {
        $result = StaffWithdraw::findOrEmpty($data['id']);
        if ($result->isEmpty()) {
            return '提现记录不存在';
        }
        if ($result->status != WithdrawEnum::STATUS_WAIT) {
            return '提现记录状态异常';
        }

        return true;
    }

    /**
     * @notes 校验审核
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/6 下午4:11
     */
    public function checkTransfer($value, $rule, $data)
    {
        $result = StaffWithdraw::findOrEmpty($data['id']);
        if ($result->isEmpty()) {
            return '提现记录不存在';
        }
        if ($result->status != WithdrawEnum::STATUS_ING) {
            return '提现记录状态异常';
        }

        return true;
    }
}