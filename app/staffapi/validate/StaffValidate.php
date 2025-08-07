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

namespace app\staffapi\validate;


use app\common\enum\notice\NoticeEnum;
use app\common\enum\StaffEnum;
use app\common\model\staff\Staff;
use app\common\model\staff\StaffApply;
use app\common\model\staff\StaffImproveInfo;
use app\common\service\sms\SmsDriver;
use app\common\validate\BaseValidate;
use think\facade\Config;

class StaffValidate extends BaseValidate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'password' => 'require|length:6,20|alphaNum',
        'code' => 'require|checkCode',
        'password_confirm' => 'require|confirm:password',

        'staff_id' => 'require',
        'skill_id' => 'require',
        'goods_id' => 'require|array',
        'name' => 'require',
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
        'credentials_image' => 'array|max:10',

        'day' => 'require',
        'busytime' => 'array',

        'field'             => 'require|checkField',
        'value'             => 'require',
    ];

    protected $message = [
        'mobile.require' => '请输入手机号码',
        'mobile.mobile' => '无效的手机号码',
        'password.require' => '请输入密码',
        'password.length' => '密码须在6-20位之间',
        'password.alphaDash' => '密码须为字母数字组合',
        'code.require' => '请输入验证码',
        'password_confirm.require' => '请输入确认密码',
        'password_confirm.confirm' => '两次密码不一致',

        'staff_id.require' => '参数缺失',
        'skill_id.require' => '请选择技能',
        'goods_id.require' => '请选择服务项目',
        'goods_id.array' => '服务项目格式不正确',
        'name.require' => '请输入姓名',
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
        'credentials_image.max' => '证书最多可上传十张',

        'day.require' => '日期缺失',
        'busytime.array' => '忙时时间点值错误',

        'field.require'     => '参数缺失',
        'value.require'     => '值不存在',
    ];

    public function sceneForgetPassword()
    {
        return $this->only(['mobile','password','code']);
    }

    public function sceneChangePassword()
    {
        return $this->only(['password','password_confirm'])
            ->append('password','checkChangePassword');
    }

    public function sceneApply()
    {
        return $this->only(['staff_id','skill_id','goods_id','name','sex','age','identity_number','province_id','city_id','district_id','longitude','latitude','identity_portrait_image','identity_emblem_image','portrait_image','work_image','credentials_image'])
            ->append('staff_id','checkApply');
    }

    public function sceneSetBusytime()
    {
        return $this->only(['day','busytime']);
    }

    public function sceneSetImproveInfo()
    {
        return $this->only(['staff_id','skill_id','goods_id','credentials_image','province_id','city_id','district_id','longitude','latitude'])
            ->append('staff_id','checkSetImproveInfo');
    }

    public function sceneSetInfo()
    {
        return $this->only(['field','value']);
    }


    /**
     * @notes 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/9/24 上午10:38
     */
    public function checkCode($code, $rule, $data)
    {
        $smsDriver = new SmsDriver();
        $result = $smsDriver->verify($data['mobile'], $code, NoticeEnum::RESET_PASSWORD_CAPTCHA_STAFF);
        if ($result) {
            return true;
        }
        return '验证码错误';
    }

    /**
     * @notes 校验申请入驻
     * @param $code
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/12 下午3:09
     */
    public function checkApply($code, $rule, $data)
    {
        $apply = StaffApply::where(['staff_id'=>$data['staff_id'],'apply_status'=>[StaffEnum::APPLY_STATUS_WAIT,StaffEnum::APPLY_STATUS_SUCCESS]])->findOrEmpty();
        if (!$apply->isEmpty()) {
            return '入驻申请已提交，无需重复申请';
        }
        return true;
    }

    /**
     * @notes 校验完善资料
     * @param $code
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/10/15 下午2:07
     */
    public function checkSetImproveInfo($code, $rule, $data)
    {
        $apply = StaffImproveInfo::where(['staff_id'=>$data['staff_id'],'verify_status'=>StaffEnum::VERIFY_STATUS_WAIT])->findOrEmpty();
        if (!$apply->isEmpty()) {
            return '审核中，请勿重复申请';
        }
        return true;
    }

    /**
     * @notes 校验修改密码
     * @param $code
     * @param $rule
     * @param $data
     * @return string
     * @author ljj
     * @date 2024/11/11 下午5:10
     */
    public function checkChangePassword($code, $rule, $data)
    {
        $staff = Staff::where(['id'=>$data['staff_id']])->findOrEmpty()->toArray();
        if (!empty($staff['password']) && empty($data['old_password'])) {
            return '请输入原密码';
        }

        //校验原密码是否正确
        $passwordSalt = Config::get('project.unique_identification');
        $oldPassword = create_password($data['old_password'], $passwordSalt);
        if ($oldPassword !== $staff['password']) {
            return '原密码错误';
        }

        return true;
    }

    /**
     * @notes 校验设置用户信息
     * @param $value
     * @param $rule
     * @param $data
     * @return string|true
     * @author ljj
     * @date 2024/11/11 下午5:52
     */
    protected function checkField($value,$rule,$data)
    {
        $allowField = ['sex','mobile'];
        if(!in_array($value,$allowField)){
            return '参数错误';
        }
        switch ($value) {
            case 'mobile':
                $staff = Staff::where([
                    ['mobile', '=', $data['value']],
                    ['id', '<>', $data['staff_id']]
                ])->findOrEmpty();
                if(!$staff->isEmpty()) {
                    return '手机号已被绑定';
                }
                break;
        }

        return true;

    }
}