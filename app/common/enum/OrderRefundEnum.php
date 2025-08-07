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

namespace app\common\enum;


class OrderRefundEnum
{
    //操作人类型
    const TYPE_SYSTEM   = 1;//系统
    const TYPE_ADMIN    = 2;//后台
    const TYPE_USER     = 3;//用户

    //退款状态
    const STATUS_ING        = 0;//退款中
    const STATUS_SUCCESS    = 1;//退款成功
    const STATUS_FAIL       = 2;//退款失败

    //退款方式
    const REFUND_WAY_ORIGINAL = 1;//原路退款
    const REFUND_WAY_BALANCE  = 2;//退回余额


    //退款订单分类
    const ORDER_CATEGORY_BASICS = 1;//基础
    const ORDER_CATEGORY_ADDITIONAL = 2;//加项
    const ORDER_CATEGORY_DIFFERENCE = 3;//补差价


    /**
     * @notes 操作人
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/11 2:17 下午
     */
    public static function getOperatorDesc($value = true)
    {
        $desc = [
            self::TYPE_SYSTEM           => '系统',
            self::TYPE_ADMIN            => '后台',
            self::TYPE_USER             => '用户',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }


    /**
     * @notes 退款状态
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/9/8 6:45 下午
     */
    public static function getStatusDesc($value = true)
    {
        $desc = [
            self::STATUS_ING                => '退款中',
            self::STATUS_SUCCESS            => '退款成功',
            self::STATUS_FAIL               => '退款失败',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }


    /**
     * @notes 退款方式
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/13 下午4:53
     */
    public static function getRefundWayDesc($value = true)
    {
        $desc = [
            self::REFUND_WAY_ORIGINAL => '原路退款',
            self::REFUND_WAY_BALANCE => '退回余额',
        ];

        if (true === $value) {
            return $desc;
        }
        return $desc[$value];
    }
}