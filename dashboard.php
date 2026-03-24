<?php
// --- 1. ส่วนคำนวณ PHP ---

// ตัวแปรสำหรับข้อมูลทั่วไป
$genderCounts = ['ชาย' => 0, 'หญิง' => 0];
$riskStats = ['Malnutrition' => 0, 'Bedridden' => 0, 'Autoimmune' => 0, 'Difficult Line' => 0];
$statusStats = ['Admitted' => 0, 'Discharged' => 0];

// ตัวแปรสำหรับสถิติ Complication (เพิ่ม Post Phlebitis)
$compStats = [
    'Phlebitis' => 0,
    'Infiltration' => 0,
    'Extravasation' => 0,
    'PostPhlebitis' => 0, // เพิ่มใหม่
    'PLABSI' => 0
];

// ตัวแปรสำหรับ Line Use (นับจากรายเส้น)
$lineUseStats = [
    'Solution' => 0,
    'Antibiotic' => 0,
    'High Alert' => 0,
    'TPN' => 0
];

$totalLinesAllPatients = 0; // ตัวหาร: จำนวน Line ทุกเส้นของทุกคน
$admitted = 0; 
$discharged = 0;

foreach($patients as $p) {
    // 1. นับข้อมูลทั่วไป (ระดับคนไข้)
    if(isset($genderCounts[$p['gender']])) $genderCounts[$p['gender']]++;
    
    if(($p['malnutrition'] ?? 'No') == 'Yes') $riskStats['Malnutrition']++;
    if(($p['bedridden'] ?? 'No') == 'Yes') $riskStats['Bedridden']++;
    if(($p['autoimmune'] ?? 'No') == 'Yes') $riskStats['Autoimmune']++;
    if(($p['difficult_line'] ?? 'No') == 'Yes') $riskStats['Difficult Line']++;
    
    if($p['status'] == 'Admitted') { $admitted++; $statusStats['Admitted']++; }
    if($p['status'] == 'Discharged') { $discharged++; $statusStats['Discharged']++; }

    // 2. เจาะลึกข้อมูล Line (ระดับเส้น - JSON Decode)
    $lines = json_decode($p['lines_json'] ?? '{}', true);
    
    if(is_array($lines)) {
        foreach($lines as $line) {
            // ตรวจสอบว่าเป็น Line ที่มีการใช้งานจริง
            if(!empty($line['date_start']) || !empty($line['side']) || !empty($line['comp_type'])) {
                $totalLinesAllPatients++; // บวกตัวหาร

                $type = $line['comp_type'] ?? '';
                $level = intval($line['comp_level'] ?? 0);
                $culture = $line['culture_result'] ?? '';
                $use = $line['line_use'] ?? '';

                // --- A. นับ Complications ---

                // 1. Phlebitis
                if ($type === '1. Phlebitis' && $level != 0) $compStats['Phlebitis']++;

                // 2. Infiltration
                if ($type === '2. Infiltration Scale' && $level != 0) $compStats['Infiltration']++;

                // 3. Extravasation
                if ($type === '3. Extravasation Scale' && $level != 0) $compStats['Extravasation']++;

                // 4. POST Phlebitis (เพิ่มใหม่)
                if ($type === '4.POST Phlebitis') $compStats['PostPhlebitis']++;

                // 5. PLABSI
                if (strpos($type, '5.PLABST') !== false && !empty($culture)) $compStats['PLABSI']++;

                // --- B. นับ Line Use (ตามตัวเลือก 4 ข้อ) ---
                // ใช้ strpos เพื่อความยืดหยุ่น (เผื่อ string เป็น "1. Solution" หรือ "Solution")
                if (strpos($use, 'Solution') !== false) $lineUseStats['Solution']++;
                elseif (strpos($use, 'Antibiotic') !== false) $lineUseStats['Antibiotic']++;
                elseif (strpos($use, 'High Alert') !== false) $lineUseStats['High Alert']++;
                elseif (strpos($use, 'TPN') !== false) $lineUseStats['TPN']++;
            }
        }
    }
}

$uniquePatients = count(array_unique(array_column($patients, 'hn')));
$totalAdmit = count($patients);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6 slide-in">
    <h2 class="text-2xl font-bold text-slate-800 border-b pb-2">Dashboard ภาพรวม</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div><p class="text-slate-500 text-sm">จำนวนผู้ป่วย</p><p class="text-2xl font-bold text-slate-800"><?= $uniquePatients ?></p></div>
            <div class="bg-indigo-500 p-3 rounded-full text-white bg-opacity-90"><i data-lucide="users"></i></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div><p class="text-slate-500 text-sm">Total Lines (เส้น)</p><p class="text-2xl font-bold text-blue-600"><?= $totalLinesAllPatients ?></p></div>
            <div class="bg-blue-500 p-3 rounded-full text-white bg-opacity-90"><i data-lucide="activity"></i></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div><p class="text-slate-500 text-sm">Phlebitis Events</p><p class="text-2xl font-bold text-red-600"><?= $compStats['Phlebitis'] ?></p></div>
            <div class="bg-red-500 p-3 rounded-full text-white bg-opacity-90"><i data-lucide="alert-circle"></i></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-center justify-between">
            <div><p class="text-slate-500 text-sm">Post Phlebitis</p><p class="text-2xl font-bold text-pink-600"><?= $compStats['PostPhlebitis'] ?></p></div>
            <div class="bg-pink-500 p-3 rounded-full text-white bg-opacity-90"><i data-lucide="clock"></i></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i data-lucide="user" class="text-purple-600 w-4 h-4"></i> ข้อมูลเพศ
            </h3>
            <div class="h-48 relative">
                <canvas id="genderChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i data-lucide="stethoscope" class="text-purple-600 w-4 h-4"></i> ปัจจัยเสี่ยง
            </h3>
            <div class="h-48 relative">
                <canvas id="riskChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="text-red-600 w-4 h-4"></i> ภาวะแทรกซ้อน (Complications)
            </h3>
            <div class="h-48 relative">
                <canvas id="compChart"></canvas>
            </div>
            <div class="text-xs text-slate-400 text-center mt-2">* จากทั้งหมด <?= $totalLinesAllPatients ?> เส้น</div>
        </div>

         <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i data-lucide="droplet" class="text-blue-600 w-4 h-4"></i> ประเภทการใช้งาน (Line Use)
            </h3>
            <div class="h-48 relative">
                <canvas id="lineUseChart"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
    Chart.defaults.font.family = "'Sarabun', sans-serif";
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    };

    // 1. Gender Chart
    new Chart(document.getElementById('genderChart'), {
        type: 'bar',
        data: {
            labels: ['ชาย', 'หญิง'],
            datasets: [{
                label: 'คน',
                data: [<?= $genderCounts['ชาย'] ?>, <?= $genderCounts['หญิง'] ?>],
                backgroundColor: ['#3b82f6', '#ec4899'],
                borderRadius: 4
            }]
        },
        options: commonOptions
    });

    // 2. Risk Chart
    new Chart(document.getElementById('riskChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($riskStats)) ?>,
            datasets: [{
                label: 'คน',
                data: <?= json_encode(array_values($riskStats)) ?>,
                backgroundColor: ['#f87171', '#fbbf24', '#34d399', '#a78bfa'],
                borderRadius: 4
            }]
        },
        options: commonOptions
    });

    // 3. Complications Chart (เพิ่ม Post Phlebitis เข้าไป)
    new Chart(document.getElementById('compChart'), {
        type: 'bar',
        data: {
            labels: ['Phlebitis', 'Infiltration', 'Extravasation', 'Post Phlebitis', 'PLABSI'],
            datasets: [{
                label: 'จำนวน (ครั้ง)',
                data: [
                    <?= $compStats['Phlebitis'] ?>, 
                    <?= $compStats['Infiltration'] ?>, 
                    <?= $compStats['Extravasation'] ?>, 
                    <?= $compStats['PostPhlebitis'] ?>, // เพิ่มข้อมูล Post
                    <?= $compStats['PLABSI'] ?>
                ],
                backgroundColor: [
                    '#ef4444', // Phlebitis (แดง)
                    '#f59e0b', // Infiltration (เหลืองเข้ม)
                    '#8b5cf6', // Extravasation (ม่วง)
                    '#ec4899', // Post Phlebitis (ชมพูเข้ม)
                    '#f97316'  // PLABSI (ส้ม)
                ],
                borderRadius: 4,
                barPercentage: 0.7
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            let val = context.parsed.y;
                            let total = <?= $totalLinesAllPatients ?: 1 ?>;
                            let percent = (val / total * 100).toFixed(2);
                            return `คิดเป็น ${percent}% ของเส้นทั้งหมด`;
                        }
                    }
                }
            }
        }
    });

    // 4. Line Use Chart (กราฟใหม่สำหรับ Solution, Antibiotic, etc.)
    new Chart(document.getElementById('lineUseChart'), {
        type: 'bar',
        data: {
            labels: ['Solution', 'Antibiotic', 'High Alert', 'TPN'],
            datasets: [{
                label: 'จำนวน (เส้น)',
                data: [
                    <?= $lineUseStats['Solution'] ?>,
                    <?= $lineUseStats['Antibiotic'] ?>,
                    <?= $lineUseStats['High Alert'] ?>,
                    <?= $lineUseStats['TPN'] ?>
                ],
                backgroundColor: [
                    '#60a5fa', // Solution (ฟ้า)
                    '#34d399', // Antibiotic (เขียว)
                    '#f87171', // High Alert (แดง)
                    '#fbbf24'  // TPN (เหลือง)
                ],
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: commonOptions
    });
</script>