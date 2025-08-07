<?php
namespace app\api\logic;
use app\common\enum\OrderEnum;
use app\common\model\decorate\DecoratePage;
use app\common\model\decorate\DecorateStyle;
use app\common\model\decorate\DecorateTabbar;
use app\common\model\goods\Goods;
use app\common\model\order\Order;

/**
 * 装修逻辑类
 * Class DecorateLogic
 * @package app\api\logic
 */
class DecorateLogic
{


    /**
     * @notes 获取装修页面
     * @param int $id
     * @param int $cityId
     * @return array
     * @author cjhao
     * @date 2024/10/8 15:11
     */
    public function page(int $id,int $cityId,int $userId)
    {
        $detail = DecoratePage::where(['id'=>$id])->json(['data','meta'],true)->findOrEmpty()->toArray();
        if(1 == $id && $cityId){
            foreach ($detail['data'] as $key => $datum){
                if('area-goods' == $datum['name']){
                    $showNum = $datum['content']['show_num'] ?? 1;
                    $goodsLists = Goods::where(['status'=>1])
                        ->whereRaw('open_city_id is null or JSON_CONTAINS(open_city_id, "['.$cityId.']", "$")')
                        ->field('id,name,image,min_price as price,min_line_price as scribing_price,label,virtual_sale_num')
                        ->append(['order_num','min_duration'])
                        ->limit($showNum)
                        ->order('sort desc,id desc')
                        ->select()
                        ->toArray();
                    $datum['content']['goods_list'] = $goodsLists;
                    $detail['data'][$key] = $datum;
                }
            }
        }
        if(2 == $id && $userId){
            foreach ($detail['data'] as $key => $datum){
                if('user-order' == $datum['name']){
                    $payOrderNum = Order::where(['user_id'=>$userId,'order_status'=>OrderEnum::ORDER_STATUS_WAIT_PAY])->count();
                    $subscribeOrderNum = Order::where(['user_id'=>$userId,'order_status'=>OrderEnum::ORDER_STATUS_WAIT_SERVICE])->count();
                    $serviceOrderNum = Order::where(['user_id'=>$userId,'order_status'=>OrderEnum::ORDER_STATUS_SERVICE])->count();

                    $datum['content']['pay_order_num'] = $payOrderNum;
                    $datum['content']['subscribe_order_num'] = $subscribeOrderNum;
                    $datum['content']['service_order_num'] = $serviceOrderNum;
                    $detail['data'][$key] = $datum;
                }
            }
        }
        return $detail;
    }

    /**
     * @notes 获取装修风格
     * @return array
     * @author cjhao
     * @date 2024/10/8 15:13
     */
    public function style()
    {
        $detail = DecorateStyle::where(['source'=>1])->findOrEmpty()->toArray();
        return $detail;
    }


    /**
     * @notes 底部菜单
     * @return array
     * @author cjhao
     * @date 2024/10/8 15:59
     */
    public function tabbar()
    {
        $detail = DecorateTabbar::where(['source'=>1])->findOrEmpty()->toArray();
        return $detail;
    }

}