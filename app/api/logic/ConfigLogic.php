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

namespace app\api\logic;


use app\common\logic\BaseLogic;
use app\common\model\decorate\DecorateTabbar;
use app\common\model\Protocol;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\TencentMapKeyService;

class ConfigLogic extends BaseLogic
{
    /**
     * @notes 基础配置信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 10:30 上午
     */
    public function config()
    {
        $defaultAvatar = ConfigService::get('config', 'default_avatar',  config('project.default_image.user_avatar'));
        $defaultAvatar = FileService::getFileUrl($defaultAvatar);
        $config = [
            //商城名称
            'shop_name'                 => ConfigService::get('website', 'shop_name'),
            //商城简称
            'shop_abbrev'                 => ConfigService::get('website', 'shop_abbrev'),
            //商城logo
            'shop_logo'                 => FileService::getFileUrl(ConfigService::get('website', 'shop_logo')),
            // 登录方式
            'login_way'                 => ConfigService::get('login', 'login_way', config('project.login.login_way')),
            // 注册强制绑定手机
            'coerce_mobile'             => ConfigService::get('login', 'coerce_mobile', config('project.login.coerce_mobile')),
            // 第三方登录 开关
            'third_auth'                => ConfigService::get('login', 'third_auth', config('project.login.third_auth')),
            // 微信授权登录
            'wechat_auth'               => ConfigService::get('login', 'wechat_auth', config('project.login.wechat_auth')),
            //底部导航
            'navigation_menu'           => DecorateTabbar::getTabbarLists(),
            // 导航颜色
            'style'                     => ConfigService::get('tabbar', 'style', '{}'),
            //地图key
            'tencent_map_key'           => (new TencentMapKeyService())->getTencentMapKey(),
            //版本号
            'version'                   => request()->header('version'),
            //默认头像
            'default_avatar'            => $defaultAvatar,
            //H5设置
            'h5_settings'               => [
                // 渠道状态 0-关闭 1-开启
                'status' => ConfigService::get('web_page', 'status', 1),
                // 关闭后渠道后访问页面 0-空页面 1-自定义链接
                'page_status' => ConfigService::get('web_page', 'page_status', 0),
                // 自定义链接
                'page_url' => ConfigService::get('web_page', 'page_url', ''),
                'url' => request()->domain() . '/mobile'
            ],
            //文件域名
            'domain' => request()->domain().'/',
            //备案号
            'copyright_list' => ConfigService::get('copyright', 'config', []),
        ];
        return $config;
    }

    /**
     * @notes 政策协议
     * @return array
     * @author ljj
     * @date 2022/2/23 11:42 上午
     */
    public function agreement($get)
    {
        $id = empty($get) ? 1 : $get;
        $result = Protocol::where(['id'=>$id])->findOrEmpty()->toArray();
        return $result;
    }


    /**
     * @notes 获取客服配置
     * @return array
     * @author ljj
     * @date 2024/8/28 下午4:06
     */
    public function getKefuConfig()
    {
        $defaultData = [
            'way' => 1,
            'name' => '',
            'remarks' => '',
            'phone' => '',
            'business_time' => '',
            'qr_code' => '',
            'enterprise_id' => '',
            'kefu_link' => ''
        ];
        $config = [
            'mnp' => ConfigService::get('kefu_config', 'mnp', $defaultData),
            'oa' => ConfigService::get('kefu_config', 'oa', $defaultData),
            'h5' => ConfigService::get('kefu_config', 'h5', $defaultData),
            'pc' => ConfigService::get('kefu_config', 'pc', $defaultData),
            'app' => ConfigService::get('kefu_config', 'app', $defaultData),
        ];
        if (!empty($config['mnp']['qr_code'])) $config['mnp']['qr_code'] = FileService::getFileUrl($config['mnp']['qr_code']);
        if (!empty($config['oa']['qr_code'])) $config['oa']['qr_code'] = FileService::getFileUrl($config['oa']['qr_code']);
        if (!empty($config['h5']['qr_code'])) $config['h5']['qr_code'] = FileService::getFileUrl($config['h5']['qr_code']);
        if (!empty($config['pc']['qr_code'])) $config['pc']['qr_code'] = FileService::getFileUrl($config['pc']['qr_code']);
        if (!empty($config['app']['qr_code'])) $config['app']['qr_code'] = FileService::getFileUrl($config['app']['qr_code']);

        return $config;
    }
}