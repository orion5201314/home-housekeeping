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

namespace app\api\logic;


use app\common\enum\notice\NoticeEnum;
use app\common\enum\user\UserTerminalEnum;
use app\common\enum\YesNoEnum;
use app\common\logic\BaseLogic;
use app\common\model\decorate\DecoratePage;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsComment;
use app\common\model\user\User;
use app\common\model\user\UserAuth;
use app\common\service\ConfigService;
use app\common\service\sms\SmsDriver;
use app\common\service\WeChatConfigService;
use EasyWeChat\Factory;
use think\facade\Config;

class UserLogic extends BaseLogic
{
    /**
     * @notes 用户中心
     * @param $userInfo
     * @return array
     * @author ljj
     * @date 2022/2/23 5:24 下午
     */
    public function center($userInfo)
    {
        $user_id = $userInfo['user_id'] ?? 0;
        $terminal = $userInfo['terminal'] ?? 0;
        $user = User::where(['id'=>$user_id])
            ->field('id,sn,nickname,avatar,mobile,sex,create_time,is_new_user,account')
            ->findOrEmpty()
            ->toArray();

        //支付是否需要授权
        $user['pay_auth'] = 0;
        if (in_array($terminal, [UserTerminalEnum::WECHAT_MMP, UserTerminalEnum::WECHAT_OA])) {
            $auth = self::hasWechatAuth($user_id);
            $user['pay_auth'] = $auth ? YesNoEnum::NO : YesNoEnum::YES;
        }

        //获取评价数量
        $user['comment_count'] = GoodsComment::where(['user_id'=>$user_id])->count();

        return $user;
    }

    /**
     * @notes 用户收藏列表
     * @param $user_id
     * @return mixed
     * @author ljj
     * @date 2022/2/24 3:07 下午
     */
    public function collectLists($user_id)
    {
        $lists = Goods::alias('g')
            ->join('goods_collect gc', 'g.id = gc.goods_id')
            ->field('gc.goods_id,g.image,g.name,g.min_price as price,g.status')
            ->where(['gc.user_id'=>$user_id])
            ->order('gc.id','desc')
            ->select()
            ->toArray();

        return $lists;
    }

    /**
     * @notes 用户信息
     * @param $userId
     * @return array
     * @author ljj
     * @date 2022/3/7 5:52 下午
     */
    public static function info($user_id)
    {
        $user = User::where(['id'=>$user_id])
            ->field('id,sn,sex,account,password,nickname,real_name,avatar,mobile,create_time')
            ->findOrEmpty();
        $user['has_password'] = !empty($user['password']);
        $user['has_auth'] = self::hasWechatAuth($user_id);
        $user['version'] = config('project.version');
        $user->hidden(['password']);
        return $user->toArray();
    }

    /**
     * @notes 设置用户信息
     * @param int $userId
     * @param array $params
     * @return bool
     * @author ljj
     * @date 2022/2/24 3:44 下午
     */
    public static function setInfo(int $userId,array $params):bool
    {
        User::update(['id'=>$userId,$params['field']=>$params['value']]);
        return true;
    }

    /**
     * @notes 获取微信手机号并绑定
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/2/24 4:41 下午
     */
    public static function getMobileByMnp(array $params)
    {
        try {
            $getMnpConfig = WeChatConfigService::getMnpConfig();
            $app = Factory::miniProgram($getMnpConfig);
            $response = $app->phone_number->getUserPhoneNumber($params['code']);

            $phoneNumber = $response['phone_info']['purePhoneNumber'] ?? '';
            if (empty($phoneNumber)) {
                throw new \Exception('获取手机号码失败');
            }

            $user = User::where([
                ['mobile', '=', $phoneNumber],
                ['id', '<>', $params['user_id']]
            ])->findOrEmpty();

            if (!$user->isEmpty()) {
                throw new \Exception('手机号已被其他账号绑定');
            }

            // 绑定手机号
            User::update([
                'id' => $params['user_id'],
                'mobile' => $phoneNumber
            ]);

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 重置登录密码
     * @param $params
     * @return bool
     * @author 段誉
     * @date 2022/9/16 18:06
     */
    public static function resetPassword(array $params)
    {
        try {
            // 校验验证码
            $smsDriver = new SmsDriver();
            if (!$smsDriver->verify($params['mobile'], $params['code'], NoticeEnum::RESET_PASSWORD_CAPTCHA)) {
                throw new \Exception('验证码错误');
            }

            // 重置密码
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);

            // 更新
            User::where('mobile', $params['mobile'])->update([
                'password' => $password
            ]);

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 设置登录密码
     * @author Tab
     * @date 2021/10/22 18:10
     */
    public static function setPassword($params)
    {
        try {
            $user = User::findOrEmpty($params['user_id']);
            if ($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }
            if (!empty($user->password)) {
                throw new \Exception('用户已设置登录密码');
            }
            $passwordSalt = Config::get('project.unique_identification');
            $password = create_password($params['password'], $passwordSalt);
            $user->password = $password;
            $user->save();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 修稿密码
     * @param $params
     * @param $userId
     * @return bool
     * @author 段誉
     * @date 2022/9/20 19:13
     */
    public static function changePassword(array $params, int $userId)
    {
        try {
            $user = User::findOrEmpty($userId);
            if ($user->isEmpty()) {
                throw new \Exception('用户不存在');
            }

            // 密码盐
            $passwordSalt = Config::get('project.unique_identification');

            if (!empty($user['password'])) {
                if (empty($params['old_password'])) {
                    throw new \Exception('请填写旧密码');
                }
                $oldPassword = create_password($params['old_password'], $passwordSalt);
                if ($oldPassword != $user['password']) {
                    throw new \Exception('原密码不正确');
                }
            }

            // 保存密码
            $password = create_password($params['password'], $passwordSalt);
            $user->password = $password;
            $user->save();

            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }

    /**
     * @notes 判断用户是否有设置登录密码
     * @param $userId
     * @author Tab
     * @date 2021/10/22 18:25
     */
    public static function hasPassword($userId)
    {
        $user = User::findOrEmpty($userId);
        return empty($user->password) ? false : true;
    }


    /**
     * @notes 绑定手机号
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/8/25 17:55
     */
    public static function bindMobile($params)
    {
        try {
            $smsDriver = new SmsDriver();
            $result = $smsDriver->verify($params['mobile'], $params['code']);
            if(!$result) {
                throw new \Exception('验证码错误');
            }
            $user = User::where('mobile', $params['mobile'])->findOrEmpty();
            if(!$user->isEmpty()) {
                throw new \Exception('该手机号已被其他账号绑定');
            }
            unset($params['code']);
            User::update($params);
            return true;
        } catch (\Exception $e) {
            self::setError($e->getMessage());
            return false;
        }
    }


    /**
     * @notes 是否有微信授权信息
     * @param $userId
     * @return bool
     * @author 段誉
     * @date 2022/9/20 19:36
     */
    public static function hasWechatAuth(int $userId)
    {
        //是否有微信授权登录
        $terminal = [UserTerminalEnum::WECHAT_MMP, UserTerminalEnum::WECHAT_OA];
        $auth = UserAuth::where(['user_id' => $userId])
            ->whereIn('terminal', $terminal)
            ->findOrEmpty();
        return !$auth->isEmpty();
    }

    /**
     * @notes 我的钱包
     * @param int $userId
     * @return array
     * @author ljj
     * @date 2022/12/12 9:35 上午
     */
    public static function wallet(int $userId): array
    {
        $result = User::where(['id' => $userId])
            ->field('id,user_money,user_earnings')
            ->findOrEmpty()
            ->toArray();
        if (!empty($result)) {
            $result['total_money'] = round($result['user_money'] + $result['user_earnings'],2);
            $result['recharge_open'] = ConfigService::get('recharge', 'recharge_open',1);
        }

        return $result;
    }
}