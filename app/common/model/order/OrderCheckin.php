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

namespace app\common\model\order;


use app\common\enum\OrderEnum;
use app\common\model\BaseModel;
use app\common\model\Region;
use app\common\service\FileService;

class OrderCheckin extends BaseModel
{
    /**
     * @notes 获取签到图片
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2024/9/14 下午5:31
     */
    public function getImageInfoAttr($value,$data)
    {
        $data['image_info'] = json_decode($data['image_info'],true);
        foreach ($data['image_info'] as $key => $item) {
            $data['image_info'][$key] = FileService::getFileUrl($item);
        }
        return $data['image_info'];
    }

    /**
     * @notes 设置签到图片
     * @param $value
     * @param $data
     * @return false|string
     * @author ljj
     * @date 2024/9/14 下午5:32
     */
    public function setImageInfoAttr($value,$data)
    {
        foreach ($data['image_info'] as $key => $item) {
            $data['image_info'][$key] = FileService::setFileUrl($item);
        }

        return json_encode($data['image_info']);
    }

    /**
     * @notes 订单状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author ljj
     * @date 2024/9/14 下午5:26
     */
    public function getOrderStatusDescAttr($value,$data)
    {
        if ($data['order_status'] == OrderEnum::ORDER_STATUS_WAIT_SERVICE) {
            return OrderEnum::getOrderSubStatusDesc($data['order_sub_status']);
        } else {
            return OrderEnum::getOrderStatusDesc($data['order_status']);
        }
    }

    /**
     * @notes 地址信息
     * @param $value
     * @param $data
     * @return mixed
     * @author ljj
     * @date 2024/9/14 下午5:28
     */
    public function getAddressInfoAttr($value,$data)
    {
        $result = json_decode($data['address_info'],true);
        $result['province'] = Region::where(['id'=>$result['province_id']])->value('name');
        $result['city'] = Region::where(['id'=>$result['city_id']])->value('name');
        $result['district'] = Region::where(['id'=>$result['district_id']])->value('name');
        return $result;
    }
}