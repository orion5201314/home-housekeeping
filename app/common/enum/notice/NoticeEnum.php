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
namespace app\common\enum\notice;

/**
 * 通知枚举
 * Class NoticeEnum
 * @package app\common\enum
 */
class NoticeEnum
{
    /**
     * 通知类型
     */
    const SYSTEM = 1;
    const SMS = 2;
    const OA = 3;
    const MNP = 4;


    /**
     * 短信验证码场景
     */
    const LOGIN_CAPTCHA = 101;//登录验证码
    const BIND_MOBILE_CAPTCHA = 102;//绑定手机验证码
    const CHANGE_MOBILE_CAPTCHA = 103;//变更手机验证码
    const RESET_PASSWORD_CAPTCHA = 104;//重设登录密码验证码
    const REGISTER_CAPTCHA = 105;//注册验证码
    const REGISTER_CAPTCHA_STAFF = 106;//注册验证码-师傅
    const LOGIN_CAPTCHA_STAFF = 107;//登录验证码-师傅
    const RESET_PASSWORD_CAPTCHA_STAFF = 108;//重设登录密码验证码-师傅
    const CHANGE_MOBILE_CAPTCHA_STAFF = 109;//变更手机验证码-师傅


    /**
     * 短信业务通知
     */
    const ORDER_PAY_NOTICE = 201;//订单付款通知
    const ACCEPT_ORDER_NOTICE = 202;//订单接单通知
    const START_SERVICE_NOTICE = 203;//开始服务通知
    const FINISH_SERVICE_NOTICE = 204;//完成服务通知
    const ORDER_CANCEL_NOTICE = 205;//取消订单通知
    const ORDER_REFUND_NOTICE = 206;//服务退款通知
    const ORDER_PAY_NOTICE_PLATFORM = 207;//订单付款通知-平台
    const STAFF_APPLY_NOTICE_PLATFORM = 208;//入住申请通知-平台
    const ORDER_ABNORMAL_NOTICE_PLATFORM = 209;//订单异常通知-平台
    const APPLY_SUCCESS_NOTICE_STAFF = 210;//入驻审核通过通知-师傅
    const APPLY_FAIL_NOTICE_STAFF = 211;//入驻审核未通过通知-师傅
    const GRAB_ORDER_NOTICE_STAFF = 212;//抢单通知-师傅
    const ACCEPT_ORDER_NOTICE_STAFF = 213;//接单通知-师傅
    const START_SERVICE_NOTICE_STAFF = 214;//开始服务通知-师傅
    const END_SERVICE_NOTICE_STAFF = 215;//结束服务通知-师傅
    const ORDER_CANCEL_NOTICE_STAFF = 216;//取消订单通知-师傅


    /**
     * 验证码场景
     */
    const SMS_SCENE = [
        self::LOGIN_CAPTCHA,
        self::BIND_MOBILE_CAPTCHA,
        self::CHANGE_MOBILE_CAPTCHA,
        self::RESET_PASSWORD_CAPTCHA,
        self::REGISTER_CAPTCHA,
        self::REGISTER_CAPTCHA_STAFF,
        self::LOGIN_CAPTCHA_STAFF,
        self::RESET_PASSWORD_CAPTCHA_STAFF,
        self::CHANGE_MOBILE_CAPTCHA_STAFF,
    ];


    //通知类型
    const BUSINESS_NOTIFICATION = 1;//业务通知
    const VERIFICATION_CODE = 2;//验证码


    /**
     * @notes 通知类型
     * @param bool $value
     * @return string|string[]
     * @author ljj
     * @date 2022/2/17 2:49 下午
     */
    public static function getTypeDesc($value = true)
    {
        $data = [
            self::BUSINESS_NOTIFICATION => '业务通知',
            self::VERIFICATION_CODE => '验证码'
        ];
        if ($value === true) {
            return $data;
        }
        return $data[$value];
    }


    /**
     * @notes 获取场景描述
     * @param $sceneId
     * @param false $flag
     * @return string|string[]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getSceneDesc($sceneId, $flag = false)
    {
        $desc = [
            self::LOGIN_CAPTCHA => '登录验证码',
            self::BIND_MOBILE_CAPTCHA => '绑定手机验证码',
            self::CHANGE_MOBILE_CAPTCHA => '变更手机验证码',
            self::RESET_PASSWORD_CAPTCHA => '重设登录密码验证码',
            self::REGISTER_CAPTCHA => '注册验证码',
            self::REGISTER_CAPTCHA_STAFF => '注册验证码',
            self::LOGIN_CAPTCHA_STAFF => '登录验证码',
            self::RESET_PASSWORD_CAPTCHA_STAFF => '重设登录密码验证码',
            self::CHANGE_MOBILE_CAPTCHA_STAFF => '变更手机验证码',

            self::ORDER_PAY_NOTICE => '订单付款通知',
            self::ACCEPT_ORDER_NOTICE => '订单接单通知',
            self::START_SERVICE_NOTICE => '开始服务通知',
            self::FINISH_SERVICE_NOTICE => '完成服务通知',
            self::ORDER_CANCEL_NOTICE => '取消订单通知',
            self::ORDER_REFUND_NOTICE => '服务退款通知',
            self::ORDER_PAY_NOTICE_PLATFORM => '订单付款通知',
            self::STAFF_APPLY_NOTICE_PLATFORM => '入住申请通知',
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => '订单异常通知',
            self::APPLY_SUCCESS_NOTICE_STAFF => '入驻审核通过通知',
            self::APPLY_FAIL_NOTICE_STAFF => '入驻审核未通过通知',
            self::GRAB_ORDER_NOTICE_STAFF => '抢单通知',
            self::ACCEPT_ORDER_NOTICE_STAFF => '接单通知',
            self::START_SERVICE_NOTICE_STAFF => '开始服务通知',
            self::END_SERVICE_NOTICE_STAFF => '结束服务通知',
            self::ORDER_CANCEL_NOTICE_STAFF => '取消订单通知',
        ];

        if ($flag) {
            return $desc;
        }

        return $desc[$sceneId] ?? '';
    }


    /**
     * @notes 更具标记获取场景
     * @param $tag
     * @return int|string
     * @author 段誉
     * @date 2022/9/15 15:08
     */
    public static function getSceneByTag($tag)
    {
        $scene = [
            // 手机验证码登录
            'YZMDL' => self::LOGIN_CAPTCHA,
            // 绑定手机号验证码
            'BDSJHM' => self::BIND_MOBILE_CAPTCHA,
            // 变更手机号验证码
            'BGSJHM' => self::CHANGE_MOBILE_CAPTCHA,
            // 重设登录密码
            'CSDLMM' => self::RESET_PASSWORD_CAPTCHA,
            // 注册验证码
            'ZCYZM' => self::REGISTER_CAPTCHA,
            // 注册验证码-师傅
            'ZCYZMSF' => self::REGISTER_CAPTCHA_STAFF,
            // 手机验证码登录-师傅
            'YZMDLSF' => self::LOGIN_CAPTCHA_STAFF,
            // 重设登录密码-师傅
            'CSDLMMSF' => self::RESET_PASSWORD_CAPTCHA_STAFF,
            // 变更手机号验证码-师傅
            'BGSJHMSF' => self::CHANGE_MOBILE_CAPTCHA_STAFF,
        ];
        return $scene[$tag] ?? '';
    }


    /**
     * @notes 获取场景变量
     * @param $sceneId
     * @param false $flag
     * @return array|string[]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getVars($sceneId, $flag = false)
    {
        $desc = [
            self::LOGIN_CAPTCHA => '验证码:code',
            self::BIND_MOBILE_CAPTCHA => '验证码:code',
            self::CHANGE_MOBILE_CAPTCHA => '验证码:code',
            self::RESET_PASSWORD_CAPTCHA => '验证码:code',
            self::REGISTER_CAPTCHA => '验证码:code',
            self::REGISTER_CAPTCHA_STAFF => '验证码:code',
            self::LOGIN_CAPTCHA_STAFF => '验证码:code',
            self::RESET_PASSWORD_CAPTCHA_STAFF => '验证码:code',
            self::CHANGE_MOBILE_CAPTCHA_STAFF => '验证码:code',

            self::ORDER_PAY_NOTICE => '预约时间:appoint_time',
            self::ACCEPT_ORDER_NOTICE => '预约时间:appoint_time',
            self::START_SERVICE_NOTICE => '预约时间:appoint_time',
            self::FINISH_SERVICE_NOTICE => '预约时间:finish_time',
            self::ORDER_CANCEL_NOTICE => '',
            self::ORDER_REFUND_NOTICE => '退款金额:refund_amount',
            self::ORDER_PAY_NOTICE_PLATFORM => '',
            self::STAFF_APPLY_NOTICE_PLATFORM => '师傅名称:staff_name',
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => '',
            self::APPLY_SUCCESS_NOTICE_STAFF => '师傅名称:staff_name 审核状态:apply_status_desc',
            self::APPLY_FAIL_NOTICE_STAFF => '师傅名称:staff_name 审核状态:apply_status_desc',
            self::GRAB_ORDER_NOTICE_STAFF => '',
            self::ACCEPT_ORDER_NOTICE_STAFF => '师傅名称:staff_name',
            self::START_SERVICE_NOTICE_STAFF => '',
            self::END_SERVICE_NOTICE_STAFF => '',
            self::ORDER_CANCEL_NOTICE_STAFF => '师傅名称:staff_name 预约时间:appoint_time',
        ];

        if ($flag) {
            return $desc;
        }

        return isset($desc[$sceneId]) ? ['可选变量 ' . $desc[$sceneId]] : [];
    }


    /**
     * @notes 获取系统通知示例
     * @param $sceneId
     * @param false $flag
     * @return array|string[]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getSystemExample($sceneId, $flag = false)
    {
        $desc = [
            self::ORDER_PAY_NOTICE => '您预约${appoint_time}的订单已支付成功，师傅届时将会与您联系，请保持手机畅通。',
            self::ACCEPT_ORDER_NOTICE => '您预约${appoint_time}的订单已被接单，师傅届时将会与您联系，请保持手机畅通。',
            self::START_SERVICE_NOTICE => '您的订单${order_sn}已开始服务。',
            self::FINISH_SERVICE_NOTICE => '您的订单${order_sn}已完成服务。',
            self::ORDER_CANCEL_NOTICE => '您的订单${order_sn}已被取消。',
            self::ORDER_REFUND_NOTICE => '您的订单${order_sn}已被退款，退款金额${refund_amount}元。',
            self::ORDER_PAY_NOTICE_PLATFORM => '亲爱的商家，您有新的订单${order_sn}，请及时处理。',
            self::STAFF_APPLY_NOTICE_PLATFORM => '亲爱的商家，用户${staff_name}，提交了师傅入驻申请，请及时处理。',
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => '亲爱的商家，${order_sn}的订单存在异常，请及时处理。',
            self::APPLY_SUCCESS_NOTICE_STAFF => '您好，${staff_name}，您的入驻申请已通过，请登录师傅端进行查看。',
            self::APPLY_FAIL_NOTICE_STAFF => '您好，{staff_name}，您的入驻申请未通过，请登录师傅端进行查看。',
            self::GRAB_ORDER_NOTICE_STAFF => '附近有新的预约订单可供抢单，请登录师傅端进行查看。',
            self::ACCEPT_ORDER_NOTICE_STAFF => '您好，${staff_name}，您有新的预约订单，请登录师傅端确认接单。',
            self::START_SERVICE_NOTICE_STAFF => '订单${order_sn}已开始服务，请严格遵守法律法规提供服务。',
            self::END_SERVICE_NOTICE_STAFF => '订单${order_sn}已结束服务，服务人员请注意核实服务的各项细节是否已完成无误。',
            self::ORDER_CANCEL_NOTICE_STAFF => '您好，${staff_name}，用户预约${appoint_time}的订单已被取消，您无需操作。',
        ];

        if ($flag) {
            return $desc;
        }

        return isset($desc[$sceneId]) ? [$desc[$sceneId]] : [];
    }


    /**
     * @notes 获取短信通知示例
     * @param $sceneId
     * @param false $flag
     * @return array|string[]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getSmsExample($sceneId, $flag = false)
    {
        $desc = [
            self::LOGIN_CAPTCHA => '您正在登录，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::BIND_MOBILE_CAPTCHA => '您正在绑定手机号，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::CHANGE_MOBILE_CAPTCHA => '您正在变更手机号，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::RESET_PASSWORD_CAPTCHA => '您正在重设登录密码，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::REGISTER_CAPTCHA => '您正在注册账号，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::REGISTER_CAPTCHA_STAFF => '您正在注册账号，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::LOGIN_CAPTCHA_STAFF => '您正在登录，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',
            self::RESET_PASSWORD_CAPTCHA_STAFF => '您正在重设登录密码，验证码${code}，切勿将验证码泄露于他人，本条验证码有效期5分钟。',

            self::ORDER_PAY_NOTICE => '您预约${appoint_time}的订单已支付成功，师傅届时将会与您联系，请保持手机畅通。',
            self::ACCEPT_ORDER_NOTICE => '您预约${appoint_time}的订单已被接单，师傅届时将会与您联系，请保持手机畅通。',
            self::START_SERVICE_NOTICE => '您的订单${order_sn}已开始服务。',
            self::FINISH_SERVICE_NOTICE => '您的订单${order_sn}已完成服务。',
            self::ORDER_CANCEL_NOTICE => '您的订单${order_sn}已被取消。',
            self::ORDER_REFUND_NOTICE => '您的订单${order_sn}已被退款，退款金额${refund_amount}元。',
            self::ORDER_PAY_NOTICE_PLATFORM => '亲爱的商家，您有新的订单${order_sn}，请及时处理。',
            self::STAFF_APPLY_NOTICE_PLATFORM => '亲爱的商家，用户${staff_name}，提交了师傅入驻申请，请及时处理。',
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => '亲爱的商家，${order_sn}的订单存在异常，请及时处理。',
            self::APPLY_SUCCESS_NOTICE_STAFF => '您好，${staff_name}，您的入驻申请已通过，请登录师傅端进行查看。',
            self::APPLY_FAIL_NOTICE_STAFF => '您好，{staff_name}，您的入驻申请未通过，请登录师傅端进行查看。',
            self::GRAB_ORDER_NOTICE_STAFF => '附近有新的预约订单可供抢单，请登录师傅端进行查看。',
            self::ACCEPT_ORDER_NOTICE_STAFF => '您好，${staff_name}，您有新的预约订单，请登录师傅端确认接单。',
            self::START_SERVICE_NOTICE_STAFF => '订单${order_sn}已开始服务，请严格遵守法律法规提供服务。',
            self::END_SERVICE_NOTICE_STAFF => '订单${order_sn}已结束服务，服务人员请注意核实服务的各项细节是否已完成无误。',
            self::ORDER_CANCEL_NOTICE_STAFF => '您好，${staff_name}，用户预约${appoint_time}的订单已被取消，您无需操作。',
        ];

        if ($flag) {
            return $desc;
        }

        return isset($desc[$sceneId]) ? ['示例：' . $desc[$sceneId]] : [];
    }


    /**
     * @notes 获取公众号模板消息示例
     * @param $sceneId
     * @param false $flag
     * @return array|string[]|\string[][]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getOaExample($sceneId, $flag = false)
    {
        $desc = [
            self::ORDER_PAY_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ACCEPT_ORDER_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::START_SERVICE_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::FINISH_SERVICE_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ORDER_CANCEL_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ORDER_REFUND_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ORDER_PAY_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::STAFF_APPLY_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::APPLY_SUCCESS_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::APPLY_FAIL_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::GRAB_ORDER_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ACCEPT_ORDER_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::START_SERVICE_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::END_SERVICE_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
            self::ORDER_CANCEL_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用编号：OPENTM201285651的模板，添加，获得模板ID。',
                '头部内容：您的订单已支付成功。',
                '尾部内容：商家正在快马加鞭为您安排发货。',
                '字段名 字段值 字段内容',
                '商品名称 keyword1 {goods_name}',
                '订单编号 keyword2 {order_sn}',
                '支付金额 keyword3 {order_amount}',
            ],
        ];

        if ($flag) {
            return $desc;
        }

        return $desc[$sceneId] ?? [];
    }


    /**
     * @notes 获取小程序订阅消息示例
     * @param $sceneId
     * @param false $flag
     * @return array|mixed
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getMnpExample($sceneId, $flag = false)
    {
        $desc = [
            self::ORDER_PAY_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ACCEPT_ORDER_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::START_SERVICE_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::FINISH_SERVICE_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ORDER_CANCEL_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ORDER_REFUND_NOTICE => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ORDER_PAY_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::STAFF_APPLY_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ORDER_ABNORMAL_NOTICE_PLATFORM => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::APPLY_SUCCESS_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::APPLY_FAIL_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::GRAB_ORDER_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ACCEPT_ORDER_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::START_SERVICE_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::END_SERVICE_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
            self::ORDER_CANCEL_NOTICE_STAFF => [
                '模板库: 搜索 “订单支付成功通知”，选用类目：软件服务提供商的模板，选用并选择以下参数，提交获得模板ID。',
                '字段名 字段值 字段内容',
                '订单编号 character_string1 {order_sn}',
                '支付时间 time2 {pay_time}',
                '订单金额 amount3 {order_amount}',
                '商品名称 thing4 {goods_name}',
            ],
        ];

        if ($flag) {
            return $desc;
        }

        return $desc[$sceneId] ?? [];
    }


    /**
     * @notes 提示
     * @param $type
     * @param $sceneId
     * @return array|string|string[]|\string[][]
     * @author 段誉
     * @date 2022/3/29 11:33
     */
    public static function getOperationTips($type, $sceneId)
    {
        // 场景变量
        $vars = self::getVars($sceneId);
        // 其他提示
        $other = [];
        // 示例
        switch ($type) {
            case self::SYSTEM:
                $example = self::getSystemExample($sceneId);
                break;
            case self::SMS:
                $other[] = '生效条件：1、管理后台完成短信设置。 2、第三方短信平台申请模板 3、若是腾讯云模板变量名须换成变量名出现顺序对应的数字(例：您好{nickname},您的订单{order_sn}已发货! 须改为 您好{1},您的订单{2}已发货!)';
                $example = self::getSmsExample($sceneId);
                break;
            case self::OA:
                $other[] = '配置路径：公众号后台 > 广告与服务 > 模板消息';
                $other[] = '推荐行业：主营行业：IT科技/互联网|电子商务 副营行业：消费品/消费品';
                $example = self::getOaExample($sceneId);
                break;
            case self::MNP:
                $other[] = '配置路径：小程序后台 > 功能 > 订阅消息';
                $example = self::getMnpExample($sceneId);
                break;
        }
        $tips = array_merge($vars, $example, $other);

        return $tips;
    }
}