<?php
// +----------------------------------------------------------------------
// | likeshop100%开源免费商用商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/likeshop_gitee
// | github下载：https://github.com/likeshop-github
// | 访问官网：https://www.likeshop.cn
// | 访问社区：https://home.likeshop.cn
// | 访问手册：http://doc.likeshop.cn
// | 微信公众号：likeshop技术社区
// | likeshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeshopTeam
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\common\controller;


use app\BaseController;
use app\common\lists\BaseDataLists;
use app\common\service\JsonService;
use think\facade\App;

class BaseLikeShopController extends BaseController
{

    public array $notNeedLogin = [];

    /**
     * @notes 操作成功
     * @param string $msg
     * @param array $data
     * @param int $code
     * @param int $show
     * @return \think\response\Json
     * @author 令狐冲
     * @date 2021/7/8 00:39
     */
    protected function success(string $msg = 'success', array $data = [], int $code = 1, int $show = 0)
    {
        return JsonService::success($msg, $data, $code, $show);
    }

    /**
     * @notes 数据返回
     * @param $data
     * @return \think\response\Json
     * @author 令狐冲
     * @date 2021/7/8 11:13
     */
    protected function data($data)
    {
        return JsonService::data($data);
    }


    /**
     * @notes 列表数据返回
     * @param \app\common\lists\BaseDataLists|null $lists
     * @return \think\response\Json
     * @author 令狐冲
     * @date 2021/7/8 00:40
     */
    protected function dataLists(BaseDataLists $lists = null)
    {
        //列表类和控制器一一对应，"app/应用/controller/控制器的方法" =》"app\应用\lists\"目录下
        //（例如："app/adminapi/controller/auth/AdminController.php的lists()方法" =》 "app/adminapi/lists/auth/AminLists.php")
        //当对象为空时，自动创建列表对象
        if (is_null($lists)) {
            $listName = str_replace('.', '\\', App::getNamespace() . '\\lists\\' . $this->request->controller() . ucwords($this->request->action()));
            $lists = invoke($listName);
        }
        return JsonService::dataLists($lists);
    }

    /**
     * @notes 操作失败
     * @param string $msg
     * @param array $data
     * @param int $code
     * @param int $show
     * @return \think\response\Json
     * @author 令狐冲
     * @date 2021/7/8 00:39
     */
    protected function fail(string $msg = 'fail', array $data = [], int $code = 0, int $show = 1)
    {
        return JsonService::fail($msg, $data, $code, $show);
    }


    /**
     * @notes 是否免登录验证
     * @return bool
     * @author 令狐冲
     * @date 2021/8/3 14:36
     */
    public function isNotNeedLogin()
    {

        $notNeedLogin = $this->notNeedLogin;
        if (empty($notNeedLogin)) {
            return false;
        }
        $action = $this->request->action();

        if (!in_array(trim($action), $notNeedLogin)) {
            return false;
        }
        return true;
    }


}