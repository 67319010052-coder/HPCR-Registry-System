# HPCR (Hatyai Peripheral Care Registry) 🏥

ระบบลงทะเบียนผู้ป่วยและติดตามผลการดูแลสายสวนหลอดเลือดส่วนปลาย พัฒนาขึ้นเพื่อเพิ่มประสิทธิภาพในการจัดเก็บข้อมูลและวิเคราะห์สถิติภายในโรงพยาบาล

## ✨ คุณสมบัติเด่น (Main Features)
- **Interactive Dashboard:** แสดงสถิติแยกตามเพศ, สถานะผู้ป่วย และสถิติภาวะแทรกซ้อน (Complications) ในรูปแบบกราฟ
- **Patient Management:** ระบบค้นหาและจัดการรายชื่อผู้ป่วย (List/Search)
- **System Logs:** ติดตามประวัติการแก้ไขข้อมูลโดยเจ้าหน้าที่ (History Tracking)
- **Thai Date Support:** ปรับแต่ง Datepicker ให้รองรับปี พ.ศ. (Thai Buddhist Calendar)
- **Responsive Design:** พัฒนาด้วย Tailwind CSS รองรับการใช้งานทุกหน้าจอ

## 🛠️ เทคโนโลยีที่ใช้ (Tech Stack)
- **Frontend:** Tailwind CSS, Lucide Icons, jQuery, Chart.js
- **Backend:** PHP (PDO)
- **Database:** MySQL / MariaDB
- **Tools:** GitHub, SweetAlert2 (for delete confirmation)

## 📁 โครงสร้างไฟล์สำคัญ
- `index.php`: ไฟล์หลักที่จัดการ Routing ของหน้าเว็บ
- `data.php`: ไฟล์เชื่อมต่อฐานข้อมูลและฟังก์ชันกลาง
- `dashboard.php`: ส่วนแสดงผลสถิติและกราฟวิเคราะห์ข้อมูล
- `history.php`: ระบบจัดเก็บ Log การทำงานของเจ้าหน้าที่
