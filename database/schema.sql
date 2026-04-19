-- ============================================
-- 企业合规培训 SaaS 系统 - 数据库设计
-- 版本：1.0
-- 创建时间：2026-04-19
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------
-- 1. 系统超级管理员表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_admin`;
CREATE TABLE `tp_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（加密）',
  `realname` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号',
  `role` tinyint(1) DEFAULT 1 COMMENT '角色：1 超级管理员 2 运营管理员',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 禁用 1 正常',
  `last_login_time` int(11) DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录 IP',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统管理员表';

-- --------------------------------------------
-- 2. SaaS 企业租户表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_tenant`;
CREATE TABLE `tp_tenant` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_name` varchar(100) NOT NULL COMMENT '企业名称',
  `tenant_code` varchar(50) NOT NULL COMMENT '租户编码（唯一）',
  `contact_name` varchar(50) DEFAULT NULL COMMENT '联系人姓名',
  `contact_mobile` varchar(20) DEFAULT NULL COMMENT '联系人手机',
  `package_type` tinyint(1) DEFAULT 1 COMMENT '套餐类型：1 基础版 2 标准版 3 企业版',
  `max_users` int(11) DEFAULT 100 COMMENT '学员上限',
  `max_courses` int(11) DEFAULT 50 COMMENT '课程数量上限',
  `max_export` int(11) DEFAULT 100 COMMENT '月度导出次数上限',
  `storage_limit` int(11) DEFAULT 5120 COMMENT '存储空间限制（MB）',
  `expire_time` int(11) DEFAULT 0 COMMENT '到期时间',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 禁用 1 正常 2 到期',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_code` (`tenant_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SaaS 企业租户表';

-- --------------------------------------------
-- 3. 企业员工用户表（核心实名认证）
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名（手机号）',
  `password` varchar(255) DEFAULT NULL COMMENT '登录密码（加密）',
  `realname` varchar(50) NOT NULL COMMENT '真实姓名',
  `mobile` varchar(20) NOT NULL COMMENT '手机号',
  `id_card` varchar(100) DEFAULT NULL COMMENT '身份证号（AES 加密）',
  `id_card_front` varchar(255) DEFAULT NULL COMMENT '身份证正面照片路径',
  `id_card_back` varchar(255) DEFAULT NULL COMMENT '身份证反面照片路径',
  `auth_status` tinyint(1) DEFAULT 0 COMMENT '认证状态：0 待认证 1 已认证 2 审核中 3 驳回',
  `auth_remark` varchar(255) DEFAULT NULL COMMENT '认证驳回原因',
  `wx_openid` varchar(100) DEFAULT NULL COMMENT '微信 OpenID',
  `wx_unionid` varchar(100) DEFAULT NULL COMMENT '微信 UnionID',
  `dept_id` int(11) DEFAULT 0 COMMENT '部门 ID',
  `position` varchar(50) DEFAULT NULL COMMENT '岗位',
  `employee_no` varchar(50) DEFAULT NULL COMMENT '企业工号',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 禁用 1 正常',
  `last_login_time` int(11) DEFAULT 0 COMMENT '最后登录时间',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  UNIQUE KEY `mobile_tenant` (`mobile`, `tenant_id`),
  KEY `id_card` (`id_card`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='企业员工用户表';

-- --------------------------------------------
-- 4. 部门表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_department`;
CREATE TABLE `tp_department` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `parent_id` int(11) DEFAULT 0 COMMENT '父部门 ID',
  `dept_name` varchar(100) NOT NULL COMMENT '部门名称',
  `dept_leader` varchar(50) DEFAULT NULL COMMENT '部门负责人',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 禁用 1 正常',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门表';

-- --------------------------------------------
-- 5. 合规课程表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_course`;
CREATE TABLE `tp_course` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `course_name` varchar(200) NOT NULL COMMENT '课程名称',
  `course_type` tinyint(1) DEFAULT 1 COMMENT '课程类型：1 视频 2 文档 3 混合',
  `category_id` int(11) DEFAULT 0 COMMENT '分类 ID',
  `category_name` varchar(50) DEFAULT NULL COMMENT '分类名称（安全生产/法务合规/财税合规等）',
  `cover_image` varchar(255) DEFAULT NULL COMMENT '封面图片路径',
  `video_url` varchar(500) DEFAULT NULL COMMENT '视频文件路径/URL',
  `video_duration` int(11) DEFAULT 0 COMMENT '视频时长（秒）',
  `doc_url` varchar(500) DEFAULT NULL COMMENT '文档文件路径',
  `content` text COMMENT '课程介绍',
  `is_required` tinyint(1) DEFAULT 0 COMMENT '是否必修：0 选修 1 必修',
  `is_publish` tinyint(1) DEFAULT 0 COMMENT '是否上架：0 下架 1 上架',
  `view_count` int(11) DEFAULT 0 COMMENT '学习次数',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合规课程表';

-- --------------------------------------------
-- 6. 课程分类表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_course_category`;
CREATE TABLE `tp_course_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `category_name` varchar(50) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) DEFAULT 0 COMMENT '父分类 ID',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 禁用 1 正常',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='课程分类表';

-- --------------------------------------------
-- 7. 学习任务表（企业下发给员工）
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_learning_task`;
CREATE TABLE `tp_learning_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `task_name` varchar(200) NOT NULL COMMENT '任务名称',
  `course_ids` text COMMENT '课程 ID 集合（JSON）',
  `target_type` tinyint(1) DEFAULT 1 COMMENT '目标类型：1 全员 2 指定部门 3 指定岗位 4 指定人员',
  `target_ids` text COMMENT '目标 ID 集合（JSON）',
  `deadline` int(11) DEFAULT 0 COMMENT '截止时间',
  `is_required` tinyint(1) DEFAULT 1 COMMENT '是否必修',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 停止 1 进行中 2 已结束',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习任务表';

-- --------------------------------------------
-- 8. 用户学习记录表（核心进度追踪）
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_learning_record`;
CREATE TABLE `tp_learning_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户 ID',
  `task_id` int(11) DEFAULT 0 COMMENT '学习任务 ID',
  `course_id` int(11) unsigned NOT NULL COMMENT '课程 ID',
  `video_progress` int(11) DEFAULT 0 COMMENT '视频进度（秒）',
  `video_duration` int(11) DEFAULT 0 COMMENT '视频总时长（秒）',
  `doc_progress` tinyint(1) DEFAULT 0 COMMENT '文档阅读进度：0 未读 1 已读',
  `doc_read_time` int(11) DEFAULT 0 COMMENT '文档阅读时长（秒）',
  `total_progress` decimal(5,2) DEFAULT 0 COMMENT '总进度百分比',
  `status` tinyint(1) DEFAULT 0 COMMENT '状态：0 未开始 1 学习中 2 已完成 3 逾期',
  `last_learn_time` int(11) DEFAULT 0 COMMENT '最后学习时间',
  `complete_time` int(11) DEFAULT 0 COMMENT '完成时间',
  `anti_cheat_log` text COMMENT '防作弊日志（JSON）',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  UNIQUE KEY `user_course` (`user_id`, `course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户学习记录表';

-- --------------------------------------------
-- 9. 学习行为日志表（防作弊）
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_learning_log`;
CREATE TABLE `tp_learning_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_id` int(11) unsigned NOT NULL COMMENT '学习记录 ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户 ID',
  `course_id` int(11) unsigned NOT NULL COMMENT '课程 ID',
  `action_type` varchar(50) DEFAULT NULL COMMENT '行为类型：play/pause/seek/exit/heartbeat',
  `action_data` text COMMENT '行为数据（JSON）',
  `client_ip` varchar(50) DEFAULT NULL COMMENT '客户端 IP',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户设备信息',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习行为日志表';

-- --------------------------------------------
-- 10. 导出记录表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_export_record`;
CREATE TABLE `tp_export_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned NOT NULL COMMENT '所属租户 ID',
  `admin_id` int(11) unsigned NOT NULL COMMENT '操作管理员 ID',
  `export_type` varchar(50) DEFAULT NULL COMMENT '导出类型：single/batch/department/all',
  `export_format` varchar(20) DEFAULT NULL COMMENT '导出格式：excel/pdf/word/zip',
  `filter_condition` text COMMENT '筛选条件（JSON）',
  `file_path` varchar(255) DEFAULT NULL COMMENT '文件路径',
  `file_size` int(11) DEFAULT 0 COMMENT '文件大小（字节）',
  `download_count` int(11) DEFAULT 0 COMMENT '下载次数',
  `expire_time` int(11) DEFAULT 0 COMMENT '过期时间',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0 失效 1 有效',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='导出记录表';

-- --------------------------------------------
-- 11. 操作日志表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_operation_log`;
CREATE TABLE `tp_operation_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) unsigned DEFAULT 0 COMMENT '所属租户 ID（0 为系统日志）',
  `user_id` int(11) unsigned DEFAULT 0 COMMENT '操作用户 ID',
  `user_type` tinyint(1) DEFAULT 1 COMMENT '用户类型：1 系统管理员 2 企业管理员 3 普通员工',
  `action` varchar(100) DEFAULT NULL COMMENT '操作行为',
  `module` varchar(50) DEFAULT NULL COMMENT '操作模块',
  `request_data` text COMMENT '请求数据（JSON）',
  `response_code` int(11) DEFAULT 200 COMMENT '响应状态码',
  `client_ip` varchar(50) DEFAULT NULL COMMENT '客户端 IP',
  `user_agent` varchar(255) DEFAULT NULL COMMENT '用户设备',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';

-- --------------------------------------------
-- 12. 系统配置表
-- --------------------------------------------
DROP TABLE IF EXISTS `tp_system_config`;
CREATE TABLE `tp_system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` text COMMENT '配置值',
  `config_type` varchar(50) DEFAULT 'string' COMMENT '配置类型：string/json/encrypted',
  `config_group` varchar(50) DEFAULT 'base' COMMENT '配置分组：base/sms/wx/storage/export',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- --------------------------------------------
-- 初始化数据
-- --------------------------------------------

-- 默认超级管理员（密码：admin123）
INSERT INTO `tp_admin` (`username`, `password`, `realname`, `role`, `status`, `create_time`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系统管理员', 1, 1, UNIX_TIMESTAMP());

-- 默认系统配置
INSERT INTO `tp_system_config` (`config_key`, `config_value`, `config_type`, `config_group`, `create_time`) VALUES
('sms_driver', 'aliyun', 'string', 'sms', UNIX_TIMESTAMP()),
('sms_sign', '合规培训', 'string', 'sms', UNIX_TIMESTAMP()),
('wx_appid', '', 'string', 'wx', UNIX_TIMESTAMP()),
('wx_appsecret', '', 'string', 'wx', UNIX_TIMESTAMP()),
('storage_driver', 'local', 'string', 'storage', UNIX_TIMESTAMP()),
('storage_path', '/uploads/', 'string', 'storage', UNIX_TIMESTAMP()),
('encrypt_key', 'ComplianceSaaS2026Key', 'encrypted', 'base', UNIX_TIMESTAMP());

SET FOREIGN_KEY_CHECKS = 1;
