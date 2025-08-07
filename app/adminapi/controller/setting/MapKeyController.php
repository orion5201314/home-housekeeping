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
use app\adminapi\lists\setting\MapKeyLists;
use app\adminapi\logic\setting\MapKeyLogic;
use app\adminapi\validate\setting\MapKeyValidate;

class MapKeyController extends BaseAdminController
{
    /**
     * @notes 地图key列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/5 下午1:55
     */
    public function lists()
    {
        return $this->dataLists(new MapKeyLists());
    }

    /**
     * @notes 公共列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/11/5 下午1:57
     */
    public function commonLists()
    {
        $result = (new MapKeyLogic())->commonLists();
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 新增key
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/5 下午2:05
     */
    public function add()
    {
        $params = (new MapKeyValidate())->post()->goCheck('add');
        (new MapKeyLogic())->add($params);
        return $this->success('操作成功', [],1,1);
    }

    /**
     * @notes 详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/5 下午2:07
     */
    public function detail()
    {
        $params = (new MapKeyValidate())->get()->goCheck('detail');
        $result = (new MapKeyLogic())->detail($params['id']);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 编辑
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/5 下午2:21
     */
    public function edit()
    {
        $params = (new MapKeyValidate())->post()->goCheck('edit');
        (new MapKeyLogic())->edit($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 删除
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/5 下午2:21
     */
    public function del()
    {
        $params = (new MapKeyValidate())->post()->goCheck('del');
        (new MapKeyLogic())->del($params['id']);
        return $this->success('操作成功',[],1,1);
    }
}