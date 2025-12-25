<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/../function.php';

// بررسی لاگین ادمین
$query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
$query->bindParam("username", $_SESSION["user"], PDO::PARAM_STR);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if( !isset($_SESSION["user"]) || !$result ){
    header('Location: login.php');
    return;
}

// دریافت اطلاعات کاربر
$query = $pdo->prepare("SELECT * FROM user WHERE id=:id");
$query->bindParam("id", $_GET["id"], PDO::PARAM_STR);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

$setting = select("setting","*",null,null);
$otherservice = select("topicid","idreport","report","otherservice","select")['idreport'];
$paymentreports = select("topicid","idreport","report","paymentreport","select")['idreport'];

// --- لاجیک‌های عملیات ---
if(isset($_GET['status']) and $_GET['status']){
    if($_GET['status'] == "block"){
        $textblok = "کاربر با آیدی عددی {$_GET['id']} در ربات مسدود گردید \n\nادمین مسدود کننده : پنل تحت وب\nنام کاربری : {$_SESSION['user']}";
        if (strlen($setting['Channel_Report']) > 0) {
            telegram('sendmessage',[
                'chat_id' => $setting['Channel_Report'],
                'message_thread_id' => $otherservice,
                'text' => $textblok,
                'parse_mode' => "HTML"
            ]);
        }
    }else{
        sendmessage($_GET['id'],"✳️ حساب کاربری شما از مسدودی خارج شد ✳️\nاکنون میتوانید از ربات استفاده کنید ", null, 'HTML');
    }
    update("user", "User_Status", $_GET['status'], "id", $_GET['id']);
    header("Location: user.php?id={$_GET['id']}");
    exit;
}

if(isset($_GET['priceadd']) and $_GET['priceadd']){
    $priceadd = number_format($_GET['priceadd'],0);
    $textadd = "💎 کاربر عزیز مبلغ {$priceadd} تومان به موجودی کیف پول تان اضافه گردید.";
    sendmessage($_GET['id'], $textadd, null, 'HTML');
    if (strlen($setting['Channel_Report']) > 0) {
        $textaddbalance = "📌 یک ادمین موجودی کاربر را از پنل تحت وب افزایش داده است :\n\n🪪 ادمین : {$_SESSION['user']}\n👤 کاربر : {$_GET['id']}\nمبلغ : $priceadd";
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $textaddbalance,
            'parse_mode' => "HTML"
        ]);
    }
    $value = intval($user['Balance'])+intval($_GET['priceadd']);
    update("user", "Balance", $value, "id", $_GET['id']);
    header("Location: user.php?id={$_GET['id']}");
    exit;
}

if(isset($_GET['pricelow']) and $_GET['pricelow']){
    $pricelow = number_format($_GET['pricelow'],0);
    if (strlen($setting['Channel_Report']) > 0) {
        $textlowbalance = "📌 یک ادمین موجودی کاربر را از پنل تحت وب کسر کرده است :\n\n🪪 ادمین : {$_SESSION['user']}\n👤 کاربر : {$_GET['id']}\nمبلغ کسر شده : $pricelow";
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $paymentreports,
            'text' => $textlowbalance,
            'parse_mode' => "HTML"
        ]);
    }
    $value = intval($user['Balance'])-intval($_GET['pricelow']);
    update("user", "Balance", $value, "id", $_GET['id']);
    header("Location: user.php?id={$_GET['id']}");
    exit;
}

if(isset($_GET['agent']) and $_GET['agent']){
    update("user", "agent", $_GET['agent'], "id", $_GET['id']);
    header("Location: user.php?id={$_GET['id']}");
    exit;
}

if(isset($_GET['textmessage']) and $_GET['textmessage']){
    $messagetext = "📥 یک پیام از مدیریت برای شما ارسال شد.\n\nمتن پیام : {$_GET['textmessage']}";
    sendmessage($_GET['id'], $messagetext, null, 'HTML');
    if (strlen($setting['Channel_Report']) > 0) {
        $textmsg = "📌 پیام مدیریت ارسال شد\n\n🪪 ادمین : {$_SESSION['user']}\n👤 گیرنده : {$_GET['id']}\nمتن : {$_GET['textmessage']}";
        telegram('sendmessage',[
            'chat_id' => $setting['Channel_Report'],
            'message_thread_id' => $otherservice,
            'text' => $textmsg,
            'parse_mode' => "HTML"
        ]);
    }
    header("Location: user.php?id={$_GET['id']}");
    exit;
}

// وضعیت‌ها برای نمایش
$status_label = ($user['User_Status'] == 'block') ? 'مسدود' : 'فعال';
$status_class = ($user['User_Status'] == 'block') ? 'text-danger' : 'text-success';
$number_display = ($user['number'] == "none") ? 'ثبت نشده' : $user['number'];

// تبدیل نوع کاربر به متن فارسی
$agent_types = [
    'f' => 'کاربر عادی',
    'n' => 'نماینده معمولی',
    'n2' => 'نماینده پیشرفته'
];
$agent_display = isset($agent_types[$user['agent']]) ? $agent_types[$user['agent']] : 'نامشخص';

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربر <?php echo $user['username']; ?></title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* --- استایل‌های پایه --- */
        :root {
            --bg-body: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --color-primary: #10b981;
            --color-danger: #ef4444;
            --color-warning: #f59e0b;
            --color-info: #3b82f6;
            --input-bg: rgba(255, 255, 255, 0.05);
            
            /* رنگ دکمه تم */
            --color-purple: #8b5cf6;
        }

        [data-theme="light"] {
            --bg-body: #f0f2f5;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --input-bg: rgba(0, 0, 0, 0.03);
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
        }

        body::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.85); z-index: -1; transition: 0.3s;
        }
        [data-theme="light"] body::before { background: rgba(241, 245, 249, 0.5); }

        #container { display: flex; flex-direction: column; width: 100%; }
        #main-content { margin-top: 80px; margin-right: 260px; padding: 30px; transition: all 0.3s ease; }
        @media (max-width: 992px) { #main-content { margin-right: 0 !important; padding: 20px; } }

        /* --- استایل‌های اختصاصی صفحه کاربر --- */
        
        .profile-header {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-info));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .user-title h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .user-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 14px; }
        
        .telegram-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #229ED9;
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .telegram-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(34, 158, 217, 0.4); color: white; }

        .info-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 20px;
        }

        .info-card h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: var(--color-primary);
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed var(--glass-border);
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--text-muted); }
        .info-value { font-weight: 600; }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            border: none;
            padding: 15px;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            width: 100%;
        }

        .btn-block-user { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-block-user:hover { background: #ef4444; color: white; }

        .btn-unblock { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .btn-unblock:hover { background: #10b981; color: white; }

        .btn-money-add { background: var(--color-info); }
        .btn-money-add:hover { background: #2563eb; transform: translateY(-2px); }

        .btn-money-low { background: var(--color-warning); color: #fff; }
        .btn-money-low:hover { background: #d97706; transform: translateY(-2px); }

        .btn-agent { background: #8b5cf6; }
        .btn-agent:hover { background: #7c3aed; transform: translateY(-2px); }

        .btn-msg { background: #64748b; }
        .btn-msg:hover { background: #475569; transform: translateY(-2px); }

        /* --- مودال اختصاصی --- */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal-box {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            transform: scale(0.9);
            transition: transform 0.3s;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-overlay.active .modal-box { transform: scale(1); }

        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .modal-title { font-size: 18px; font-weight: bold; }
        .close-modal { cursor: pointer; font-size: 24px; color: var(--text-muted); }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted); }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background: var(--input-bg);
            color: var(--text-main);
            outline: none;
            transition: 0.3s;
        }
        
        /* اصلاح رنگ متن گزینه‌های سلکت */
        select.form-control option {
            color: #000; 
            background: #fff;
        }

        .form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }

        .btn-submit {
            width: 100%; padding: 12px; border-radius: 10px; border: none;
            background: var(--color-primary); color: white; cursor: pointer; font-weight: bold;
        }
        .btn-submit:hover { filter: brightness(1.1); }

        /* دکمه تم - دقیقاً مشابه صفحات دیگر */
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
            z-index: 3000; /* بالاتر از همه */
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
                
                <div class="profile-header">
                    <div class="avatar-circle">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="user-title">
                        <h1><?php echo $user['username']; ?></h1>
                        <p>شناسه عددی: <?php echo $user['id']; ?></p>
                    </div>
                    <a href="https://t.me/<?php echo $user['username']; ?>" target="_blank" class="telegram-btn">
                        <i class="fa-brands fa-telegram"></i>
                        مشاهده در تلگرام
                    </a>
                </div>

                <div class="info-container">
                    
                    <div class="info-card">
                        <h3><i class="fa-solid fa-circle-info"></i> مشخصات حساب</h3>
                        <div class="info-row">
                            <span class="info-label">وضعیت حساب</span>
                            <span class="info-value <?php echo ($user['User_Status'] == 'block' ? 'text-danger' : 'text-success'); ?>"><?php echo $status_label; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">موجودی کیف پول</span>
                            <span class="info-value" style="color: var(--color-primary);"><?php echo number_format($user['Balance']); ?> تومان</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">شماره موبایل</span>
                            <span class="info-value"><?php echo $number_display; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">نوع کاربری</span>
                            <span class="info-value"><?php echo $agent_display; ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3><i class="fa-solid fa-chart-pie"></i> آمار فعالیت</h3>
                        <div class="info-row">
                            <span class="info-label">تعداد زیرمجموعه</span>
                            <span class="info-value"><?php echo $user['affiliatescount']; ?> نفر</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">معرف (بالاسری)</span>
                            <span class="info-value"><?php echo ($user['affiliates'] ?: '---'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">محدودیت تست</span>
                            <span class="info-value"><?php echo $user['limit_usertest']; ?></span>
                        </div>
                    </div>

                </div>

                <div class="info-card">
                    <h3><i class="fa-solid fa-screwdriver-wrench"></i> عملیات مدیریت</h3>
                    <div class="actions-grid">
                        
                        <?php if($user['User_Status'] == 'block'): ?>
                            <a href="user.php?id=<?php echo $user['id'];?>&status=active" class="action-btn btn-unblock">
                                <i class="fa-solid fa-check"></i> رفع مسدودی
                            </a>
                        <?php else: ?>
                            <a href="user.php?id=<?php echo $user['id'];?>&status=block" class="action-btn btn-block-user">
                                <i class="fa-solid fa-ban"></i> مسدود کردن
                            </a>
                        <?php endif; ?>

                        <button onclick="openModal('modal-add-balance')" class="action-btn btn-money-add">
                            <i class="fa-solid fa-plus-circle"></i> افزایش موجودی
                        </button>

                        <button onclick="openModal('modal-low-balance')" class="action-btn btn-money-low">
                            <i class="fa-solid fa-minus-circle"></i> کسر موجودی
                        </button>

                        <button onclick="openModal('modal-change-agent')" class="action-btn btn-agent">
                            <i class="fa-solid fa-user-tag"></i> تغییر نوع کاربر
                        </button>

                        <a href="user.php?id=<?php echo $user['id'];?>&agent=f" class="action-btn btn-block-user" onclick="return confirm('آیا مطمئن هستید؟')">
                            <i class="fa-solid fa-user-xmark"></i> حذف نمایندگی
                        </a>

                        <button onclick="openModal('modal-send-msg')" class="action-btn btn-msg">
                            <i class="fa-solid fa-paper-plane"></i> ارسال پیام
                        </button>

                    </div>
                </div>

            </section>
        </section>
    </section>

    <div id="modal-add-balance" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">افزایش موجودی کاربر</span>
                <span class="close-modal" onclick="closeModal('modal-add-balance')">&times;</span>
            </div>
            <form action="user.php" method="GET">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label class="form-label">مبلغ (تومان)</label>
                    <input type="number" name="priceadd" class="form-control" placeholder="مثلا 50000" required>
                </div>
                <button type="submit" class="btn-submit">افزایش موجودی</button>
            </form>
        </div>
    </div>

    <div id="modal-low-balance" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">کسر موجودی کاربر</span>
                <span class="close-modal" onclick="closeModal('modal-low-balance')">&times;</span>
            </div>
            <form action="user.php" method="GET">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label class="form-label">مبلغ کسر (تومان)</label>
                    <input type="number" name="pricelow" class="form-control" placeholder="مثلا 10000" required>
                </div>
                <button type="submit" class="btn-submit" style="background: var(--color-warning);">کسر موجودی</button>
            </form>
        </div>
    </div>

    <div id="modal-change-agent" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">تغییر سطح کاربری</span>
                <span class="close-modal" onclick="closeModal('modal-change-agent')">&times;</span>
            </div>
            <form action="user.php" method="GET">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label class="form-label">انتخاب سطح</label>
                    <select name="agent" class="form-control">
                        <option value="f" <?php if($user['agent']=='f') echo 'selected'; ?>>کاربر عادی</option>
                        <option value="n" <?php if($user['agent']=='n') echo 'selected'; ?>>نماینده معمولی</option>
                        <option value="n2" <?php if($user['agent']=='n2') echo 'selected'; ?>>نماینده پیشرفته</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit" style="background: #8b5cf6;">تغییر سطح</button>
            </form>
        </div>
    </div>

    <div id="modal-send-msg" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">ارسال پیام خصوصی</span>
                <span class="close-modal" onclick="closeModal('modal-send-msg')">&times;</span>
            </div>
            <form action="user.php" method="GET">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label class="form-label">متن پیام</label>
                    <textarea name="textmessage" class="form-control" rows="4" placeholder="پیام خود را بنویسید..." required></textarea>
                </div>
                <button type="submit" class="btn-submit" style="background: #64748b;">ارسال پیام</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('active'); }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('active');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('active');
                setTimeout(() => { event.target.style.display = 'none'; }, 300);
            }
        }

        const themeToggleBtn = document.querySelector('.theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'light') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        }

        function toggleTheme() {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'light') {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        }
    </script>
</body>
</html>