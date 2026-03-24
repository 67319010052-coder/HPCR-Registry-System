<?php $p = $_GET['page'] ?? 'dashboard'; ?>
<div class="w-64 bg-slate-900 text-white min-h-screen flex flex-col shadow-lg hidden md:flex">
    
    <div class="p-6 border-b border-purple-900 bg-gradient-to-r from-purple-800 to-indigo-900">
        <div class="flex items-center gap-3 mb-1">
            <div class="bg-white/10 p-2 rounded-lg shadow-inner">
                <i data-lucide="stethoscope" class="w-7 h-7 text-fuchsia-300"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-wide">HPCR System</h1>
            </div>
        </div>
        <p class="text-[10px] text-purple-200 mt-2 opacity-80 uppercase tracking-wider pl-1">Hatyai Peripheral Care Registry</p>
    </div>
    
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a href="?page=dashboard" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='dashboard' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span>Dashboard</span>
        </a>
        <a href="?page=list" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='list' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="users" class="w-5 h-5"></i><span>รายชื่อผู้ป่วย</span>
        </a>
        <a href="?page=register" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='register' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="file-plus" class="w-5 h-5"></i><span>ลงทะเบียน</span>
        </a>
        <a href="?page=manual" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='manual' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="book-open" class="w-5 h-5"></i><span>คู่มือ</span>
        </a>
        <a href="?page=history" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='history' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="history" class="w-5 h-5"></i><span>ประวัติ</span>
        </a>
        <a href="?page=settings" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $p=='settings' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' ?>">
            <i data-lucide="settings" class="w-5 h-5"></i><span>ตั้งค่าระบบ</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800 bg-slate-900/50">
        <a href="logout.php" onclick="return confirm('ยืนยันที่จะออกจากระบบ?');" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-red-400 hover:bg-red-500/10 hover:text-red-300 border border-transparent hover:border-red-500/20 group">
            <i data-lucide="log-out" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
            <span>ออกจากระบบ</span>
        </a>
    </div>
</div>