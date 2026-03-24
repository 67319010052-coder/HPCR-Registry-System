// file: datepicker-thai.js

$(document).ready(function() {
    // =========================================================
    // 1. ตั้งค่าปฏิทินให้หัวข้อปีเป็น พ.ศ. (Override Function)
    // =========================================================
    var _generateMonthYearHeader = $.datepicker._generateMonthYearHeader;
    $.datepicker._generateMonthYearHeader = function(inst, drawMonth, drawYear, minDate, maxDate, secondary, monthNames, monthNamesShort) {
        var htmlYearMonth = _generateMonthYearHeader.apply(this, arguments);
        var newHtml = htmlYearMonth.replace(/<option value="(\d+)">(\d+)<\/option>/g, function(match, value, text) {
            var year = parseInt(value);
            var thaiYear = year + 543;
            return '<option value="' + value + '">' + thaiYear + '</option>';
        });
        return newHtml;
    };

    // =========================================================
    // 2. ตั้งค่าภาษาไทย (Thai Localization)
    // =========================================================
    $.datepicker.regional['th'] = {
        closeText: 'ปิด',
        prevText: 'ย้อน',
        nextText: 'ถัดไป',
        currentText: 'วันนี้',
        monthNames: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
        monthNamesShort: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
        dayNames: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
        dayNamesShort: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
        dayNamesMin: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
        dateFormat: 'dd/mm/yy',
        isRTL: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['th']);

    // =========================================================
    // 3. ฟังก์ชันสร้าง Input วันที่ (พร้อมตกแต่ง Tailwind)
    // =========================================================
    function setupThaiDateInput(inputId, hiddenId, options) {
        var $input = $(inputId);
        var $hidden = $(hiddenId);
        
        // Default Options
        var settings = $.extend({
            yearRange: "c-5:c+5",
            changeMonth: true,
            changeYear: true
        }, options);

        // 3.1 ฟังก์ชันอัปเดตค่าลง Hidden Input (แปลง พ.ศ. -> ค.ศ.)
        function updateValue() {
            var val = $input.val();
            if (val.length >= 8) { // อย่างน้อยมีตัวเลขครบ
                var parts = val.split('/');
                if(parts.length === 3) {
                    var day = parts[0];
                    var month = parts[1];
                    var yearInput = parseInt(parts[2]);
                    var thYear, enYear;

                    // ตรวจสอบว่า user พิมพ์ พ.ศ. หรือ ค.ศ. มา
                    if (yearInput > 2400) {
                        enYear = yearInput - 543;
                        thYear = yearInput;
                    } else {
                        thYear = yearInput + 543;
                        enYear = yearInput;
                        // อัปเดตช่องโชว์ให้เป็น พ.ศ. เสมอ
                        // $input.val(day + '/' + month + '/' + thYear); // เอาออกเพื่อให้พิมพ์ได้ลื่นไหล
                    }

                    // เก็บลง DB เป็น YYYY-MM-DD (ค.ศ.)
                    var isoDate = enYear + '-' + month + '-' + day;
                    $hidden.val(isoDate).trigger('change'); 
                }
            } else {
                $hidden.val("").trigger('change');
            }
        }

        // 3.2 จัดการปุ่ม Enter (ป้องกัน Submit Form และปิดปฏิทิน)
        $input.keydown(function(e) {
            if (e.keyCode == 13) { 
                e.preventDefault(); 
                $(this).datepicker("hide"); 
                updateValue(); 
                $(this).blur(); 
            }
        });

        // 3.3 Event Listeners
        $input.on('input change blur', updateValue);

        // 3.4 เริ่มต้น Datepicker
        $input.datepicker({
            changeMonth: settings.changeMonth,
            changeYear: settings.changeYear,
            yearRange: settings.yearRange,
            
            // เพิ่ม Class Tailwind ให้ตัวปฏิทินตอนเปิด
            beforeShow: function(input, inst) {
                setTimeout(function() {
                    inst.dpDiv.addClass('shadow-xl border border-slate-100 rounded-xl font-sans bg-white p-2');
                }, 0);
            },
            
            onSelect: function(dateText) {
                var dateObj = $(this).datepicker('getDate');
                if (dateObj) {
                    var year = dateObj.getFullYear();
                    // แปลงกลับเป็น พ.ศ. เพื่อแสดงผล
                    if (year < 2400) year += 543; 
                    
                    var thYear = year;
                    var day = String(dateObj.getDate()).padStart(2, '0');
                    var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    
                    $(this).val(day + '/' + month + '/' + thYear);
                    updateValue(); // บันทึกค่าทันทีที่เลือก
                }
            }
        });
    }

    // =========================================================
    // 4. เรียกใช้งาน (Setup) กับ ID ในหน้า Register.php
    // =========================================================
    
    // ข้อมูลทั่วไป
    setupThaiDateInput('#refer_date_input', '#refer_date');
    setupThaiDateInput('#admit_date_input', '#admit_date');
    setupThaiDateInput('#discharge_date_input', '#discharge_date');
    
    // Drug Use
    setupThaiDateInput('#antibiotic_date_input', '#antibiotic_date');
    setupThaiDateInput('#high_alert_date_input', '#high_alert_date');
    
    // Site Care Fields
    setupThaiDateInput('#line_date_start_input', '#line_date_start');
    setupThaiDateInput('#line_date_off_input', '#line_date_off');
    setupThaiDateInput('#comp_date_input', '#comp_date');

    // เรียก Line 1 เริ่มต้น (เพื่อให้ Datepicker ทำงานทันทีถ้ามีค่า)
    if(typeof setActiveLine === 'function') {
        setActiveLine(1);
    }
});