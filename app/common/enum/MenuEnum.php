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

namespace app\common\enum;


class MenuEnum
{
    //商城页面
    const SHOP_PAGE = [
        [
            'is_tab'     => 1,
            'index'     => 1,
            'name'      => '商城首页',
            'path'      => '/pages/index/index',
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'is_tab'     => 0,
            'index'     => 2,
            'name'      => '找师傅',
            'path'      => '/bundle/pages/master_worker_list/index',
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'is_tab'     => 1,
            'index'     => 3,
            'name'      => '预约订单',
            'path'      => '/pages/order/index',
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'is_tab'     => 0,
            'index'     => 4,
            'name'      => '地址管理',
            'path'      => '/bundle/pages/user_address/index',
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'is_tab'     => 0,
            'index'     => 5,
            'name'      => '个人资料',
            'path'      => '/bundle/pages/user_profile/index',
            'params'    => [],
            'type'      => 'shop',
        ],
        [
            'is_tab'     => 0,
            'index'     => 6,
            'name'      => '联系客服',
            'path'      => '/bundle/pages/contact_service/index',
            'params'    => [],
            'type'      => 'shop',
        ],
    ];


    //菜单类型
    const NAVIGATION_HOME = 1;//首页导航
    const NAVIGATION_PERSONAL = 2;//个人中心

    //链接类型
    const LINK_SHOP = 1;//商城页面
    const LINK_CATEGORY = 2;//分类页面
    const LINK_CUSTOM = 3;//自定义链接

    /**
     * @notes 链接类型
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/14 12:14 下午
     */
    public static function getLinkDesc($value = true)
    {
        $data = [
            self::LINK_SHOP => '商城页面',
            self::LINK_CATEGORY => '分类页面',
            self::LINK_CUSTOM => '自定义链接'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }
}