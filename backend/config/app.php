<?php
// +----------------------------------------------------------------------
// | 企业合规培训 SaaS 系统 - 应用配置文件
// +----------------------------------------------------------------------

return [
    // 应用命名空间
    'app_namespace' => 'app',
    
    // 应用调试模式
    'app_debug' => true,
    
    // 应用 Trace
    'app_trace' => false,
    
    // 应用模式
    'app_status' => 'develop',
    
    // 是否组成多模块
    'multi_module' => true,
    
    // 模块类库映射
    'module_map' => [],
    
    // 缓存类型
    'default_return_type' => 'json',
    
    // 默认错误跳转页面
    'http_exception_template' => [
        404 => '',
        500 => '',
    ],
    
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    
    // 语言切换默认语言
    'default_lang' => 'zh-cn',
    
    // 是否允许语言列表自动选择
    'lang_switch_on' => false,
    
    // 默认全局过滤
    'default_filter' => 'strip_tags,htmlspecialchars',
    
    // 默认 AJAX 请求返回
    'default_ajax_return' => 'json',
];
