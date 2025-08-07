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


namespace app\common\enum;


class StaffEnum
{
    //服务状态
    const STATUS_NORMAL = 1;//正常
    const STATUS_FROZEN = 0;//冻结

    //工作状态
    const WORK_STATUS_AFOOT = 1;//接单中
    const WORK_STATUS_REST = 0;//休息中

    //申请状态
    const APPLY_STATUS_WAIT = 0;//待审核
    const APPLY_STATUS_SUCCESS = 1;//审核成功
    const APPLY_STATUS_FAIL = 2;//审核拒绝

    //审核状态
    const VERIFY_STATUS_WAIT = 0;//待审核
    const VERIFY_STATUS_SUCCESS = 1;//审核成功
    const VERIFY_STATUS_FAIL = 2;//审核拒绝


    /**
     * @notes 服务状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 上午10:57
     */
    public static function getStatusDesc($value = true)
    {
        $data = [
            self::STATUS_NORMAL => '正常',
            self::STATUS_FROZEN => '冻结'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }

    /**
     * @notes 工作状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 上午10:57
     */
    public static function getWorkStatusDesc($value = true)
    {
        $data = [
            self::WORK_STATUS_AFOOT => '接单中',
            self::WORK_STATUS_REST => '休息中'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }

    /**
     * @notes 学历
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 上午10:57
     */
    public static function getEducationDesc($value = true)
    {
        $data = ['小学','初中','高中','大专','本科','硕士','博士'];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }

    /**
     * @notes 民族
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 下午5:53
     */
    public static function getNationDesc($value = true)
    {
        $data = ['汉族','蒙古族','回族','藏族','维吾尔族','苗族','彝族','壮族','布依族','朝鲜族','满族','侗族','瑶族','白族','土家族','哈尼族','哈萨克族','傣族','黎族','僳僳族','佤族','畲族','高山族','拉祜族','水族','东乡族','纳西族','景颇族','柯尔克孜族','土族','达斡尔族','仫佬族','羌族','布朗族','撒拉族','毛南族','仡佬族','锡伯族','阿昌族','普米族','塔吉克族','怒族','乌孜别克族','俄罗斯族','鄂温克族','德昂族','保安族','裕固族','京族','塔塔尔族','独龙族','鄂伦春族','赫哲族','门巴族','珞巴族','基诺族'];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }

    /**
     * @notes 申请状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/4 下午5:40
     */
    public static function getApplyStatusDesc($value = true)
    {
        $data = [
            self::APPLY_STATUS_WAIT => '待审核',
            self::APPLY_STATUS_SUCCESS => '审核成功',
            self::APPLY_STATUS_FAIL => '审核拒绝'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }

    /**
     * @notes 审核状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/5 下午12:10
     */
    public static function getVerifyStatusDesc($value = true)
    {
        $data = [
            self::VERIFY_STATUS_WAIT => '待审核',
            self::VERIFY_STATUS_SUCCESS => '审核成功',
            self::VERIFY_STATUS_FAIL => '审核拒绝'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value] ?? '';
    }
}