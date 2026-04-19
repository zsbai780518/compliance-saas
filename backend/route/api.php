<?php
// +----------------------------------------------------------------------
// | 企业合规培训 SaaS 系统 - 路由配置
// +----------------------------------------------------------------------

use think\facade\Route;

// API 路由组
Route::group('api', function () {
    // 认证相关
    Route::post('auth/register', 'auth/register');          // 用户注册
    Route::post('auth/login', 'auth/login');                // 账号登录
    Route::post('auth/sms_login', 'auth/smsLogin');         // 短信登录
    Route::post('auth/sms_code', 'auth/sendSmsCode');       // 发送验证码
    Route::post('auth/wx_bind', 'auth/wxBind');             // 微信绑定
    Route::post('auth/logout', 'auth/logout');              // 退出登录
    
    // 实名认证
    Route::post('auth/idcard_upload', 'auth/uploadIdCard'); // 上传身份证
    Route::get('auth/auth_status', 'auth/authStatus');      // 认证状态
    
    // 个人中心
    Route::get('user/profile', 'user/profile');             // 个人信息
    Route::post('user/update', 'user/update');              // 更新信息
    Route::post('user/password', 'user/changePassword');    // 修改密码
    
    // 课程相关
    Route::get('course/list', 'course/index');              // 课程列表
    Route::get('course/detail', 'course/detail');           // 课程详情
    Route::get('course/category', 'course/categories');     // 课程分类
    
    // 学习相关
    Route::get('learn/progress', 'learn/progress');         // 学习进度
    Route::post('learn/record', 'learn/record');            // 记录学习行为
    Route::post('learn/heartbeat', 'learn/heartbeat');      // 心跳同步
    Route::get('learn/record', 'learn/records');            // 学习记录
    
    // 报告导出
    Route::get('report/personal', 'report/personal');       // 个人报告
    Route::post('report/export', 'report/export');          // 申请导出
    
})->allowCrossDomain();

// 企业管理后台路由组
Route::group('admin', function () {
    // 登录
    Route::post('login', 'admin/login');
    Route::post('logout', 'admin/logout');
    
    // 租户管理（仅超级管理员）
    Route::resource('tenant', 'tenant');
    
    // 组织架构
    Route::resource('department', 'department');
    Route::resource('employee', 'employee');
    Route::post('employee/import', 'employee/import');      // 批量导入
    Route::get('employee/export', 'employee/export');       // 导出员工
    
    // 课程管理
    Route::resource('course', 'course');
    Route::post('course/upload', 'course/uploadFile');      // 上传课件
    Route::post('course/publish', 'course/publish');        // 上架/下架
    
    // 学习任务
    Route::resource('task', 'task');
    Route::post('task/assign', 'task/assign');              // 下发任务
    
    // 进度监控
    Route::get('monitor/overview', 'monitor/overview');     // 整体概览
    Route::get('monitor/department', 'monitor/byDepartment'); // 按部门
    Route::get('monitor/uncompleted', 'monitor/uncompleted'); // 未完成人员
    
    // 报表导出
    Route::post('export/report', 'export/report');          // 学习报告
    Route::post('export/batch', 'export/batch');            // 批量导出
    Route::get('export/download/:id', 'export/download');   // 下载文件
    
    // 操作日志
    Route::get('log/operation', 'log/operation');           // 操作日志
    Route::get('log/learning', 'log/learning');             // 学习日志
    
})->allowCrossDomain();

// 系统超级管理员路由
Route::group('system', function () {
    Route::post('login', 'system/login');
    Route::get('dashboard', 'system/dashboard');            // 数据看板
    Route::resource('admin', 'admin');                      // 管理员管理
    Route::get('config', 'system/config');                  // 系统配置
    Route::post('config/save', 'system/saveConfig');        // 保存配置
    
})->allowCrossDomain();
