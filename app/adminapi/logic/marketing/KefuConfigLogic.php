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

namespace app\adminapi\logic\marketing;

use app\common\logic\BaseLogic;
use app\common\service\ConfigService;
use app\common\service\FileService;
use think\facade\Db;


class KefuConfigLogic extends BaseLogic
{
    /**
     * @notes 获取
     * @return array
     * @author ljj
     * @date 2024/8/27 下午5:17
     */
    public static function getConfig()
    {
        $defaultData = [
            'way' => '1',
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

    /**
     * @notes 设置
     * @param $params
     * @return string|true
     * @author ljj
     * @date 2024/8/27 下午5:17
     */
    public static function setConfig($params)
    {
        Db::startTrans();
        try {
            foreach($params as $key => $value) {
                if(!in_array($key, ['mnp','oa','h5','pc','app'])) {
                    throw new \think\Exception('数据异常');
                }
                ConfigService::set('kefu_config', $key, $value);
            }

            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e->getMessage();
        }
    }
}