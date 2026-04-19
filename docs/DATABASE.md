# 数据库设计文档

## 概述

本系统采用 MySQL 8.0 数据库，字符集 `utf8mb4`，排序规则 `utf8mb4_unicode_ci`。

表前缀：`tp_`

---

## 数据表清单

| 序号 | 表名 | 说明 | 记录量预估 |
|------|------|------|-----------|
| 1 | tp_admin | 系统管理员表 | <100 |
| 2 | tp_tenant | SaaS 企业租户表 | <1000 |
| 3 | tp_user | 企业员工用户表 | <100 万 |
| 4 | tp_department | 部门表 | <1 万 |
| 5 | tp_course | 合规课程表 | <10 万 |
| 6 | tp_course_category | 课程分类表 | <1000 |
| 7 | tp_learning_task | 学习任务表 | <10 万 |
| 8 | tp_learning_record | 学习记录表 | <1000 万 |
| 9 | tp_learning_log | 学习行为日志表 | <1 亿 |
| 10 | tp_export_record | 导出记录表 | <10 万 |
| 11 | tp_operation_log | 操作日志表 | <1000 万 |
| 12 | tp_system_config | 系统配置表 | <100 |

---

## 表结构详解

### 1. tp_admin - 系统管理员表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| username | varchar | 50 | 是 | - | 用户名（唯一） |
| password | varchar | 255 | 是 | - | 密码（bcrypt 加密） |
| realname | varchar | 50 | 否 | NULL | 真实姓名 |
| mobile | varchar | 20 | 否 | NULL | 手机号 |
| role | tinyint | 1 | 否 | 1 | 角色：1 超级管理员 2 运营管理员 |
| status | tinyint | 1 | 否 | 1 | 状态：0 禁用 1 正常 |
| last_login_time | int | 11 | 否 | 0 | 最后登录时间 |
| last_login_ip | varchar | 50 | 否 | NULL | 最后登录 IP |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `username` (`username`)

---

### 2. tp_tenant - SaaS 企业租户表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_name | varchar | 100 | 是 | - | 企业名称 |
| tenant_code | varchar | 50 | 是 | - | 租户编码（唯一） |
| contact_name | varchar | 50 | 否 | NULL | 联系人姓名 |
| contact_mobile | varchar | 20 | 否 | NULL | 联系人手机 |
| package_type | tinyint | 1 | 否 | 1 | 套餐类型：1 基础版 2 标准版 3 企业版 |
| max_users | int | 11 | 否 | 100 | 学员上限 |
| max_courses | int | 11 | 否 | 50 | 课程数量上限 |
| max_export | int | 11 | 否 | 100 | 月度导出次数上限 |
| storage_limit | int | 11 | 否 | 5120 | 存储空间限制（MB） |
| expire_time | int | 11 | 否 | 0 | 到期时间 |
| status | tinyint | 1 | 否 | 1 | 状态：0 禁用 1 正常 2 到期 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `tenant_code` (`tenant_code`)

---

### 3. tp_user - 企业员工用户表（核心表）

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| username | varchar | 50 | 否 | NULL | 用户名（手机号） |
| password | varchar | 255 | 否 | NULL | 登录密码（bcrypt 加密） |
| realname | varchar | 50 | 是 | - | 真实姓名 |
| mobile | varchar | 20 | 是 | - | 手机号 |
| id_card | varchar | 100 | 否 | NULL | 身份证号（AES 加密存储） |
| id_card_front | varchar | 255 | 否 | NULL | 身份证正面照片路径 |
| id_card_back | varchar | 255 | 否 | NULL | 身份证反面照片路径 |
| auth_status | tinyint | 1 | 否 | 0 | 认证状态：0 待认证 1 已认证 2 审核中 3 驳回 |
| auth_remark | varchar | 255 | 否 | NULL | 认证驳回原因 |
| wx_openid | varchar | 100 | 否 | NULL | 微信 OpenID |
| wx_unionid | varchar | 100 | 否 | NULL | 微信 UnionID |
| dept_id | int | 11 | 否 | 0 | 部门 ID |
| position | varchar | 50 | 否 | NULL | 岗位 |
| employee_no | varchar | 50 | 否 | NULL | 企业工号 |
| status | tinyint | 1 | 否 | 1 | 状态：0 禁用 1 正常 |
| last_login_time | int | 11 | 否 | 0 | 最后登录时间 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)
- UNIQUE KEY `mobile_tenant` (`mobile`, `tenant_id`)
- KEY `id_card` (`id_card`) - 加密后的字段

**核心逻辑**:
- **三重绑定唯一性**: `realname` + `mobile` + `id_card` 组合确保一人一号
- **租户隔离**: 同手机号可在不同租户注册，但同一租户内手机号唯一
- **敏感数据加密**: `id_card` 字段使用 AES-128-ECB 加密存储

---

### 4. tp_department - 部门表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| parent_id | int | 11 | 否 | 0 | 父部门 ID（支持多级） |
| dept_name | varchar | 100 | 是 | - | 部门名称 |
| dept_leader | varchar | 50 | 否 | NULL | 部门负责人 |
| sort | int | 11 | 否 | 0 | 排序 |
| status | tinyint | 1 | 否 | 1 | 状态：0 禁用 1 正常 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)

---

### 5. tp_course - 合规课程表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| course_name | varchar | 200 | 是 | - | 课程名称 |
| course_type | tinyint | 1 | 否 | 1 | 课程类型：1 视频 2 文档 3 混合 |
| category_id | int | 11 | 否 | 0 | 分类 ID |
| category_name | varchar | 50 | 否 | NULL | 分类名称 |
| cover_image | varchar | 255 | 否 | NULL | 封面图片路径 |
| video_url | varchar | 500 | 否 | NULL | 视频文件路径/URL |
| video_duration | int | 11 | 否 | 0 | 视频时长（秒） |
| doc_url | varchar | 500 | 否 | NULL | 文档文件路径 |
| content | text | - | 否 | NULL | 课程介绍 |
| is_required | tinyint | 1 | 否 | 0 | 是否必修：0 选修 1 必修 |
| is_publish | tinyint | 1 | 否 | 0 | 是否上架：0 下架 1 上架 |
| view_count | int | 11 | 否 | 0 | 学习次数 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)
- KEY `category_id` (`category_id`)

---

### 6. tp_learning_record - 学习记录表（核心表）

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| user_id | int | 11 | 是 | - | 用户 ID |
| task_id | int | 11 | 否 | 0 | 学习任务 ID |
| course_id | int | 11 | 是 | - | 课程 ID |
| video_progress | int | 11 | 否 | 0 | 视频进度（秒） |
| video_duration | int | 11 | 否 | 0 | 视频总时长（秒） |
| doc_progress | tinyint | 1 | 否 | 0 | 文档阅读进度：0 未读 1 已读 |
| doc_read_time | int | 11 | 否 | 0 | 文档阅读时长（秒） |
| total_progress | decimal | 5,2 | 否 | 0 | 总进度百分比 |
| status | tinyint | 1 | 否 | 0 | 状态：0 未开始 1 学习中 2 已完成 3 逾期 |
| last_learn_time | int | 11 | 否 | 0 | 最后学习时间 |
| complete_time | int | 11 | 否 | 0 | 完成时间 |
| anti_cheat_log | text | - | 否 | NULL | 防作弊日志（JSON） |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)
- KEY `user_id` (`user_id`)
- KEY `course_id` (`course_id`)
- UNIQUE KEY `user_course` (`user_id`, `course_id`) - 每人每课唯一记录

**进度计算逻辑**:
- 纯视频课程：进度 = 已播放时长 / 总时长 × 100%
- 纯文档课程：进度 = 已读 ? 100% : 0%
- 混合课程：进度 = 视频进度 × 70% + 文档进度 × 30%

---

### 7. tp_learning_log - 学习行为日志表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| record_id | int | 11 | 是 | - | 学习记录 ID |
| user_id | int | 11 | 是 | - | 用户 ID |
| course_id | int | 11 | 是 | - | 课程 ID |
| action_type | varchar | 50 | 否 | NULL | 行为类型：play/pause/seek/exit/heartbeat |
| action_data | text | - | 否 | NULL | 行为数据（JSON） |
| client_ip | varchar | 50 | 否 | NULL | 客户端 IP |
| user_agent | varchar | 255 | 否 | NULL | 用户设备信息 |
| create_time | int | 11 | 否 | 0 | 创建时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `record_id` (`record_id`)
- KEY `user_id` (`user_id`)

**行为类型说明**:
- `play`: 开始播放
- `pause`: 暂停播放
- `seek`: 拖拽进度
- `exit`: 离开页面
- `heartbeat`: 心跳同步
- `doc_read`: 文档阅读

---

### 8. tp_learning_task - 学习任务表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| task_name | varchar | 200 | 是 | - | 任务名称 |
| course_ids | text | - | 否 | NULL | 课程 ID 集合（JSON） |
| target_type | tinyint | 1 | 否 | 1 | 目标类型：1 全员 2 指定部门 3 指定岗位 4 指定人员 |
| target_ids | text | - | 否 | NULL | 目标 ID 集合（JSON） |
| deadline | int | 11 | 否 | 0 | 截止时间 |
| is_required | tinyint | 1 | 否 | 1 | 是否必修 |
| status | tinyint | 1 | 否 | 1 | 状态：0 停止 1 进行中 2 已结束 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)

---

### 9. tp_export_record - 导出记录表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 是 | - | 所属租户 ID |
| admin_id | int | 11 | 是 | - | 操作管理员 ID |
| export_type | varchar | 50 | 否 | NULL | 导出类型：single/batch/department/all |
| export_format | varchar | 20 | 否 | NULL | 导出格式：excel/pdf/word/zip |
| filter_condition | text | - | 否 | NULL | 筛选条件（JSON） |
| file_path | varchar | 255 | 否 | NULL | 文件路径 |
| file_size | int | 11 | 否 | 0 | 文件大小（字节） |
| download_count | int | 11 | 否 | 0 | 下载次数 |
| expire_time | int | 11 | 否 | 0 | 过期时间 |
| status | tinyint | 1 | 否 | 1 | 状态：0 失效 1 有效 |
| create_time | int | 11 | 否 | 0 | 创建时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)
- KEY `admin_id` (`admin_id`)

---

### 10. tp_operation_log - 操作日志表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| tenant_id | int | 11 | 否 | 0 | 所属租户 ID（0 为系统日志） |
| user_id | int | 11 | 否 | 0 | 操作用户 ID |
| user_type | tinyint | 1 | 否 | 1 | 用户类型：1 系统管理员 2 企业管理员 3 普通员工 |
| action | varchar | 100 | 否 | NULL | 操作行为 |
| module | varchar | 50 | 否 | NULL | 操作模块 |
| request_data | text | - | 否 | NULL | 请求数据（JSON） |
| response_code | int | 11 | 否 | 200 | 响应状态码 |
| client_ip | varchar | 50 | 否 | NULL | 客户端 IP |
| user_agent | varchar | 255 | 否 | NULL | 用户设备 |
| create_time | int | 11 | 否 | 0 | 创建时间 |

**索引**:
- PRIMARY KEY (`id`)
- KEY `tenant_id` (`tenant_id`)
- KEY `user_id` (`user_id`)
- KEY `create_time` (`create_time`) - 用于按时间查询

---

### 11. tp_system_config - 系统配置表

| 字段 | 类型 | 长度 | 必填 | 默认 | 说明 |
|------|------|------|------|------|------|
| id | int | 11 | 是 | AI | 主键 |
| config_key | varchar | 100 | 是 | - | 配置键（唯一） |
| config_value | text | - | 否 | NULL | 配置值 |
| config_type | varchar | 50 | 否 | string | 配置类型：string/json/encrypted |
| config_group | varchar | 50 | 否 | base | 配置分组：base/sms/wx/storage/export |
| remark | varchar | 255 | 否 | NULL | 备注 |
| create_time | int | 11 | 否 | 0 | 创建时间 |
| update_time | int | 11 | 否 | 0 | 更新时间 |

**索引**:
- PRIMARY KEY (`id`)
- UNIQUE KEY `config_key` (`config_key`)

**默认配置项**:

| config_key | config_value | config_group | 说明 |
|------------|--------------|--------------|------|
| sms_driver | aliyun | sms | 短信服务商 |
| sms_sign | 合规培训 | sms | 短信签名 |
| wx_appid | - | wx | 微信小程序 AppID |
| wx_appsecret | - | wx | 微信小程序 AppSecret |
| storage_driver | local | storage | 存储驱动 |
| storage_path | /uploads/ | storage | 存储路径 |
| encrypt_key | - | base | 加密密钥（加密存储） |

---

## ER 关系图

```
tp_tenant (1) ──< (N) tp_user
tp_tenant (1) ──< (N) tp_department
tp_tenant (1) ──< (N) tp_course
tp_tenant (1) ──< (N) tp_learning_task
tp_tenant (1) ──< (N) tp_learning_record
tp_tenant (1) ──< (N) tp_export_record

tp_department (1) ──< (N) tp_user

tp_course (1) ──< (N) tp_learning_record
tp_course (1) ──< (N) tp_learning_log

tp_user (1) ──< (N) tp_learning_record
tp_user (1) ──< (N) tp_learning_log

tp_learning_record (1) ──< (N) tp_learning_log
```

---

## 性能优化建议

### 1. 索引优化

- 为高频查询字段添加索引
- 避免在 `WHERE` 子句中对索引列使用函数
- 定期使用 `EXPLAIN` 分析查询计划

### 2. 分表策略

当 `tp_learning_record` 超过 1000 万记录时，考虑：
- 按 `tenant_id` 水平分表
- 按 `create_time` 月份分区

### 3. 归档策略

- `tp_learning_log` 保留 6 个月数据
- `tp_operation_log` 保留 1 年数据
- 定期归档历史数据到冷存储

### 4. 缓存策略

- 用户信息缓存（Redis，TTL 1 小时）
- 课程列表缓存（Redis，TTL 10 分钟）
- 配置信息缓存（Redis，TTL 永久，更新时失效）

---

## 数据安全

### 1. 敏感字段加密

- `tp_user.id_card`: AES-128-ECB 加密
- `tp_user.password`: bcrypt 加密
- `tp_system_config` 中 `encrypt_key`: 使用环境变量存储

### 2. 数据备份

- 每日全量备份（凌晨 2 点）
- 每小时增量备份
- 备份保留 30 天

### 3. 权限控制

- 数据库账号最小权限原则
- 禁止远程 root 登录
- 应用层使用独立账号

---

**版本**: 1.0  
**更新时间**: 2026-04-19
