<?php
// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if(isset($pdo)) {
    try {
        // สร้างตาราง logs ถ้ายังไม่มี
        $pdo->exec("CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            staff VARCHAR(100),
            ward VARCHAR(50),
            action VARCHAR(50),
            detail TEXT
        )");

        $stmt = $pdo->query("SELECT * FROM system_logs ORDER BY timestamp DESC LIMIT 50");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $logs = []; 
    }
} else {
    $logs = $logs ?? []; 
}

// ฟังก์ชันเลือกสีป้ายกำกับ (Badge Color)
function getActionClass($action) {
    switch (strtolower($action)) {
        case 'create': return 'bg-green-100 text-green-700 border border-green-200';
        case 'update': return 'bg-amber-100 text-amber-700 border border-amber-200';
        case 'delete': return 'bg-red-100 text-red-700 border border-red-200';
        default:       return 'bg-slate-100 text-slate-600 border border-slate-200';
    }
}
?>

<div class="space-y-6 slide-in">
    <h2 class="text-2xl font-bold text-slate-800 border-b pb-2 flex items-center gap-2">
        <i data-lucide="history" class="text-purple-600"></i> ประวัติการทำรายการ
    </h2>
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider">
                <tr>
                    <th class="p-4 border-b font-semibold w-48">วัน/เวลา</th>
                    <th class="p-4 border-b font-semibold w-40">ผู้ทำรายการ</th>
                    <th class="p-4 border-b font-semibold w-32">หน่วยงาน</th>
                    <th class="p-4 border-b font-semibold w-24 text-center">กิจกรรม</th>
                    <th class="p-4 border-b font-semibold">รายละเอียด</th>
                </tr>
            </thead>
            <tbody class="text-slate-700 text-sm">
                <?php if(empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-400">
                            <i data-lucide="file-clock" class="w-12 h-12 mx-auto mb-2 opacity-20"></i>
                            ยังไม่มีประวัติการทำรายการ
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                    <tr class="border-b last:border-0 hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-slate-500 font-mono text-xs whitespace-nowrap">
                            <?= thaiDate($log['timestamp']) ?> 
                            <span class="text-slate-400 ml-1"><?= date('H:i', strtotime($log['timestamp'])) ?></span>
                        </td>
                        <td class="p-4 font-medium text-slate-800">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-[10px] font-bold">
                                    <?= mb_substr($log['staff'], 0, 1) ?>
                                </div>
                                <?= $log['staff'] ?>
                            </div>
                        </td>
                        <td class="p-4 text-slate-600"><?= $log['ward'] ?></td>
                        <td class="p-4 text-center">
                            <span class="px-2 py-1 rounded text-xs font-bold inline-block min-w-[80px] text-center <?= getActionClass($log['action']) ?>">
                                <?= $log['action'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-slate-600 leading-relaxed"><?= $log['detail'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>