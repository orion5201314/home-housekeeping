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

namespace app\staffapi\logic;


use app\common\logic\BaseLogic;
use app\common\model\decorate\DecorateTabbar;
use app\common\model\decorate\Navigation;
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
        $defaultAvatar = ConfigService::get('config', 'default_staff_avatar',  config('project.default_image.staff_avatar'));
        $defaultAvatar = FileService::getFileUrl($defaultAvatar);
        $config = [
            //网站名称
            'staff_name'                => ConfigService::get('website', 'staff_name'),
            //网站logo
            'staff_logo'                => FileService::getFileUrl(ConfigService::get('website', 'staff_logo')),
            //版本号
            'version'                   => request()->header('version'),
            //默认头像
            'default_avatar'            => $defaultAvatar,
            //文件域名
            'domain'                    => request()->domain().'/',
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
        $id = empty($get['id']) ? 3 : $get['id'];
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