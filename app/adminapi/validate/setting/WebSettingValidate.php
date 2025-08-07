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

namespace app\adminapi\validate\setting;

use app\common\validate\BaseValidate;
use WpOrg\Requests\Requests;

/**
 * 网站设置验证器
 * Class WebSettingValidate
 * @package app\adminapi\validate\setting
 */
class WebSettingValidate extends BaseValidate
{
    protected $rule = [
        'name' => 'require|max:20',
        'web_favicon' => 'require',
        'web_logo' => 'require',
        'login_image' => 'require',
        'web_login_security' => 'require|in:0,1',
        'web_login_error_num' => 'requireIf:web_login_security,1|number',
        'web_login_error_time' => 'requireIf:web_login_security,1|number',
        'document_status' => 'require|in:0,1|checkDocumentStatus',
        'web_contact_name' => 'require',
        'web_contact_mobile' => 'require',

        'shop_name' => 'require|max:20',
        'shop_abbrev' => 'require|max:20',
        'shop_logo' => 'require',

        'staff_name' => 'require|max:20',
        'staff_logo' => 'require',
    ];

    protected $message = [
        'name.require' => '请填写平台名称',
        'name.max' => '平台名称最长为20个字符',
        'web_favicon.require' => '请上传平台图标',
        'web_logo.require' => '请上传平台logo',
        'login_image.require' => '请上传登录封面图',
        'document_status.require' => '请选择文档信息开关',
        'document_status.in' => '文档信息值错误',
        'web_login_security.require' => '请选择登录安全',
        'web_login_security.in' => '登录安全值错误',
        'web_login_error_num.requireIf' => '请输入输错密码次数',
        'web_login_error_num.number' => '输错密码次数值错误',
        'web_login_error_time.requireIf' => '请输入限制登录时长',
        'web_login_error_time.number' => '限制登录时长值错误',
        'web_contact_name.require' => '请输入联系姓名',
        'web_contact_mobile.require' => '请输入联系号码',

        'shop_name.require' => '请输入用户端名称',
        'shop_name.max' => '用户端名称最长为20个字符',
        'shop_abbrev.require' => '请填写用户端简称',
        'shop_abbrev.max' => '用户端简称最长为20个字符',
        'shop_logo.require' => '请上传用户端logo',

        'staff_name.require' => '请输入用户端名称',
        'staff_name.max' => '用户端名称最长为20个字符',
        'staff_logo.require' => '请上传用户端logo',
    ];

    protected $scene = [
        'website' => ['name', 'web_favicon', 'web_logo', 'login_image','document_status','web_login_security','web_login_error_num','web_login_error_time','shop_name','shop_logo','shop_abbrev','staff_name','staff_logo','web_contact_name','web_contact_mobile'],
    ];



    /**
     * @notes 校验产品授权
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author ljj
     * @date 2023/5/16 11:25 上午]
     */
    public function checkDocumentStatus($value,$rule,$data)
    {
        if ($value == 0) {
            $check_domain = config('project.check_domain');
            $product_code = config('project.product_code');
            $domain = $_SERVER['HTTP_HOST'];
            $result = Requests::get($check_domain.'/api/version/productAuth?code='.$product_code.'&domain='.$domain);
            $result = json_decode($result->body,true);
            if (!$result['data']['result']) {
                return '产品未授权，要去官网授权才能操作';
            }
        }

        return true;
    }
}