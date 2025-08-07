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

namespace app\common\model\staff;


use app\common\enum\DefaultEnum;
use app\common\enum\StaffEnum;
use app\common\model\BaseModel;
use app\common\model\goods\Goods;
use app\common\model\Region;
use app\common\model\user\User;
use app\common\service\FileService;
use think\model\concern\SoftDelete;

class Staff extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';


    /**
     * @notes 性别
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2022/2/10 11:41 上午
     */
    public function getSexDescAttr($value,$data)
    {
        return DefaultEnum::getSexDesc($data['sex']);
    }

    /**
     * @notes 省
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2022/4/6 6:57 下午
     */
    public function getProvinceAttr($value,$data)
    {
        return Region::where(['id'=>$data['province_id']])->value('name');
    }

    /**
     * @notes 市
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2022/4/6 7:02 下午
     */
    public function getCityAttr($value,$data)
    {
        return Region::where(['id'=>$data['city_id']])->value('name');
    }

    /**
     * @notes 区
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2022/4/6 7:02 下午
     */
    public function getDistrictAttr($value,$data)
    {
        return Region::where(['id'=>$data['district_id']])->value('name');
    }

    /**
     * @notes 设置身份证人像面
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function getIdentityPortraitImageAttr($value,$data)
    {
        return empty($data['identity_portrait_image']) ? '' : FileService::getFileUrl($data['identity_portrait_image']);
    }

    /**
     * @notes 获取身份证人像面
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function setIdentityPortraitImageAttr($value,$data)
    {
        return empty($data['identity_portrait_image']) ? '' : FileService::setFileUrl($data['identity_portrait_image']);
    }

    /**
     * @notes 设置身份证国徽面
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function getIdentityEmblemImageAttr($value,$data)
    {
        return empty($data['identity_emblem_image']) ? '' : FileService::getFileUrl($data['identity_emblem_image']);
    }

    /**
     * @notes 获取身份证国徽面
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function setIdentityEmblemImageAttr($value,$data)
    {
        return empty($data['identity_emblem_image']) ? '' : FileService::setFileUrl($data['identity_emblem_image']);
    }

    /**
     * @notes 设置人像实拍
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function getPortraitImageAttr($value,$data)
    {
        return empty($data['portrait_image']) ? '' : FileService::getFileUrl($data['portrait_image']);
    }

    /**
     * @notes 获取人像实拍
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function setPortraitImageAttr($value,$data)
    {
        return empty($data['portrait_image']) ? '' : FileService::setFileUrl($data['portrait_image']);
    }

    /**
     * @notes 设置证书
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function getCredentialsImageAttr($value,$data)
    {
        if (empty($data['credentials_image'])) {
            return '';
        }
        $data['credentials_image'] = json_decode($data['credentials_image'],true);
        foreach ($data['credentials_image'] as $key => $item) {
            $data['credentials_image'][$key] = FileService::getFileUrl($item);
        }
        return $data['credentials_image'];
    }

    /**
     * @notes 获取证书
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 下午12:15
     */
    public function setCredentialsImageAttr($value,$data)
    {
        if (empty($data['credentials_image'])) {
            return '';
        }
        foreach ($data['credentials_image'] as $key => $item) {
            $data['credentials_image'][$key] = FileService::setFileUrl($item);
        }

        return json_encode($data['credentials_image']);
    }

    /**
     * @notes 获取工作照
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/8/30 上午10:38
     */
    public function getWorkImageAttr($value,$data)
    {
        return empty($data['work_image']) ? '' : FileService::getFileUrl($data['work_image']);
    }

    /**
     * @notes 设置工作照
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/8/30 上午10:38
     */
    public function setWorkImageAttr($value,$data)
    {
        return empty($data['work_image']) ? '' : FileService::setFileUrl($data['work_image']);
    }

    /**
     * @notes 服务状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 上午10:42
     */
    public function getStatusDescAttr($value,$data)
    {
        return StaffEnum::getStatusDesc($data['status']);
    }

    /**
     * @notes 工作状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/8/30 上午10:42
     */
    public function getWorkStatusDescAttr($value,$data)
    {
        return StaffEnum::getWorkStatusDesc($data['work_status']);
    }

    /**
     * @notes 获取保证金套餐信息
     * @param $value
     * @param $data
     * @return array
     * @author ljj
     * @date 2024/8/30 上午10:46
     */
    public function getDepositInfoAttr($value,$data)
    {
        $StaffDeposit = StaffDeposit::where('amount','<=',$data['staff_deposit'])->order(['amount'=>'desc'])->findOrEmpty()->toArray();
        $info = [
            'name' => $StaffDeposit['name'] ?? '',
            'amount' => $StaffDeposit['amount'] ?? 0.00,
            'order_num' => $StaffDeposit['order_num'] ?? 0
        ];
        return $info;
    }

    /**
     * @notes 累计接单数量
     * @param $value
     * @param $data
     * @return int
     * @author ljj
     * @date 2024/8/30 上午10:47
     */
    public function getTotalOrderNumAttr($value,$data)
    {
        return 0;
    }

    /**
     * @notes 设置服务id
     * @param $value
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/9/4 上午11:29
     */
    public function setGoodsIdAttr($value,$data)
    {
        return empty($data['goods_id']) ? null : implode(',',$data['goods_id']);
    }

    /**
     * @notes 获取服务id
     * @param $value
     * @param $data
     * @return false|string[]
     * @author ljj
     * @date 2024/9/4 上午11:30
     */
    public function getGoodsIdAttr($value,$data)
    {
        $goods_id = empty($data['goods_id']) ? null : explode(',',$data['goods_id']);
        return empty($goods_id) ? null : array_map('intval',$goods_id);
    }

    /**
     * @notes 申请状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/4 下午5:42
     */
    public function getApplyStatusDescAttr($value,$data)
    {
        return StaffEnum::getApplyStatusDesc($data['apply_status']);
    }

    /**
     * @notes 技能名称
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2024/9/4 下午5:43
     */
    public function getSkillNameAttr($value,$data)
    {
        return StaffSkill::where(['id'=>$data['skill_id']])->value('name');
    }

    /**
     * @notes 审核状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/5 下午12:11
     */
    public function getVerifyStatusDescAttr($value,$data)
    {
        return StaffEnum::getVerifyStatusDesc($data['verify_status']);
    }

    /**
     * @notes 最后一次定位地址信息
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2024/10/10 下午5:33
     */
    public function getLastAddressInfoAttr($value,$data)
    {
        $result = json_decode($data['last_address_info'],true);
        $result['province'] = Region::where(['id'=>$result['province_id'] ?? 0])->value('name');
        $result['city'] = Region::where(['id'=>$result['city_id'] ?? 0])->value('name');
        $result['district'] = Region::where(['id'=>$result['district_id'] ?? 0])->value('name');
        return $result;
    }

    /**
     * @notes 学历
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/10/12 下午5:26
     */
    public function getEducationNameAttr($value,$data)
    {
        return StaffEnum::getEducationDesc($data['education']);
    }

    /**
     * @notes 民族
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/10/12 下午5:26
     */
    public function getNationNameAttr($value,$data)
    {
        return StaffEnum::getNationDesc($data['nation']);
    }
}
