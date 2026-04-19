<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * SaaS 企业租户模型
 */
class Tenant extends Model
{
    protected $table = 'tp_tenant';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 关联用户
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }
    
    /**
     * 关联课程
     */
    public function courses()
    {
        return $this->hasMany(Course::class, 'tenant_id', 'id');
    }
    
    /**
     * 关联部门
     */
    public function departments()
    {
        return $this->hasMany(Department::class, 'tenant_id', 'id');
    }
    
    /**
     * 检查套餐限制
     */
    public function checkLimit($type, $current)
    {
        $limits = [
            'users' => $this->max_users,
            'courses' => $this->max_courses,
            'export' => $this->max_export,
            'storage' => $this->storage_limit * 1024 * 1024, // 转字节
        ];
        
        return $current < ($limits[$type] ?? 0);
    }
    
    /**
     * 是否已到期
     */
    public function isExpired()
    {
        return $this->expire_time > 0 && $this->expire_time < time();
    }
    
    /**
     * 生成租户编码
     */
    public static function generateCode($name)
    {
        return 'T' . strtoupper(substr(md5($name . time()), 0, 8));
    }
}
