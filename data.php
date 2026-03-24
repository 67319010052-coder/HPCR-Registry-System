<?php
// config/data.php

$host = 'localhost';
$dbname = 'hpcrs_db';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ดึงข้อมูลผู้ป่วย
try {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY last_updated DESC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $patients = [];
}

// --- ฟังก์ชันแปลงวันที่เป็นไทย (10 ก.พ. 2569) ---
function thaiDate($date) {
    if(!$date || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '-';
    $timestamp = strtotime($date);
    $d = date("d", $timestamp);
    $y = date("Y", $timestamp) + 543; // บวก 543 ปี
    $months = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    $m = $months[date("n", $timestamp)];
    return "$d $m $y";
}

// --- ฟังก์ชันแปลงวันที่และเวลาเป็นไทย (10 ก.พ. 2569 14:30) ---
function thaiDateTime($datetime) {
    if(!$datetime || $datetime == '0000-00-00 00:00:00') return '-';
    $datePart = thaiDate($datetime);
    $timePart = date("H:i", strtotime($datetime));
    // ส่งคืน HTML ที่จัดรูปแบบแล้ว
    return "$datePart <span class='text-slate-400 ml-1'>$timePart</span>";
}
?>