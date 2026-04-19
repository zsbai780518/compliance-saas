<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Course;
use app\model\LearningRecord;
use app\model\LearningLog;
use think\facade\Db;

/**
 * 学习控制器 - 课程学习/进度追踪/防作弊
 */
class Learn extends BaseController
{
    /**
     * 获取学习进度
     */
    public function progress()
    {
        $userId = $this->request->userId;
        $courseId = $this->request->get('course_id', 0);
        
        if ($courseId > 0) {
            // 单课程进度
            $record = LearningRecord::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->find();
            
            if (!$record) {
                return json([
                    'code' => 200,
                    'data' => [
                        'status' => 0,
                        'status_text' => '未开始',
                        'total_progress' => 0,
                        'video_progress' => 0,
                        'doc_progress' => 0,
                    ]
                ]);
            }
            
            $course = Course::get($courseId);
            
            return json([
                'code' => 200,
                'data' => [
                    'course_id' => $courseId,
                    'course_name' => $course->course_name,
                    'course_type' => $course->course_type,
                    'status' => $record->status,
                    'status_text' => $this->getStatusText($record->status),
                    'total_progress' => $record->total_progress,
                    'video_progress' => $record->video_progress,
                    'video_duration' => $record->video_duration,
                    'doc_progress' => $record->doc_progress,
                    'last_learn_time' => $record->last_learn_time,
                    'complete_time' => $record->complete_time,
                ]
            ]);
        } else {
            // 全部课程进度统计
            $records = LearningRecord::where('user_id', $userId)
                ->alias('r')
                ->join('course c', 'r.course_id = c.id')
                ->field('r.*, c.course_name, c.course_type')
                ->select();
            
            $total = $records->count();
            $completed = $records->where('status', 2)->count();
            $learning = $records->where('status', 1)->count();
            $notStarted = $records->where('status', 0)->count();
            
            return json([
                'code' => 200,
                'data' => [
                    'summary' => [
                        'total' => $total,
                        'completed' => $completed,
                        'learning' => $learning,
                        'not_started' => $notStarted,
                    ],
                    'list' => $records->map(function($item) {
                        return [
                            'course_id' => $item->course_id,
                            'course_name' => $item->course_name,
                            'status' => $item->status,
                            'status_text' => $this->getStatusText($item->status),
                            'total_progress' => $item->total_progress,
                            'last_learn_time' => $item->last_learn_time,
                        ];
                    }),
                ]
            ]);
        }
    }
    
    /**
     * 记录学习行为（心跳/进度同步）
     */
    public function record()
    {
        $userId = $this->request->userId;
        $data = $this->request->post();
        
        $validate = validate([
            'course_id' => 'require|integer',
            'action_type' => 'require',
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        $course = Course::get($data['course_id']);
        if (!$course) {
            return json(['code' => 400, 'msg' => '课程不存在']);
        }
        
        // 获取或创建学习记录
        $record = LearningRecord::where('user_id', $userId)
            ->where('course_id', $data['course_id'])
            ->find();
        
        if (!$record) {
            $record = new LearningRecord();
            $record->tenant_id = $this->request->tenantId;
            $record->user_id = $userId;
            $record->course_id = $data['course_id'];
            $record->status = 1;
        }
        
        // 根据行为类型处理
        switch ($data['action_type']) {
            case 'play':
                // 开始播放
                $record->status = 1;
                break;
                
            case 'heartbeat':
                // 心跳同步进度
                if (isset($data['progress'])) {
                    $record->updateVideoProgress(
                        (int)$data['progress'],
                        (int)($data['duration'] ?? $record->video_duration)
                    );
                }
                break;
                
            case 'seek':
                // 拖拽 - 记录防作弊日志
                if (isset($data['from']) && isset($data['to'])) {
                    // 检查是否恶意拖拽（跳过超过 30 秒）
                    if (abs($data['to'] - $data['from']) > 30) {
                        $record->addAntiCheatLog('seek', [
                            'from' => $data['from'],
                            'to' => $data['to'],
                            'warning' => '大跨度拖拽',
                        ]);
                    }
                }
                break;
                
            case 'exit':
                // 离开页面 - 保存最终进度
                if (isset($data['progress'])) {
                    $record->updateVideoProgress(
                        (int)$data['progress'],
                        (int)($data['duration'] ?? $record->video_duration)
                    );
                }
                break;
                
            case 'doc_read':
                // 文档阅读
                $record->doc_progress = 1;
                $record->doc_read_time = ($record->doc_read_time ?? 0) + ($data['read_time'] ?? 0);
                $record->calculateTotalProgress();
                break;
        }
        
        // 保存记录
        $record->last_learn_time = time();
        $record->save();
        
        // 记录学习日志
        $log = new LearningLog();
        $log->record_id = $record->id;
        $log->user_id = $userId;
        $log->course_id = $data['course_id'];
        $log->action_type = $data['action_type'];
        $log->action_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $log->client_ip = $this->request->ip();
        $log->user_agent = $this->request->header('User-Agent');
        $log->save();
        
        return json([
            'code' => 200,
            'msg' => '同步成功',
            'data' => [
                'progress' => $record->total_progress,
                'status' => $record->status,
            ]
        ]);
    }
    
    /**
     * 心跳同步（轻量级）
     */
    public function heartbeat()
    {
        $userId = $this->request->userId;
        $data = $this->request->post();
        
        if (!isset($data['course_id']) || !isset($data['progress'])) {
            return json(['code' => 400, 'msg' => '参数错误']);
        }
        
        // 更新进度
        $record = LearningRecord::where('user_id', $userId)
            ->where('course_id', $data['course_id'])
            ->find();
        
        if ($record) {
            $record->updateVideoProgress(
                (int)$data['progress'],
                (int)($data['duration'] ?? 0)
            );
            
            return json([
                'code' => 200,
                'data' => ['progress' => $record->total_progress]
            ]);
        }
        
        return json(['code' => 400, 'msg' => '记录不存在']);
    }
    
    /**
     * 获取学习记录列表
     */
    public function records()
    {
        $userId = $this->request->userId;
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        
        $list = LearningRecord::where('user_id', $userId)
            ->alias('r')
            ->join('course c', 'r.course_id = c.id')
            ->field('r.*, c.course_name, c.course_type, c.cover_image')
            ->order('r.last_learn_time', 'desc')
            ->page($page, $limit)
            ->select();
        
        $total = LearningRecord::where('user_id', $userId)->count();
        
        return json([
            'code' => 200,
            'data' => [
                'total' => $total,
                'list' => $list->map(function($item) {
                    return [
                        'course_id' => $item->course_id,
                        'course_name' => $item->course_name,
                        'course_type' => $item->course_type,
                        'cover_image' => $item->cover_image,
                        'status' => $item->status,
                        'status_text' => $this->getStatusText($item->status),
                        'total_progress' => $item->total_progress,
                        'last_learn_time' => $item->last_learn_time,
                        'complete_time' => $item->complete_time,
                    ];
                }),
            ]
        ]);
    }
    
    /**
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $map = [
            0 => '未开始',
            1 => '学习中',
            2 => '已完成',
            3 => '已逾期',
        ];
        return $map[$status] ?? '未知';
    }
}
