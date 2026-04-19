<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 合规课程模型
 */
class Course extends Model
{
    protected $table = 'tp_course';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 关联租户
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
    
    /**
     * 关联学习记录
     */
    public function learningRecords()
    {
        return $this->hasMany(LearningRecord::class, 'course_id', 'id');
    }
    
    /**
     * 获取课程类型文本
     */
    public function getCourseTypeTextAttr($value, $data)
    {
        $typeMap = [
            1 => '视频课程',
            2 => '文档课程',
            3 => '混合课程',
        ];
        return $typeMap[$data['course_type']] ?? '未知';
    }
    
    /**
     * 统计学习人数
     */
    public function getLearnerCountAttr($value, $data)
    {
        return LearningRecord::where('course_id', $data['id'])->count();
    }
    
    /**
     * 统计完成人数
     */
    public function getCompleteCountAttr($value, $data)
    {
        return LearningRecord::where('course_id', $data['id'])
            ->where('status', 2)
            ->count();
    }
}
