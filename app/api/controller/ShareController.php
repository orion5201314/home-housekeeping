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
namespace app\api\controller;

use app\api\logic\ShareLogic;
use app\api\validate\ShareValidate;

/**
 * 分享控制器
 * Class ShareController
 * @package app\shopapi\controller
 */
class ShareController extends BaseShopController
{
    public array $notNeedLogin = ['getMnpQrCode','getGoodsImagesBase64'];

    /**
     * @notes 获取小程序码
     * @return \think\response\Json
     * @author ljj
     * @date 2023/2/28 5:08 下午
     */
    public function getMnpQrCode()
    {
        $params = (new ShareValidate())->goCheck('getMnpQrCode');
        $res = (new ShareLogic())->getMnpQrCode($params);
        if(true !== $res){
            return $this->fail(ShareLogic::getReturnData());
        }
        return $this->success('获取成功',['qr_code'=>ShareLogic::getReturnData()]);

    }

    /**
     * @notes 服务海报图片转base64
     * @return \think\response\Json
     * @author ljj
     * @date 2024/10/23 下午3:14
     */
    function getGoodsImagesBase64()
    {
        $params = (new ShareValidate())->goCheck('getGoodsImagesBase64');
        $result = (new ShareLogic())->getGoodsImagesBase64($params);
        return $this->data($result);
    }
}