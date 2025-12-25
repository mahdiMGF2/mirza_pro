<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../jdf.php';

// بررسی لاگین
$query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
$query->bindParam("username", $_SESSION["user"], PDO::PARAM_STR);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if( !isset($_SESSION["user"]) || !$result ){
    header('Location: login.php');
    return;
}

// دریافت لیست خدمات
$query = $pdo->prepare("SELECT * FROM service_other ORDER BY id DESC");
$query->execute();
$listservices = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لیست خدمات انجام شده | ربات میرزا</title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        /* --- استایل‌های پایه (مشترک) --- */
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
            --color-warning: #f59e0b;
            --color-info: #06b6d4;
        }

        [data-theme="light"] {
            --bg-body: #f0f2f5;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-hover: rgba(255, 255, 255, 1);
        }

        * { box-sizing: border-box; font-family: 'Vazir', sans-serif; }

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
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.85); z-index: -1; transition: 0.3s;
        }
        [data-theme="light"] body::before { background: rgba(241, 245, 249, 0.5); }

        #container { display: flex; flex-direction: column; width: 100%; }
        #main-content { margin-top: 80px; margin-right: 260px; padding: 30px; transition: all 0.3s ease; min-height: calc(100vh - 80px); }
        @media (max-width: 992px) { #main-content { margin-right: 0 !important; padding: 20px; } }

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
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;
        }

        .page-title { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .page-title i { color: var(--color-primary); }

        /* --- جدول DataTables --- */
        table.dataTable { width: 100% !important; border-collapse: collapse !important; color: var(--text-main) !important; }
        
        table.dataTable thead th {
            background: rgba(255, 255, 255, 0.05); color: var(--text-muted); font-weight: 600;
            padding: 15px 15px 15px 35px !important; border-bottom: 1px solid var(--glass-border) !important;
            text-align: right !important; position: relative;
        }

        table.dataTable tbody td {
            padding: 15px !important; border-bottom: 1px solid var(--glass-border) !important;
            vertical-align: middle; font-size: 14px;
        }

        table.dataTable tbody tr { background-color: transparent !important; transition: background 0.2s; }
        table.dataTable tbody tr:hover { background-color: rgba(255, 255, 255, 0.05) !important; }

        /* --- وضعیت‌ها (Badges) --- */
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 500; display: inline-block; white-space: nowrap; }
        
        .badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-info { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-purple { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2); }
        .badge-cyan { background: rgba(6, 182, 212, 0.15); color: #06b6d4; border: 1px solid rgba(6, 182, 212, 0.2); }
        .badge-gray { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }

        /* --- استایل اینپوت‌ها و صفحه‌بندی --- */
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border);
            color: var(--text-main); border-radius: 8px; padding: 6px 10px; outline: none; margin-right: 15px !important;
        }
        
        .dataTables_wrapper .dataTables_length select {
            background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border);
            color: var(--text-main); border-radius: 8px; padding: 6px 10px; outline: none;
            appearance: none; -webkit-appearance: none; text-align: center; width: 50px;
        }
        .dataTables_wrapper .dataTables_length select option { background-color: #fff !important; color: #333 !important; }

        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_processing {
            color: var(--text-muted) !important; margin-bottom: 15px;
        }

        .dataTables_wrapper .bottom {
            display: flex; justify-content: space-between; align-items: center; margin-top: 20px;
            padding-top: 15px; border-top: 1px solid var(--glass-border); flex-wrap: wrap; gap: 10px;
        }

        .dataTables_wrapper .dataTables_info { color: var(--text-muted) !important; margin: 0 !important; padding: 0 !important; }
        .dataTables_wrapper .dataTables_paginate { display: flex; align-items: center; }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-main) !important; border-radius: 6px !important; padding: 5px 12px !important;
            margin: 0 2px; border: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--color-primary) !important; color: white !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: rgba(255,255,255,0.1) !important; color: var(--text-main) !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color: var(--text-muted) !important; background: transparent !important; cursor: default; }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        @media (max-width: 768px) {
            .dataTables_wrapper .bottom { flex-direction: column; align-items: flex-start; }
            .dataTables_wrapper .dataTables_paginate { margin-top: 10px !important; width: 100%; justify-content: center; }
        }

        /* دکمه تم */
        .theme-toggle {
            position: fixed; bottom: 30px; left: 30px; width: 50px; height: 50px;
            background: var(--color-purple); border-radius: 50%; display: flex; align-items: center;
            justify-content: center; cursor: pointer; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
            z-index: 2000; transition: transform 0.3s; color: white; font-size: 20px; border: none;
        }
        .theme-toggle:hover { transform: scale(1.1) rotate(15deg); }
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
                            <i class="fa-solid fa-server"></i>
                            لیست خدمات انجام شده
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="servicesTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>شناسه</th>
                                    <th>آیدی کاربر</th>
                                    <th>نام کانفیگ</th>
                                    <th>تاریخ سفارش</th>
                                    <th>قیمت (تومان)</th>
                                    <th>نوع خدمت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($listservices as $list): 
                                    // فرمت تاریخ
                                    $time = $list['time'];
                                    if(is_numeric($time)){
                                        $time = jdate('Y/m/d | H:i',$time);
                                    }

                                    // تعیین متن و رنگ بج برای نوع خدمت
                                    $typeText = 'نامشخص';
                                    $badgeClass = 'badge-gray';

                                    switch($list['type']) {
                                        case 'extend_user': $typeText = 'تمدید سرویس'; $badgeClass = 'badge-success'; break;
                                        case 'extend_user_by_admin': $typeText = 'تمدید توسط ادمین'; $badgeClass = 'badge-info'; break;
                                        case 'extra_user': $typeText = 'حجم اضافه'; $badgeClass = 'badge-purple'; break;
                                        case 'extra_time_user': $typeText = 'زمان اضافه'; $badgeClass = 'badge-purple'; break;
                                        case 'transfertouser': $typeText = 'انتقال سرویس'; $badgeClass = 'badge-warning'; break;
                                        case 'extends_not_user': $typeText = 'تمدید (خارج از لیست)'; $badgeClass = 'badge-gray'; break;
                                        case 'change_location': $typeText = 'تغییر لوکیشن'; $badgeClass = 'badge-cyan'; break;
                                        default: $typeText = $list['type'];
                                    }

                                    $price = ($list['price'] == 0) ? 'رایگان' : number_format($list['price']);
                                ?>
                                <tr>
                                    <td><?php echo $list['id']; ?></td>
                                    <td><a href="user.php?id=<?php echo $list['id_user']; ?>" style="color:var(--color-blue); text-decoration:none;"><?php echo $list['id_user']; ?></a></td>
                                    <td style="direction: ltr; text-align: right;"><?php echo $list['username']; ?></td>
                                    <td style="direction: ltr; text-align: right;"><?php echo $time; ?></td>
                                    <td><?php echo $price; ?></td>
                                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $typeText; ?></span></td>
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
            $('#servicesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json"
                },
                "order": [[ 0, "desc" ]],
                "pageLength": 10,
                "dom": '<"top"lf>rt<"bottom"ip><"clear">'
            });
        });

    </script>
</body>
</html>