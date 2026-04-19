<?php
// +----------------------------------------------------------------------
// | 企业合规培训 SaaS 系统 - 中间件配置
// +----------------------------------------------------------------------

return [
    // 全局中间件
    'global' => [
        // 跨域请求支持
        \app\middleware\AllowCrossDomain::class,
        // 请求缓存
        \app\middleware\CheckRequestCache::class,
        // 多租户隔离中间件
        \app\middleware\TenantCheck::class,
    ],
    
    // 模块中间件
    'module' => [
        'api' => [
            // API 认证中间件
            \app\middleware\AuthCheck::class,
            // 操作日志中间件
            \app\middleware\OperationLog::class,
        ],
        'admin' => [
            // 后台管理员认证
            \app\middleware\AdminAuth::class,
            // 权限检查
            \app\middleware\PermissionCheck::class,
        ],
    ],
    
    // 控制器中间件
    'controller' => [],
];
