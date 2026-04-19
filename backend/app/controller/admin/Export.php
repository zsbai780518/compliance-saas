<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\model\User;
use app\model\Course;
use app\model\LearningRecord;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use Dompdf\Dompdf;

/**
 * 报表导出控制器
 */
class Export extends BaseController
{
    /**
     * 导出个人学习报告
     */
    public function personal()
    {
        $userId = $this->request->userId;
        $format = $this->request->get('format', 'excel');
        
        $user = User::get($userId);
        $records = LearningRecord::where('user_id', $userId)
            ->alias('r')
            ->join('course c', 'r.course_id = c.id')
            ->field('r.*, c.course_name, c.course_type')
            ->select();
        
        switch ($format) {
            case 'excel':
                return $this->exportExcel($user, $records);
            
            case 'pdf':
                return $this->exportPdf($user, $records);
            
            case 'word':
                return $this->exportWord($user, $records);
            
            default:
                return json(['code' => 400, 'msg' => '不支持的格式']);
        }
    }
    
    /**
     * 批量导出学习报告
     */
    public function batch()
    {
        $tenantId = $this->request->tenantId;
        $data = $this->request->post();
        
        $validate = validate([
            'export_type' => 'require', // department/postion/all
            'format' => 'require', // excel/pdf/zip
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'msg' => $validate->getError()]);
        }
        
        // 构建查询条件
        $where = [['tenant_id', '=', $tenantId]];
        
        if ($data['export_type'] == 'department' && !empty($data['dept_id'])) {
            $where[] = ['dept_id', '=', $data['dept_id']];
        }
        
        if (!empty($data['start_time'])) {
            // 筛选学习时间范围内的用户
            $userIds = LearningRecord::where('tenant_id', $tenantId)
                ->where('last_learn_time', '>=', strtotime($data['start_time']))
                ->column('user_id');
            $where[] = ['id', 'in', $userIds];
        }
        
        $users = User::where($where)->select();
        
        // 导出
        switch ($data['format']) {
            case 'excel':
                return $this->exportBatchExcel($users, $tenantId);
            
            case 'zip':
                return $this->exportBatchZip($users, $tenantId);
            
            default:
                return json(['code' => 400, 'msg' => '不支持的格式']);
        }
    }
    
    /**
     * 下载导出文件
     */
    public function download($id)
    {
        $record = \app\model\ExportRecord::get($id);
        
        if (!$record || $record->tenant_id != $this->request->tenantId) {
            return json(['code' => 400, 'msg' => '文件不存在']);
        }
        
        if ($record->expire_time < time()) {
            return json(['code' => 400, 'msg' => '下载链接已过期']);
        }
        
        $filePath = root_path() . 'public' . $record->file_path;
        
        if (!file_exists($filePath)) {
            return json(['code' => 400, 'msg' => '文件不存在']);
        }
        
        // 增加下载次数
        $record->download_count++;
        $record->save();
        
        return download($filePath, basename($record->file_path));
    }
    
    // ==================== 导出实现 ====================
    
    /**
     * 导出 Excel
     */
    private function exportExcel($user, $records)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        $headers = ['序号', '课程名称', '课程类型', '学习进度', '状态', '最后学习时间', '完成时间'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // 填充数据
        $row = 2;
        $index = 1;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $index++);
            $sheet->setCellValue('B' . $row, $record->course_name);
            $sheet->setCellValue('C' . $row, $record->course_type_text ?? '未知');
            $sheet->setCellValue('D' . $row, $record->total_progress . '%');
            $sheet->setCellValue('E' . $row, $this->getStatusText($record->status));
            $sheet->setCellValue('F' . $row, $record->last_learn_time ? date('Y-m-d H:i', $record->last_learn_time) : '-');
            $sheet->setCellValue('G' . $row, $record->complete_time ? date('Y-m-d H:i', $record->complete_time) : '-');
            $row++;
        }
        
        // 设置列宽
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // 保存文件
        $filename = $user->realname . '_学习报告_' . date('YmdHis') . '.xlsx';
        $savePath = runtime_path() . 'exports/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($savePath);
        
        // 返回下载
        return download($savePath, $filename);
    }
    
    /**
     * 导出 PDF
     */
    private function exportPdf($user, $records)
    {
        $html = $this->generateReportHtml($user, $records);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = $user->realname . '_学习报告_' . date('YmdHis') . '.pdf';
        
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * 导出 Word
     */
    private function exportWord($user, $records)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // 标题
        $section->addText('个人学习报告', ['size' => 18, 'bold' => true], ['align' => 'center']);
        $section->addTextBreak(1);
        
        // 个人信息
        $section->addText("姓名：{$user->realname}", ['size' => 12]);
        $section->addText("手机号：{$user->mobile}", ['size' => 12]);
        $section->addText("部门：" . ($user->department->dept_name ?? '未分配'), ['size' => 12]);
        $section->addText("岗位：" . ($user->position ?? '未分配'), ['size' => 12]);
        $section->addTextBreak(1);
        
        // 学习记录表格
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80]);
        
        // 表头
        $table->addRow();
        $table->addCell(2000)->addText('课程名称', ['bold' => true]);
        $table->addCell(1000)->addText('进度', ['bold' => true]);
        $table->addCell(1000)->addText('状态', ['bold' => true]);
        $table->addCell(2000)->addText('完成时间', ['bold' => true]);
        
        // 数据行
        foreach ($records as $record) {
            $table->addRow();
            $table->addCell(2000)->addText($record->course_name);
            $table->addCell(1000)->addText($record->total_progress . '%');
            $table->addCell(1000)->addText($this->getStatusText($record->status));
            $table->addCell(2000)->addText($record->complete_time ? date('Y-m-d H:i', $record->complete_time) : '-');
        }
        
        $filename = $user->realname . '_学习报告_' . date('YmdHis') . '.docx';
        $savePath = runtime_path() . 'exports/' . $filename;
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($savePath);
        
        return download($savePath, $filename);
    }
    
    /**
     * 批量导出 Excel
     */
    private function exportBatchExcel($users, $tenantId)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 表头
        $headers = ['序号', '姓名', '手机号', '部门', '岗位', '已学课程数', '已完成课程数', '总学习时长', '认证状态'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // 数据
        $row = 2;
        $index = 1;
        foreach ($users as $user) {
            $records = LearningRecord::where('user_id', $user->id)->select();
            $completed = $records->where('status', 2)->count();
            
            $sheet->setCellValue('A' . $row, $index++);
            $sheet->setCellValue('B' . $row, $user->realname);
            $sheet->setCellValue('C' . $row, $user->mobile);
            $sheet->setCellValue('D' . $row, $user->department->dept_name ?? '未分配');
            $sheet->setCellValue('E' . $row, $user->position ?? '未分配');
            $sheet->setCellValue('F' . $row, $records->count());
            $sheet->setCellValue('G' . $row, $completed);
            $sheet->setCellValue('H' . $row, $this->calculateTotalHours($records) . '小时');
            $sheet->setCellValue('I' . $row, $user->auth_status_text);
            $row++;
        }
        
        // 设置样式
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = '企业学习报告_' . date('YmdHis') . '.xlsx';
        $savePath = runtime_path() . 'exports/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($savePath);
        
        return download($savePath, $filename);
    }
    
    /**
     * 批量导出 ZIP（包含多个 PDF 报告）
     */
    private function exportBatchZip($users, $tenantId)
    {
        $zip = new \ZipArchive();
        $zipFilename = '企业学习报告_' . date('YmdHis') . '.zip';
        $zipPath = runtime_path() . 'exports/' . $zipFilename;
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return json(['code' => 500, 'msg' => '创建压缩文件失败']);
        }
        
        foreach ($users as $user) {
            $records = LearningRecord::where('user_id', $user->id)->select();
            $html = $this->generateReportHtml($user, $records);
            
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->render();
            
            $pdfContent = $dompdf->output();
            $zip->addFromString($user->realname . '_学习报告.pdf', $pdfContent);
        }
        
        $zip->close();
        
        return download($zipPath, $zipFilename);
    }
    
    /**
     * 生成报告 HTML
     */
    private function generateReportHtml($user, $records)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "SimSun", serif; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>个人学习报告</h1>
    <p><strong>姓名：</strong>' . $user->realname . '</p>
    <p><strong>手机号：</strong>' . $user->mobile . '</p>
    <p><strong>部门：</strong>' . ($user->department->dept_name ?? '未分配') . '</p>
    <p><strong>岗位：</strong>' . ($user->position ?? '未分配') . '</p>
    <table>
        <tr><th>课程名称</th><th>进度</th><th>状态</th><th>完成时间</th></tr>';
        
        foreach ($records as $record) {
            $html .= '<tr>
                <td>' . $record->course_name . '</td>
                <td>' . $record->total_progress . '%</td>
                <td>' . $this->getStatusText($record->status) . '</td>
                <td>' . ($record->complete_time ? date('Y-m-d H:i', $record->complete_time) : '-') . '</td>
            </tr>';
        }
        
        $html .= '</table></body></html>';
        return $html;
    }
    
    private function getStatusText($status)
    {
        $map = [0 => '未开始', 1 => '学习中', 2 => '已完成', 3 => '已逾期'];
        return $map[$status] ?? '未知';
    }
    
    private function calculateTotalHours($records)
    {
        $totalSeconds = 0;
        foreach ($records as $record) {
            $totalSeconds += $record->video_progress;
            $totalSeconds += $record->doc_read_time ?? 0;
        }
        return round($totalSeconds / 3600, 2);
    }
}
