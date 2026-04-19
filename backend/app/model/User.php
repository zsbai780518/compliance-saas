<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 企业员工用户模型
 * 核心实名认证：姓名 + 手机号 + 身份证三重绑定
 */
class User extends Model
{
    // 表名
    protected $table = 'tp_user';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 隐藏字段
    protected $hidden = ['password', 'id_card'];
    
    // 追加属性
    protected $append = ['auth_status_text', 'is_certified'];
    
    /**
     * 关联租户
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
    
    /**
     * 关联部门
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'id');
    }
    
    /**
     * 关联学习记录
     */
    public function learningRecords()
    {
        return $this->hasMany(LearningRecord::class, 'user_id', 'id');
    }
    
    /**
     * 加密身份证号
     */
    public function setIdCardAttr($value)
    {
        if ($value) {
            $key = config('app.encrypt_key', 'ComplianceSaaS2026Key');
            return openssl_encrypt($value, 'AES-128-ECB', $key);
        }
        return $value;
    }
    
    /**
     * 解密身份证号
     */
    public function getIdCardAttr($value)
    {
        if ($value) {
            $key = config('app.encrypt_key', 'ComplianceSaaS2026Key');
            return openssl_decrypt($value, 'AES-128-ECB', $key);
        }
        return $value;
    }
    
    /**
     * 获取认证状态文本
     */
    public function getAuthStatusTextAttr($value, $data)
    {
        $statusMap = [
            0 => '待认证',
            1 => '已认证',
            2 => '审核中',
            3 => '已驳回',
        ];
        return $statusMap[$data['auth_status']] ?? '未知';
    }
    
    /**
     * 是否已认证
     */
    public function getIsCertifiedAttr($value, $data)
    {
        return $data['auth_status'] == 1;
    }
    
    /**
     * 验证三重绑定唯一性
     */
    public static function checkUnique($realname, $mobile, $idCard, $tenantId, $excludeId = 0)
    {
        $where = [
            ['realname', '=', $realname],
            ['mobile', '=', $mobile],
            ['id_card', '=', self::make()->setIdCardAttr($idCard)],
            ['tenant_id', '=', $tenantId],
        ];
        
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        
        return self::where($where)->find();
    }
    
    /**
     * 验证手机号在租户内唯一
     */
    public static function checkMobileUnique($mobile, $tenantId, $excludeId = 0)
    {
        $where = [
            ['mobile', '=', $mobile],
            ['tenant_id', '=', $tenantId],
        ];
        
        if ($excludeId > 0) {
            $where[] = ['id', '<>', $excludeId];
        }
        
        return self::where($where)->find();
    }
}
