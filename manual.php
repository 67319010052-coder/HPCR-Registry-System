<div class="space-y-6 slide-in">
    <h2 class="text-2xl font-bold text-slate-800 border-b pb-2 flex items-center gap-2">
        <i data-lucide="book-open" class="text-purple-600"></i> คู่มือและเกณฑ์การประเมิน
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        $manuals = [
            [
                "title" => "Infiltration Scale",
                "file" => "Infiltration Scale.jpg",
                "desc" => "เกณฑ์การประเมินภาวะสารน้ำแทรกซึมออกนอกหลอดเลือด"
            ],
            [
                "title" => "Phlebitis Scale",
                "file" => "Phlebitis Scale.jpg",
                "desc" => "เกณฑ์การประเมินภาวะหลอดเลือดดำอักเสบ"
            ],
            [
                "title" => "Extravasation Scale",
                "file" => "Extravasation Scale.jpg",
                "desc" => "เกณฑ์การประเมินภาวะยาเคมีบำบัดรั่วซึม"
            ]
        ];

        foreach($manuals as $m):
            // สร้าง Path ของรูปภาพ (โฟลเดอร์ img ต้องอยู่ใน HPCRS)
            $imagePath = "img/" . $m['file'];
        ?>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
            <div class="aspect-[3/4] bg-slate-100 rounded-lg flex flex-col items-center justify-center mb-4 border border-slate-200 overflow-hidden relative group">
                
                <?php if(file_exists($imagePath)): ?>
                    <img src="<?= $imagePath ?>" 
                         alt="<?= $m['title'] ?>" 
                         class="w-full h-full object-contain cursor-pointer transition-transform duration-300 group-hover:scale-105"
                         onclick="window.open(this.src, '_blank')">
                    
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all flex items-center justify-center pointer-events-none">
                        <span class="text-white opacity-0 group-hover:opacity-100 bg-black bg-opacity-50 px-2 py-1 rounded text-xs">คลิกเพื่อขยาย</span>
                    </div>
                <?php else: ?>
                    <i data-lucide="image-off" class="w-12 h-12 text-slate-300 mb-2"></i>
                    <span class="text-xs text-red-400">ไม่พบไฟล์ภาพ</span>
                    <span class="text-[10px] text-slate-400"><?= $m['file'] ?></span>
                <?php endif; ?>

            </div>
            
            <h3 class="font-semibold text-slate-800 mb-1"><?= $m['title'] ?></h3>
            <p class="text-sm text-slate-500"><?= $m['desc'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>