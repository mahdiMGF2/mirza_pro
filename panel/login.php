<?php
ini_set('session.cookie_httponly', true);
session_start();
session_regenerate_id(true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';
require_once __DIR__ . '/../botapi.php';
$allowed_ips = select("setting","*",null,null,"select");

$user_ip = $_SERVER['REMOTE_ADDR'];
$admin_ids = select("admin", "id_admin", null, null, "FETCH_COLUMN");
$check_ip = $allowed_ips['iplogin'] == $user_ip ? true : false;
$texterrr = "";
$_SESSION["user"] = null;
if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
    $query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
    $query->bindParam("username", $username, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ( !$result ) {
        $texterrr = 'نام کاربری یا رمزعبور وارد شده اشتباه است!';
    } else {
        if ( $password == $result["password"]) {
            foreach ($admin_ids as $admin) {
                $texts = "کاربر با نام کاربری $username وارد پنل تحت وب شد";
                sendmessage($admin, $texts, null, 'html');
            }
            $_SESSION["user"] = $result["username"];
            header('Location: index.php');
            exit;
        } else {
            $texterrr =  'رمز صحیح نمی باشد';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    
    <style>
        :root {
            --aurora-green: #4ade80;
            --btn-gradient: linear-gradient(135deg, #10b981 0%, #8b5cf6 100%);
            --glass-bg: rgba(15, 23, 42, 0.85);
            --border-color: rgba(167, 243, 208, 0.25);
            --text-main: #f0fdf4;
            --input-bg: rgba(255, 255, 255, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Vazir', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: url('https://www.visitfinland.com/dam/jcr:10ead74c-e5bf-4742-aa7a-1bec21cd4130/800L__20160205_01_Thomas%20Kast_noise.jpg') no-repeat center center fixed;
            background-size: cover;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.4'/%3E%3C/svg%3E");
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
            mix-blend-mode: overlay;
        }

        .paper-tint {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(2, 6, 23, 0.75);
            backdrop-filter: sepia(10%) contrast(100%) brightness(80%);
            z-index: 0;
        }

        /* --- استایل باکس مادر (دسکتاپ) --- */
        .login-card {
            width: 100%;
            max-width: 400px; 
            min-height: 460px;
            padding: 45px 35px;
            
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            box-shadow: 0 30px 60px -10px rgba(0, 0, 0, 0.9);
            text-align: center;
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* --- مدیا کوئری برای موبایل (اضافه شده برای حل مشکل شما) --- */
        @media screen and (max-width: 480px) {
            .login-card {
                max-width: 90%; /* در موبایل عرض ۹۰ درصد صفحه باشد */
                min-height: auto; /* ارتفاع خودکار */
                padding: 30px 20px; /* پدینگ کمتر */
                margin: 20px; /* فاصله از لبه‌ها */
            }
            
            .login-header h2 {
                font-size: 22px !important;
            }
            
            .btn-submit {
                font-size: 14px !important;
            }
        }

        .login-header h2 {
            color: var(--text-main);
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 0 15px rgba(74, 222, 128, 0.3);
        }

        .login-header p {
            color: #d1fae5;
            font-size: 14px;
            margin-bottom: 35px;
            opacity: 0.7;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: right;
        }

        .form-label {
            display: block;
            color: var(--aurora-green);
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* --- استایل اینپوت‌ها --- */
        .input-container-merged {
            display: flex;
            align-items: center;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
            height: 50px; 
            padding: 0 10px;
            position: relative;
        }

        .input-icon {
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            font-size: 18px;
            transition: 0.3s;
        }

        .form-control {
            flex: 1;
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 14px;
            height: 100%;
            padding: 0 10px;
            outline: none;
            width: 100%; /* برای جلوگیری از سرریز در موبایل */
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .input-container-merged:focus-within {
            border-color: var(--aurora-green);
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.15);
        }

        .input-container-merged:focus-within .input-icon {
            color: var(--aurora-green);
        }

        .toggle-password {
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--aurora-green);
        }

        /* --- بخش دکمه --- */
        .submit-wrapper {
            display: flex;
            align-items: stretch;
            gap: 12px;
            margin-top: 30px;
        }

        .submit-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            min-width: 50px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--aurora-green);
            transition: 0.3s;
        }
        
        .btn-submit {
            flex: 1; 
            height: 50px;
            background: var(--btn-gradient);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 25px rgba(139, 92, 246, 0.3);
            text-align: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 35px rgba(74, 222, 128, 0.4);
            filter: brightness(1.1);
        }
        
        .submit-wrapper:hover .submit-icon-box {
            border-color: var(--aurora-green);
            background: rgba(74, 222, 128, 0.1);
        }

        .error-message {
            background: rgba(220, 38, 38, 0.15);
            color: #fda4af;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid rgba(220, 38, 38, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .ip-error-card {
            background: var(--glass-bg);
            padding: 30px;
            border-radius: 28px;
            border: 1px solid var(--aurora-green);
            text-align: center;
            color: #fff;
            max-width: 90%;
            width: 500px;
            backdrop-filter: blur(25px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
            z-index: 10;
        }
        
        .ip-box {
            background: rgba(0, 0, 0, 0.4);
            padding: 18px;
            border-radius: 12px;
            font-family: monospace;
            color: var(--aurora-green);
            border: 1px dashed var(--aurora-green);
            margin-top: 25px;
            font-size: 16px;
            direction: ltr;
            word-break: break-all; /* جلوگیری از بیرون زدن متن IP */
        }

    </style>
</head>

<body>

    <div class="paper-tint"></div>

    <?php if(!$check_ip){ ?>
        <div class="ip-error-card">
            <svg xmlns="http://www.w3.org/2000/svg" width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:20px">
                <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h2 style="font-size: 22px; margin-bottom: 15px;">دسترسی محدود شده</h2>
            <p style="color: #d1fae5; line-height: 1.8; font-size: 14px;">برای ورود به سیستم، آی‌پی زیر را در تنظیمات ربات ثبت کنید.</p>
            <div class="ip-box"><?php echo $user_ip; ?></div>
        </div>
    <?php } ?>

    <?php if($check_ip){ ?>
        <div class="login-card">
            <div class="login-header">
                <h2>پنل مدیریت</h2>
                <p>خوش آمدید، لطفاً وارد حساب خود شوید</p>
            </div>

            <?php if(!empty($texterrr)): ?>
                <div class="error-message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo $texterrr; ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label class="form-label">نام کاربری</label>
                    <div class="input-container-merged">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <input type="text" name="username" class="form-control" placeholder="نام کاربری..." required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">رمز عبور</label>
                    <div class="input-container-merged">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eyeOffIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="submit-wrapper">
                    <div class="submit-icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(180deg);">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                    </div>
                    <button type="submit" name="login" class="btn-submit">
                        ورود به پنل
                    </button>
                </div>

            </form>
        </div>
    <?php } ?>

    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("passwordInput");
            var eyeIcon = document.getElementById("eyeIcon");
            var eyeOffIcon = document.getElementById("eyeOffIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.style.display = "none";
                eyeOffIcon.style.display = "block";
            } else {
                passwordInput.type = "password";
                eyeIcon.style.display = "block";
                eyeOffIcon.style.display = "none";
            }
        }
    </script>
</body>
</html>