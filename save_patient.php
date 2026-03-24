<?php
require_once 'data.php'; // หรือ db_connection.php ตามที่คุณใช้

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // --- 1. เตรียมตาราง Log (ถ้ายังไม่มี) ---
        $pdo->exec("CREATE TABLE IF NOT EXISTS system_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            staff VARCHAR(100),
            ward VARCHAR(50),
            action VARCHAR(50),
            detail TEXT
        )");

        // --- 2. รับค่าจากฟอร์ม ---
        $staff_name = $_POST['staff_name'] ?? 'Unknown';
        $staff_ward = $_POST['staff_ward'] ?? 'General';
        
        $id = $_POST['id'] ?? null;
        $hn = $_POST['hn'];
        $an = $_POST['an'];
        $name = $_POST['name'];
        $age = !empty($_POST['age']) ? $_POST['age'] : 0;
        $gender = $_POST['gender'];
        $insurance = $_POST['insurance'] ?? '';
        $ward = $_POST['ward']; // Ward ของคนไข้
        
        // Refer & Dates
        $refer_hospital = $_POST['refer_hospital'] ?? '';
        $refer_province = $_POST['refer_province'] ?? '';
        $refer_date = !empty($_POST['refer_date']) ? $_POST['refer_date'] : null;
        $admit_date = !empty($_POST['admit_date']) ? $_POST['admit_date'] : null;
        $discharge_date = !empty($_POST['discharge_date']) ? $_POST['discharge_date'] : null;
        
        $diagnosis = $_POST['diagnosis'] ?? '';
        $line_on = $_POST['line_on'] ?? 'No';

        // Risk Factors
        $malnutrition = $_POST['malnutrition'] ?? 'No';
        $bedridden = $_POST['bedridden'] ?? 'No';
        $autoimmune = $_POST['autoimmune'] ?? 'No';
        $difficult_line = $_POST['difficult_line'] ?? 'No';

        // JSON Data (เก็บไว้แสดงผลหน้าเว็บ)
        $lines_json = $_POST['lines_json'] ?? '{}';

        // Drug & Solution
        $antibiotic_name = $_POST['antibiotic_name'] ?? '';
        $antibiotic_date = !empty($_POST['antibiotic_date']) ? $_POST['antibiotic_date'] : null;
        $high_alert_name = $_POST['high_alert_name'] ?? '';
        $high_alert_date = !empty($_POST['high_alert_date']) ? $_POST['high_alert_date'] : null;
        
        $sol_nss = $_POST['sol_nss'] ?? 'No';
        $sol_d5 = $_POST['sol_d5'] ?? 'No';
        $sol_rl = $_POST['sol_rl'] ?? 'No';
        $sol_acetar = $_POST['sol_acetar'] ?? 'No';
        $sol_tpn = $_POST['sol_tpn'] ?? 'No';

        // --- 3. เริ่ม Transaction ---
        $pdo->beginTransaction();

        $action = '';
        $detail = '';
        $patient_id = 0; // ตัวแปรสำหรับเก็บ ID เพื่อใช้กับตารางลูก

        if ($id) {
            // ================= UPDATE =================
            $sql = "UPDATE patients SET 
                    hn=?, an=?, name=?, age=?, gender=?, insurance=?, ward=?, 
                    refer_hospital=?, refer_province=?, refer_date=?, 
                    admit_date=?, discharge_date=?, diagnosis=?, line_on=?,
                    malnutrition=?, bedridden=?, autoimmune=?, difficult_line=?,
                    lines_json=?,
                    antibiotic_name=?, antibiotic_date=?, high_alert_name=?, high_alert_date=?,
                    sol_nss=?, sol_d5=?, sol_rl=?, sol_acetar=?, sol_tpn=?,
                    recorder_name=?, recorder_ward=?, last_updated=NOW() 
                    WHERE id=?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $hn, $an, $name, $age, $gender, $insurance, $ward,
                $refer_hospital, $refer_province, $refer_date,
                $admit_date, $discharge_date, $diagnosis, $line_on,
                $malnutrition, $bedridden, $autoimmune, $difficult_line,
                $lines_json,
                $antibiotic_name, $antibiotic_date, $high_alert_name, $high_alert_date,
                $sol_nss, $sol_d5, $sol_rl, $sol_acetar, $sol_tpn,
                $staff_name, $staff_ward,
                $id
            ]);

            $patient_id = $id; // ใช้ ID เดิม
            $action = 'Update';
            $detail = "แก้ไขข้อมูลผู้ป่วย: $name (HN: $hn)";

        } else {
            // ================= INSERT =================
            $sql = "INSERT INTO patients (
                    hn, an, name, age, gender, insurance, ward, 
                    refer_hospital, refer_province, refer_date, 
                    admit_date, discharge_date, diagnosis, line_on,
                    malnutrition, bedridden, autoimmune, difficult_line,
                    lines_json,
                    antibiotic_name, antibiotic_date, high_alert_name, high_alert_date,
                    sol_nss, sol_d5, sol_rl, sol_acetar, sol_tpn,
                    recorder_name, recorder_ward
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, 
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?
                )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $hn, $an, $name, $age, $gender, $insurance, $ward,
                $refer_hospital, $refer_province, $refer_date,
                $admit_date, $discharge_date, $diagnosis, $line_on,
                $malnutrition, $bedridden, $autoimmune, $difficult_line,
                $lines_json,
                $antibiotic_name, $antibiotic_date, $high_alert_name, $high_alert_date,
                $sol_nss, $sol_d5, $sol_rl, $sol_acetar, $sol_tpn,
                $staff_name, $staff_ward
            ]);

            $patient_id = $pdo->lastInsertId(); // ดึง ID ใหม่ที่เพิ่งสร้าง
            $action = 'Create';
            $detail = "ลงทะเบียนผู้ป่วยใหม่: $name (HN: $hn)";
        }

        // ================= 4. Manage Patient Lines (ส่วนที่เพิ่มใหม่) =================
        // ส่วนนี้สำคัญ: แตกไฟล์ JSON ลงตาราง patient_lines เพื่อให้ Query ง่ายขึ้น
        
        // 4.1 ลบข้อมูลเก่าของคนไข้คนนี้ออกก่อน (เพื่อป้องกันข้อมูลซ้ำ)
        $delSql = "DELETE FROM patient_lines WHERE patient_id = ?";
        $pdo->prepare($delSql)->execute([$patient_id]);

        // 4.2 แปลง JSON กลับเป็น Array
        $linesArray = json_decode($lines_json, true);

        // 4.3 เตรียม SQL Insert
        $lineSql = "INSERT INTO patient_lines (
            patient_id, line_number, side, position, 
            date_start, time_start, place_start,
            date_off, time_off, place_off,
            line_use, comp_type, comp_level, 
            comp_date, comp_time, comp_ward, culture_result
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $lineStmt = $pdo->prepare($lineSql);

        if (is_array($linesArray)) {
            foreach ($linesArray as $num => $line) {
                // กรอง: บันทึกเฉพาะ Line ที่มีข้อมูล (มีวันใส่ หรือ มีตำแหน่ง หรือ มีภาวะแทรกซ้อน)
                // เพื่อไม่ให้ Database เต็มไปด้วยข้อมูลว่างเปล่า
                $hasData = !empty($line['date_start']) || !empty($line['side']) || !empty($line['comp_type']);
                
                if ($hasData) {
                    // จัดการค่า NULL สำหรับ Date/Time (ถ้าส่งค่าว่าง '' ไป SQL จะ Error)
                    $d_start = !empty($line['date_start']) ? $line['date_start'] : null;
                    $t_start = !empty($line['time_start']) ? $line['time_start'] : null;
                    $d_off   = !empty($line['date_off']) ? $line['date_off'] : null;
                    $t_off   = !empty($line['time_off']) ? $line['time_off'] : null;
                    $d_comp  = !empty($line['comp_date']) ? $line['comp_date'] : null;
                    $t_comp  = !empty($line['comp_time']) ? $line['comp_time'] : null;
                    $c_level = (isset($line['comp_level']) && is_numeric($line['comp_level'])) ? $line['comp_level'] : null;

                    $lineStmt->execute([
                        $patient_id,
                        $num, // เลข Line (1-15)
                        $line['side'] ?? null,
                        $line['position'] ?? null,
                        $d_start, $t_start, $line['place_start'] ?? null,
                        $d_off, $t_off, $line['place_off'] ?? null,
                        $line['line_use'] ?? null,
                        $line['comp_type'] ?? null,
                        $c_level,
                        $d_comp, $t_comp,
                        $line['comp_ward'] ?? null,
                        $line['culture_result'] ?? null
                    ]);
                }
            }
        }
        // ================= จบส่วนที่เพิ่มใหม่ =================

        // ================= 5. บันทึก System Log =================
        $logSql = "INSERT INTO system_logs (staff, ward, action, detail) VALUES (?, ?, ?, ?)";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute([$staff_name, $staff_ward, $action, $detail]);

        // Commit Transaction
        $pdo->commit();

        // ส่งกลับไปหน้า Register พร้อมแจ้งสถานะสำเร็จ (ถ้าต้องการ)
        header("Location: index.php?page=register&status=success&id=" . $patient_id);
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<h3>เกิดข้อผิดพลาด (Database Error):</h3>";
        echo "Error: " . $e->getMessage();
        echo "<br><pre>" . $e->getTraceAsString() . "</pre>"; // ดูบรรทัดที่ error
        echo "<br><br><a href='index.php?page=register'>กลับไปหน้าลงทะเบียน</a>";
        exit();
    }
} else {
    // ถ้าเข้าไฟล์นี้โดยตรง (ไม่ใช่ POST) ให้ดีดกลับ
    header("Location: index.php");
    exit();
}
?>