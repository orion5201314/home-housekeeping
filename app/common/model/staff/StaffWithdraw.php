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

namespace app\common\model\staff;

use app\common\enum\WithdrawEnum;
use app\common\model\BaseModel;
use app\common\service\FileService;
use think\model\concern\SoftDelete;

class StaffWithdraw extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';


    /**
     * @notes 提现方法
     * @return string|string[]
     * @author ljj
     * @date 2024/9/6 下午3:33
     */
    public function getTypeDescAttr($value,$data)
    {
        return WithdrawEnum::getTypeDesc($data['type']);
    }

    /**
     * @notes 提现来源
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/6 下午3:46
     */
    public function getSourceTypeDescAttr($value,$data)
    {
        return WithdrawEnum::getSourceTypeDesc($data['source_type']);
    }

    /**
     * @notes 提现状态
     * @param $value
     * @return string|string[]
     * @author ljj
     * @date 2024/9/6 下午3:33
     */
    public function getStatusDescAttr($value,$data)
    {
        return WithdrawEnum::getStatusDesc($data['status']);
    }

    /**
     * @notes 获取转账凭证
     * @param $value
     * @return string
     * @author ljj
     * @date 2024/9/6 下午3:34
     */
    public function getTransferVoucherAttr($value)
    {
        return empty($value) ? '' : FileService::getFileUrl($value);
    }

    /**
     * @notes 设置转账凭证
     * @param $value
     * @return string
     * @author ljj
     * @date 2024/9/6 下午3:34
     */
    public function setTransferVoucherAttr($value)
    {
        return empty($value) ? '' : FileService::setFileUrl($value);
    }

    /**
     * @notes 转账时间
     * @param $value
     * @return false|string
     * @author ljj
     * @date 2024/9/6 下午3:35
     */
    public function getTransferTimeAttr($value)
    {
        return empty($value) ? '-' : date('Y-m-d H:i:s',$value);
    }

    /**
     * @notes 审核时间
     * @param $value
     * @return false|string
     * @author ljj
     * @date 2024/9/6 下午3:35
     */
    public function getVerifyTimeAttr($value)
    {
        return empty($value) ? '-' : date('Y-m-d H:i:s',$value);
    }

    /**
     * @notes 师傅头像
     * @param $value
     * @return string
     * @author ljj
     * @date 2024/9/6 下午3:41
     */
    public function getWorkImageAttr($value)
    {
        return empty($value) ? '' : FileService::getFileUrl($value);
    }
}