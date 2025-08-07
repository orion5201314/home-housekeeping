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

namespace app\staffapi\controller;


use app\staffapi\logic\IndexLogic;

class IndexController extends BaseStaffController
{
    public array $notNeedLogin = ['index','geocoder','geocoderCoordinate','address','openCity'];


    /**
     * @notes 首页信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 4:49 下午
     */
    public function index()
    {
        $get = $this->request->get();
        $result = (new IndexLogic())->index($get);
        return $this->success('',$result);
    }

    /**
     * @notes 地址解析（地址转坐标）
     * @return \think\response\Json
     * @author ljj
     * @date 2022/10/13 12:06 下午
     * 本接口提供由文字地址到经纬度的转换能力，并同时提供结构化的省市区地址信息。
     */
    public function geocoder()
    {
        $get = $this->request->get();
        if (!isset($get['address']) || $get['address'] == '') {
            return $this->fail('地址缺失');
        }

        $result = IndexLogic::geocoder($get);
        if ($result['status'] !== 0) {
            return $this->fail($result['message']);
        }
        return $this->success('',$result);
    }

    /**
     * @notes 逆地址解析（坐标位置描述）
     * @return \think\response\Json
     * @author ljj
     * @date 2022/10/13 2:44 下午
     * 本接口提供由经纬度到文字地址及相关位置信息的转换能力
     */
    public function geocoderCoordinate()
    {
        $get = $this->request->get();
        if (!isset($get['location']) || $get['location'] == '') {
            return $this->fail('经纬度缺失');
        }

        $result = IndexLogic::geocoderCoordinate($get);
        if ($result['status'] !== 0) {
            return $this->fail($result['message']);
        }
        return $this->success('',$result);
    }

    /**
     * @notes 搜索附近地址
     * @return \think\response\Json
     * @author ljj
     * @date 2024/7/23 上午11:21
     */
    public function address()
    {
        $params = $this->request->get();
        if (!isset($params['latitude']) || $params['latitude'] == '' || !isset($params['longitude']) || $params['longitude'] == '') {
            return $this->fail('参数缺失');
        }
        $result = (new IndexLogic())->address($params);
        if ($result['status'] !== 0) {
            return $this->fail($result['message']);
        }
        return $this->success('',$result);
    }

    /**
     * @notes 开通城市列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/16 下午2:11
     */
    public function openCity()
    {
        $result = (new IndexLogic())->openCity();
        return $this->success('获取成功',$result);
    }
}