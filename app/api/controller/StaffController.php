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

namespace app\api\controller;


use app\api\lists\StaffGoodsCommentLists;
use app\api\lists\StaffGoodsLists;
use app\api\lists\StaffLists;
use app\api\logic\StaffLogic;

class StaffController extends BaseShopController
{
    public array $notNeedLogin = ['lists','detail'];


    /**
     * @notes 师傅列表
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/23 5:56 下午
     */
    public function lists()
    {
        return $this->dataLists(new StaffLists());
    }

    /**
     * @notes 师傅详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 6:34 下午
     */
    public function detail()
    {
        $params = $this->request->get();
        $result = (new StaffLogic())->detail($params);
        return $this->success('',$result);
    }

    /**
     * @notes 师傅服务列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/24 下午5:13
     */
    public function goodsLists()
    {
        return $this->dataLists(new StaffGoodsLists());
    }

    /**
     * @notes 师傅评价列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/24 下午5:13
     */
    public function goodsCommentLists()
    {
        return $this->dataLists(new StaffGoodsCommentLists());
    }
}