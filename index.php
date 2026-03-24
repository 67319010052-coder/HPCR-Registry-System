<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// index.php
require_once 'data.php'; // ดึงข้อมูลและฟังก์ชันกลางมาใช้

include 'header.php';
include 'sidebar.php';
?>

<div class="flex-1 flex flex-col h-screen overflow-hidden">
    <?php include 'topbar.php'; ?>
    
    <main class="flex-1 p-6 overflow-y-auto bg-slate-50">
        <?php
        $page = $_GET['page'] ?? 'dashboard';
        
        // ตรวจสอบว่าไฟล์หน้าที่จะ include มีอยู่จริงหรือไม่
        $allowed_pages = ['dashboard', 'list', 'register', 'manual', 'history', 'settings'];
        if (in_array($page, $allowed_pages)) {
            include $page . '.php';
        } else {
            include 'dashboard.php';
        }
        ?>
    </main>
</div>

<?php include 'footer.php'; ?>