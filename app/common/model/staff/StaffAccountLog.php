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

use app\common\enum\StaffAccountLogEnum;
use app\common\model\BaseModel;
use app\common\service\FileService;
use think\model\concern\SoftDelete;


class StaffAccountLog extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    /**
     * @notes 关联师傅模型
     * @return \think\model\relation\HasOne
     * @author ljj
     * @date 2024/9/4 下午4:14
     */
    public function staff()
    {
        return $this->hasOne(Staff::class,'id','staff_id')
            ->field('id,sn,name,work_image,mobile');
    }


    /**
     * @notes 变动类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/5/31 4:45 下午
     */
    public function getChangeTypeDescAttr($value,$data)
    {
        return StaffAccountLogEnum::getChangeTypeDesc($data['change_type']);
    }

    /**
     * @notes 师傅头像
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/6 下午2:20
     */
    public function getWorkImageAttr($value,$data)
    {
        return empty($data['work_image']) ? '' : FileService::getFileUrl($data['work_image']);
    }
}