<?php
session_start();
require_once 'data.php'; // เรียกใช้การเชื่อมต่อ Database ($pdo)

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไปหน้า Dashboard เลย
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        // เตรียมคำสั่ง SQL เพื่อดึงข้อมูล User ตาม username ที่กรอกมา
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่าพบ User หรือไม่ และรหัสผ่านตรงกันไหม
        // หมายเหตุ: เพื่อความง่ายเราเทียบ Text ตรงๆ แต่ในระบบจริงควรใช้ password_verify()
        if ($user && $user['password'] === $password) {
            
            // Login สำเร็จ: เก็บค่าลง Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name']; // เก็บชื่อจริงไว้แสดงผลได้

            header("Location: index.php");
            exit();
        } else {
            $error = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง';
        }

    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาดระบบ: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - HPCR System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border border-slate-200">
        <div class="text-center mb-6">
            <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.18 4.02c-.68 1.7-1.56 2.77-2.28 3.49"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">HPCR System</h1>
            <p class="text-slate-500 text-sm">ระบบลงทะเบียนผู้ป่วยและติดตามผล</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 border border-red-100 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">ชื่อผู้ใช้งาน</label>
                <input type="text" name="username" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Username" required autofocus>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" class="w-full p-2.5 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none" placeholder="Password" required>
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white py-2.5 rounded-lg hover:bg-purple-700 font-bold shadow-md transition-all">เข้าสู่ระบบ</button>
        </form>
        
        <p class="text-center text-xs text-slate-400 mt-6">
            © 2026 Hatyai Hospital. All rights reserved.
        </p>
    </div>

</body>
</html>