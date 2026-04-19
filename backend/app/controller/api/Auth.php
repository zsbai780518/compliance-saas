<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\User;
use app\model\Tenant;
use think\facade\Db;
use think\facade\Log;

/**
 * 认证控制器 - 注册/登录/实名认证
 */
class Auth extends BaseController
{
    /**
     * 用户注册（三重绑定）
     */
    public function register()
    {
        $data = $this->request->post();
        
        // 验证必填字段
        $validate = validate([
            'realname' => 'require|chsAlphaNum',
            'mobile' => 'require|mobile',
            'id_card' => 'require|idCard',
            'password' => 'require|min:6',
            'sms_code' => 'require',
            'tenant_id' => 'require|integer',
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        // 验证短信验证码
        if (!$this->verifySmsCode($data['mobile'], $data['sms_code'])) {
            return json(['code' => 400, 'msg' => '短信验证码错误']);
        }
        
        // 检查租户是否存在
        $tenant = Tenant::get($data['tenant_id']);
        if (!$tenant || $tenant->isExpired()) {
            return json(['code' => 400, 'msg' => '企业租户不存在或已到期']);
        }
        
        // 检查租户人数限制
        $userCount = User::where('tenant_id', $data['tenant_id'])->count();
        if ($userCount >= $tenant->max_users) {
            return json(['code' => 400, 'msg' => '企业学员数量已达上限']);
        }
        
        // 检查三重绑定唯一性
        $existUser = User::checkUnique($data['realname'], $data['mobile'], $data['id_card'], $data['tenant_id']);
        if ($existUser) {
            return json(['code' => 400, 'msg' => '该用户已在企业注册']);
        }
        
        // 检查手机号在租户内唯一
        $existMobile = User::checkMobileUnique($data['mobile'], $data['tenant_id']);
        if ($existMobile) {
            return json(['code' => 400, 'msg' => '该手机号已在企业注册']);
        }
        
        // 创建用户
        $user = new User();
        $user->tenant_id = $data['tenant_id'];
        $user->realname = $data['realname'];
        $user->mobile = $data['mobile'];
        $user->username = $data['mobile'];
        $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $user->id_card = $data['id_card'];
        $user->auth_status = 0; // 待认证
        $user->status = 1;
        
        if ($user->save()) {
            // 清除短信验证码
            $this->clearSmsCode($data['mobile']);
            
            Log::info("用户注册成功：{$data['mobile']}，租户 ID: {$data['tenant_id']}");
            
            return json([
                'code' => 200,
                'msg' => '注册成功',
                'data' => [
                    'user_id' => $user->id,
                    'mobile' => $user->mobile,
                    'realname' => $user->realname,
                ]
            ]);
        }
        
        return json(['code' => 500, 'msg' => '注册失败']);
    }
    
    /**
     * 账号密码登录
     */
    public function login()
    {
        $data = $this->request->post();
        
        $validate = validate([
            'mobile' => 'require|mobile',
            'password' => 'require',
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        $user = User::where('mobile', $data['mobile'])->find();
        if (!$user) {
            return json(['code' => 400, 'msg' => '用户不存在']);
        }
        
        if (!password_verify($data['password'], $user->password)) {
            return json(['code' => 400, 'msg' => '密码错误']);
        }
        
        if ($user->status != 1) {
            return json(['code' => 400, 'msg' => '账号已被禁用']);
        }
        
        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip = $this->request->ip();
        $user->save();
        
        // 生成 Token
        $token = $this->generateToken($user);
        
        Log::info("用户登录成功：{$data['mobile']}");
        
        return json([
            'code' => 200,
            'msg' => '登录成功',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'mobile' => $user->mobile,
                    'realname' => $user->realname,
                    'auth_status' => $user->auth_status,
                    'auth_status_text' => $user->auth_status_text,
                    'tenant_id' => $user->tenant_id,
                ]
            ]
        ]);
    }
    
    /**
     * 发送短信验证码
     */
    public function sendSmsCode()
    {
        $data = $this->request->post();
        
        $validate = validate([
            'mobile' => 'require|mobile',
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        // 检查发送频率
        if ($this->checkSmsFrequency($data['mobile'])) {
            return json(['code' => 400, 'msg' => '发送过于频繁，请稍后再试']);
        }
        
        // 生成验证码
        $code = sprintf('%06d', mt_rand(0, 999999));
        
        // TODO: 调用短信服务发送
        // SmsService::send($data['mobile'], $code);
        
        // 存储验证码（5 分钟有效期）
        cache('sms_code_' . $data['mobile'], $code, 300);
        
        Log::info("发送短信验证码：{$data['mobile']}");
        
        return json([
            'code' => 200,
            'msg' => '验证码已发送',
            'data' => ['expire' => 300]
        ]);
    }
    
    /**
     * 上传身份证（实名认证）
     */
    public function uploadIdCard()
    {
        $userId = $this->request->userId;
        $user = User::get($userId);
        
        if (!$user) {
            return json(['code' => 400, 'msg' => '用户不存在']);
        }
        
        // 获取上传的文件
        $fileFront = $this->request->file('id_card_front');
        $fileBack = $this->request->file('id_card_back');
        
        if (!$fileFront || !$fileBack) {
            return json(['code' => 400, 'msg' => '请上传身份证正反面照片']);
        }
        
        // 验证文件大小（不超过 5MB）
        if ($fileFront->getSize() > 5 * 1024 * 1024 || $fileBack->getSize() > 5 * 1024 * 1024) {
            return json(['code' => 400, 'msg' => '图片大小不能超过 5MB']);
        }
        
        // 保存文件
        $savePath = runtime_path() . 'uploads/id_card/' . date('Ymd') . '/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        $frontName = $savePath . uniqid() . '_front_' . $fileFront->hashName();
        $backName = $savePath . uniqid() . '_back_' . $fileBack->hashName();
        
        $fileFront->move($savePath, basename($frontName));
        $fileBack->move($savePath, basename($backName));
        
        // 更新用户信息
        $user->id_card_front = '/uploads/id_card/' . date('Ymd') . '/' . basename($frontName);
        $user->id_card_back = '/uploads/id_card/' . date('Ymd') . '/' . basename($backName);
        $user->auth_status = 2; // 审核中
        $user->save();
        
        Log::info("用户上传身份证：用户 ID {$userId}");
        
        return json([
            'code' => 200,
            'msg' => '身份证已上传，等待审核',
        ]);
    }
    
    /**
     * 查询认证状态
     */
    public function authStatus()
    {
        $userId = $this->request->userId;
        $user = User::get($userId);
        
        if (!$user) {
            return json(['code' => 400, 'msg' => '用户不存在']);
        }
        
        return json([
            'code' => 200,
            'data' => [
                'auth_status' => $user->auth_status,
                'auth_status_text' => $user->auth_status_text,
                'auth_remark' => $user->auth_remark,
                'is_certified' => $user->is_certified,
            ]
        ]);
    }
    
    /**
     * 微信绑定
     */
    public function wxBind()
    {
        $userId = $this->request->userId;
        $data = $this->request->post();
        
        $validate = validate([
            'wx_openid' => 'require',
            'wx_code' => 'require',
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        // TODO: 调用微信 API 验证 code
        // $wxInfo = WxService::getOAuthInfo($data['wx_code']);
        
        $user = User::get($userId);
        $user->wx_openid = $data['wx_openid'];
        // $user->wx_unionid = $wxInfo['unionid'] ?? '';
        $user->save();
        
        return json([
            'code' => 200,
            'msg' => '微信绑定成功',
        ]);
    }
    
    /**
     * 退出登录
     */
    public function logout()
    {
        $token = $this->request->header('Authorization');
        if ($token) {
            // 使 Token 失效
            cache('token_' . md5($token), null);
        }
        
        return json([
            'code' => 200,
            'msg' => '退出成功',
        ]);
    }
    
    // ==================== 辅助方法 ====================
    
    /**
     * 验证短信验证码
     */
    private function verifySmsCode($mobile, $code)
    {
        $cacheCode = cache('sms_code_' . $mobile);
        if (!$cacheCode) {
            return false;
        }
        return $cacheCode === $code;
    }
    
    /**
     * 清除短信验证码
     */
    private function clearSmsCode($mobile)
    {
        cache('sms_code_' . $mobile, null);
    }
    
    /**
     * 检查短信发送频率
     */
    private function checkSmsFrequency($mobile)
    {
        $key = 'sms_freq_' . $mobile;
        $count = cache($key) ?? 0;
        
        if ($count >= 5) {
            return true;
        }
        
        cache($key, $count + 1, 3600); // 1 小时内最多 5 次
        return false;
    }
    
    /**
     * 生成 Token
     */
    private function generateToken($user)
    {
        $token = md5(uniqid() . $user->id . time());
        
        // 存储 Token 信息（7 天有效期）
        $tokenData = [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'expire' => time() + 7 * 24 * 3600,
        ];
        
        cache('token_' . md5($token), $tokenData, 7 * 24 * 3600);
        
        return $token;
    }
}
