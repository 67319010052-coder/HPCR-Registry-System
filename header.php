<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hatyai Peripheral Care Registry (HPCR)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8fafc; }
        .slide-in { animation: slideIn 0.3s ease-out forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Hide Scrollbar */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Tab Logic */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-btn.active { color: #7e22ce; border-bottom-color: #7e22ce; font-weight: 600; }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen">