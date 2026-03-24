<?php
// --- Logic สำหรับการลบข้อมูล ---
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    
    try {
        // ลบข้อมูลจากตาราง patients (และตารางลูกที่เกี่ยวข้องถ้ามีการตั้ง FK cascade หรือต้องลบแยก)
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$del_id]);
        
        // ลบเสร็จให้รีโหลดหน้าเว็บเพื่อเคลียร์ค่า delete_id ออกจาก URL
        echo "<script>window.location.href='?page=list';</script>"; 
        exit();
    } catch (Exception $e) {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "');</script>";
    }
}

$search = $_GET['search'] ?? '';
// Filter Logic
$filteredPatients = $patients;
if($search) {
    $filteredPatients = array_filter($patients, function($p) use ($search) {
        return (strpos($p['hn'], $search) !== false) || 
               (strpos($p['an'], $search) !== false) || 
               (strpos($p['name'], $search) !== false);
    });
}
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="space-y-6 slide-in">
    <h2 class="text-2xl font-bold text-slate-800 border-b pb-2 flex items-center gap-2">
        <i data-lucide="list" class="text-purple-600"></i> รายชื่อผู้ป่วย
    </h2>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row justify-between md:items-center gap-4 bg-slate-50">
            <form class="relative w-full md:w-96" method="GET">
                <input type="hidden" name="page" value="list">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="search" class="w-4 h-4"></i></span>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ค้นหา HN / AN / ชื่อ-สกุล" class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 w-full shadow-sm outline-none">
            </form>
            <div class="text-sm text-slate-500">พบข้อมูล: <strong><?= count($filteredPatients) ?></strong> รายการ</div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm uppercase tracking-wider">
                        <th class="p-4 border-b font-semibold">HN</th>
                        <th class="p-4 border-b font-semibold">AN</th>
                        <th class="p-4 border-b font-semibold">Admit Date</th>
                        <th class="p-4 border-b font-semibold">ชื่อ-สกุล</th>
                        <th class="p-4 border-b font-semibold">Ward Admit</th>
                        <th class="p-4 border-b font-semibold">แก้ไขล่าสุด</th>
                        <th class="p-4 border-b font-semibold text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    <?php if(empty($filteredPatients)): ?>
                        <tr><td colspan="7" class="p-10 text-center text-slate-400"><i data-lucide="search-x" class="w-12 h-12 mx-auto mb-2 opacity-20"></i>ไม่พบข้อมูลที่ค้นหา</td></tr>
                    <?php else: ?>
                        <?php foreach($filteredPatients as $p): ?>
                        <tr class="hover:bg-purple-50 border-b last:border-0 transition-colors">
                            <td class="p-4 font-mono font-medium text-purple-700"><?= $p['hn'] ?></td>
                            <td class="p-4 font-mono"><?= $p['an'] ?></td>
                            <td class="p-4 text-slate-600"><?= thaiDate($p['admit_date']) ?></td>
                            <td class="p-4 font-medium text-slate-900"><?= $p['name'] ?></td>
                            <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold <?= $p['ward']=='ICU'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700' ?>"><?= $p['ward'] ?></span></td>
                            
                            <td class="p-4 text-xs text-slate-500">
                                <?= thaiDate($p['last_updated']) ?> <span class="text-slate-400 ml-1"><?= date('H:i', strtotime($p['last_updated'])) ?></span>
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button type="button" 
                                        onclick="openLabelModal(this)"
                                        data-name="<?= htmlspecialchars($p['name']) ?>"
                                        data-hn="<?= $p['hn'] ?>"
                                        data-an="<?= $p['an'] ?>"
                                        data-age="<?= $p['age'] ?? '-' ?>" 
                                        data-sex="<?= $p['sex'] ?? '-' ?>"
                                        data-ward="<?= $p['ward'] ?>"
                                        data-rights="<?= $p['rights'] ?? '-' ?>"
                                        data-doctor="<?= $p['doctor_name'] ?? '-' ?>"
                                        data-admit="<?= thaiDate($p['admit_date']) ?> <?= date('H:i', strtotime($p['admit_date'])) ?>"
                                        data-idno="<?= $p['id_card'] ?? '-' ?>"
                                        class="text-blue-500 hover:bg-blue-50 p-2 rounded border border-blue-200 bg-white transition-all" 
                                        title="ดูฉลากข้อมูล">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    <a href="?page=register&id=<?= $p['id'] ?>" class="text-amber-500 hover:bg-amber-50 p-2 rounded border border-amber-200 bg-white" title="แก้ไข"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                    <button type="button" onclick="confirmDelete(<?= $p['id'] ?>)" class="text-red-500 hover:bg-red-50 p-2 rounded border border-red-200 bg-white" title="ลบ"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Patient Label Modal -->
<div id="patientLabelModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform scale-95 transition-transform duration-300" id="modalContent">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                <i data-lucide="sticker" class="text-purple-600"></i> ข้อมูลฉลากผู้ป่วย
            </h3>
            <button onclick="closeLabelModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="p-6 bg-slate-50 flex justify-center">
            
            <div class="bg-slate-200/80 p-6 rounded shadow-sm w-full border border-slate-300 font-sarabun relative overflow-hidden" style="max-width: 600px; font-family: 'Sarabun', sans-serif;">
                
                <div class="absolute top-0 left-0 w-full h-8 bg-gradient-to-b from-white/30 to-transparent pointer-events-none"></div>

                <div class="grid grid-cols-12 gap-y-3 text-slate-900 text-lg leading-snug">
                    
                    <div class="col-span-8 font-bold text-xl tracking-tight">
                        ชื่อ : <span id="lbl_name" class="font-normal ml-2">นาย ชม ทองราช</span>
                    </div>
                    <div class="col-span-4 text-right">
                        HN : <span id="lbl_hn" class="font-mono text-xl">8435/59</span>
                    </div>

                    <div class="col-span-8 flex flex-wrap gap-4">
                        <div>เพศ : <span id="lbl_sex">ชาย</span></div>
                        <div>อายุ : <span id="lbl_age">68 ปี</span></div>
                        <div>Ward : <span id="lbl_ward" class="font-semibold">ICU-02</span></div>
                    </div>
                    <div class="col-span-4 text-right">
                        AN : <span id="lbl_an" class="font-mono text-xl">3236/69</span>
                    </div>

                    <div class="col-span-7">
                        สิทธิ : <span id="lbl_rights">บัตรทอง นอกเขต</span>
                    </div>
                    <div class="col-span-5 text-right text-base">
                        วันที่รับ : <span id="lbl_admit">20/ม.ค./69 11:01</span>
                    </div>

                    <div class="col-span-7">
                        แพทย์ : <span id="lbl_doctor">นพ. วิศรุต จิรพงศกร</span>
                    </div>
                    <div class="col-span-5 text-right">
                        IDNo : <span id="lbl_idno" class="font-mono text-base tracking-tighter">3-9306-00433-56-7</span>
                    </div>

                </div>
            </div>

        </div>

        <div class="p-4 border-t bg-slate-50 flex justify-end">
            <button onclick="window.print()" class="mr-2 px-4 py-2 text-slate-600 hover:bg-slate-200 rounded flex gap-2 items-center">
                <i data-lucide="printer" class="w-4 h-4"></i> พิมพ์
            </button>
            <button onclick="closeLabelModal()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded shadow">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันเปิด Modal และใส่ข้อมูล
    function openLabelModal(btn) {
        const modal = document.getElementById('patientLabelModal');
        const content = document.getElementById('modalContent');
        
        // รับค่าจากปุ่มมาใส่ใน Label
        document.getElementById('lbl_name').textContent = btn.dataset.name;
        document.getElementById('lbl_hn').textContent = btn.dataset.hn;
        document.getElementById('lbl_an').textContent = btn.dataset.an;
        document.getElementById('lbl_sex').textContent = btn.dataset.sex;
        document.getElementById('lbl_age').textContent = btn.dataset.age + ' ปี';
        document.getElementById('lbl_ward').textContent = btn.dataset.ward;
        document.getElementById('lbl_rights').textContent = btn.dataset.rights;
        document.getElementById('lbl_doctor').textContent = btn.dataset.doctor;
        document.getElementById('lbl_admit').textContent = btn.dataset.admit;
        document.getElementById('lbl_idno').textContent = btn.dataset.idno;

        // Show Modal with animation
        modal.classList.remove('hidden');
        // เล็กน้อย delay เพื่อให้ CSS transition ทำงาน
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }

    // ฟังก์ชันปิด Modal
    function closeLabelModal() {
        const modal = document.getElementById('patientLabelModal');
        const content = document.getElementById('modalContent');

        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // ปิดเมื่อคลิกพื้นที่ด้านนอก
    document.getElementById('patientLabelModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLabelModal();
        }
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบข้อมูล?',
            text: "ข้อมูลผู้ป่วยและประวัติ Line ทั้งหมดจะหายไป ไม่สามารถกู้คืนได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'ยืนยันลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?page=list&delete_id=' + id;
            }
        });
    }
</script>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">