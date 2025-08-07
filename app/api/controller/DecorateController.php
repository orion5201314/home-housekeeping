<?php
namespace app\api\controller;
use app\api\logic\DecorateLogic;

/**
 * 装修风格控制器类
 * Class DecorateController
 * @package app\api\controller
 */
class DecorateController extends BaseShopController
{
    public array $notNeedLogin = ['page','style','tabbar'];
    /**
     * @notes 获取装修页面
     * @return \think\response\Json
     * @author cjhao
     * @date 2024/10/8 15:13
     */
    public function page()
    {
        $id = $this->request->get('id/d',1);
        $cityId = $this->request->get('city_id/d',0);
        $detail = (new DecorateLogic())->page($id,$cityId,$this->userId);
        return $this->success('',$detail);
    }


    /**
     * @notes 获取装修风格
     * @return \think\response\Json
     * @author cjhao
     * @date 2024/10/8 15:14
     */
    public function style()
    {
        $detail = (new DecorateLogic())->style();
        return $this->success('',$detail);
    }

    /**
     * @notes 底部菜单
     * @return \think\response\Json
     * @author cjhao
     * @date 2024/10/8 15:57
     */
    public function tabbar()
    {
        $detail = (new DecorateLogic())->tabbar();
        return $this->success('',$detail);
    }
}