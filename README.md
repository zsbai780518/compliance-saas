# 企业合规培训 SaaS 系统
> Enterprise Compliance Training SaaS Platform

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://php.net)
[![ThinkPHP](https://img.shields.io/badge/ThinkPHP-6.0+-blue.svg)](https://thinkphp.cn)
[![UniApp](https://img.shields.io/badge/UniApp-3.0+-green.svg)](https://uniapp.dcloud.net.cn)

## 📖 项目简介

企业合规培训 SaaS 系统是一套面向各行业中小企业/集团的多租户培训平台，提供合规课程线上学习、人员实名认证、进度管控、合规档案、批量报表导出等一体化服务。

### ✨ 核心特性

- 🔐 **三重实名认证** - 姓名 + 手机号 + 身份证唯一绑定，防止虚假账号
- 🏢 **多租户隔离** - 企业数据完全独立，支持 SaaS 化运营
- 📊 **学习进度管控** - 实时追踪、防作弊、断点续播
- 📑 **批量报表导出** - Excel/PDF/Word 多种格式，支持批量打包
- 📱 **多端适配** - H5 + 微信小程序 + APP，一套代码多端运行
- 🔒 **隐私合规** - 敏感数据 AES 加密存储，符合数据安全要求

## 🛠️ 技术栈

| 层级 | 技术 | 版本 |
|------|------|------|
| 前端 | UniApp | 3.x |
| 后端 | ThinkPHP | 6.x |
| 数据库 | MySQL | 8.0+ |
| Web 服务器 | Nginx | 1.20+ |
| PHP | PHP | 8.0+ |

## 📦 快速开始

### 环境要求

- PHP >= 8.0
- MySQL >= 8.0
- Nginx >= 1.20
- Composer

### 安装步骤

**1. 克隆项目**
```bash
git clone https://github.com/your-username/compliance-saas.git
cd compliance-saas
```

**2. 安装后端依赖**
```bash
cd backend
composer install --no-dev --optimize-autoloader
```

**3. 创建数据库**
```bash
mysql -u root -p
CREATE DATABASE `compliance_saas` DEFAULT CHARACTER SET utf8mb4;
EXIT;
```

**4. 导入数据表**
```bash
mysql -u root -p compliance_saas < database/schema.sql
```

**5. 配置数据库**

编辑 `backend/config/database.php`:
```php
'database' => 'compliance_saas',
'username' => 'your_username',
'password' => 'your_password',
```

**6. 设置权限**
```bash
chmod -R 777 backend/runtime
chmod -R 777 backend/public/uploads
```

**7. 启动服务**

配置 Nginx 后访问 `http://your-domain.com`

默认管理员账号：`admin` / `admin123`

## 📁 项目结构

```
compliance-saas/
├── backend/                 # 后端代码（ThinkPHP6）
│   ├── app/
│   │   ├── controller/     # 控制器
│   │   ├── model/          # 模型
│   │   ├── middleware/     # 中间件
│   │   └── validate/       # 验证器
│   ├── config/             # 配置文件
│   ├── route/              # 路由配置
│   ├── public/             # 静态资源
│   └── runtime/            # 运行时目录
├── frontend/               # 前端代码（UniApp）
│   ├── pages/              # 页面
│   ├── components/         # 组件
│   ├── static/             # 静态资源
│   └── utils/              # 工具类
├── database/               # 数据库相关
│   └── schema.sql          # 数据表结构
├── docs/                   # 文档
│   ├── API.md              # 接口文档
│   ├── DATABASE.md         # 数据库设计文档
│   └── MANUAL.md           # 操作手册
├── deploy/                 # 部署相关
│   └── INSTALL.md          # 部署文档
└── README.md               # 项目说明
```

## 📚 文档

| 文档 | 说明 |
|------|------|
| [部署文档](deploy/INSTALL.md) | LNMP 环境搭建、项目部署、SSL 配置 |
| [API 文档](docs/API.md) | 完整接口说明、请求参数、响应格式 |
| [数据库文档](docs/DATABASE.md) | 表结构设计、字段说明、索引优化 |
| [操作手册](docs/MANUAL.md) | 超级管理员/企业管理员/员工使用教程 |

## 🎯 核心功能

### 认证模块
- 用户注册（三重绑定）
- 账号密码登录
- 短信验证码登录
- 微信快捷登录
- 实名认证（身份证上传）

### 租户管理
- 企业租户开通
- 套餐配置
- 到期管控
- 数据隔离

### 企业管理
- 组织架构管理
- 员工批量导入
- 课程上传管理
- 学习任务下发

### 学习端
- 视频学习（防拖拽）
- 文档阅读
- 进度追踪
- 断点续播

### 报表导出
- 个人报告（Excel/PDF/Word）
- 批量导出
- ZIP 打包下载

## 🔒 安全特性

- **数据加密**: 身份证号 AES-128-ECB 加密，密码 bcrypt 加密
- **防作弊机制**: 视频拖拽限制、频繁切页检测、学习行为日志
- **多租户隔离**: 中间件自动注入租户 ID，数据完全隔离
- **操作日志**: 全程留痕，可追溯

## 📄 开源协议

本项目采用 [MIT](LICENSE) 协议开源。

## 🤝 贡献指南

欢迎提交 Issue 和 Pull Request！

## 📧 联系方式

- 邮箱：support@your-company.com
- 问题反馈：[GitHub Issues](https://github.com/your-username/compliance-saas/issues)

---

**版本**: 1.0.0  
**创建时间**: 2026-04-19  
**最后更新**: 2026-04-19
