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
namespace app\api\logic;


use app\common\logic\BaseLogic;
use app\common\model\goods\Goods;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\service\WeChatService;

/**
 * 分享逻辑层
 * Class ShareLogic
 * @package app\shopapi\logic
 */
class ShareLogic extends BaseLogic
{
    /**
     * @notes 获取小程序码
     * @param $params
     * @return bool|mixed|string
     * @author ljj
     * @date 2023/2/28 5:09 下午
     */
    public function getMnpQrCode($params)
    {
        $data['page'] = $params['page'];
        if (isset($params['id']) && $params['id'] != '') {
            $data['scene'] = 'id='.$params['id'];
        }

        $result = WeChatService::makeMpQrCode($data,'base64');
        return $result;
    }

    /**
     * @notes 服务海报图片转base64
     * @param $params
     * @return array
     * @author ljj
     * @date 2024/10/24 上午11:05
     */
    public function getGoodsImagesBase64($params) : array
    {
        //获取商品图片
        $goodsImage = Goods::where('id', $params['id'])->value('image');
        //获取商城logo
        $shopLogo = FileService::getFileUrl(ConfigService::get('website', 'shop_logo'));

        return [
            'goods_image'   => self::getBase64($goodsImage),
            'shop_logo'   => self::getBase64($shopLogo),
        ];
    }

    /**
     * @notes 获取图片base64
     * @param $image
     * @return string
     * @author ljj
     * @date 2024/10/24 上午11:02
     */
    function getBase64($image)
    {
        $content    = file_get_contents(FileService::getFileUrl($image));
        $base64     = 'data:image/png;base64,' . chunk_split(base64_encode($content));
        return $base64;
    }
}