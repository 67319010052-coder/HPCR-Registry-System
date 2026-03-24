<?php 
// register.php
// Check if editing
$editId = $_GET['id'] ?? null;

// Default Data
$data = [
    'hn' => '', 'an' => '', 'name' => '', 'age' => '', 'gender' => 'ชาย', 'insurance' => '',
    'refer_hospital' => '', 'refer_province' => '', 'refer_date' => '',
    'admit_date' => date('Y-m-d'), 'ward' => '', 'discharge_date' => '', 
    'diagnosis' => '', 'line_on' => 'No',
    'malnutrition' => 'No', 'bedridden' => 'No', 'autoimmune' => 'No', 'difficult_line' => 'No'
];

// If editing, pull from DB
if($editId && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$editId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    if($patient) $data = array_merge($data, $patient);
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        display: flex;
        align-items: center;
        border-color: #e2e8f0; /* สีเส้นขอบ */
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    /* ซ่อนลูกศรสามเหลี่ยมของ Datalist ใน Chrome/Edge */
    input::-webkit-calendar-picker-indicator {
        display: none !important;
        opacity: 0;
    }

    /* ปรับแต่งช่อง Input ให้ดูสะอาดตา */
    input[list] {
        appearance: none;
        -webkit-appearance: none;
    }
</style>

<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-sm border border-slate-200 slide-in flex flex-col min-h-[700px]">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <i data-lucide="<?= $editId ? 'edit' : 'file-plus' ?>" class="text-purple-600"></i> 
            <?= $editId ? "แก้ไขข้อมูลผู้ป่วย" : "ลงทะเบียนผู้ป่วย" ?>
        </h2>
        <div class="flex gap-2 text-sm font-medium text-slate-500">
            <span id="step-indicator">Step 1 of 5</span>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="w-full bg-slate-100 h-1.5 mt-0">
        <div id="progress-bar" class="bg-purple-600 h-1.5 transition-all duration-300" style="width: 20%"></div>
    </div>

    <form id="patientForm" action="save_patient.php" method="POST" class="p-6 md:p-8 flex-1 flex flex-col">
        <input type="hidden" name="id" value="<?= $editId ?>">
        <input type="hidden" name="lines_json" id="lines_json" value='<?= htmlspecialchars($data['lines_json'] ?? '') ?>'>
        
        <!-- Hidden Staff Info -->
        <input type="hidden" name="staff_name" id="hidden_staff_name">
        <input type="hidden" name="staff_ward" id="hidden_staff_ward">
        
        <div id="tab1" class="tab-content active space-y-6">
            <h3 class="text-lg font-semibold text-purple-700 mb-2 flex items-center gap-2"><i data-lucide="user" class="w-4 h-4"></i> ข้อมูลทั่วไป</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">HN</label>
                    <div class="flex gap-2">
                        <input type="text" name="hn" value="<?= $data['hn'] ?>" class="w-full p-2 border rounded focus:ring-2 focus:ring-purple-500" placeholder="xxxxxx" required>
                        <button type="button" class="p-2 bg-slate-200 rounded text-slate-600 hover:bg-slate-300" title="ดึงข้อมูล"><i data-lucide="search" class="w-5 h-5"></i></button>
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">AN</label><input type="text" name="an" value="<?= $data['an'] ?>" class="w-full p-2 border rounded" placeholder="xx-xxxxx" required></div>
                <div class="col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">ชื่อ-นามสกุล</label><input type="text" id="main_name" name="name" value="<?= $data['name'] ?>" class="w-full p-2 border rounded" placeholder="ระบุชื่อ..." required></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">อายุ</label><input type="number" name="age" value="<?= $data['age'] ?>" class="w-full p-2 border rounded" placeholder="ระบุอายุ"></div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">เพศ</label>
                    <input type="hidden" name="gender" id="genderInput" value="<?= $data['gender'] ?>">
                    <div class="flex bg-slate-100 rounded-lg p-1 border border-slate-200 h-[42px]">
                        <button type="button" onclick="setGender('ชาย')" id="btnMale" class="flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 <?= $data['gender']=='ชาย' ? 'bg-white text-blue-600 shadow-sm font-bold border border-slate-100' : 'text-slate-500' ?>"><i data-lucide="user" class="w-4 h-4"></i> ชาย</button>
                        <button type="button" onclick="setGender('หญิง')" id="btnFemale" class="flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 <?= $data['gender']=='หญิง' ? 'bg-white text-pink-500 shadow-sm font-bold border border-slate-100' : 'text-slate-500' ?>"><i data-lucide="user" class="w-4 h-4"></i> หญิง</button>
                    </div>
                </div>

                <div class="w-full">
                    <label class="block text-sm font-bold text-slate-700 mb-1">สิทธิการรักษา</label>
                    <input type="text" name="insurance" value="<?= $data['insurance'] ?>" placeholder="ระบุสิทธิ..." class="w-full p-2 border rounded border-slate-300 focus:ring-2 focus:ring-purple-500 h-[42px] bg-white outline-none text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded border border-slate-100">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Refer มาจาก รพ.</label>
                    <select id="hospital_select" name="refer_hospital" class="w-full p-2 border rounded bg-white" style="width: 100%;">
                        <option value="">-- ค้นหาโรงพยาบาล --</option>
                        <?php if (!empty($data['refer_hospital'])): ?>
                            <option value="<?= htmlspecialchars($data['refer_hospital']) ?>" selected>
                                <?= htmlspecialchars($data['refer_hospital']) ?>
                            </option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">จังหวัด</label>
                    <input type="text" name="refer_province" id="provinceInput" value="<?= $data['refer_province'] ?? '' ?>" readonly class="w-full p-2 border rounded bg-slate-100 text-slate-500 font-bold" placeholder="จังหวัดจะขึ้นอัตโนมัติ">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">วันที่ Refer</label>
                    <input type="date" name="refer_date" value="<?= $data['refer_date'] ?>" class="w-full p-2 border rounded bg-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-purple-50 p-4 rounded border border-purple-100">
                <div>
                    <label class="block text-sm font-medium text-purple-800 mb-1">วันที่ Admit</label>
                    <input type="date" name="admit_date" id="admitDate" value="<?= $data['admit_date'] ?>" onchange="calcLOS()" class="w-full p-2 border rounded border-purple-200">
                </div>
               <div>
    <label class="block text-sm font-medium text-purple-800 mb-1">Ward</label>
    <input 
        type="text" 
        id="main_ward" 
        name="ward" 
        value="<?= $data['ward'] ?? '' ?>" 
        class="w-full p-2 border rounded border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none" 
        placeholder="ระบุชื่อ Ward..." 
        required
    >
</div>
                <div>
                    <label class="block text-sm font-medium text-purple-800 mb-1">วันที่ D/C</label>
                    <input 
                        type="date" 
                        name="discharge_date" 
                        id="dischargeDate" 
                        value="<?= $data['discharge_date'] ?>" 
                        onchange="calcLOS()" 
                        class="w-full p-2 border rounded border-purple-200 transition-colors <?= $data['line_on'] === 'Yes' ? 'bg-white' : 'bg-slate-100 cursor-not-allowed text-slate-400' ?>"
                        <?= $data['line_on'] !== 'Yes' ? 'disabled' : '' ?> 
                    >
                </div>
                <div>
    <label class="block text-sm font-medium text-purple-800 mb-1">Ward</label>
    <input 
        type="text" 
        id="main_ward" 
        name="ward" 
        value="<?= $data['ward'] ?? '' ?>" 
        class="w-full p-2 border rounded border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none" 
        placeholder="ระบุชื่อ Ward..." 
        required
    >
</div>
                <div>
                    <label class="block text-sm font-medium text-purple-800 mb-1">LOS (วัน)</label>
                    <input type="text" id="losInput" readonly class="w-full p-2 border rounded bg-white text-center font-bold text-purple-600" value="0">
                </div>
                
                <div class="flex items-center gap-4 bg-purple-50 px-4 py-2 rounded border border-purple-100 md:col-span-2">
                    <span class="text-sm font-bold text-purple-700">Line On:</span>
                    <input type="hidden" name="line_on" id="lineOnInput" value="<?= $data['line_on'] ?>">
                    <div class="flex bg-white rounded-lg p-1 border border-purple-200 h-[36px]">
                        <button type="button" onclick="setLineOn('No')" id="btnLineNo" class="px-4 text-sm rounded-md transition-all <?= $data['line_on']=='No' ? 'bg-purple-600 text-white shadow-sm font-bold' : 'text-slate-500 hover:text-purple-600' ?>">No</button>
                        <button type="button" onclick="setLineOn('Yes')" id="btnLineYes" class="px-4 text-sm rounded-md transition-all <?= $data['line_on']=='Yes' ? 'bg-purple-600 text-white shadow-sm font-bold' : 'text-slate-500 hover:text-purple-600' ?>">Yes</button>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Diagnosis (ICD-10)</label>
                <select name="diagnosis" class="w-full p-3 border rounded-lg text-sm bg-slate-50 focus:bg-white transition-colors">
                    <option value="">-- เลือกการวินิจฉัยโรค (ICD-10) --</option>
                    <optgroup label="Atherosclerosis (Lower Extremity)">
                        <option value="I70.20" <?= $data['diagnosis']=='I70.20'?'selected':'' ?>>I70.20 - Unsp. atherosclerosis</option>
                        <option value="I70.21" <?= $data['diagnosis']=='I70.21'?'selected':'' ?>>I70.21 - Atherosclerosis with intermittent claudication</option>
                        <option value="I70.22" <?= $data['diagnosis']=='I70.22'?'selected':'' ?>>I70.22 - Atherosclerosis with rest pain</option>
                        <option value="I70.23" <?= $data['diagnosis']=='I70.23'?'selected':'' ?>>I70.23 - Atherosclerosis with ulceration</option>
                        <option value="I70.24" <?= $data['diagnosis']=='I70.24'?'selected':'' ?>>I70.24 - Atherosclerosis with gangrene</option>
                    </optgroup>
                    <optgroup label="Diabetes">
                        <option value="E11.5" <?= $data['diagnosis']=='E11.5'?'selected':'' ?>>E11.5 - T2DM with peripheral complications</option>
                    </optgroup>
                </select>
            </div>
        </div>
        <div id="tab2" class="tab-content space-y-6">
    <h3 class="text-lg font-semibold text-purple-700 mb-2 flex items-center gap-2">
        <i data-lucide="stethoscope" class="w-4 h-4"></i> ปัจจัยเสี่ยง (Risk Factors)
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php 
        $risks = [
            'Malnutrition' => 'malnutrition',
            'Autoimmune Disease' => 'autoimmune',
            'Bedridden' => 'bedridden',
            'Difficult Line' => 'difficult_line'
        ];

        // กำหนด Class สำหรับปุ่มที่ "ถูกเลือก" (สีม่วง) และ "ไม่ถูกเลือก" (สีขาว/เทา)
        $activeClass = "bg-purple-600 text-white shadow-md";
        $inactiveClass = "bg-white text-slate-400 hover:text-purple-600";

        foreach($risks as $label => $key): 
            $val = $data[$key] ?? 'No'; // ค่าเริ่มต้นเป็น No
        ?>
        <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-purple-200 transition-colors toggle-group">
            <span class="text-sm font-medium text-slate-700"><?= $label ?></span>
            
            <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
            
            <div class="flex bg-slate-100 rounded-lg p-1 border border-slate-200 h-[40px]">
                <button type="button" 
                        class="toggle-btn px-4 text-sm rounded-md transition-all font-medium <?= $val=='No' ? $activeClass : $inactiveClass ?>" 
                        data-value="No">No</button>
                
                <button type="button" 
                        class="toggle-btn px-4 text-sm rounded-md transition-all font-medium <?= $val=='Yes' ? $activeClass : $inactiveClass ?>" 
                        data-value="Yes">Yes</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

       <div id="tab3" class="tab-content space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-purple-700 flex items-center gap-2">
            <i data-lucide="activity" class="w-5 h-5"></i> Site care (Line Management)
        </h3>
        <span class="text-xs text-slate-500">เลือกหมายเลข Line เพื่อกรอกข้อมูล</span>
    </div>

    <div class="grid grid-cols-5 md:grid-cols-8 gap-2 mb-6" id="lineSelectors">
        <?php for($i=1; $i<=15; $i++): ?>
            <button type="button" onclick="setActiveLine(<?= $i ?>)" id="btnLine<?= $i ?>" 
                class="line-nav-btn h-10 rounded font-medium text-sm transition-all border bg-white text-slate-500 border-slate-200 hover:border-purple-300 hover:text-purple-600">
                Line <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>

    <div id="lineDetailContainer" class="bg-slate-50 border border-slate-200 rounded-lg p-4 slide-in mt-4 hidden">
        <h4 class="font-bold text-purple-800 border-b border-purple-100 pb-2 mb-4 flex items-center gap-2">
            <div class="flex items-center gap-2 flex-1">
                <span id="displayLineNumber" class="bg-purple-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                รายละเอียด Line <span id="displayLineLabel">1</span>
            </div>
            <button type="button" onclick="clearLine()" class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded border border-transparent hover:border-red-200 transition-all flex items-center gap-1"><i data-lucide="trash-2" class="w-3 h-3"></i> ล้างข้อมูล</button>
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded border border-slate-200">
                <label class="block text-sm font-bold text-slate-700 mb-2">ตำแหน่ง (Location)</label>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <select id="line_side" onchange="updateLineField('side', this.value)" class="p-2 border rounded text-sm bg-white">
                        <option value="">- ข้าง (Side) -</option>
                        <option value="Left">ซ้าย (Left)</option>
                        <option value="Right">ขวา (Right)</option>
                    </select>
                    <select id="line_position" onchange="updateLineField('position', this.value)" class="p-2 border rounded text-sm bg-white">
                        <option value="">- ตำแหน่ง -</option>
                        <option value="Dorsum Hand">หลังมือ</option>
                        <option value="Forearm">ท้องแขน</option>
                        <option value="Antecubital">ข้อพับแขน</option>
                        <option value="Upper Arm">ต้นแขน</option>
                        <option value="Foot">เท้า</option>
                        <option value="Jugular">คอ</option>
                        <option value="Subclavian">ไหปลาร้า</option>
                        <option value="Femoral">ขาหนีบ</option>
                    </select>
                </div>
                <div class="relative w-full h-64 bg-white border border-slate-200 rounded-lg flex justify-center overflow-hidden">
    
    <svg viewBox="0 0 100 230" class="h-full w-auto opacity-50" fill="#94a3b8">
        <circle cx="50" cy="20" r="15" />
        <rect x="42" y="32" width="16" height="10" />
        <rect x="30" y="42" width="40" height="70" rx="5" />
        <rect x="10" y="45" width="15" height="60" rx="5" transform="rotate(10 10 45)" />
        <rect x="75" y="45" width="15" height="60" rx="5" transform="rotate(-10 75 45)" />
        <rect x="32" y="110" width="16" height="80" rx="5" />
        <rect x="52" y="110" width="16" height="80" rx="5" />
    </svg>
    
    <div id="bodyMarker" class="absolute w-4 h-4 bg-red-600 rounded-full shadow-lg border-2 border-white hidden transition-all duration-300 transform -translate-x-1/2 -translate-y-1/2" 
         style="top: 50%; left: 50%;">
         <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap shadow-sm z-10" id="markerLabel">
            ตำแหน่ง
         </span>
         <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
    </div>
</div>
            </div>

            <div class="space-y-4">
                <div class="bg-green-50 p-3 rounded border border-green-100">
                    <div class="text-xs font-bold text-green-800 mb-2 uppercase flex items-center gap-1"><i data-lucide="play-circle" class="w-3 h-3"></i> Date Start</div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" id="line_date_start" onchange="updateLineField('date_start', this.value); updateLineDuration();" class="p-1.5 text-sm border rounded">
                        <input type="text" id="line_time_start" class="time-picker p-1.5 text-sm border rounded bg-white" placeholder="00:00" onchange="updateLineField('time_start', this.value)">
                        <input type="text" id="line_place_start" onchange="updateLineField('place_start', this.value)" placeholder="Ward/รพ." class="p-1.5 text-sm border rounded col-span-2">
                    </div>
                </div>
                <div class="bg-red-50 p-3 rounded border border-red-100">
                    <div class="text-xs font-bold text-red-800 mb-2 uppercase flex items-center gap-1"><i data-lucide="stop-circle" class="w-3 h-3"></i> Date Off</div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" id="line_date_off" onchange="updateLineField('date_off', this.value); updateLineDuration(); checkPostPhlebitis();" class="p-1.5 text-sm border rounded">
                        <input type="text" id="line_time_off" class="time-picker p-1.5 text-sm border rounded bg-white" placeholder="00:00" onchange="updateLineField('time_off', this.value)">
                        <input type="text" id="line_place_off" onchange="updateLineField('place_off', this.value)" placeholder="Ward/รพ." class="p-1.5 text-sm border rounded col-span-2">
                    </div>
                </div>
            </div>
        </div>
 <div class="mt-4 pt-4 border-t border-slate-200">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white p-3 rounded-xl border border-slate-200 shadow-sm gap-2 mb-4">
        <label class="text-sm font-bold text-slate-800">Phlebitis / Complication:</label>
    </div>

    <div class="bg-red-50 p-4 rounded border border-red-200 space-y-4">
        <label class="block text-xs font-bold text-red-700 mb-2 uppercase">เลือกหัวข้อ (Select One):</label>
        <div class="flex flex-wrap gap-2" id="compTypeContainer">
            <?php 
            $compTypes = ['1. Phlebitis', '2. Infiltration Scale', '3. Extravasation Scale', '4.POST Phlebitis', '5.PLABST'];
            foreach($compTypes as $type): ?>
                <button type="button" onclick="setCompType('<?= $type ?>')" 
                    class="comp-btn px-3 py-2 rounded text-sm border bg-white text-red-800 border-red-200 hover:border-red-400 transition-all">
                    <?= $type ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div id="compDetailsSection" class="hidden space-y-4 pt-4 border-t border-red-200 animate-fade-in">
            <div class="font-bold text-red-800 text-sm" id="selectedCompTitle">Details</div>
            
            <div>
                <label class="block text-xs text-red-700 mb-2">Level (0-4):</label>
                <div class="flex gap-2">
                    <?php for($l=0; $l<=4; $l++): ?>
                        <button type="button" onclick="setCompLevel(<?= $l ?>)" 
                            class="level-btn w-10 h-10 rounded-full border bg-white text-slate-600 flex items-center justify-center font-bold text-sm transition-all hover:border-red-400">
                            <?= $l ?>
                        </button>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-red-700 mb-1" id="compDateLabel">Onset Time (Date/Time):</label>
                    <div class="flex gap-2">
                        <input type="date" id="comp_date" onchange="updateLineField('comp_date', this.value); checkPostPhlebitis();" class="w-full p-2 text-sm border rounded">
                        <input type="text" id="comp_time" class="time-picker w-full p-2 text-sm border rounded bg-white" placeholder="00:00" onchange="updateLineField('comp_time', this.value)">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-red-700 mb-1">Ward / หน่วยงาน:</label>
                    <input type="text" id="comp_ward" onchange="updateLineField('comp_ward', this.value)" class="w-full p-2 text-sm border rounded" placeholder="ระบุหน่วยงาน">
                </div>
            </div>

            <div id="postPhlebitisWarning" class="hidden">
                <label class="block text-xs text-red-700 mb-1 font-bold">Duration Post Phlebitis (day):</label>
                <input type="number" id="post_duration_display" readonly class="w-full p-2 text-sm border rounded font-bold bg-red-100 text-red-700 border-red-400">
                <p class="text-[10px] text-red-600 mt-1 font-bold" id="post_warning_text" style="display:none;">* ระยะเวลาต้องอย่างน้อย 3 วัน จึงจะบันทึกข้อมูลได้</p>
            </div>

            <div id="cultureResultSection" class="hidden space-y-2">
                <label class="block text-xs text-red-700 mb-1 font-bold uppercase">ผลเพาะเชื้อ (Culture Result):</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setCulture('1. Staphylococcus aureus')" class="culture-btn px-3 py-2 rounded text-xs border bg-white text-orange-800 border-orange-200">1. Staphylococcus aureus</button>
                    <button type="button" onclick="setCulture('2. Gram negative bacilli')" class="culture-btn px-3 py-2 rounded text-xs border bg-white text-orange-800 border-orange-200">2. Gram negative bacilli</button>
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="mt-4 pt-4 border-t border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">Line Use (เลือก 1 ข้อ)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="lineUseOptions">
                        <?php 
                        $options = ['1. Solution', '2. Antibiotic', '3. High Alert Drug', '4. TPN'];
                        foreach($options as $opt): ?>
                            <button type="button" onclick="setLineUse('<?= $opt ?>')" 
                                class="line-use-btn p-2 rounded-lg text-xs font-medium text-left border bg-white text-slate-600 hover:border-purple-300">
                                <?= $opt ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">On Duration Time</label>
                    <div class="flex items-center gap-2">
                        <div id="line_duration_display" class="text-3xl font-bold text-purple-600 bg-white px-4 py-2 rounded border border-purple-200 shadow-inner">0</div>
                        <span class="text-sm text-slate-500">วัน (Days)</span>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
    
</div>
     <div id="tab4" class="tab-content space-y-6">
    <h3 class="text-lg font-semibold text-purple-700 mb-2 flex items-center gap-2">
        <i data-lucide="pill" class="w-4 h-4"></i> Drug use
    </h3>

    <datalist id="drug_list">
        </datalist>

    <div class="bg-slate-50 p-4 rounded border border-slate-100 shadow-sm">
        <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs">4.1</span> Antibiotic
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อยา Antibiotic</label>
                <input type="text" name="antibiotic_name" value="<?= htmlspecialchars($data['antibiotic_name'] ?? '') ?>" 
                       list="drug_list" 
                       class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500" 
                       placeholder="พิมพ์ชื่อยาที่ต้องการ..." autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">วันที่เริ่มให้ยา</label>
                <input type="date" name="antibiotic_date" value="<?= $data['antibiotic_date'] ?? '' ?>" class="w-full p-2 border rounded bg-white">
            </div>
        </div>
    </div>

    <div class="bg-red-50 p-4 rounded border border-red-100 shadow-sm">
        <h4 class="font-bold text-red-800 mb-3 flex items-center gap-2">
            <span class="bg-red-200 text-red-800 px-2 py-0.5 rounded text-xs">4.2</span> High Alert Drug
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-red-800 mb-1">ชื่อยา High Alert</label>
                <input type="text" name="high_alert_name" value="<?= htmlspecialchars($data['high_alert_name'] ?? '') ?>" 
                       list="drug_list" 
                       class="w-full p-2 border rounded border-red-200 focus:ring-2 focus:ring-red-500" 
                       placeholder="พิมพ์ชื่อยาที่ต้องการ..." autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-medium text-red-800 mb-1">วันที่เริ่มให้ยา</label>
                <input type="date" name="high_alert_date" value="<?= $data['high_alert_date'] ?? '' ?>" class="w-full p-2 border rounded border-red-200 bg-white">
            </div>
        </div>
    </div>
</div>
       <div id="tab5" class="tab-content space-y-6">
    <h3 class="text-lg font-semibold text-purple-700 mb-2 flex items-center gap-2">
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php 
        // กำหนดรายการสารน้ำและชื่อตัวแปร
        $solutions = [
            '5.1 0.9% NaCl (NSS)' => 'sol_nss',
            '5.2 5% D/N/2' => 'sol_d5',
            '5.3 Ringer\'s Lactate' => 'sol_rl',
            '5.4 Acetar' => 'sol_acetar',
            '5.5 TPN' => 'sol_tpn'
        ];

        // Class สำหรับปุ่ม Active (ม่วง) และ Inactive (ขาว)
        $activeClass = "bg-purple-600 text-white shadow-md";
        $inactiveClass = "bg-white text-slate-400 hover:text-purple-600";

        foreach($solutions as $label => $key): 
            $val = $data[$key] ?? 'No'; // ค่าเริ่มต้นเป็น No
        ?>
        <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-purple-200 transition-colors toggle-group">
            <span class="text-sm font-medium text-slate-700"><?= $label ?></span>
            
            <input type="hidden" name="<?= $key ?>" value="<?= $val ?>">
            
            <div class="flex bg-slate-100 rounded-lg p-1 border border-slate-200 h-[40px]">
                <button type="button" 
                        class="toggle-btn px-4 text-sm rounded-md transition-all font-medium <?= $val=='No' ? $activeClass : $inactiveClass ?>" 
                        data-value="No">No</button>
                <button type="button" 
                        class="toggle-btn px-4 text-sm rounded-md transition-all font-medium <?= $val=='Yes' ? $activeClass : $inactiveClass ?>" 
                        data-value="Yes">Yes</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

        <div class="mt-auto pt-8 flex justify-between gap-4 border-t border-slate-100 bg-white z-10">
            <button type="button" id="btnBack" onclick="changeStep(-1)" class="px-6 py-2 border border-slate-300 text-slate-600 rounded hover:bg-slate-50 hidden">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i> ย้อนกลับ
            </button>
            <div class="ml-auto flex gap-2">
                <button type="button" id="btnNext" onclick="changeStep(1)" class="px-8 py-2.5 bg-purple-600 text-white font-bold rounded hover:bg-purple-700 shadow-lg flex items-center gap-2">
                    ถัดไป <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
                <button type="button" id="btnSave" onclick="submitWizard()" class="px-8 py-2.5 bg-green-600 text-white font-bold rounded hover:bg-green-700 shadow-lg flex items-center gap-2 hidden">
                    <i data-lucide="save" class="w-4 h-4"></i> บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Staff Confirmation Modal -->
<div id="staffModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm p-4 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden slide-in">
        <div class="bg-gradient-to-r from-purple-700 to-indigo-800 p-4 flex justify-between items-center">
            <h3 class="text-white font-bold flex items-center gap-2">
                <i data-lucide="user-check" class="text-white"></i>
                ยืนยันผู้บันทึกข้อมูล
            </h3>
            <button type="button" onclick="closeModal()" class="text-purple-100 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="p-6">
            <p class="text-slate-600 mb-4 text-sm bg-purple-50 p-3 rounded border border-purple-100 flex items-center">
                <i data-lucide="info" class="w-4 h-4 inline mr-2 text-purple-500"></i>
                กรุณาระบุชื่อและหน่วยงานก่อนทำรายการถัดไป
            </p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">หน่วยงาน (Ward/Unit)</label>
                    <input type="text" id="modal_staff_ward" class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" placeholder="ระบุหน่วยงาน (ไม่บังคับ)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อ-นามสกุล (ผู้บันทึก)</label>
                    <input type="text" id="modal_staff_name" class="w-full p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" placeholder="ระบุชื่อ-สกุล (ไม่บังคับ)">
                </div>
                
                <div class="flex gap-3 mt-6 pt-2">
                    <button type="button" onclick="closeModal()" class="flex-1 py-2.5 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 font-medium transition-colors">
                        ยกเลิก
                    </button>
                    <button type="button" onclick="confirmStaff()" class="flex-1 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold shadow-md transition-colors flex justify-center items-center gap-2">
                        ยืนยัน <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Wizard Logic ---
    let currentStep = 1;
    const totalSteps = 5;
    let staffConfirmed = false;

    function showStep(step) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => {
            el.style.display = 'none';
            el.classList.remove('active');
        });
        
        // Show current tab
        const currentTab = document.getElementById('tab' + step);
        if(currentTab) {
            currentTab.style.display = 'block';
            currentTab.classList.add('active');
        }

        // Update Progress Bar
        const progress = (step / totalSteps) * 100;
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('step-indicator').innerText = `Step ${step} of ${totalSteps}`;

        // Button Visibility
        const btnBack = document.getElementById('btnBack');
        const btnNext = document.getElementById('btnNext');
        const btnSave = document.getElementById('btnSave');

        if(step === 1) btnBack.classList.add('hidden');
        else btnBack.classList.remove('hidden');

        if(step === totalSteps) {
            btnNext.classList.add('hidden');
            btnSave.classList.remove('hidden');
        } else {
            btnNext.classList.remove('hidden');
            btnSave.classList.add('hidden');
        }
    }

    function changeStep(n) {
        // Validation on Step 1
        if (currentStep === 1 && n === 1) {
            const name = document.getElementById('main_name').value.trim();
            const ward = document.getElementById('main_ward').value;
            
            if (!name || !ward) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูล',
                    text: 'กรุณาระบุ ชื่อ-นามสกุล และ Ward ให้ครบถ้วน',
                    confirmButtonColor: '#9333ea'
                });
                return;
            }

            // Check Staff Confirmation
            if (!staffConfirmed) {
                document.getElementById('staffModal').classList.remove('hidden');
                return; // Stop here, wait for modal
            }
        }

        currentStep += n;
        if (currentStep > totalSteps) currentStep = totalSteps;
        if (currentStep < 1) currentStep = 1;
        showStep(currentStep);
    }

    function confirmStaff() {
        const sName = document.getElementById('modal_staff_name').value.trim();
        const sWard = document.getElementById('modal_staff_ward').value.trim();
        
        document.getElementById('hidden_staff_name').value = sName;
        document.getElementById('hidden_staff_ward').value = sWard;
        
        staffConfirmed = true;
        document.getElementById('staffModal').classList.add('hidden');
        changeStep(1); // Resume next step
    }
    
    function closeModal() {
        document.getElementById('staffModal').classList.add('hidden');
    }

    function submitWizard() {
        Swal.fire({
            title: 'บันทึกเสร็จสิ้น!',
            text: 'กำลังบันทึกข้อมูลเข้าสู่ระบบ...',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            document.getElementById('patientForm').submit();
        });
    }

    // Initialize Wizard
    document.addEventListener("DOMContentLoaded", () => {
        showStep(1);
    });

    $(document).ready(function() {
        // เรียกใช้ Flatpickr กับ input ที่มี class "time-picker"
        $(".time-picker").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i", // H is 24-hour format (00-23)
            time_24hr: true,   // บังคับโหมด 24 ชั่วโมง
            defaultDate: null  // ไม่ต้องตั้งค่าเริ่มต้น (หรือจะใส่ "12:00" ก็ได้)
        });

        // เรียกใช้งานครั้งแรกเพื่อแสดงสีปุ่มที่มีข้อมูลอยู่แล้ว
        updateLineButtons();
    });

    // ข้อมูลโรงพยาบาลจำลอง (Mock Data)
    const mockHospitalData = [
        // --- จังหวัดสงขลา ---
        { hospital: "โรงพยาบาลหาดใหญ่", province: "สงขลา" },
        { hospital: "โรงพยาบาลสงขลา", province: "สงขลา" },
        { hospital: "โรงพยาบาลสงขลานครินทร์", province: "สงขลา" },
        { hospital: "โรงพยาบาลจิตเวชสงขลาราชนครินทร์", province: "สงขลา" },
        { hospital: "โรงพยาบาลจะนะ", province: "สงขลา" },
        { hospital: "โรงพยาบาลนาหม่อม", province: "สงขลา" },
        { hospital: "โรงพยาบาลบางกล่ำ", province: "สงขลา" },
        { hospital: "โรงพยาบาลระโนด", province: "สงขลา" },
        { hospital: "โรงพยาบาลรัตภูมิ", province: "สงขลา" },
        { hospital: "โรงพยาบาลสทิงพระ", province: "สงขลา" },
        { hospital: "โรงพยาบาลสะบ้าย้อย", province: "สงขลา" },
        { hospital: "โรงพยาบาลสะเดา", province: "สงขลา" },
        { hospital: "โรงพยาบาลควนเนียง", province: "สงขลา" },
        { hospital: "โรงพยาบาลเทพา", province: "สงขลา" },
        { hospital: "โรงพยาบาลนาทวี", province: "สงขลา" },
        { hospital: "โรงพยาบาลสิงหนคร", province: "สงขลา" },
        { hospital: "โรงพยาบาลกระแสสินธุ์", province: "สงขลา" },
        { hospital: "โรงพยาบาลคลองหอยโข่ง", province: "สงขลา" },
        // --- เอกชน สงขลา ---
        { hospital: "โรงพยาบาลศิครินทร์หาดใหญ่", province: "สงขลา" },
        { hospital: "โรงพยาบาลราษฎร์ยินดี", province: "สงขลา" },
        { hospital: "โรงพยาบาลกรุงเทพหาดใหญ่", province: "สงขลา" },
        { hospital: "โรงพยาบาลมิตรภาพสามัคคี (มูลนิธิท่งเซียเซี่ยงตึ๊ง)", province: "สงขลา" },
        // --- สตูล ---
        { hospital: "โรงพยาบาลสตูล", province: "สตูล" },
        { hospital: "โรงพยาบาลละงู", province: "สตูล" },
        { hospital: "โรงพยาบาลควนโดน", province: "สตูล" },
        // --- ตรัง ---
        { hospital: "โรงพยาบาลตรัง", province: "ตรัง" },
        { hospital: "โรงพยาบาลห้วยยอด", province: "ตรัง" },
        { hospital: "โรงพยาบาลกันตัง", province: "ตรัง" },
        // --- พัทลุง ---
        { hospital: "โรงพยาบาลพัทลุง", province: "พัทลุง" },
        { hospital: "โรงพยาบาลควนขนุน", province: "พัทลุง" },
        { hospital: "โรงพยาบาลตะโหมด", province: "พัทลุง" },
        // --- ปัตตานี ---
        { hospital: "โรงพยาบาลปัตตานี", province: "ปัตตานี" },
        { hospital: "โรงพยาบาลโคกโพธิ์", province: "ปัตตานี" },
        { hospital: "โรงพยาบาลสายบุรี", province: "ปัตตานี" },
        // --- ยะลา ---
        { hospital: "โรงพยาบาลยะลา", province: "ยะลา" },
        { hospital: "โรงพยาบาลเบตง", province: "ยะลา" },
        { hospital: "โรงพยาบาลยะหา", province: "ยะลา" },
        // --- นราธิวาส ---
        { hospital: "โรงพยาบาลนราธิวาสราชนครินทร์", province: "นราธิวาส" },
        { hospital: "โรงพยาบาลสุไหงโก-ลก", province: "นราธิวาส" },
        { hospital: "โรงพยาบาลตากใบ", province: "นราธิวาส" },
        // --- อื่นๆ ---
        { hospital: "อื่นๆ", province: "" }
    ];
    
    $(document).ready(function() {
        // 1. เตรียมข้อมูลสำหรับ Select2
        // แปลงข้อมูล Mock Data ให้อยู่ในรูปแบบที่ Select2 เข้าใจ (id, text)
        var select2Data = mockHospitalData.map(function(item) {
            return {
                id: item.hospital,
                text: item.hospital,
                province: item.province // ฝากข้อมูลจังหวัดไว้ด้วย
            };
        });

        // 2. เริ่มต้นใช้งาน Select2
        $('#hospital_select').select2({
            data: select2Data, // ใส่ข้อมูล Mock เข้าไปตรงนี้
            placeholder: '-- พิมพ์ค้นหาโรงพยาบาล --',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "ไม่พบข้อมูล";
                }
            }
        });

        // 3. เมื่อเลือกโรงพยาบาล -> ให้ดึงจังหวัดมาใส่ช่องข้างล่าง
        $('#hospital_select').on('select2:select', function(e) {
            var data = e.params.data;
            var selectedProvince = data.province || ''; // ดึงค่าจังหวัดจากข้อมูลที่ฝากไว้
            
            // ถ้าเป็น "อื่นๆ" ให้เปิดให้พิมพ์จังหวัดเอง (หรือจะปล่อยว่างก็ได้)
            if(data.id === 'อื่นๆ') {
                 $('#provinceInput').val('').prop('readonly', false).focus();
            } else {
                 $('#provinceInput').val(selectedProvince).prop('readonly', true);
            }
        });

        // 4. เมื่อกดลบข้อมูล (Clear)
        $('#hospital_select').on('select2:unselecting', function(e) {
            $('#provinceInput').val('').prop('readonly', true);
        });
        
        // ** กรณีแก้ไข (Edit Mode): ถ้ามีค่าเดิมอยู่แล้ว ให้ดึงจังหวัดมาแสดงทันที **
        var initialHospital = "<?= $data['refer_hospital'] ?? '' ?>";
        if(initialHospital) {
            // Set value for Select2
            $('#hospital_select').val(initialHospital).trigger('change');
            
            var found = mockHospitalData.find(x => x.hospital === initialHospital);
            if(found) {
                $('#provinceInput').val(found.province);
            }
        }
    });

    // 2. Logic คำนวณวันนอน (LOS)
    function calcLOS() {
        const admit = new Date(document.getElementById('admitDate').value);
        const discharge = new Date(document.getElementById('dischargeDate').value);
        
        if (admit && discharge && !isNaN(admit) && !isNaN(discharge)) {
            const diffTime = Math.abs(discharge - admit);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('losInput').value = diffDays;
        } else {
            document.getElementById('losInput').value = 0;
        }
    }

    // 3. Logic ปุ่ม Gender (ชาย/หญิง)
    function setGender(val) {
        document.getElementById('genderInput').value = val;
        // Reset Style
        document.getElementById('btnMale').className = "flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-700";
        document.getElementById('btnFemale').className = "flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-slate-700";
        
        // Active Style
        if(val === 'ชาย') {
            document.getElementById('btnMale').className = "flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm font-bold border border-slate-100";
        } else {
            document.getElementById('btnFemale').className = "flex-1 text-sm rounded-md transition-all flex items-center justify-center gap-2 bg-white text-pink-500 shadow-sm font-bold border border-slate-100";
        }
    }

    // 4. Logic ปุ่ม Line On (Yes/No)
    function setLineOn(val) {
        document.getElementById('lineOnInput').value = val;
        document.getElementById('btnLineNo').className = "px-4 text-sm rounded-md transition-all text-slate-500 hover:text-purple-600";
        document.getElementById('btnLineYes').className = "px-4 text-sm rounded-md transition-all text-slate-500 hover:text-purple-600";
        
        if(val === 'Yes') {
            document.getElementById('btnLineYes').className = "px-4 text-sm rounded-md transition-all bg-purple-600 text-white shadow-sm font-bold";
            
            const dDate = document.getElementById('dischargeDate');
            dDate.disabled = false;
            dDate.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-400');
            dDate.classList.add('bg-white');
        } else {
            document.getElementById('btnLineNo').className = "px-4 text-sm rounded-md transition-all bg-purple-600 text-white shadow-sm font-bold";
            
            const dDate = document.getElementById('dischargeDate');
            dDate.disabled = true;
            dDate.classList.remove('bg-white');
            dDate.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-400');
            dDate.value = '';
            calcLOS();
        }
    }
    
    // Logic ปุ่ม Toggle (Tab 2) - แก้ไขใหม่
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const parent = this.closest('.toggle-group');
            const hiddenInput = parent.querySelector('input[type="hidden"]');
            
            // 1. อัปเดตค่าลง Input (No หรือ Yes)
            hiddenInput.value = this.dataset.value;

            // 2. รีเซ็ต "ทุกปุ่ม" ในกลุ่มให้เป็นสีปกติ (สีเทา/ขาว)
            parent.querySelectorAll('.toggle-btn').forEach(b => {
                b.className = 'toggle-btn px-4 text-sm rounded-md transition-all font-medium bg-white text-slate-400 hover:text-purple-600';
            });

            // 3. ตั้งค่า "ปุ่มที่กด" ให้เป็นสีม่วง (Active)
            this.className = 'toggle-btn px-4 text-sm rounded-md transition-all font-medium bg-purple-600 text-white shadow-md';
        });
    });
    // --- ส่วนจัดการ Site Care (JSON Data) ---
    let currentLineIdx = null;
    let allLines = {};

    // สร้าง Object เก็บข้อมูล 15 Line
    for(let i=1; i<=15; i++) {
        allLines[i] = { 
            side:'', position:'', date_start:'', time_start:'', place_start:'', 
            date_off:'', time_off:'', place_off:'', line_use:'', 
            comp_type:'', comp_level:'', comp_date:'', comp_time:'', comp_ward:'', culture_result:'',
            duration: 0, post_phlebitis_duration: 0
        };
    }

    // --- Load Saved Data (PHP Injection) ---
    try {
        // ดึงข้อมูล JSON จาก PHP มาใส่ในตัวแปร JS
        const savedLinesData = <?= !empty($data['lines_json']) ? $data['lines_json'] : '{}' ?>;
        for (const k in savedLinesData) {
            if (allLines[k]) {
                allLines[k] = { ...allLines[k], ...savedLinesData[k] };
            }
        }
        // อัปเดตค่าลงใน input hidden ทันที
        document.getElementById("lines_json").value = JSON.stringify(allLines);
    } catch (e) {
        console.error("Error loading lines data", e);
    }

    // Helper: อัปเดตสีปุ่ม Line ตามสถานะ (Active / Has Data / Empty)
    function updateLineButtons() {
        for(let i=1; i<=15; i++) {
            const btn = document.getElementById("btnLine" + i);
            if(!btn) continue;

            // ตรวจสอบว่า Line นี้มีข้อมูลสำคัญหรือไม่
            const d = allLines[i];
            const hasData = d.date_start || d.side || d.position || d.date_off;

            if (currentLineIdx === i) {
                // สถานะ Active (กำลังเลือกอยู่) - สีม่วงเข้ม
                btn.className = "line-nav-btn h-10 rounded font-medium text-sm transition-all border bg-purple-600 text-white border-purple-600 shadow-md scale-105";
            } else if (hasData) {
                // สถานะ Has Data (มีข้อมูลบันทึกไว้) - สีม่วงอ่อน
                btn.className = "line-nav-btn h-10 rounded font-medium text-sm transition-all border bg-purple-100 text-purple-700 border-purple-300 font-bold hover:border-purple-400";
            } else {
                // สถานะ Empty (ว่างเปล่า) - สีขาวปกติ
                btn.className = "line-nav-btn h-10 rounded font-medium text-sm transition-all border bg-white text-slate-500 border-slate-200 hover:border-purple-300";
            }
        }
    }

    // ฟังก์ชันล้างข้อมูล Line
    function clearLine() {
        if(!currentLineIdx) return;
        if(!confirm('ต้องการล้างข้อมูล Line ' + currentLineIdx + ' ใช่หรือไม่?')) return;

        allLines[currentLineIdx] = { 
            side:'', position:'', date_start:'', time_start:'', place_start:'', 
            date_off:'', time_off:'', place_off:'', line_use:'', 
            comp_type:'', comp_level:'', comp_date:'', comp_time:'', comp_ward:'', culture_result:'',
            duration: 0, post_phlebitis_duration: 0
        };
        
        document.getElementById("lines_json").value = JSON.stringify(allLines);
        setActiveLine(currentLineIdx); // Reload UI
    }

    // ฟังก์ชันเลือก Line (1-15)
    function setActiveLine(num) {
        currentLineIdx = num;
        
        // 1. อัปเดตสีปุ่ม
        updateLineButtons();

        // 2. แสดง Container และอัปเดตหัวข้อ
        document.getElementById("lineDetailContainer").classList.remove("hidden");
        document.getElementById("displayLineNumber").innerText = num;
        document.getElementById("displayLineLabel").innerText = num;
        
        // 3. ดึงข้อมูลจาก Memory มาใส่ใน Input
        const data = allLines[num];
        document.getElementById("line_side").value = data.side;
        document.getElementById("line_position").value = data.position;
        document.getElementById("line_date_start").value = data.date_start;
        
        // Handle Flatpickr for time inputs
        if(document.getElementById("line_time_start")._flatpickr) {
            document.getElementById("line_time_start")._flatpickr.setDate(data.time_start || "");
        } else {
            document.getElementById("line_time_start").value = data.time_start;
        }

        document.getElementById("line_place_start").value = data.place_start;
        document.getElementById("line_date_off").value = data.date_off;
        
        if(document.getElementById("line_time_off")._flatpickr) {
            document.getElementById("line_time_off")._flatpickr.setDate(data.time_off || "");
        } else {
            document.getElementById("line_time_off").value = data.time_off;
        }

        document.getElementById("line_place_off").value = data.place_off;
        
        document.getElementById("comp_date").value = data.comp_date;
        
        if(document.getElementById("comp_time")._flatpickr) {
            document.getElementById("comp_time")._flatpickr.setDate(data.comp_time || "");
        } else {
            document.getElementById("comp_time").value = data.comp_time;
        }
        
        document.getElementById("comp_ward").value = data.comp_ward;

        // 4. อัปเดตสถานะปุ่มต่างๆ (Line Use, Complication, Level)
        updateUIState(data);
        updateLineDuration();
        checkPostPhlebitis();
    }

    // ฟังก์ชันอัปเดตข้อมูลเมื่อมีการพิมพ์/เปลี่ยนค่า
    function updateLineField(field, val) {
        if(!currentLineIdx) return;
        allLines[currentLineIdx][field] = val;
        document.getElementById("lines_json").value = JSON.stringify(allLines);
        // อัปเดตสีปุ่มทันทีเมื่อมีการกรอกข้อมูลสำคัญ
        if(['date_start', 'side', 'position'].includes(field)) updateLineButtons();
    }

    // อัปเดตหน้าจอตามข้อมูลที่มี
    function updateUIState(data) {
        // Line Use Buttons
        document.querySelectorAll(".line-use-btn").forEach(btn => {
            btn.className = (btn.innerText.trim() === data.line_use) 
                ? "line-use-btn p-2 rounded-lg text-xs font-medium text-left border bg-purple-600 text-white shadow-sm" 
                : "line-use-btn p-2 rounded-lg text-xs font-medium text-left border bg-white text-slate-600 hover:border-purple-300";
        });

        // Complication Type Buttons
        document.querySelectorAll(".comp-btn").forEach(btn => {
            btn.className = (btn.innerText.trim() === data.comp_type) 
                ? "comp-btn px-3 py-2 rounded text-sm border bg-red-600 text-white shadow" 
                : "comp-btn px-3 py-2 rounded text-sm border bg-white text-red-800 hover:border-red-400";
        });

        // แสดง/ซ่อน Section รายละเอียด Complication
        const detailsSection = document.getElementById("compDetailsSection");
        if(data.comp_type) {
            detailsSection.classList.remove("hidden");
            document.getElementById("selectedCompTitle").innerText = data.comp_type + " Details";
            
            // ปรับ Label ตามประเภท
            document.getElementById("compDateLabel").innerText = (data.comp_type === "4.POST Phlebitis") ? "POST phlebitis date & Time:" : "Onset Time:";
            
            // แสดง/ซ่อน Warning และ Culture
            document.getElementById("postPhlebitisWarning").classList.toggle("hidden", data.comp_type !== "4.POST Phlebitis");
            document.getElementById("cultureResultSection").classList.toggle("hidden", data.comp_type !== "5.PLABST");
        } else {
            detailsSection.classList.add("hidden");
        }

        // Level Buttons
        document.querySelectorAll(".level-btn").forEach(btn => {
            btn.className = (parseInt(btn.innerText) === parseInt(data.comp_level)) 
                ? "level-btn w-10 h-10 rounded-full border bg-red-600 text-white shadow font-bold flex items-center justify-center" 
                : "level-btn w-10 h-10 rounded-full border bg-white text-slate-600 flex items-center justify-center hover:border-red-400";
        });

        // Culture Buttons
        document.querySelectorAll(".culture-btn").forEach(btn => {
            btn.className = (btn.innerText.trim() === data.culture_result)
                ? "culture-btn px-3 py-2 rounded text-xs border bg-orange-500 text-white border-orange-500 shadow"
                : "culture-btn px-3 py-2 rounded text-xs border bg-white text-orange-800 border-orange-200 hover:bg-orange-50";
        });
    }

    // Setter Functions (เรียกจาก onclick ใน HTML)
    function setLineUse(val) {
        updateLineField('line_use', val);
        updateUIState(allLines[currentLineIdx]);
    }

    function setCompType(type) {
        updateLineField('comp_type', type);
        // รีเซ็ตค่าลูกเมื่อเปลี่ยนประเภทหลัก
        updateLineField('comp_level', '');
        updateLineField('culture_result', '');
        updateUIState(allLines[currentLineIdx]);
        checkPostPhlebitis();
    }

    function setCompLevel(lvl) {
        updateLineField('comp_level', lvl);
        updateUIState(allLines[currentLineIdx]);
    }

    function setCulture(res) {
        updateLineField('culture_result', res);
        updateUIState(allLines[currentLineIdx]);
    }

    // คำนวณระยะเวลา Line On
    function updateLineDuration() {
        const start = document.getElementById("line_date_start").value;
        const off = document.getElementById("line_date_off").value;
        let days = 0;
        if(start && off) {
            const d1 = new Date(start);
            const d2 = new Date(off);
            days = Math.ceil(Math.abs(d2 - d1) / (1000 * 60 * 60 * 24));
        }
        document.getElementById("line_duration_display").innerText = days;
        updateLineField('duration', days);
    }

    // ตรวจสอบเงื่อนไข POST Phlebitis (ต้อง >= 3 วัน)
    function checkPostPhlebitis() {
        const type = allLines[currentLineIdx]?.comp_type;
        if(type !== "4.POST Phlebitis") return;

        const compDate = document.getElementById("comp_date").value;
        const dateOff = document.getElementById("line_date_off").value;
        const display = document.getElementById("post_duration_display");
        const warningText = document.getElementById("post_warning_text");

        if(compDate && dateOff) {
            const dComp = new Date(compDate);
            const dOff = new Date(dateOff);
            const diffDays = Math.ceil((dComp - dOff) / (1000 * 60 * 60 * 24));
            
            display.value = diffDays;
            updateLineField('post_phlebitis_duration', diffDays);

            if(diffDays < 3) {
                display.className = "w-full p-2 text-sm border rounded font-bold bg-red-100 text-red-700 border-red-400";
                warningText.style.display = "block";
            } else {
                display.className = "w-full p-2 text-sm border rounded font-bold bg-green-100 text-green-700 border-green-400";
                warningText.style.display = "none";
            }
        } else {
            display.value = "";
        }
    }
        // แสดง/ซ่อน Section รายละเอียด Complication
        const detailsSection = document.getElementById("compDetailsSection");
        if(data.comp_type) {
            detailsSection.classList.remove("hidden");
            document.getElementById("selectedCompTitle").innerText = data.comp_type + " Details";
            
            // ปรับ Label ตามประเภท
            document.getElementById("compDateLabel").innerText = (data.comp_type === "4.POST Phlebitis") ? "POST phlebitis date & Time:" : "Onset Time:";
            
            // แสดง/ซ่อน Warning และ Culture
            document.getElementById("postPhlebitisWarning").classList.toggle("hidden", data.comp_type !== "4.POST Phlebitis");
            document.getElementById("cultureResultSection").classList.toggle("hidden", data.comp_type !== "5.PLABST");
        } else {
            detailsSection.classList.add("hidden");
        }

        // Level Buttons
        document.querySelectorAll(".level-btn").forEach(btn => {
            btn.className = (parseInt(btn.innerText) === parseInt(data.comp_level)) 
                ? "level-btn w-10 h-10 rounded-full border bg-red-600 text-white shadow font-bold flex items-center justify-center" 
                : "level-btn w-10 h-10 rounded-full border bg-white text-slate-600 flex items-center justify-center hover:border-red-400";
        });

        // Culture Buttons
        document.querySelectorAll(".culture-btn").forEach(btn => {
            btn.className = (btn.innerText.trim() === data.culture_result)
                ? "culture-btn px-3 py-2 rounded text-xs border bg-orange-500 text-white border-orange-500 shadow"
                : "culture-btn px-3 py-2 rounded text-xs border bg-white text-orange-800 border-orange-200 hover:bg-orange-50";
        });
    // Setter Functions (เรียกจาก onclick ใน HTML)
    function setLineUse(val) {
        updateLineField('line_use', val);
        updateUIState(allLines[currentLineIdx]);
    }

    function setCompType(type) {
        updateLineField('comp_type', type);
        // รีเซ็ตค่าลูกเมื่อเปลี่ยนประเภทหลัก
        updateLineField('comp_level', '');
        updateLineField('culture_result', '');
        updateUIState(allLines[currentLineIdx]);
        checkPostPhlebitis();
    }

    function setCompLevel(lvl) {
        updateLineField('comp_level', lvl);
        updateUIState(allLines[currentLineIdx]);
    }

    function setCulture(res) {
        updateLineField('culture_result', res);
        updateUIState(allLines[currentLineIdx]);
    }

    // คำนวณระยะเวลา Line On
    function updateLineDuration() {
        const start = document.getElementById("line_date_start").value;
        const off = document.getElementById("line_date_off").value;
        let days = 0;
        if(start && off) {
            const d1 = new Date(start);
            const d2 = new Date(off);
            days = Math.ceil(Math.abs(d2 - d1) / (1000 * 60 * 60 * 24));
        }
        document.getElementById("line_duration_display").innerText = days;
    }

    // ตรวจสอบเงื่อนไข POST Phlebitis (ต้อง >= 3 วัน)
    function checkPostPhlebitis() {
        const type = allLines[currentLineIdx]?.comp_type;
        if(type !== "4.POST Phlebitis") return;

        const compDate = document.getElementById("comp_date").value;
        const dateOff = document.getElementById("line_date_off").value;
        const display = document.getElementById("post_duration_display");
        const warningText = document.getElementById("post_warning_text");

        if(compDate && dateOff) {
            const dComp = new Date(compDate);
            const dOff = new Date(dateOff);
            const diffDays = Math.ceil((dComp - dOff) / (1000 * 60 * 60 * 24));
            
            display.value = diffDays;

            if(diffDays < 3) {
                display.className = "w-full p-2 text-sm border rounded font-bold bg-red-100 text-red-700 border-red-400";
                warningText.style.display = "block";
            } else {
                display.className = "w-full p-2 text-sm border rounded font-bold bg-green-100 text-green-700 border-green-400";
                warningText.style.display = "none";
            }
        } else {
            display.value = "";
        }
    }
  </script>
  <script>
    // รายการยา (Master Drug List)
    const drugList = [
        "ACYCLOVIR (INJ)", "ACYCLOVIR (TAB)", "ALBENDAZOLE (TAB)", "AMIKACIN (INJ.)", 
        "AMOX.+CLAVULANATE TAB (AUGMENTIN 1 G)", "AMOX.+CLAVULANATE INJ(1G+200MG)(CAVUMOX/AUGMENTIN)", 
        "AMOX.+CLAVULANATE SYRUP 457 MG", "AMOXYCILLIN (CAP)", "AMOXYCILLIN DRY SYRUP", 
        "AMPHOTERICIN-B (INJ)", "AMPICILLIN (INJ)", "AMPICILLIN 2 GM+SULBACTAM 1000 MG (Unasyn)", 
        "AZITHROMYCIN (CAP)", "AZITHROMYCIN SUS.", "BENZATHINE PENICILLIN (INJ)", 
        "BIAPENEM (INJ) (OMEGACIN)", "CEFAZOLIN (INJ)", "CEFDINIR (CAP)", "CEFDINIR DRY SYRUP", 
        "CEFIXIME 100 MG CAPSULE", "CEFOPERAZONE+SULBACTAM INJ(SULCEF)500/500", "CEFOTAXIME INJ 1 GM", 
        "CEFTAZIDIME INJ 1 GM", "CEFTAZIDIME/AVIBACTAM 2+0.5G ZAVICEFTA(รวม)", 
        "CEFTOLOZANE 1 G + TAZOBACTAM 500 MG(ZERBAXA) INJ", "CEFTRIAXONE (INJ)", 
        "CEFUROXIME AXETIL (TAB)", "CEPHALEXIN (CAP)", "CEPHALEXIN DRY SYRUP", 
        "CHLORAMPHENICOL 1% (ทาแผล) OINTMENT", "CHLORAMPHENICOL 1% EYE OINTMENT", 
        "CIPROFLOXACIN (INJ)<CIPRACIN>", "CIPROFLOXACIN (TAB)", "CLARITHROMYCIN (TAB)", 
        "CLARITHROMYCIN PROLONGED RELEASE (TAB)", "CLINDAMYCIN (CAP)", "CLINDAMYCIN (INJ)", 
        "CLOTRIMAZOLE 1% CREAM", "CLOTRIMAZOLE VAGINAL (TAB)", "CLOXACILLIN (INJ)", 
        "COLISTIMETHATE SODIUM (INJ)", "CO-TRIMOXAZOLE (INJ) (SULFA.400 MG+TRIM.80 MG)", 
        "CO-TRIMOXAZOLE (SUSP.)(SULFA.200 MG+TRIM.40 MG)", "CO-TRIMOXAZOLE (TAB)(SULFA.400 MG+TRIM.80 MG)", 
        "DEXA.+NEOMYCIN E/E DROP (DEX-OPH)", "DEXA+NEOMYCIN+POLYMYXIN EO.(MAXITROL)", 
        "DICLOXACILLIN (CAP)", "DIETHYLCARBAMAZINE (TAB)", "DOXYCYCLINE (CAP)", 
        "ERTAPENEM (INJ) (INVANZ)", "ERYTHROMYCIN DRY SYRUP", "FAVIPIRAVIR 200 MG TAB(AVIGAN-L)", 
        "FLUCONAZOLE (CAP)", "FLUCONAZOLE (INJ)", "FLUCYTOSINE (CAP)", "FOSFOMYCIN (INJ)", 
        "FOSFOMYCIN GRANULE 3 G/SAC. NEW EN 68", "FUSIDIC ACID 1% EYE DROP (FUCITHALMIC)", 
        "FUSIDIC ACID+HYDROCORTISONE CREAM (FUCIDIN-H)", "GANCICLOVIR (INJ)", "GENTAMICIN (INJ.)", 
        "GENTAMICIN 0.3% EYE DROP", "GRISEOFULVIN (TAB)", "IMIPENEM+CILASTATIN (INJ)", 
        "ITRACONAZOLE (CAP)", "IVERMECTIN (TAB)", "KETOCONAZOLE (TAB)", "LEVOFLOXACIN (INJ)(CRAVIT)", 
        "LEVOFLOXACIN (TAB)", "LINCOMYCIN (INJ)", "LINEZOLID (INJ)", "LIPOSOMAL-AMPHOTERICIN B", 
        "MEBENDAZOLE (SUSPENSION)", "MEROPENEM (INJ)", "MICAFUNGIN (INJ) (MYCAMINE)", 
        "Molnupiravir (CAP)", "MOXIFLOXACIN (TAB)", "MOXIFLOXACIN EYE DROP 0.5%", 
        "MUPIROCIN OINTMENT (BACTROBAN)", "N68-DICLOXACILLIN (CAP)", "N68-KETOCONAZOLE CREAM", 
        "N68-Myda-B cream (Bet+Clotrimazole)", "NATAMYCIN 5% EYE DROP (NATACYN)", 
        "NEOMYCIN+POLYMIXIN+GRAMICIN ED(POLY-OPH)", "NORFLOXACIN (TAB) (FOIL)", 
        "NYSTATIN (SUSPENSION)", "OFLOXACIN (TAB)", "OSELTAMIVIR (CAP)", 
        "PAXLOVID TAB (NIRMATRELVIR 150 +RITONAVIR 100 MG)", "PENICILLIN G (INJ)", 
        "PENICILLIN V (TAB)", "PIPERACILLIN+TAZOBACTAM (Astaz-P)(INJ)", 
        "POLYMYXIN B 50,000 UNIT (INJ)", "POSACONAZOLE (TAB)", "PSEUDOEPHEDRINE (TAB)", 
        "REMDESIVIR (INJ)", "ROXITHROMYCIN (TAB)", "SILVER SULFADIAZINE 1% CREAM", 
        "SITAFLOXACIN (TAB) (GRACEVIT)", "SODIUM FUSIDATE (TAB)(FUCIDIN)", 
        "SULBACTAM (INJ) (SIBATAM)", "SULFADIAZINE (TAB)", "TERRAMYCIN EYE OINT", 
        "TIGECYCLINE (INJ) (TYGACIL-L)", "TIMI CREAM", "TOBRAMYCIN+DEXA. EYE D. (TOBRADEX)", 
        "VALACICLOVIR (TAB) (VALTREX)", "VALGANCICLOVIR (TAB)", "VANCOMYCIN (CAP)", 
        "VANCOMYCIN (INJ)", "VORICONAZOLE (INJ)", "VORICONAZOLE (TAB)", "WHITFIELD'S OINTMENT"
    ];

    $(document).ready(function() {
        const dataList = document.getElementById('drug_list');
        
        // วนลูปสร้าง Option ใส่ใน Datalist
        drugList.forEach(drug => {
            const option = document.createElement('option');
            option.value = drug;
            dataList.appendChild(option);
        });
    });

    $(document).ready(function() {
        const dataList = document.getElementById('drug_list');
        
        // วนลูปสร้าง Option ใส่ใน Datalist
        drugList.forEach(drug => {
            const option = document.createElement('option');
            option.value = drug;
            dataList.appendChild(option);
        });
    });
</script>
<script>
// พิกัดตำแหน่ง (Top %, Left %) เทียบกับกรอบรูป
// หมายเหตุ: ต้องปรับจูนตัวเลข % ให้ตรงกับรูปที่คุณเลือกใช้จริงอีกทีนะครับ
const bodyCoordinates = {
    'Left': { // ซ้ายของคนไข้ (คือด้านขวาของรูป ถ้ามองหน้าตรง)
        'Dorsum Hand': { top: '55%', left: '75%' },  // หลังมือซ้าย
        'Forearm':     { top: '45%', left: '72%' },  // ท้องแขนซ้าย
        'Antecubital': { top: '38%', left: '68%' },  // ข้อพับแขนซ้าย
        'Upper Arm':   { top: '28%', left: '65%' },  // ต้นแขนซ้าย
        'Foot':        { top: '92%', left: '60%' },  // เท้าซ้าย
        'Jugular':     { top: '12%', left: '53%' },  // คอซ้าย
        'Subclavian':  { top: '15%', left: '55%' },  // ไหปลาร้าซ้าย
        'Femoral':     { top: '50%', left: '55%' }   // ขาหนีบซ้าย
    },
    'Right': { // ขวาของคนไข้ (คือด้านซ้ายของรูป)
        'Dorsum Hand': { top: '55%', left: '25%' },
        'Forearm':     { top: '45%', left: '28%' },
        'Antecubital': { top: '38%', left: '32%' },
        'Upper Arm':   { top: '28%', left: '35%' },
        'Foot':        { top: '92%', left: '40%' },
        'Jugular':     { top: '12%', left: '47%' },
        'Subclavian':  { top: '15%', left: '45%' },
        'Femoral':     { top: '50%', left: '45%' }
    }
};

function updateBodyMap() {
    const side = document.getElementById('line_side').value;
    const pos = document.getElementById('line_position').value;
    const marker = document.getElementById('bodyMarker');
    const label = document.getElementById('markerLabel');

    if (side && pos && bodyCoordinates[side] && bodyCoordinates[side][pos]) {
        // ดึงพิกัด
        const coords = bodyCoordinates[side][pos];
        
        // ย้ายจุด
        marker.style.top = coords.top;
        marker.style.left = coords.left;
        
        // เปลี่ยนป้ายชื่อ
        label.innerText = side + ' - ' + pos;
        
        // แสดงจุด
        marker.classList.remove('hidden');
    } else {
        // ซ่อนจุดถ้าเลือกไม่ครบ
        marker.classList.add('hidden');
    }
}

// ผูก Event กับ Dropdown เดิมของคุณ
document.getElementById('line_side').addEventListener('change', updateBodyMap);
document.getElementById('line_position').addEventListener('change', updateBodyMap);

// เรียกใช้ครั้งแรกเผื่อมีค่าเดิม
updateBodyMap();
</script>