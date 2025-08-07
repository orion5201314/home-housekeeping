<?php
// +----------------------------------------------------------------------
// | likeadmin快速开发前后端分离管理后台（PHP版）
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | gitee下载：https://gitee.com/likeshop_gitee/likeadmin
// | github下载：https://github.com/likeshop-github/likeadmin
// | 访问官网：https://www.likeadmin.cn
// | likeadmin团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: likeadminTeam
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\common\enum\notice\NoticeEnum;
use app\common\enum\YesNoEnum;
use app\common\model\notice\NoticeRecord;
use app\common\model\notice\NoticeSetting;
use app\common\model\order\Order;
use app\common\model\staff\Staff;
use app\common\model\user\User;
use app\common\service\sms\SmsMessageService;
use app\common\service\WechatMessageService;


/**
 * 通知逻辑层
 * Class NoticeLogic
 * @package app\common\logic
 */
class NoticeLogic extends BaseLogic
{

    /**
     * @notes 根据场景发送短信
     * @param $params
     * @return bool
     * @author 段誉
     * @date 2022/9/15 15:28
     */
    public static function noticeByScene($params)
    {
        try {
            $noticeSetting = NoticeSetting::where('scene_id', $params['scene_id'])->findOrEmpty()->toArray();
            if (empty($noticeSetting)) {
                throw new \Exception('找不到对应场景的配置');
            }
            // 合并额外参数
            $params = self::mergeParams($params);
            $res = false;
            self::setError('发送通知失败');

            // 短信通知
            if (isset($noticeSetting['sms_notice']['status']) && $noticeSetting['sms_notice']['status'] == YesNoEnum::YES) {
                $res = (new SmsMessageService())->send($params);
            }
            // 公众号消息
            if (isset($noticeSetting['oa_notice']['status']) &&  $noticeSetting['oa_notice']['status'] == YesNoEnum::YES) {
                $res = (new WechatMessageService($params['params']['user_id'], NoticeEnum::OA))->send($params);
            }
            // 微信小程序
            if (isset($noticeSetting['mnp_notice']['status']) &&  $noticeSetting['mnp_notice']['status'] == YesNoEnum::YES) {
                $res = (new WechatMessageService($params['params']['user_id'], NoticeEnum::MNP))->send($params);
            }

            return $res;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 整理参数
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/9/15 15:28
     */
    public static function mergeParams($params)
    {
        // 订单相关
        if(!empty($params['params']['order_id'])) {
            $order = Order::where(['id'=>$params['params']['order_id']])->with(['order_goods'])->append(['appoint_time_desc'])->findOrEmpty()->toArray();

            $params['params']['order_sn'] = $order['sn'];
            $params['params']['order_amount'] = $order['order_amount'];
            $params['params']['pay_time'] = $order['pay_time'];
            $params['params']['finish_time'] = $order['finish_time'];
            $params['params']['mobile'] = $params['params']['mobile'] ?? $order['mobile'];
            $params['params']['appoint_time'] = $order['appoint_time_desc'];
            $params['params']['goods_name'] = $order['order_goods'][0]['goods_name'];
            if(mb_strlen($params['params']['goods_name']) > 17) {
                $params['params']['goods_name'] = mb_substr($params['params']['goods_name'], 0, 17) . '...';
            }
        }
        // 用户相关
        if (!empty($params['params']['user_id'])) {
            $user = User::findOrEmpty($params['params']['user_id'])->toArray();
            $params['params']['nickname'] = $user['nickname'];
            $params['params']['mobile'] = $params['params']['mobile'] ?? $user['mobile'];
            $params['params']['user_name'] = $user['nickname'];
        }
        // 师傅相关
        if (!empty($params['params']['staff_id'])) {
            $staff = Staff::findOrEmpty($params['params']['staff_id'])->append(['apply_status_desc'])->toArray();
            $params['params']['staff_name'] = $staff['name'];
            $params['params']['mobile'] = $params['params']['mobile'] ?? $staff['mobile'];
            $params['params']['apply_status_desc'] = $staff['apply_status_desc'];
        }

        // 跳转路径
        $jumpPath = self::getPathByScene($params['scene_id'], $params['params']['order_id'] ?? 0);
        $params['url'] = $jumpPath['url'];
        $params['page'] = $jumpPath['page'];

        return $params;
    }


    /**
     * @notes 根据场景获取跳转链接
     * @param $sceneId
     * @param $extraId
     * @return string[]
     * @author 段誉
     * @date 2022/9/15 15:29
     */
    public static function getPathByScene($sceneId, $extraId)
    {
        // 小程序主页路径
        $page = '/pages/index/index';
        // 公众号主页路径
        $url = '/mobile/pages/index/index';
        return [
            'url' => $url,
            'page' => $page,
        ];
    }


    /**
     * @notes 替换消息内容中的变量占位符
     * @param $content
     * @param $params
     * @return array|mixed|string|string[]
     * @author 段誉
     * @date 2022/9/15 15:29
     */
    public static function contentFormat($content, $params)
    {
        foreach ($params['params'] as $k => $v) {
            $search = '{' . $k . '}';
            $content = str_replace($search, $v, $content);
        }
        return $content;
    }


    /**
     * @notes 添加通知记录
     * @param $params
     * @param $noticeSetting
     * @param $sendType
     * @param $content
     * @param string $extra
     * @return NoticeRecord|\think\Model
     * @author 段誉
     * @date 2022/9/15 15:29
     */
    public static function addNotice($params, $noticeSetting, $sendType, $content, $extra = '')
    {
        return NoticeRecord::create([
            'user_id' => $params['params']['user_id'] ?? 0,
            'title' => self::getTitleByScene($sendType, $noticeSetting),
            'content' => $content,
            'scene_id' => $noticeSetting['scene_id'],
            'read' => YesNoEnum::NO,
            'recipient' => $noticeSetting['recipient'],
            'send_type' => $sendType,
            'notice_type' => $noticeSetting['type'],
            'extra' => $extra,
        ]);
    }


    /**
     * @notes 通知记录标题
     * @param $sendType
     * @param $noticeSetting
     * @return string
     * @author 段誉
     * @date 2022/9/15 15:30
     */
    public static function getTitleByScene($sendType, $noticeSetting)
    {
        switch ($sendType) {
            case NoticeEnum::SMS:
                $title = '';
                break;
            case NoticeEnum::OA:
                $title = $noticeSetting['oa_notice']['name'] ?? '';
                break;
            case NoticeEnum::MNP:
                $title = $noticeSetting['mnp_notice']['name'] ?? '';
                break;
            default:
                $title = '';
        }
        return $title;
    }

}