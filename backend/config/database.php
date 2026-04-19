<?php
// +----------------------------------------------------------------------
// | 企业合规培训 SaaS 系统 - 数据库配置文件
// +----------------------------------------------------------------------

return [
    // 数据库连接配置
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
            // 数据库名称
            'database' => 'compliance_saas',
            // 用户名
            'username' => 'root',
            // 密码
            'password' => 'root',
            // 端口
            'hostport' => '3306',
            // 数据库连接参数
            'params' => [],
            // 数据库编码默认采用 utf8mb4
            'charset' => 'utf8mb4',
            // 数据库表前缀
            'prefix' => 'tp_',
            // 数据库部署方式，common 集中式多数据库共享，separate 分布式多数据库分离
            'deploy' => 0,
            // 是否主从复制，默认 false
            'rw_separate' => false,
            // 主从分布式服务器列表
            'master_no' => 0,
            // 读服务器列表
            'slave_no' => '',
            // 是否严格检查字段是否存在
            'fields_strict' => true,
            // 数据集返回类型
            'resultset_type' => 'array',
            // 自动写入时间戳字段
            'auto_timestamp' => true,
            // 时间字段取出后的默认处理类型
            'datetime_format' => 'Y-m-d H:i:s',
            // 是否需要进行 SQL 性能分析
            'sql_explain' => true,
            // Builder 类
            'builder' => '',
            // Query 类
            'query' => '\\think\\db\\Query',
            // 是否断线重连
            'break_reconnect' => true,
            // 关闭 SQL 监听缓存
            'trigger_sql' => true,
        ],
    ],
];
