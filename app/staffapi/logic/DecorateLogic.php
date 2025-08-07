<?php
namespace app\staffapi\logic;
use app\common\model\decorate\DecoratePage;
use app\common\model\decorate\DecorateStyle;
use app\common\model\decorate\DecorateTabbar;
use app\common\model\staff\Staff;

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
     * @return array
     * @author cjhao
     * @date 2024/10/8 15:11
     */
    public function page(int $id,int $staffId)
    {
        $detail = DecoratePage::where(['id'=>$id])->json(['data','meta'],true)->findOrEmpty()->toArray();
        if(6 == $id){
            $isStaff = Staff::where(['id'=>$staffId])->value('is_staff') || 0;
            if (!$isStaff) {
                foreach ($detail['data'] as $key => $datum){
                    if('user-service' == $datum['name']){
                        foreach ($datum['content']['data'] as $k => $data){
                            if(!in_array($data['link']['path'],['/pages/agreement/agreement','/pages/user_set/index','/bundle/pages/contact_service/index'])){
                                unset($datum['content']['data'][$k]);
                            }
                        }
                    }
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
        $detail = DecorateStyle::where(['source'=>2])->findOrEmpty()->toArray();
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
        $detail = DecorateTabbar::where(['source'=>2])->findOrEmpty()->toArray();
        return $detail;
    }

}