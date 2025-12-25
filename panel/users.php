<?php
session_start();
require_once __DIR__ . '/../config.php';

// بررسی لاگین
$query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
$query->bindParam("username", $_SESSION["user"], PDO::PARAM_STR);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if( !isset($_SESSION["user"]) || !$result ){
    header('Location: login.php');
    return;
}

// دریافت لیست کاربران
$query = $pdo->prepare("SELECT * FROM user ORDER BY id DESC");
$query->execute();
$listusers = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران | ربات میرزا</title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        /* --- متغیرها --- */
        :root {
            --bg-body: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --card-hover: rgba(51, 65, 85, 0.8);
            --color-primary: #10b981;
            --color-rose: #f43f5e;
            --color-blue: #3b82f6;
            --color-purple: #8b5cf6;
        }

        [data-theme="light"] {
            --bg-body: #f0f2f5;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-hover: rgba(255, 255, 255, 1);
        }

        * {
            box-sizing: border-box;
            font-family: 'Vazir', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            background-image: url('https://www.visitfinland.com/dam/jcr:10ead74c-e5bf-4742-aa7a-1bec21cd4130/800L__20160205_01_Thomas%20Kast_noise.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-blend-mode: overlay;
            min-height: 100vh;
            color: var(--text-main);
            margin: 0;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.85);
            z-index: -1;
            transition: 0.3s;
        }

        [data-theme="light"] body::before {
            background: rgba(241, 245, 249, 0.5);
        }

        /* --- لی‌اوت اصلی --- */
        #container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #main-content {
            margin-top: 80px; 
            margin-right: 260px; 
            padding: 30px;
            transition: all 0.3s ease;
            min-height: calc(100vh - 80px);
        }

        @media (max-width: 992px) {
            #main-content {
                margin-right: 0 !important;
                padding: 20px;
            }
        }

        /* --- استایل باکس جدول --- */
        .table-wrapper {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 15px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            color: var(--color-primary);
        }

        /* --- کاستوم کردن جدول DataTables --- */
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            color: var(--text-main) !important;
        }

        /* هدر جدول */
        table.dataTable thead th {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            font-weight: 600;
            padding: 15px 15px 15px 35px !important; 
            border-bottom: 1px solid var(--glass-border) !important;
            text-align: right !important;
            position: relative;
        }

        /* ردیف‌های جدول */
        table.dataTable tbody td {
            padding: 15px !important;
            border-bottom: 1px solid var(--glass-border) !important;
            vertical-align: middle;
            font-size: 14px;
        }

        table.dataTable tbody tr {
            background-color: transparent !important;
            transition: background 0.2s;
        }

        table.dataTable tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        /* --- وضعیت‌ها --- */
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-block {
            background: rgba(244, 63, 94, 0.15);
            color: #f43f5e;
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        /* --- دکمه عملیات --- */
        .btn-action {
            background: var(--color-blue);
            color: white;
            padding: 6px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }

        .btn-action:hover {
            background: #2563eb;
            transform: translateY(-2px);
            color: white;
        }

        /* --- استایل اینپوت‌های سرچ و تعداد DataTables --- */
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            border-radius: 8px;
            padding: 6px 10px;
            outline: none;
            margin-right: 15px !important;
        }
        
        /* استایل دراپ‌داون تعداد (اصلاح شده) */
        .dataTables_wrapper .dataTables_length select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            border-radius: 8px;
            padding: 6px 10px;
            outline: none;
            
            /* حذف آیکون پیش‌فرض مرورگر */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            text-align: center;
            width: 50px;
        }

        /* حل مشکل رنگ سفید متن در دراپ‌داون */
        .dataTables_wrapper .dataTables_length select option {
            background-color: #fff !important;
            color: #333 !important;
        }

        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter, 
        .dataTables_wrapper .dataTables_processing {
            color: var(--text-muted) !important;
            margin-bottom: 15px;
        }

        /* --- بخش پایینی جدول --- */
        .dataTables_wrapper .bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--glass-border);
            flex-wrap: wrap;
            gap: 10px;
        }

        .dataTables_wrapper .dataTables_info {
            color: var(--text-muted) !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-muted) !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-main) !important;
            border-radius: 6px !important;
            padding: 5px 12px !important;
            margin: 0 2px;
            border: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--color-primary) !important;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(255,255,255,0.1) !important;
            color: var(--text-main) !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            color: var(--text-muted) !important;
            background: transparent !important;
            cursor: default;
        }

        /* رسپانسیو */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            .dataTables_wrapper .bottom {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .dataTables_wrapper .dataTables_paginate {
                margin-top: 10px !important;
                width: 100%;
                justify-content: center;
            }
        }

        /* دکمه تم */
        .theme-toggle {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 50px;
            height: 50px;
            background: var(--color-purple);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
            z-index: 2000;
            transition: transform 0.3s;
            color: white;
            font-size: 20px;
            border: none;
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(15deg);
        }

    </style>
</head>

<body>

    <section id="container">
        
        <?php include("header.php"); ?>

        <section id="main-content">
            <section class="wrapper">
                
                <div class="table-wrapper">
                    <div class="table-header">
                        <div class="page-title">
                            <i class="fa-solid fa-users"></i>
                            لیست کاربران
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="usersTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>شناسه (ID)</th>
                                    <th>نام کاربری</th>
                                    <th>شماره تلفن</th>
                                    <th>موجودی</th>
                                    <th>زیرمجموعه</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($listusers as $list): 
                                    $statusClass = 'badge-active';
                                    $statusText = 'فعال';
                                    
                                    if(strtolower($list['User_Status']) == 'block') {
                                        $statusClass = 'badge-block';
                                        $statusText = 'مسدود';
                                    }
                                    
                                    $number = ($list['number'] == "none") ? '<span style="opacity:0.5">---</span>' : $list['number'];
                                ?>
                                <tr>
                                    <td><?php echo $list['id']; ?></td>
                                    <td style="direction: ltr; text-align:right;"><?php echo $list['username']; ?></td>
                                    <td><?php echo $number; ?></td>
                                    <td><?php echo number_format($list['Balance']); ?> تومان</td>
                                    <td><?php echo $list['affiliatescount']; ?> نفر</td>
                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                    <td>
                                        <a href="user.php?id=<?php echo $list['id']; ?>" class="btn-action">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            مدیریت
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </section>
    </section>

    <script src="js/jquery.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json"
                },
                "order": [[ 0, "desc" ]],
                "pageLength": 10,
                // این خط مهمه: به DataTables میگه اجزای پایین جدول رو توی یک div با کلاس bottom بذاره
                "dom": '<"top"lf>rt<"bottom"ip><"clear">'
            });
        });


    </script>
</body>
</html>