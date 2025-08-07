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

namespace app\common\enum;

class WithdrawEnum
{
    //提现方法
    const WAY_WECHAT = 1; //微信
    const WAY_ALI = 2; //支付宝
    const WAY_BANK = 3; //银行卡


    //提现状态
    const STATUS_WAIT = 1;
    const STATUS_ING = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAIL = 4;


    //提现来源
    const SOURCCE_TYPE_DEPOSIT = 1; //保证金提现
    const SOURCCE_TYPE_EARNINGS = 2; //佣金提现


    /**
     * @notes 提现方法描述
     * @param $type
     * @param $flag
     * @return string|string[]
     * @author ljj
     * @date 2024/9/5 下午5:03
     */
    public static function getTypeDesc($value = true)
    {
        $desc = [
            self::WAY_WECHAT => '微信',
            self::WAY_ALI => '支付宝',
            self::WAY_BANK => '银行卡',
        ];
        if($value === true) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 提现状态描述
     * @param $status
     * @param $flag
     * @return string|string[]
     * @author ljj
     * @date 2024/9/5 下午5:04
     */
    public static function getStatusDesc($value = true)
    {
        $desc = [
            self::STATUS_WAIT => '待提现',
            self::STATUS_ING => '提现中',
            self::STATUS_SUCCESS => '提现成功',
            self::STATUS_FAIL => '提现失败',
        ];
        if($value === true) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }

    /**
     * @notes 提现来源
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/6 下午3:29
     */
    public static function getSourceTypeDesc($value = true)
    {
        $desc = [
            self::SOURCCE_TYPE_DEPOSIT => '保证金提现',
            self::SOURCCE_TYPE_EARNINGS => '佣金提现'
        ];
        if($value === true) {
            return $desc;
        }
        return $desc[$value] ?? '';
    }
}
