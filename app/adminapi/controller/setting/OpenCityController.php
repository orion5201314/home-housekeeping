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

namespace app\adminapi\controller\setting;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\setting\OpenCityLists;
use app\adminapi\logic\setting\OpenCityLogic;
use app\adminapi\validate\setting\OpenCityValidate;

class OpenCityController extends BaseAdminController
{
    /**
     * @notes 开通城市列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/22 下午4:09
     */
    public function lists()
    {
        return $this->dataLists(new OpenCityLists());
    }

    /**
     * @notes 新增
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function add()
    {
        $params = (new OpenCityValidate())->post()->goCheck('add');
        (new OpenCityLogic())->add($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function detail()
    {
        $params = (new OpenCityValidate())->get()->goCheck('detail');
        $result = (new OpenCityLogic())->detail($params['id']);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 编辑
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/22 下午4:18、
     */
    public function edit()
    {
        $params = (new OpenCityValidate())->post()->goCheck('edit');
        (new OpenCityLogic())->edit($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 删除
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/22 下午4:18
     */
    public function del()
    {
        $params = (new OpenCityValidate())->post()->goCheck('del');
        (new OpenCityLogic())->del($params['id']);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 通用列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/8/22 下午5:48
     */
    public function commonLists()
    {
        $result = (new OpenCityLogic())->commonLists();
        return $this->success('',$result);
    }

    /**
     * @notes 通用完整列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/10/16 上午10:02
     */
    public function commonIntactLists()
    {
        $result = (new OpenCityLogic())->commonIntactLists();
        return $this->success('',$result);
    }
}