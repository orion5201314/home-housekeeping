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
use app\common\model\Region;
use app\common\service\TencentMapKeyService;

class IndexLogic extends BaseLogic
{
    /**
     * @notes 首页信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 4:48 下午
     */
    public function index($get)
    {
        return [];
    }

    /**
     * @notes 地址解析（地址转坐标）
     * @param $get
     * @return array|mixed
     * @author ljj
     * @date 2022/10/13 12:06 下午
     * 本接口提供由文字地址到经纬度的转换能力，并同时提供结构化的省市区地址信息。
     */
    public static function geocoder($get)
    {
        $get['key'] = (new TencentMapKeyService())->getTencentMapKey();
        if (empty($get['key'])) {
            return ['status'=>1,'message'=>'腾讯地图开发密钥不能为空'];
        }

        $query = http_build_query($get);
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $result = json_decode(file_get_contents($url.'?'.$query),true);

        if ($result['status'] !== 0) {
            $check = (new TencentMapKeyService())->checkResult($result);
            while (!$check) {
                $get['key'] = (new TencentMapKeyService())->getTencentMapKey(true);
                if (empty($get['key'])) {
                    break;
                }

                $query = http_build_query($get);
                $result = json_decode(file_get_contents($url.'?'.$query),true);
                $check = (new TencentMapKeyService())->checkResult($result);
            }
        }

        return $result;
    }

    /**
     * @notes 逆地址解析（坐标位置描述）
     * @param $get
     * @return array|mixed
     * @author ljj
     * @date 2022/10/13 2:44 下午
     * 本接口提供由经纬度到文字地址及相关位置信息的转换能力
     */
    public static function geocoderCoordinate($get)
    {
        $get['key'] = (new TencentMapKeyService())->getTencentMapKey();
        if (empty($get['key'])) {
            return ['status'=>1,'message'=>'腾讯地图开发密钥不能为空'];
        }

        $query = http_build_query($get);
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $result = json_decode(file_get_contents($url.'?'.$query),true);

        if ($result['status'] !== 0) {
            $check = (new TencentMapKeyService())->checkResult($result);
            while (!$check) {
                $get['key'] = (new TencentMapKeyService())->getTencentMapKey(true);
                if (empty($get['key'])) {
                    break;
                }

                $query = http_build_query($get);
                $result = json_decode(file_get_contents($url.'?'.$query),true);
                $check = (new TencentMapKeyService())->checkResult($result);
            }
        }

        return $result;
    }

    /**
     * @notes 搜索附近地址
     * @param $params
     * @return array|mixed
     * @author ljj
     * @date 2024/7/23 上午11:20
     */
    public function address($params)
    {
        //开发秘钥
        $data['key'] = (new TencentMapKeyService())->getTencentMapKey();
        if (empty($data['key'])) {
            return ['status' => 1, 'message' => '腾讯地图开发密钥缺失'];
        }
        //排序，按距离由近到远排序
        $data['orderby'] = '_distance';
        //排序，按距离由近到远排序
        $data['boundary'] = "nearby(" . $params['latitude'] . "," . $params['longitude'] . ",1000,1)";
        //搜索关键字
        $keyword = $params['keyword'] ?? '';
        //api地址
        //未输入搜索关键词时，默认使用周边推荐api
        $url = 'https://apis.map.qq.com/ws/place/v1/explore';

        if (!empty($keyword)) {
            $data['keyword'] = $keyword;
            //输入搜索关键词时，使用周边搜索api
            $url = 'https://apis.map.qq.com/ws/place/v1/search';
        }

        $query = http_build_query($data);
        $result = json_decode(file_get_contents($url . '?' . $query), true);

        if ($result['status'] !== 0) {
            $check = (new TencentMapKeyService())->checkResult($result);
            while (!$check) {
                $data['key'] = (new TencentMapKeyService())->getTencentMapKey(true);
                if (empty($data['key'])) {
                    break;
                }

                $query = http_build_query($data);
                $result = json_decode(file_get_contents($url . '?' . $query), true);
                $check = (new TencentMapKeyService())->checkResult($result);
            }
        }

        return $result;
    }

    /**
     * @notes 开通城市列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/16 下午2:11
     */
    public function openCity()
    {
        $lists = (new Region())->alias('r')
            ->join('open_city oc', 'oc.city_id = r.id')
            ->where('r.level','=',2)
            ->whereNull('oc.delete_time')
            ->field('r.id as city_id,r.name as city_name,r.db09_lng as longitude,r.db09_lat as latitude')
            ->select()
            ->toArray();

        return $lists;
    }
}