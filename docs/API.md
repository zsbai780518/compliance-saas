# API 接口文档

## 基础信息

- **Base URL**: `https://your-domain.com/api`
- **数据格式**: JSON
- **字符编码**: UTF-8

## 认证方式

大部分接口需要在 Header 中携带 Token：

```
Authorization: your_token_here
```

## 响应格式

```json
{
  "code": 200,
  "msg": "success",
  "data": {}
}
```

### 状态码说明

| 状态码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 未登录/Token 过期 |
| 403 | 无权限 |
| 500 | 服务器错误 |

---

## 一、认证模块

### 1.1 用户注册

**POST** `/auth/register`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| tenant_id | int | 是 | 企业租户 ID |
| realname | string | 是 | 真实姓名 |
| mobile | string | 是 | 手机号 |
| id_card | string | 是 | 身份证号 |
| sms_code | string | 是 | 短信验证码 |
| password | string | 是 | 登录密码（6 位以上） |

**响应示例**:

```json
{
  "code": 200,
  "msg": "注册成功",
  "data": {
    "user_id": 1001,
    "mobile": "13800138000",
    "realname": "张三"
  }
}
```

### 1.2 账号登录

**POST** `/auth/login`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| mobile | string | 是 | 手机号 |
| password | string | 是 | 密码 |

**响应示例**:

```json
{
  "code": 200,
  "msg": "登录成功",
  "data": {
    "token": "abc123...",
    "user": {
      "id": 1001,
      "mobile": "13800138000",
      "realname": "张三",
      "auth_status": 1,
      "auth_status_text": "已认证",
      "tenant_id": 1
    }
  }
}
```

### 1.3 发送短信验证码

**POST** `/auth/sms_code`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| mobile | string | 是 | 手机号 |

**响应示例**:

```json
{
  "code": 200,
  "msg": "验证码已发送",
  "data": {
    "expire": 300
  }
}
```

### 1.4 上传身份证

**POST** `/auth/idcard_upload`

**Content-Type**: `multipart/form-data`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| id_card_front | file | 是 | 身份证正面照片 |
| id_card_back | file | 是 | 身份证反面照片 |

**响应示例**:

```json
{
  "code": 200,
  "msg": "身份证已上传，等待审核"
}
```

### 1.5 查询认证状态

**GET** `/auth/auth_status`

**响应示例**:

```json
{
  "code": 200,
  "data": {
    "auth_status": 1,
    "auth_status_text": "已认证",
    "auth_remark": "",
    "is_certified": true
  }
}
```

### 1.6 退出登录

**POST** `/auth/logout`

---

## 二、用户模块

### 2.1 获取个人信息

**GET** `/user/profile`

### 2.2 更新个人信息

**POST** `/user/update`

### 2.3 修改密码

**POST** `/user/password`

---

## 三、课程模块

### 3.1 课程列表

**GET** `/course/list`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码，默认 1 |
| limit | int | 否 | 每页数量，默认 20 |
| category_id | int | 否 | 分类 ID |
| keyword | string | 否 | 搜索关键词 |

**响应示例**:

```json
{
  "code": 200,
  "data": {
    "total": 50,
    "list": [
      {
        "course_id": 1,
        "course_name": "安全生产培训",
        "course_type": 1,
        "course_type_text": "视频课程",
        "category_name": "安全生产",
        "cover_image": "/uploads/course/cover.jpg",
        "video_duration": 1800,
        "view_count": 100,
        "is_required": 1
      }
    ]
  }
}
```

### 3.2 课程详情

**GET** `/course/detail?course_id=1`

---

## 四、学习模块

### 4.1 获取学习进度

**GET** `/learn/progress`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| course_id | int | 否 | 课程 ID，不传返回全部 |

**响应示例**:

```json
{
  "code": 200,
  "data": {
    "summary": {
      "total": 10,
      "completed": 5,
      "learning": 3,
      "not_started": 2
    },
    "list": [
      {
        "course_id": 1,
        "course_name": "安全生产培训",
        "status": 1,
        "status_text": "学习中",
        "total_progress": 65.5,
        "last_learn_time": 1713513600
      }
    ]
  }
}
```

### 4.2 记录学习行为

**POST** `/learn/record`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| course_id | int | 是 | 课程 ID |
| action_type | string | 是 | 行为类型：play/pause/seek/exit/heartbeat/doc_read |
| progress | int | 否 | 当前进度（秒） |
| duration | int | 否 | 总时长（秒） |
| read_time | int | 否 | 阅读时长（秒） |

**响应示例**:

```json
{
  "code": 200,
  "msg": "同步成功",
  "data": {
    "progress": 65.5,
    "status": 1
  }
}
```

### 4.3 心跳同步

**POST** `/learn/heartbeat`

**请求参数**:

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| course_id | int | 是 | 课程 ID |
| progress | int | 是 | 当前进度（秒） |
| duration | int | 是 | 总时长（秒） |

### 4.4 学习记录列表

**GET** `/learn/records`

---

## 五、报告模块

### 5.1 个人报告导出

**GET** `/report/personal?format=excel`

**参数**:
- format: `excel` / `pdf` / `word`

直接返回文件下载。

### 5.2 批量导出申请

**POST** `/report/export`

---

## 六、企业管理后台

### 6.1 管理员登录

**POST** `/admin/login`

### 6.2 员工管理

- `GET /admin/employee/list` - 员工列表
- `POST /admin/employee/import` - 批量导入
- `GET /admin/employee/export` - 导出员工

### 6.3 课程管理

- `GET /admin/course/list` - 课程列表
- `POST /admin/course` - 创建课程
- `PUT /admin/course/:id` - 更新课程
- `POST /admin/course/upload` - 上传课件
- `POST /admin/course/publish` - 上架/下架

### 6.4 学习任务

- `GET /admin/task/list` - 任务列表
- `POST /admin/task` - 创建任务
- `POST /admin/task/assign` - 下发任务

### 6.5 进度监控

- `GET /admin/monitor/overview` - 整体概览
- `GET /admin/monitor/department` - 按部门统计
- `GET /admin/monitor/uncompleted` - 未完成人员

### 6.6 报表导出

- `POST /admin/export/report` - 学习报告
- `POST /admin/export/batch` - 批量导出
- `GET /admin/export/download/:id` - 下载文件

---

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 1001 | 参数错误 |
| 1002 | Token 无效 |
| 1003 | Token 已过期 |
| 2001 | 用户不存在 |
| 2002 | 密码错误 |
| 2003 | 账号已禁用 |
| 3001 | 课程不存在 |
| 4001 | 租户不存在 |
| 4002 | 租户已到期 |
| 5001 | 文件上传失败 |
| 5002 | 导出失败 |

---

## 防作弊说明

系统记录以下异常行为：

1. **大跨度拖拽**: 单次拖拽超过 30 秒
2. **频繁切页**: 5 分钟内切页超过 10 次
3. **倍速播放**: 检测到非正常播放速度
4. **后台播放**: 页面隐藏时继续记录进度

异常行为会记录在 `anti_cheat_log` 字段，管理员可查看。
