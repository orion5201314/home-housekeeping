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


use app\staffapi\logic\ConfigLogic;

class ConfigController extends BaseStaffController
{
    public array $notNeedLogin = ['config','agreement','getKefuConfig'];


    /**
     * @notes 基础配置信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/2/23 10:31 上午
     */
    public function config()
    {
        $result = (new ConfigLogic())->config();
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 政策协议
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/23 11:42 上午
     */
    public function agreement()
    {
        $get = $this->request->get();
        $result = (new ConfigLogic())->agreement($get);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 获取客服配置
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/28 下午4:07
     */
    public function getKefuConfig()
    {
        $result = (new ConfigLogic())->getKefuConfig();
        return $this->data($result);
    }
}