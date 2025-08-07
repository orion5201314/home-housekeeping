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

namespace app\adminapi\controller\staff;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\staff\StaffSkillLists;
use app\adminapi\logic\staff\StaffSkillLogic;
use app\adminapi\validate\staff\StaffSkillValidate;

class StaffSkillController extends BaseAdminController
{
    /**
     * @notes 服务技能列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:17
     */
    public function lists()
    {
        return $this->dataLists(new StaffSkillLists());
    }

    /**
     * @notes 新增技能
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:24
     */
    public function add()
    {
        $params = (new StaffSkillValidate())->post()->goCheck('add');
        (new StaffSkillLogic())->add($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 技能详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:24
     */
    public function detail()
    {
        $params = (new StaffSkillValidate())->get()->goCheck('detail');
        $result = (new StaffSkillLogic())->detail($params['id']);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 编辑技能
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:25
     */
    public function edit()
    {
        $params = (new StaffSkillValidate())->post()->goCheck('edit');
        (new StaffSkillLogic())->edit($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 删除技能
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:25
     */
    public function del()
    {
        $params = (new StaffSkillValidate())->post()->goCheck('del');
        (new StaffSkillLogic())->del($params['id']);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 修改状态
     * @return \think\response\Json
     * @author ljj
     * @date 2024/8/21 上午10:26
     */
    public function status()
    {
        $params = (new StaffSkillValidate())->post()->goCheck('status');
        (new StaffSkillLogic())->status($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 通用列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2024/8/21 下午4:20
     */
    public function allLists()
    {
        $result = (new StaffSkillLogic())->allLists();
        return $this->success('',$result);
    }
}