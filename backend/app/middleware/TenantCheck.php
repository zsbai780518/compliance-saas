<?php
declare(strict_types=1);

namespace app\middleware;

/**
 * 多租户隔离中间件
 * 确保每个企业只能访问自己的数据
 */
class TenantCheck
{
    public function handle($request, \Closure $next)
    {
        // 如果是系统级接口，跳过租户检查
        $path = $request->path();
        if (strpos($path, 'system/') === 0) {
            return $next($request);
        }
        
        // 从 Token 或请求中获取租户 ID
        $tenantId = $request->tenantId ?? $request->param('tenant_id');
        
        if (!$tenantId) {
            return json(['code' => 400, 'msg' => '缺少租户标识'], 400);
        }
        
        // 验证租户是否存在且有效
        $tenant = \app\model\Tenant::get($tenantId);
        if (!$tenant) {
            return json(['code' => 400, 'msg' => '租户不存在'], 400);
        }
        
        if ($tenant->isExpired()) {
            return json(['code' => 400, 'msg' => '租户已到期，请联系管理员续费'], 400);
        }
        
        // 将租户信息注入请求
        $request->tenant = $tenant;
        
        return $next($request);
    }
}
