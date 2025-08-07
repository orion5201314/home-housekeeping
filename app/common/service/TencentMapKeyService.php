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
namespace app\common\service;

use app\common\enum\MapKeyEnum;
use app\common\model\MapKey;
use think\facade\Cache;

class TencentMapKeyService
{
    /**
     * @notes 腾讯key数组中获取有效key
     * @param bool $isDelete //是否删除一个key
     * @return string
     * @author ljj
     * @date 2024/9/19 下午5:47
     */
    public static function getTencentMapKey(bool $isDelete = false) : string
    {
        //从缓存读取腾讯地图key
        $tencentMapKey = Cache::get('TENCENT_MAP_KEY');
        if (!$tencentMapKey) {
            //缓存没有从数据库读取腾讯地图key
            $tencentMapKey = (new MapKey())->where(['status'=>[MapKeyEnum::STATUS_WAIT,MapKeyEnum::STATUS_USE]])->order(['status'=>'desc','id'=>'desc'])->column('key');

            //设置缓存
            Cache::set('TENCENT_MAP_KEY', $tencentMapKey);
            if (empty($tencentMapKey)) {
                return '';
            }
        }

        //删除一个key
        if ($isDelete) {
            //删除缓存
            Cache::delete('TENCENT_MAP_KEY');

            //移除第一个key
            $tencentMapKey = is_array($tencentMapKey) ? $tencentMapKey : [$tencentMapKey];
            array_shift($tencentMapKey);
            if (empty($tencentMapKey)) {
                return '';
            }

            //设置缓存
            $tencentMapKey = is_array($tencentMapKey) ? $tencentMapKey : [$tencentMapKey];
            Cache::set('TENCENT_MAP_KEY', $tencentMapKey);
        }

        //更新key状态
        MapKey::where(['key'=>$tencentMapKey[0]])->update(['status'=>MapKeyEnum::STATUS_USE]);

        return $tencentMapKey[0];
    }

    /**
     * @notes 校验返回结果
     * @param $result
     * @return bool
     * @author ljj
     * @date 2024/9/20 上午10:33
     */
    public static function checkResult($result) : bool
    {
        if (!isset($result['status']) || $result['status'] === 0) {
            return true;
        } else {
            //从缓存读取腾讯地图key
            $tencentMapKey = Cache::get('TENCENT_MAP_KEY');
            $tencentMapKey = is_array($tencentMapKey) ? $tencentMapKey : [$tencentMapKey];

            //更新key状态
            MapKey::where(['key'=>$tencentMapKey[0]])->update(['status'=>MapKeyEnum::STATUS_ABNORMAL,'error_info'=>json_encode($result)]);

            if (count($tencentMapKey) <= 1) {
                //删除缓存
                Cache::delete('TENCENT_MAP_KEY');

                return true;
            }

            return false;
        }
        // 120-此key每秒请求量已达到上限  121-此key每日调用量已达到上限 190-无效的KEY 199-此key未开启webservice功能 311-key格式错误
//        if (!in_array($result['status'],[120,121,190,199,311])) {
//            return true;
//        }
    }
}