<?php
declare(strict_types=1);

namespace app\middleware;

use think\facade\Cache;

/**
 * API 认证中间件
 */
class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        // 获取 Token
        $token = $request->header('Authorization');
        
        if (!$token) {
            return json(['code' => 401, 'msg' => '请先登录'], 401);
        }
        
        // 验证 Token
        $tokenData = Cache::get('token_' . md5($token));
        
        if (!$tokenData) {
            return json(['code' => 401, 'msg' => '登录已过期，请重新登录'], 401);
        }
        
        // 检查是否过期
        if (isset($tokenData['expire']) && time() > $tokenData['expire']) {
            Cache::delete('token_' . md5($token));
            return json(['code' => 401, 'msg' => '登录已过期，请重新登录'], 401);
        }
        
        // 将用户信息注入请求
        $request->userId = $tokenData['user_id'];
        $request->tenantId = $tokenData['tenant_id'];
        
        return $next($request);
    }
}
