# LNMP 环境部署文档

## 一、服务器要求

- **操作系统**: CentOS 7.9 / Ubuntu 20.04+
- **CPU**: 2 核及以上
- **内存**: 4GB 及以上
- **磁盘**: 50GB 及以上
- **带宽**: 5Mbps 及以上（根据并发调整）

## 二、环境安装

### 2.1 安装 Nginx

```bash
# CentOS
yum install -y nginx
systemctl enable nginx
systemctl start nginx

# Ubuntu
apt update
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

### 2.2 安装 MySQL 8.0

```bash
# CentOS
yum install -y mysql-server
systemctl enable mysqld
systemctl start mysqld

# Ubuntu
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

# 初始化 MySQL
mysql_secure_installation
```

### 2.3 安装 PHP 8.0+

```bash
# CentOS (使用 Remi 源)
yum install -y yum-utils
yum-config-manager --enable remi-php80
yum install -y php php-fpm php-mysql php-gd php-zip php-mbstring php-xml php-curl php-bcmath php-openssl

# Ubuntu
apt install -y php8.0 php8.0-fpm php8.0-mysql php8.0-gd php8.0-zip php8.0-mbstring php8.0-xml php8.0-curl php8.0-bcmath php8.0-openssl
```

### 2.4 安装 PHP 扩展（报表导出必需）

```bash
# 安装 Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# 安装 PHP 扩展
pecl install zip
echo "extension=zip.so" >> /etc/php.ini

# 安装 ImageMagick（图片处理）
yum install -y ImageMagick ImageMagick-devel
pecl install imagick
```

## 三、项目部署

### 3.1 创建数据库

```bash
mysql -u root -p
```

```sql
CREATE DATABASE `compliance_saas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'compliance'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
GRANT ALL PRIVILEGES ON compliance_saas.* TO 'compliance'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3.2 导入数据表结构

```bash
mysql -u compliance -p compliance_saas < /path/to/compliance-saas/database/schema.sql
```

### 3.3 部署代码

```bash
# 创建部署目录
mkdir -p /var/www/compliance-saas
cd /var/www/compliance-saas

# 上传代码（或使用 git clone）
# 将 backend 目录内容复制到 /var/www/compliance-saas

# 安装 PHP 依赖
cd /var/www/compliance-saas
composer install --no-dev --optimize-autoloader

# 设置权限
chown -R www:www /var/www/compliance-saas
chmod -R 755 /var/www/compliance-saas
chmod -R 777 /var/www/compliance-saas/runtime
chmod -R 777 /var/www/compliance-saas/public/uploads
```

### 3.4 配置 Nginx

创建 `/etc/nginx/conf.d/compliance-saas.conf`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/compliance-saas/public;
    index index.html index.htm index.php;

    # 访问日志
    access_log /var/log/nginx/compliance-saas_access.log;
    error_log /var/log/nginx/compliance-saas_error.log;

    # 静态资源
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # PHP 处理
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
    }

    # ThinkPHP 路由重写
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # 禁止访问敏感文件
    location ~ /\.(git|env|sql)$ {
        deny all;
    }

    # 禁止访问 uploads 目录中的 PHP 文件
    location ~* /uploads/.*\.(php|php5|sh|pl|py)$ {
        deny all;
    }
}
```

### 3.5 配置 PHP-FPM

编辑 `/etc/php/8.0/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
php_admin_value[memory_limit] = 256M
```

### 3.6 启动服务

```bash
# 重启 PHP-FPM
systemctl restart php8.0-fpm

# 测试 Nginx 配置
nginx -t

# 重启 Nginx
systemctl restart nginx
```

## 四、SSL 证书配置（推荐）

### 4.1 使用 Let's Encrypt

```bash
# 安装 Certbot
yum install -y certbot python3-certbot-nginx  # CentOS
apt install -y certbot python3-certbot-nginx  # Ubuntu

# 申请证书
certbot --nginx -d your-domain.com

# 自动续期
certbot renew --dry-run
```

### 4.2 配置 HTTPS

Certbot 会自动更新 Nginx 配置。如需手动配置：

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # ... 其他配置同上
}

# HTTP 强制跳转 HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## 五、系统配置

### 5.1 修改后端配置

编辑 `backend/config/database.php`:

```php
'hostname' => '127.0.0.1',
'database' => 'compliance_saas',
'username' => 'compliance',
'password' => 'YourStrongPassword123!',
```

### 5.2 配置系统参数

登录系统后台，进入「系统配置」，设置：

- 短信服务（阿里云/腾讯云）
- 微信 AppID 和 AppSecret
- 文件存储路径
- 加密密钥

### 5.3 创建定时任务

编辑 crontab：

```bash
crontab -e
```

添加：

```cron
# 每天清理过期导出文件
0 2 * * * find /var/www/compliance-saas/runtime/exports -mtime +7 -delete

# 每小时检查逾期学习任务
0 * * * * php /var/www/compliance-saas/think check:overdue
```

## 六、验证部署

### 6.1 检查服务状态

```bash
systemctl status nginx
systemctl status php8.0-fpm
systemctl status mysqld
```

### 6.2 测试访问

访问 `http://your-domain.com` 或 `https://your-domain.com`

默认超级管理员账号：
- 用户名：`admin`
- 密码：`admin123`

**首次登录后请立即修改密码！**

## 七、常见问题

### 7.1 502 Bad Gateway

检查 PHP-FPM 是否运行：
```bash
systemctl status php8.0-fpm
```

### 7.2 403 Forbidden

检查文件权限：
```bash
chown -R www:www /var/www/compliance-saas
chmod -R 755 /var/www/compliance-saas
```

### 7.3 数据库连接失败

检查 MySQL 服务和配置：
```bash
systemctl status mysqld
mysql -u compliance -p -e "SHOW DATABASES;"
```

### 7.4 上传文件失败

检查 PHP 配置：
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

## 八、性能优化建议

1. **启用 OPcache**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **配置 Nginx 缓存**
   ```nginx
   proxy_cache_path /var/cache/nginx levels=1:2 keys_zone=my_cache:10m
   ```

3. **数据库优化**
   - 为常用查询字段添加索引
   - 定期执行 `OPTIMIZE TABLE`
   - 配置慢查询日志

4. **使用 Redis 缓存**
   ```bash
   yum install -y redis
   systemctl enable redis
   systemctl start redis
   ```

---

部署完成后，请参考《操作手册》进行系统配置和使用。
