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

// --- محاسبات آماری ---
$datefirstday = time() - 86400;

// جمع کل فروش
$query = $pdo->prepare("SELECT SUM(price_product) FROM invoice WHERE (status = 'active' OR status = 'end_of_time' OR status = 'end_of_volume' OR status = 'sendedwarn' OR status = 'send_on_hold') AND name_product != 'سرویس تست'");
$query->execute();
$subinvoice = $query->fetch(PDO::FETCH_ASSOC);
$total_income = $subinvoice['SUM(price_product)'] ?? 0; 

// تعداد کاربران
$query = $pdo->prepare("SELECT * FROM user");
$query->execute();
$resultcount = $query->rowCount();

// کاربران جدید (24 ساعت گذشته)
$stmt = $pdo->prepare("SELECT * FROM user WHERE register > :time_register AND register != 'none'");
$stmt->bindParam(':time_register', $datefirstday);
$stmt->execute();
$resultcountday = $stmt->rowCount();

// تعداد فاکتورهای فروش
$query = $pdo->prepare("SELECT * FROM invoice WHERE (status = 'active' OR status = 'end_of_time' OR status = 'end_of_volume' OR status = 'sendedwarn' OR status = 'send_on_hold') AND name_product != 'سرویس تست'");
$query->execute();
$resultcontsell = $query->rowCount();

// --- آماده‌سازی داده‌های نمودار ---
$chart_labels = [];
$chart_data = [];

if($resultcontsell != 0){
    $query = $pdo->prepare("SELECT time_sell, price_product FROM invoice ORDER BY time_sell DESC");
    $query->execute();
    $salesData = $query->fetchAll();
    
    $grouped_data = [];
    foreach ($salesData as $sell){
        // --- تغییر: محدود کردن به ۶ روز اخیر ---
        if(count($grouped_data) > 5) break; 
        
        if(!is_numeric($sell['time_sell'])) continue;
        
        $date_key = date('Y/m/d', $sell['time_sell']);
        $price = (int)$sell['price_product'];
        
        if (!isset($grouped_data[$date_key])) {
            $grouped_data[$date_key] = 0;
        }
        $grouped_data[$date_key] += $price;
    }
    
    $grouped_data = array_reverse($grouped_data);
    
    foreach($grouped_data as $date => $amount){
        $chart_labels[] = jdate('m/d', strtotime($date)); 
        $chart_data[] = $amount;
    }
}

$json_labels = json_encode($chart_labels);
$json_data = json_encode($chart_data);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت ربات میرزا</title>
    
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-body: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --card-hover: rgba(51, 65, 85, 0.8);
            --color-primary: #10b981;
            --color-purple: #8b5cf6;
            --color-blue: #3b82f6;
            --color-rose: #f43f5e;
        }

        [data-theme="light"] {
            --bg-body: #f0f2f5;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-hover: rgba(255, 255, 255, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Vazir', sans-serif; }

        body {
            background-color: var(--bg-body);
            background-image: url('https://www.visitfinland.com/dam/jcr:10ead74c-e5bf-4742-aa7a-1bec21cd4130/800L__20160205_01_Thomas%20Kast_noise.jpg');
            background-size: cover; background-attachment: fixed; background-blend-mode: overlay;
            min-height: 100vh; color: var(--text-main); overflow-x: hidden;
        }

        body::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.85); z-index: -1; transition: 0.3s;
        }
        [data-theme="light"] body::before { background: rgba(241, 245, 249, 0.5); }

        #container { display: flex; flex-direction: column; width: 100%; }
        #main-content {
            margin-top: 80px; margin-right: 260px; padding: 30px;
            transition: all 0.3s ease; min-height: calc(100vh - 80px);
        }
        @media (max-width: 992px) {
            #main-content { margin-right: 0 !important; padding: 20px; }
        }

        /* --- تنظیم عرض کانتینر اصلی --- */
        .wrapper {
            max-width: 1500px; /* عرض مناسب برای مانیتورهای بزرگ */
            width: 96%;
            margin: 0 auto;
        }

        /* گرید 4 تایی برای باکس‌ها */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* دقیقا 4 ستون */
            gap: 25px;
            margin-bottom: 30px;
        }

        /* ریسپانسیو */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
        }

        /* استایل کارت‌ها */
        .stat-card {
            background: var(--glass-bg); 
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px); 
            border-radius: 20px; 
            padding: 25px;
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            background: var(--card-hover);
        }

        .stat-icon {
            width: 60px; height: 60px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: #fff; 
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            flex-shrink: 0; 
        }

        .stat-info { text-align: left; flex-grow: 1; padding-right: 15px; }
        .stat-value {
            font-size: 24px; font-weight: 800; color: var(--text-main);
            margin-bottom: 5px; display: block;
        }
        .stat-title { font-size: 14px; color: var(--text-muted); font-weight: 500; }

        /* رنگ آیکون‌ها */
        .icon-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .icon-green { background: linear-gradient(135deg, #10b981, #059669); }
        .icon-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }

        .chart-container {
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px); border-radius: 24px; padding: 25px;
            position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: auto; 
        }

        .chart-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .chart-title {
            font-size: 18px; font-weight: 700; color: var(--text-main);
            display: flex; align-items: center; gap: 10px;
        }
        
        /* فیکس کردن ارتفاع نمودار */
        .chart-canvas-wrapper { 
            position: relative; 
            height: 400px; 
            width: 100%; 
        }
    </style>
</head>

<body>

    <section id="container">
        
        <?php include("header.php"); ?>

        <section id="main-content">
            <section class="wrapper">
                
                <div class="stats-grid">
                    
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="fa-solid fa-users"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($resultcount); ?></span>
                            <span class="stat-title">تعداد کل کاربران</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-rose"><i class="fa-solid fa-cart-shopping"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($resultcontsell); ?></span>
                            <span class="stat-title">تعداد کل سفارشات</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="fa-solid fa-coins"></i></div>
                        <div class="stat-info">
                            <span class="stat-value" style="font-size: 18px;"><?php echo number_format($total_income); ?> <small>تومان</small></span>
                            <span class="stat-title">جمع کل فروش</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-purple"><i class="fa-solid fa-user-plus"></i></div>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($resultcountday); ?></span>
                            <span class="stat-title">کاربران جدید (24h)</span>
                        </div>
                    </div>

                </div>

                <?php if($resultcontsell != 0 ): ?>
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">
                            <i class="fa-solid fa-chart-line"></i>
                            نمودار فروش ۶ روز اخیر
                        </div>
                    </div>
                    
                    <div class="chart-canvas-wrapper">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>

            </section>
        </section>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // تنظیمات نمودار
        <?php if($resultcontsell != 0 ): ?>
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); 
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

        // تشخیص رنگ متن
        let isLight = document.documentElement.getAttribute('data-theme') === 'light';
        let textColor = isLight ? '#64748b' : '#94a3b8';
        let gridColor = isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.05)';

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $json_labels; ?>,
                datasets: [{
                    label: 'فروش (تومان)',
                    data: <?php echo $json_data; ?>,
                    backgroundColor: gradient,
                    borderColor: '#10b981',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { font: { family: 'Vazir' }, color: textColor }
                    },
                    tooltip: {
                        titleFont: { family: 'Vazir' },
                        bodyFont: { family: 'Vazir' },
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('fa-IR').format(context.parsed.y) + ' تومان';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { font: { family: 'Vazir' }, color: textColor }
                    },
                    y: {
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { font: { family: 'Vazir' }, color: textColor,
                            callback: function(value) { return new Intl.NumberFormat('fa-IR').format(value); } 
                        },
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>