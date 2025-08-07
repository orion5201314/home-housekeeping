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


use app\staffapi\lists\OrderLists;
use app\staffapi\logic\OrderLogic;
use app\staffapi\validate\OrderValidate;

class OrderController extends BaseStaffController
{
    /**
     * @notes 师傅订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/14 下午4:08
     */
    public function staffOrderLists()
    {
        $result = (new OrderLogic())->staffOrderLists($this->staffId);
        return $this->data($result);
    }

    /**
     * @notes 订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午3:07
     */
    public function lists()
    {
        return $this->dataLists(new OrderLists());
    }

    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:23
     */
    public function detail()
    {
        $params = (new OrderValidate())->get()->goCheck('detail');
        $result = (new OrderLogic())->detail($params['id'],$this->staffId);
        return $this->success('',$result);
    }

    /**
     * @notes 抢单
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function grab()
    {
        $params = (new OrderValidate())->post()->goCheck('grab',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->grab($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 接单
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function receive()
    {
        $params = (new OrderValidate())->post()->goCheck('receive',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->receive($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 出发
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function setout()
    {
        $params = (new OrderValidate())->post()->goCheck('setout',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->setout($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 到达
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function arrive()
    {
        $params = (new OrderValidate())->post()->goCheck('arrive',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->arrive($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 开始服务
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function start()
    {
        $params = (new OrderValidate())->post()->goCheck('start',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->start($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 完成服务
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/18 下午5:31
     */
    public function finish()
    {
        $params = (new OrderValidate())->post()->goCheck('finish',['staff_id'=>$this->staffId]);
        $result = (new OrderLogic())->finish($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }
}