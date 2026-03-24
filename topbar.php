<div class="md:hidden bg-gradient-to-r from-purple-800 to-indigo-900 text-white p-4 flex justify-between items-center shadow-md">
    <div class="font-bold flex items-center gap-2">
        <div class="bg-white/10 p-1.5 rounded-lg">
            <i data-lucide="stethoscope" class="w-5 h-5 text-fuchsia-300"></i>
        </div>
        <span>HPCR</span>
    </div>
    <div class="flex gap-4">
        <a href="?page=dashboard" class="<?= $_GET['page']=='dashboard'?'text-fuchsia-300':'' ?>"><i data-lucide="layout-dashboard"></i></a>
        <a href="?page=list" class="<?= $_GET['page']=='list'?'text-fuchsia-300':'' ?>"><i data-lucide="users"></i></a>
        <a href="?page=register" class="<?= $_GET['page']=='register'?'text-fuchsia-300':'' ?>"><i data-lucide="file-plus"></i></a>
    </div>
</div>