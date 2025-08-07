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

namespace app\adminapi\controller\order;


use app\adminapi\controller\BaseAdminController;
use app\adminapi\lists\order\OrderLists;
use app\adminapi\lists\order\OrderOperationRefundLists;
use app\adminapi\logic\order\OrderLogic;
use app\adminapi\validate\order\OrderValidate;

class OrderController extends BaseAdminController
{
    /**
     * @notes 查看订单列表
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 11:39 上午
     */
    public function lists()
    {
        return $this->dataLists(new OrderLists());
    }

    /**
     * @notes 订单详情
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 3:01 下午
     */
    public function detail()
    {
        $params = (new OrderValidate())->get()->goCheck('detail');
        $result = (new OrderLogic())->detail($params['id']);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 取消订单
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 4:10 下午
     */
    public function cancel()
    {
        $params = (new OrderValidate())->post()->goCheck('cancel');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->cancel($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功');
    }

    /**
     * @notes 删除订单
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 4:27 下午
     */
    public function del()
    {
        $params = (new OrderValidate())->post()->goCheck('del');
        (new OrderLogic())->del($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 商家备注
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 4:48 下午
     */
    public function remark()
    {
        $params = (new OrderValidate())->post()->goCheck('remark');
        (new OrderLogic())->remark($params);
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 商家备注详情
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/11 4:56 下午
     */
    public function remarkDetail()
    {
        $params = (new OrderValidate())->get()->goCheck('remarkDetail');
        $result = (new OrderLogic())->remarkDetail($params['id']);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 指派师傅
     * @return \think\response\Json
     * @author ljj
     * @date 2022/8/29 5:26 下午
     */
    public function dispatchStaff()
    {
        $params = (new OrderValidate())->post()->goCheck('dispatchStaff');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->dispatchStaff($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 师傅列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/8/29 6:10 下午
     */
    public function staffLists()
    {
        $params = $this->request->get();
        $result = (new OrderLogic())->staffLists($params);
        return $this->success('获取成功',$result);
    }

    /**
     * @notes 退款操作列表
     * @return \think\response\Json
     * @author ljj
     * @date 2024/11/1 上午10:07
     */
    public function refundOperationLists()
    {
        return $this->dataLists(new OrderOperationRefundLists());
    }

    /**
     * @notes 退款信息
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/13 下午3:09
     */
    public function refundInfo()
    {
        $params = $this->request->get();
        $result = (new OrderLogic())->refundInfo($params);
        return $this->data($result);
    }

    /**
     * @notes 退款
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/13 下午5:09
     */
    public function refund()
    {
        $params = (new OrderValidate())->post()->goCheck('refund');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->refund($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 接取订单
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/14 下午4:30
     */
    public function acceptOrder()
    {
        $params = (new OrderValidate())->post()->goCheck('acceptOrder');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->acceptOrder($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 师傅出发
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/14 下午4:30
     */
    public function staffSetout()
    {
        $params = (new OrderValidate())->post()->goCheck('staffSetout');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->staffSetout($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 师傅到达
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/14 下午4:30
     */
    public function staffArrive()
    {
        $params = (new OrderValidate())->post()->goCheck('staffArrive');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->staffArrive($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 开始服务
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/14 下午4:30
     */
    public function startService()
    {
        $params = (new OrderValidate())->post()->goCheck('startService');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->startService($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }

    /**
     * @notes 服务完成
     * @return \think\response\Json
     * @author ljj
     * @date 2024/9/14 下午4:30
     */
    public function finish()
    {
        $params = (new OrderValidate())->post()->goCheck('finish');
        $params['admin_id'] = $this->adminId;
        $result = (new OrderLogic())->finish($params);
        if (true !== $result) {
            return $this->fail($result);
        }
        return $this->success('操作成功',[],1,1);
    }
}