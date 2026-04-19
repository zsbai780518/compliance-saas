# 贡献指南

感谢你对企业合规培训 SaaS 系统的关注！欢迎参与项目开发。

## 🤝 如何贡献

### 1. Fork 项目

在 GitHub 上点击 Fork 按钮创建你的副本。

### 2. 克隆项目

```bash
git clone https://github.com/zsbai780518/compliance-saas.git
cd compliance-saas
```

### 3. 创建分支

```bash
git checkout -b feature/your-feature-name
```

分支命名规范：
- `feature/xxx` - 新功能
- `bugfix/xxx` - Bug 修复
- `docs/xxx` - 文档更新
- `refactor/xxx` - 代码重构

### 4. 提交代码

```bash
git add .
git commit -m "feat: 添加新功能

详细描述..."
git push origin feature/your-feature-name
```

### 5. 创建 Pull Request

在 GitHub 上提交 Pull Request，说明你的改动。

## 📋 代码规范

### 提交信息格式

遵循 [Conventional Commits](https://www.conventionalcommits.org/) 规范：

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Type 类型：**
- `feat`: 新功能
- `fix`: Bug 修复
- `docs`: 文档更新
- `style`: 代码格式（不影响功能）
- `refactor`: 重构
- `test`: 测试相关
- `chore`: 构建/工具配置

**示例：**
```
feat(auth): 添加短信验证码登录功能

- 实现短信发送接口
- 添加验证码校验逻辑
- 更新登录页面 UI

Closes #123
```

### PHP 代码规范

- 遵循 PSR-12 编码规范
- 使用 4 个空格缩进
- 类名使用 PascalCase
- 方法名使用 camelCase
- 添加必要的注释

### 前端代码规范

- Vue 组件使用 PascalCase
- 方法名使用 camelCase
- 添加必要的注释
- 避免过长的组件（建议 < 300 行）

## 🐛 报告 Bug

### 创建 Issue 时请提供：

1. **问题描述** - 清晰描述遇到的问题
2. **复现步骤** - 如何重现这个问题
3. **期望行为** - 你认为正确的行为应该是什么
4. **实际行为** - 实际发生了什么
5. **环境信息**：
   - PHP 版本
   - MySQL 版本
   - 操作系统
   - 浏览器（如果是前端问题）

## 💡 功能建议

欢迎提出新功能建议！请创建 Issue 并说明：

- 功能描述
- 使用场景
- 期望效果
- 是否有类似实现

## 📝 文档贡献

文档同样重要！如果你发现：

- 文档错误
- 缺失的说明
- 可以改进的地方

欢迎提交 PR 帮助我们改进。

## 🔍 Code Review

所有 PR 都需要经过 Code Review：

- 代码是否符合规范
- 功能是否按预期工作
- 是否有充分的测试
- 是否有性能问题

## 📧 联系方式

如有问题，请通过以下方式联系：

- GitHub Issues: https://github.com/zsbai780518/compliance-saas/issues
- Email: support@your-company.com

---

再次感谢你的贡献！🎉
