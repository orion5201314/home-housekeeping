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

namespace app\adminapi\validate\staff;


use app\common\enum\StaffAccountLogEnum;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\validate\BaseValidate;

class StaffValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'skill_id' => 'require',
        'goods_id' => 'require|array',
        'name' => 'require',
        'mobile' => 'require|mobile|checkMobile',
        'sex' => 'require|in:1,2',
        'age' => 'require|number',
        'identity_number' => 'require|idCard',
        'province_id' => 'require|number',
        'city_id' => 'require|number',
        'district_id' => 'require|number',
        'longitude' => 'require',
        'latitude' => 'require',
        'identity_portrait_image' => 'require',
        'identity_emblem_image' => 'require',
        'portrait_image' => 'require',
        'work_image' => 'require',
        'credentials_image' => 'array',
        'status' => 'require|in:0,1',
        'work_status' => 'require|in:0,1',
        'sort' => 'number|max:5',
        'adjust_type' => 'require|in:'.StaffAccountLogEnum::DEPOSIT.','.StaffAccountLogEnum::EARNINGS,
        'adjust_action' => 'require|in:'.StaffAccountLogEnum::DEC.','.StaffAccountLogEnum::INC,
        'adjust_num' => 'require|float|gt:0|checkAdjustNum',
    ];

    protected $message = [
        'id.require' => '参数错误',
        'skill_id.require' => '请选择技能',
        'goods_id.require' => '请选择服务项目',
        'goods_id.array' => '服务项目格式不正确',
        'name.require' => '请输入姓名',
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '手机号码格式不正确',
        'sex.require' => '请选择性别',
        'sex.in' => '性别值错误',
        'age.require' => '请输入年龄',
        'age.number' => '年龄值错误',
        'identity_number.require' => '请输入身份证号码',
        'identity_number.idCard' => '身份证号码错误',
        'province_id.require' => '请选择省',
        'province_id.number' => '省值错误',
        'city_id.require' => '请选择市',
        'city_id.number' => '市值错误',
        'district_id.require' => '请选择区',
        'district_id.number' => '区值错误',
        'longitude.require' => '经度参数缺失',
        'latitude.require' => '纬度参数缺失',
        'identity_portrait_image.require' => '请上传身份证人像面',
        'identity_emblem_image.require' => '请上传身份证国徽面',
        'portrait_image.require' => '请上传人像实拍',
        'work_image.require' => '请上传工作照',
        'credentials_image.array' => '证书值错误',
        'status.require' => '请选择服务状态',
        'status.in' => '服务状态值错误',
        'work_status.require' => '请选择工作状态',
        'work_status.in' => '工作状态值错误',
        'sort.number' => '排序值错误',
        'sort.max' => '排序值过大',
        'adjust_type.require' => '参数缺失',
        'adjust_type.in' => '参数错误',
        'adjust_action.require' => '请选择类型',
        'adjust_action.in' => '类型值错误',
        'adjust_num.require' => '请输入金额',
        'adjust_num.float' => '金额值错误',
        'adjust_num.gt' => '金额必须大于零',
    ];

    public function sceneAdd()
    {
        return $this->only(['skill_id','goods_id','name','mobile','sex','age','identity_number','province_id','city_id','district_id','longitude','latitude','identity_portrait_image','identity_emblem_image','portrait_image','work_image','credentials_image','status','work_status','sort']);
    }

    public function sceneDetail()
    {
        return $this->only(['id']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','skill_id','goods_id','name','mobile','sex','age','identity_number','province_id','city_id','district_id','longitude','latitude','identity_portrait_image','identity_emblem_image','portrait_image','work_image','credentials_image','status','work_status','sort']);
    }

    public function sceneDel()
    {
        return $this->only(['id'])
            ->append('id','checkDel');
    }

    public function sceneAdjustAmount()
    {
        return $this->only(['id','adjust_type','adjust_action','adjust_num']);
    }

    /**
     * @notes 检验能否删除师傅
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/4/1 3:57 下午
     */
    public function checkDel($value,$rule,$data)
    {
        $result = Order::where(['staff_id'=>$value])->select()->toArray();
        if ($result) {
            return '该师傅已有关联订单，不允许删除';
        }
        return true;
    }

    /**
     * @notes 校验手机号
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/4 上午11:23
     */
    public function checkMobile($value,$rule,$data)
    {
        $where[] = ['mobile','=',$value];
        if (isset($data['id'])) {
            $where[] = ['id','<>',$data['id']];
        }
        $result = Staff::where($where)->findOrEmpty();
        if (!$result->isEmpty()) {
            return '手机号已存在';
        }
        return true;
    }

    /**
     * @notes 校验调整金额数量
     * @param $vaule
     * @param $rule
     * @param $data
     * @return string|true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/9/4 下午3:53
     */
    protected function checkAdjustNum($vaule,$rule,$data)
    {
        $staff = Staff::find($data['id']);

        if(StaffAccountLogEnum::INC == $data['adjust_action']){
            return true;
        }

        $surplusMoeny = 0;
        if(StaffAccountLogEnum::DEPOSIT == $data['adjust_type']){
            $surplusMoeny = $staff->staff_deposit - $vaule;
        }
        if(StaffAccountLogEnum::EARNINGS == $data['adjust_type']){
            $surplusMoeny = $staff->staff_earnings - $vaule;
        }
        if($surplusMoeny < 0){
            return '金额不足';
        }

        return true;
    }
}