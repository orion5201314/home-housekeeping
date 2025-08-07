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

namespace app\adminapi\logic\finance;

use app\common\enum\StaffAccountLogEnum;
use app\common\enum\WithdrawEnum;
use app\common\logic\BaseLogic;
use app\common\logic\StaffAccountLogLogic;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffWithdraw;
use think\facade\Db;

class WithdrawLogic extends BaseLogic
{
    /**
     * @notes 详情
     * @param $params
     * @return mixed
     * @author ljj
     * @date 2024/9/6 下午4:02
     */
    public static function detail($params)
    {
        $result = StaffWithdraw::alias('sw')
            ->leftJoin('staff s', 's.id = sw.staff_id')
            ->field('sw.*,s.name,s.sn as staff_sn,s.mobile as staff_mobile')
            ->append(['source_type_desc','type_desc','status_desc'])
            ->where(['sw.id'=>$params['id']])
            ->findOrEmpty()
            ->toArray();

        return $result;
    }

    /**
     * @notes 审核
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/6 下午4:30
     */
    public static function verify($params)
    {
        Db::startTrans();
        try {
            $StaffWithdraw = StaffWithdraw::findOrEmpty($params['id']);
            $StaffWithdraw->status = $params['verify_status'] == 1 ? WithdrawEnum::STATUS_ING : WithdrawEnum::STATUS_FAIL;
            $StaffWithdraw->verify_status = $params['verify_status'];
            $StaffWithdraw->verify_remarks = $params['verify_remarks'] ?? '';
            $StaffWithdraw->verify_time = time();
            $StaffWithdraw->save();

            //提现拒绝
            if ($params['verify_status'] == 2) {
                // 回退提现金额
                $staff = Staff::findOrEmpty($StaffWithdraw->staff_id);
                $changeObject = StaffAccountLogEnum::DEPOSIT;
                $changeType = StaffAccountLogEnum::STAFF_WITHDRAW_FAIL_INC_DEPOSIT;
                if ($StaffWithdraw->source_type == WithdrawEnum::SOURCCE_TYPE_DEPOSIT) {
                    $staff->staff_deposit = $staff->staff_deposit + $StaffWithdraw->money;
                }
                if ($StaffWithdraw->source_type == WithdrawEnum::SOURCCE_TYPE_EARNINGS) {
                    $changeObject = StaffAccountLogEnum::EARNINGS;
                    $changeType = StaffAccountLogEnum::WITHDRAW_FAIL_INC_EARNINGS;
                    $staff->staff_earnings = $staff->staff_earnings + $StaffWithdraw->money;
                }
                $staff->save();

                // 增加账户流水变动记录
                StaffAccountLogLogic::add($StaffWithdraw->staff_id, $changeObject,$changeType, StaffAccountLogEnum::INC, $StaffWithdraw->money, '', $params['verify_remarks'] ?? '');
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 转账
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/9/6 下午4:38
     */
    public static function transfer($params)
    {
        Db::startTrans();
        try {
            $StaffWithdraw = StaffWithdraw::findOrEmpty($params['id']);
            $StaffWithdraw->status = $params['transfer_status'] == 1 ? WithdrawEnum::STATUS_SUCCESS : WithdrawEnum::STATUS_FAIL;
            $StaffWithdraw->transfer_status = $params['transfer_status'];
            $StaffWithdraw->transfer_voucher = $params['transfer_voucher'] ?? '';
            $StaffWithdraw->transfer_remark = $params['transfer_remark'] ?? '';
            $StaffWithdraw->transfer_time = time();
            $StaffWithdraw->save();

            //转账失败
            if ($params['transfer_status'] == 2) {
                // 回退提现金额
                $staff = Staff::findOrEmpty($StaffWithdraw->staff_id);
                $changeObject = StaffAccountLogEnum::DEPOSIT;
                $changeType = StaffAccountLogEnum::STAFF_WITHDRAW_FAIL_INC_DEPOSIT;
                if ($StaffWithdraw->source_type == WithdrawEnum::SOURCCE_TYPE_DEPOSIT) {
                    $staff->staff_deposit = $staff->staff_deposit + $StaffWithdraw->money;
                }
                if ($StaffWithdraw->source_type == WithdrawEnum::SOURCCE_TYPE_EARNINGS) {
                    $changeObject = StaffAccountLogEnum::EARNINGS;
                    $changeType = StaffAccountLogEnum::WITHDRAW_FAIL_INC_EARNINGS;
                    $staff->staff_earnings = $staff->staff_earnings + $StaffWithdraw->money;
                }
                $staff->save();

                // 增加账户流水变动记录
                StaffAccountLogLogic::add($StaffWithdraw->staff_id, $changeObject,$changeType, StaffAccountLogEnum::INC, $StaffWithdraw->money, '', $params['verify_remarks'] ?? '');
            }

            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}
