<?php
// settings.php
// ตรวจสอบการเชื่อมต่อฐานข้อมูล (ถ้ายังไม่มีการเชื่อมต่อ ให้เรียกไฟล์ data.php)
if(!isset($pdo)) {
    require_once 'data.php';
}

// --- ส่วนจัดการ PHP Logic (เพิ่ม/ลบ ผู้ใช้งาน) ---
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. กรณีเพิ่มผู้ใช้งานใหม่
    if (isset($_POST['action']) && $_POST['action'] == 'add_user') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); 
        $name = trim($_POST['name']);
        
        // เช็คว่า Username ซ้ำไหม
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if($stmt->rowCount() > 0){
            $message = '<div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 border border-red-200 flex items-center gap-2"><i data-lucide="alert-circle" class="w-5 h-5"></i> Username นี้มีผู้ใช้งานแล้ว</div>';
        } else {
            // บันทึก (ในที่นี้ใช้ Plain Text ตาม Database เดิมของคุณ ถ้าต้องการความปลอดภัยสูงควรใช้ password_hash)
            $sql = "INSERT INTO users (username, password, name, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([$username, $password, $name])){
                $message = '<div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 border border-green-200 flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5"></i> เพิ่มผู้ใช้งานสำเร็จ</div>';
            }
        }
    }
    
    // 2. กรณีลบผู้ใช้งาน
    if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
        $id = $_POST['user_id'];
        // ป้องกันไม่ให้ลบ Admin คนแรก (ID 1)
        if($id != 1) { 
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 border border-green-200 flex items-center gap-2"><i data-lucide="check-circle" class="w-5 h-5"></i> ลบผู้ใช้งานสำเร็จ</div>';
        } else {
             $message = '<div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 border border-red-200 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-5 h-5"></i> ไม่สามารถลบ Main Admin ได้</div>';
        }
    }
}

// ดึงข้อมูลผู้ใช้งานทั้งหมดมาแสดง
$stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6 slide-in max-w-5xl mx-auto">
    <div class="flex justify-between items-center border-b pb-4">
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <i data-lucide="settings" class="text-purple-600"></i> ตั้งค่าระบบ (System Settings)
        </h2>
    </div>

    <?= $message ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden min-h-[500px]">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-3 flex gap-4">
            <button class="text-sm font-bold text-purple-600 border-b-2 border-purple-600 pb-2 px-2 flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> จัดการผู้ใช้งาน (Users)
            </button>
        </div>
        
        <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h3 class="font-bold text-slate-700 text-lg">รายชื่อผู้ใช้งานในระบบ</h3>
                    <p class="text-xs text-slate-500">จัดการสิทธิ์การเข้าถึงระบบ Add/Remove Users</p>
                </div>
                <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" 
                        class="bg-purple-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-purple-700 shadow-md flex items-center gap-2 transition-all hover:scale-105">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> เพิ่มผู้ใช้งานใหม่
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-100">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="p-4 border-b w-16 text-center">ID</th>
                            <th class="p-4 border-b">Username</th>
                            <th class="p-4 border-b">ชื่อ-สกุล (Display Name)</th>
                            <th class="p-4 border-b">วันที่สร้าง</th>
                            <th class="p-4 border-b text-center w-32">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        <?php foreach($users as $u): ?>
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="p-4 text-center text-slate-400 font-mono"><?= $u['id'] ?></td>
                            <td class="p-4 font-bold text-purple-700">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">
                                        <?= strtoupper(substr($u['username'],0,1)) ?>
                                    </div>
                                    <?= htmlspecialchars($u['username']) ?>
                                </div>
                            </td>
                            <td class="p-4 font-medium"><?= htmlspecialchars($u['name']) ?></td>
                            <td class="p-4 text-slate-500 text-xs">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <?php if($u['id'] != 1): ?>
                                <form method="POST" onsubmit="return confirm('⚠️ ยืนยันการลบผู้ใช้งาน : <?= htmlspecialchars($u['username']) ?> ?');" class="inline">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-white border border-red-100 hover:bg-red-50 p-2 rounded-lg transition-all shadow-sm" title="ลบผู้ใช้งาน">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-[10px] font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded-full border border-purple-200">Main Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(empty($users)): ?>
                <div class="p-8 text-center text-slate-400 bg-slate-50 rounded-lg mt-4 border border-dashed border-slate-200">
                    <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-20"></i>
                    ไม่พบข้อมูลผู้ใช้งาน
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="addUserModal" class="fixed inset-0 bg-slate-900 bg-opacity-60 hidden z-50 flex items-center justify-center backdrop-blur-sm px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden slide-in border border-slate-200">
        <div class="bg-gradient-to-r from-purple-700 to-indigo-800 p-5 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i> เพิ่มผู้ใช้งานใหม่
            </h3>
            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-purple-200 hover:text-white transition-colors bg-white/10 rounded-full p-1 hover:bg-white/20">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        
        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="action" value="add_user">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Username (สำหรับล็อกอิน)</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3 top-2.5 w-5 h-5 text-slate-400"></i>
                    <input type="text" name="username" required class="w-full pl-10 p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all" placeholder="เช่น nurse01">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-2.5 w-5 h-5 text-slate-400"></i>
                    <input type="password" name="password" required class="w-full pl-10 p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all" placeholder="กำหนดรหัสผ่าน">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">ชื่อ-นามสกุล (ที่แสดงในระบบ)</label>
                <div class="relative">
                    <i data-lucide="badge-check" class="absolute left-3 top-2.5 w-5 h-5 text-slate-400"></i>
                    <input type="text" name="name" required class="w-full pl-10 p-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all" placeholder="เช่น สมศรี พยาบาล">
                </div>
            </div>

            <div class="pt-4 flex gap-3">
                <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="flex-1 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">ยกเลิก</button>
                <button type="submit" class="flex-1 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-bold shadow-md transition-all hover:shadow-lg">บันทึกข้อมูล</button>
            </div>
        </form>
    </div>
</div>