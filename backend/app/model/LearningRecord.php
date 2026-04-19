<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 学习记录模型 - 核心进度追踪
 */
class LearningRecord extends Model
{
    protected $table = 'tp_learning_record';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * 关联课程
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
    
    /**
     * 关联学习日志
     */
    public function logs()
    {
        return $this->hasMany(LearningLog::class, 'record_id', 'id');
    }
    
    /**
     * 更新视频进度
     */
    public function updateVideoProgress($progress, $duration)
    {
        $this->video_progress = $progress;
        $this->video_duration = $duration;
        $this->last_learn_time = time();
        
        // 计算总进度
        $this->calculateTotalProgress();
        
        // 检查是否完成
        if ($this->total_progress >= 100) {
            $this->status = 2;
            $this->complete_time = time();
        } elseif ($this->status == 0) {
            $this->status = 1;
        }
        
        return $this->save();
    }
    
    /**
     * 计算总进度
     */
    private function calculateTotalProgress()
    {
        $course = Course::get($this->course_id);
        $progress = 0;
        
        if ($course->course_type == 1) {
            // 纯视频
            $progress = $this->video_duration > 0 
                ? ($this->video_progress / $this->video_duration) * 100 
                : 0;
        } elseif ($course->course_type == 2) {
            // 纯文档
            $progress = $this->doc_progress * 100;
        } else {
            // 混合：视频 70% + 文档 30%
            $videoProgress = $this->video_duration > 0 
                ? ($this->video_progress / $this->video_duration) * 70 
                : 0;
            $docProgress = $this->doc_progress * 30;
            $progress = $videoProgress + $docProgress;
        }
        
        $this->total_progress = round($progress, 2);
    }
    
    /**
     * 记录防作弊日志
     */
    public function addAntiCheatLog($action, $data)
    {
        $log = $this->anti_cheat_log ? json_decode($this->anti_cheat_log, true) : [];
        $log[] = [
            'action' => $action,
            'data' => $data,
            'time' => time(),
        ];
        
        // 只保留最近 100 条
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        
        $this->anti_cheat_log = json_encode($log, JSON_UNESCAPED_UNICODE);
        return $this->save();
    }
    
    /**
     * 检查是否逾期
     */
    public function checkOverdue($deadline)
    {
        if ($deadline > 0 && time() > $deadline && $this->status != 2) {
            $this->status = 3; // 逾期
            return $this->save();
        }
        return false;
    }
}
