<?php
// +----------------------------------------------------------------------
// | LikeShop有特色的全开源社交分销电商系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 商业用途务必购买系统授权，以免引起不必要的法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | 微信公众号：好象科技
// | 访问官网：http://www.likemarket.net
// | 访问社区：http://bbs.likemarket.net
// | 访问手册：http://doc.likemarket.net
// | 好象科技开发团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | Author: LikeShopTeam
// +----------------------------------------------------------------------

namespace app\staffapi\logic;

use app\common\enum\AccountLogEnum;
use app\common\enum\StaffAccountLogEnum;
use app\common\enum\WithdrawEnum;
use app\common\logic\AccountLogLogic;
use app\common\logic\BaseLogic;
use app\common\logic\StaffAccountLogLogic;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffWithdraw;
use app\common\model\staff\StaffWithdrawAccount;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Db;

/**
 * 提现逻辑层
 * Class WithdrawLogic
 * @package app\shopapi\logic
 */
class WithdrawLogic extends BaseLogic
{
    /**
     * @notes 获取提现配置
     * @return array
     * @author ljj
     * @date 2024/10/17 上午10:34
     */
    public static function getConfig($staffId)
    {
        $config = [
            'withdraw_way'      => ConfigService::get('withdraw_config', 'withdraw_way', [ WithdrawEnum::WAY_WECHAT ]),
            'min_money'         => ConfigService::get('withdraw_config', 'min_money', 10),
            'max_money'         => ConfigService::get('withdraw_config', 'max_money', 100),
        ];
        $withdrawAccount = StaffWithdrawAccount::where(['staff_id'=>$staffId])->findOrEmpty()->toArray();

        foreach($config['withdraw_way'] as $value) {
            $withdrawWayName = WithdrawEnum::getTypeDesc($value);
            $name = '';
            $account = '';
            $image = '';
            switch ($value) {
                case WithdrawEnum::WAY_WECHAT:
                    $name = $withdrawAccount['wechat_name'] ?? '';
                    $account = $withdrawAccount['wechat_mobile'] ?? '';
                    $image = 'resource/image/pay/wechat.png';
                    break;
                case WithdrawEnum::WAY_ALI:
                    $name = $withdrawAccount['alipay_name'] ?? '';
                    $account = $withdrawAccount['alipay_account'] ?? '';
                    $image = 'resource/image/pay/alipay.png';
                    break;
                case WithdrawEnum::WAY_BANK:
                    $name = $withdrawAccount['bank_holder_name'] ?? '';
                    $account = $withdrawAccount['bank_number'] ?? '';
                    $image = 'resource/image/pay/bank.png';
                    break;
            }

            $config['type'][] = [
                'withdraw_way_name' => $withdrawWayName,
                'name' => $name,
                'account' => $account,
                'way' => $value,
                'image' => FileService::getFileUrl($image),
            ];
        }
        return $config;
    }

    /**
     * @notes 提现申请
     * @param $params
     * @return false|mixed
     * @author ljj
     * @date 2024/10/17 上午11:13
     */
    public static function apply($params)
    {
        Db::startTrans();
        try {
            // 手续费,单位：元
            if ($params['type'] == StaffAccountLogEnum::DEPOSIT) {
                //保证金不需要手续费
                $serviceRatio = 0;
            } else {
                $serviceRatio =  ConfigService::get('withdraw_config', 'service_ratio', 5);
                $serviceRatio = bcadd(($params['money'] * $serviceRatio / 100), 0, 2);
            }

            //提现账户
            $withdrawAccount = StaffWithdrawAccount::where(['staff_id'=>$params['staff_id']])->findOrEmpty()->toArray();
            $realName = '';
            $account = '';
            $bank = '';
            $openingBank = '';
            switch ($params['way']) {
                case WithdrawEnum::WAY_WECHAT:
                    $realName = $withdrawAccount['wechat_name'];
                    $account = $withdrawAccount['wechat_mobile'];
                    break;
                case WithdrawEnum::WAY_ALI:
                    $realName = $withdrawAccount['alipay_name'];
                    $account = $withdrawAccount['alipay_account'];
                    break;
                case WithdrawEnum::WAY_BANK:
                    $realName = $withdrawAccount['bank_holder_name'];
                    $openingBank = $withdrawAccount['bank_opening'];
                    $bank = $withdrawAccount['bank_number'];
                    break;
            }

            //创建提现记录
            $withdrawApply = StaffWithdraw::create([
                'sn' => generate_sn(new StaffWithdraw(), 'sn'),
                'staff_id' => $params['staff_id'],
                'type' => $params['way'],
                'source_type' => $params['type'],
                'money' => $params['money'],
                'left_money' => $params['money'] - $serviceRatio,
                'service_ratio' => $serviceRatio,
                'real_name' => $realName,
                'account' => $account,
                'bank' => $bank,
                'opening_bank' => $openingBank,
            ]);

            // 扣减师傅金额
            $staff = Staff::find($params['staff_id']);
            $changeObject = 0;
            $changeType = 0;
            switch ($params['type']) {
                case StaffAccountLogEnum::DEPOSIT:
                    $staff->staff_deposit = $staff->staff_deposit - $params['money'];
                    $changeObject = StaffAccountLogEnum::DEPOSIT;
                    $changeType = StaffAccountLogEnum::STAFF_WITHDRAW_DEC_DEPOSIT;
                    break;
                case StaffAccountLogEnum::EARNINGS:
                    $staff->staff_earnings = $staff->staff_earnings - $params['money'];
                    $changeObject = StaffAccountLogEnum::EARNINGS;
                    $changeType = StaffAccountLogEnum::WITHDRAW_DEC_EARNINGS;
                    break;
            }
            $staff->save();

            // 增加账户流水变动记录
            StaffAccountLogLogic::add($staff->id, $changeObject,$changeType,AccountLogEnum::DEC, $params['money'], $withdrawApply->sn);

            Db::commit();
            return $withdrawApply->id;
        } catch (\Exception $e) {
            Db::rollback();
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 提现详情
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/10/17 上午11:22
     */
    public static function detail($params)
    {
        $result = StaffWithdraw::where(['id'=>$params['id']])
            ->append(['type_desc'])
            ->findOrEmpty()
            ->toArray();
        if (!empty($result)) {
            if ($result['status'] == WithdrawEnum::STATUS_WAIT) {
                $result['status_desc'] = '待审核';
            }
            if ($result['status'] == WithdrawEnum::STATUS_ING) {
                $result['status_desc'] = '审核通过';
            }
            if ($result['status'] == WithdrawEnum::STATUS_SUCCESS) {
                $result['status_desc'] = '已转账';
            }
            if ($result['status'] == WithdrawEnum::STATUS_FAIL && $result['verify_status'] == 2) {
                $result['status_desc'] = '审核拒绝';
            }
            if ($result['status'] == WithdrawEnum::STATUS_FAIL && $result['transfer_status'] == 2) {
                $result['status_desc'] = '转账失败';
            }
        }

        return $result;
    }
}