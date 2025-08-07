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

namespace app\adminapi\logic\setting\web;


use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Cache;

/**
 * 网站设置
 * Class WebSettingLogic
 * @package app\adminapi\logic\setting
 */
class WebSettingLogic extends BaseLogic
{

    /**
     * @notes 获取网站信息
     * @return array
     * @author 段誉
     * @date 2021/12/28 15:43
     */
    public static function getWebsiteInfo(): array
    {
        return [
            'name' => ConfigService::get('website', 'name'),
            'web_favicon' => FileService::getFileUrl(ConfigService::get('website', 'web_favicon')),
            'web_logo' => FileService::getFileUrl(ConfigService::get('website', 'web_logo')),
            'login_image' => FileService::getFileUrl(ConfigService::get('website', 'login_image')),
            'web_login_security' => ConfigService::get('website','web_login_security',0),
            'web_login_error_num' => ConfigService::get('website','web_login_error_num'),
            'web_login_error_time' => ConfigService::get('website','web_login_error_time'),
            'document_status' => ConfigService::get('website','document_status',1),
            'web_contact_name' => ConfigService::get('website','web_contact_name'),
            'web_contact_mobile' => ConfigService::get('website','web_contact_mobile'),

            'shop_name' => ConfigService::get('website', 'shop_name'),
            'shop_abbrev' => ConfigService::get('website', 'shop_abbrev'),
            'shop_logo' => FileService::getFileUrl(ConfigService::get('website', 'shop_logo')),

            'staff_name' => ConfigService::get('website', 'staff_name'),
            'staff_logo' => FileService::getFileUrl(ConfigService::get('website', 'staff_logo')),
        ];
    }


    /**
     * @notes 设置网站信息
     * @param array $params
     * @author 段誉
     * @date 2021/12/28 15:43
     */
    public static function setWebsiteInfo(array $params)
    {
        $favicon = FileService::setFileUrl($params['web_favicon']);
        $logo = FileService::setFileUrl($params['web_logo']);
        $login = FileService::setFileUrl($params['login_image']);
        $shop_logo = FileService::setFileUrl($params['shop_logo']);
        $staff_logo = FileService::setFileUrl($params['staff_logo']);

        ConfigService::set('website', 'name', $params['name']);
        ConfigService::set('website', 'web_favicon', $favicon);
        ConfigService::set('website', 'web_logo', $logo);
        ConfigService::set('website', 'login_image', $login);
        ConfigService::set('website','web_login_security', $params['web_login_security']);
        ConfigService::set('website','web_login_error_num', $params['web_login_error_num'] ?? '');
        ConfigService::set('website','web_login_error_time', $params['web_login_error_time'] ?? '');
        ConfigService::set('website','document_status', $params['document_status']);
        ConfigService::set('website','web_contact_name', $params['web_contact_name']);
        ConfigService::set('website','web_contact_mobile', $params['web_contact_mobile']);

        ConfigService::set('website', 'shop_name', $params['shop_name']);
        ConfigService::set('website', 'shop_abbrev', $params['shop_abbrev']);
        ConfigService::set('website', 'shop_logo', $shop_logo);

        ConfigService::set('website', 'staff_name', $params['staff_name']);
        ConfigService::set('website', 'staff_logo', $staff_logo);
    }


    /**
     * @notes 获取版权备案
     * @return array
     * @author 段誉
     * @date 2021/12/28 16:09
     */
    public static function getCopyright() : array
    {
        return ConfigService::get('copyright', 'config', []);
    }


    /**
     * @notes 设置版权备案
     * @param array $params
     * @return bool
     * @author 段誉
     * @date 2022/8/8 16:33
     */
    public static function setCopyright(array $params)
    {
        try {
            if (!is_array($params['config'])) {
                throw new \Exception('参数异常');
            }
            ConfigService::set('copyright', 'config', $params['config'] ?? []);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

//    /**
//     * @notes 设置政策协议
//     * @param array $params
//     * @author ljj
//     * @date 2022/2/15 10:59 上午
//     */
//    public static function setAgreement(array $params)
//    {
//        ConfigService::set('agreement', 'service_title', $params['service_title'] ?? '');
//        ConfigService::set('agreement', 'service_content', $params['service_content'] ?? '');
//        ConfigService::set('agreement', 'privacy_title', $params['privacy_title'] ?? '');
//        ConfigService::set('agreement', 'privacy_content', $params['privacy_content'] ?? '');
//    }
//
//    /**
//     * @notes 获取政策协议
//     * @return array
//     * @author ljj
//     * @date 2022/2/15 11:15 上午
//     */
//    public static function getAgreement() : array
//    {
//        $config = [
//            'service_title' => ConfigService::get('agreement', 'service_title'),
//            'service_content' => ConfigService::get('agreement', 'service_content'),
//            'privacy_title' => ConfigService::get('agreement', 'privacy_title'),
//            'privacy_content' => ConfigService::get('agreement', 'privacy_content'),
//        ];
//        return $config;
//    }


    /**
     * @notes 设置地图钥匙
     * @param array $params
     * @return bool
     * @author ljj
     * @date 2022/3/10 5:11 下午
     */
    public static function setMapKey(array $params)
    {
        try {
            if (!is_array($params['tencent_map_key'])) {
                throw new \Exception('腾讯地图key错误');
            }
            foreach ($params['tencent_map_key'] as $key => $value) {
                if (empty($value)) {
                    unset($params['tencent_map_key'][$key]);
                }
            }
            if (empty($params['tencent_map_key'])) {
                throw new \Exception('腾讯地图key不能为空');
            }
            if (count($params['tencent_map_key']) > 10) {
                throw new \Exception('腾讯地图key不能超过10个');
            }

            ConfigService::set('map', 'tencent_map_key', json_encode($params['tencent_map_key']));

            //删除缓存
            Cache::delete('TENCENT_MAP_KEY');
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

    }

    /**
     * @notes 获取地图钥匙
     * @return array
     * @author ljj
     * @date 2022/3/10 5:12 下午
     */
    public static function getMapKey(): array
    {
        $tencent_map_key = ConfigService::get('map', 'tencent_map_key',['']);
        return [
            'tencent_map_key' => $tencent_map_key,
        ];
    }
}