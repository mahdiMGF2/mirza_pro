<?php
session_start();
require_once __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../function.php'; 

// بررسی لاگین
$query = $pdo->prepare("SELECT * FROM admin WHERE username=:username");
$query->bindParam("username", $_SESSION["user"], PDO::PARAM_STR);
$query->execute();
$result = $query->fetch(PDO::FETCH_ASSOC);

if( !isset($_SESSION["user"]) || !$result ){
    header('Location: login.php');
    return;
}

$statusmessage = false;
$infomesssage = "";
$id_product = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
$product = select("product","*","id",$id_product,"select");

if($product == false){
    $statusmessage = true;
    $infomesssage = "محصول مورد نظر یافت نشد!";
} else {
    // پردازش فرم ویرایش
    if(isset($_GET['action']) && $_GET['action'] == "save"){
        
        // نام محصول
        $name_product = htmlspecialchars($_POST['name_product'], ENT_QUOTES, 'UTF-8');
        $prodcutcheck = select("product","*","name_product",$name_product,"count");
        
        // اگر نام تغییر کرده و تکراری است
        if($product['name_product'] != $name_product && $prodcutcheck != 0){
            $statusmessage = true;
            $infomesssage = "نام محصول تکراری است.";
        } else {
            if($product['name_product'] != $name_product){
                update("product","name_product",$name_product,"id",$id_product);
            }
        }

        // قیمت
        $price_product = htmlspecialchars($_POST['price_product'], ENT_QUOTES, 'UTF-8');
        if(!is_numeric($price_product)){
            $statusmessage = true; $infomesssage ="مبلغ محصول باید عدد باشد";
        } else if($product['price_product'] != $price_product){
            update("product","price_product",$price_product,"id",$id_product);
        }

        // حجم
        $Volume_constraint = htmlspecialchars($_POST['Volume_constraint'], ENT_QUOTES, 'UTF-8');
        if(!is_numeric($Volume_constraint)){
            $statusmessage = true; $infomesssage ="حجم محصول باید عدد باشد";
        } else if($product['Volume_constraint'] != $Volume_constraint){
            update("product","Volume_constraint",$Volume_constraint,"id",$id_product);
        }

        // زمان
        $Service_time = htmlspecialchars($_POST['Service_time'], ENT_QUOTES, 'UTF-8');
        if(!is_numeric($Service_time)){
            $statusmessage = true; $infomesssage ="زمان محصول باید عدد باشد";
        } else if($product['Service_time'] != $Service_time){
            update("product","Service_time",$Service_time,"id",$id_product);
        }

        // نوع کاربر
        $agent = htmlspecialchars($_POST['agent'], ENT_QUOTES, 'UTF-8');
        if(!in_array($agent,['f','n','n2'])){
            $statusmessage = true; $infomesssage ="گروه کاربری نامعتبر است";
        } else if($product['agent'] != $agent){
            update("product","agent",$agent,"id",$id_product);
        }

        // دسته‌بندی
        $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
        if($product['category'] != $category){
            update("product","category",$category,"id",$id_product);
        }

        // یادداشت
        $note = htmlspecialchars($_POST['note'], ENT_QUOTES, 'UTF-8');
        if($product['note'] != $note){
            update("product","note",$note,"id",$id_product);
        }
        
        if(!$statusmessage){
             header('Location: product.php');
             exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش محصول | ربات میرزا</title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* --- استایل‌های پایه (مشترک) --- */
        :root {
            --bg-body: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --color-primary: #10b981;
            --color-rose: #f43f5e;
            --color-blue: #3b82f6;
            --color-purple: #8b5cf6;
            --input-bg: rgba(255, 255, 255, 0.05);
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

        /* --- استایل فرم ویرایش --- */
        .edit-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-header {
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-title i { color: var(--color-primary); }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        
        .form-control {
            width: 100%; padding: 12px 15px; border-radius: 10px;
            border: 1px solid var(--glass-border); background: var(--input-bg);
            color: var(--text-main); outline: none; transition: 0.3s;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        /* استایل اختصاصی برای select */
        select.form-control option { color: #333; background: #fff; }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--color-primary), #059669);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-back {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--text-main); }

        /* پیام خطا */
        .alert-error {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: var(--color-rose);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ریسپانسیو */
        @media (max-width: 768px) {
            .edit-card { padding: 20px; }
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
                
                <div class="edit-card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-pen-to-square"></i>
                            ویرایش محصول
                        </div>
                        <a href="product.php" class="btn-back">
                            <i class="fa-solid fa-arrow-right"></i> بازگشت
                        </a>
                    </div>

                    <?php if($statusmessage): ?>
                    <div class="alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo $infomesssage; ?>
                    </div>
                    <?php endif; ?>

                    <?php if($product): ?>
                    <form action="productedit.php?action=save&id=<?php echo $id_product ?>" method="POST">
                        
                        <div class="form-group">
                            <label class="form-label">نام محصول</label>
                            <input type="text" name="name_product" class="form-control" value="<?php echo $product['name_product']; ?>" required>
                        </div>

                        <div class="row" style="display:flex; gap:15px; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:250px;">
                                <label class="form-label">قیمت (تومان)</label>
                                <input type="number" name="price_product" class="form-control" value="<?php echo $product['price_product']; ?>" required>
                            </div>
                            <div class="form-group" style="flex:1; min-width:250px;">
                                <label class="form-label">حجم (GB)</label>
                                <input type="number" name="Volume_constraint" class="form-control" value="<?php echo $product['Volume_constraint']; ?>" required>
                            </div>
                        </div>

                        <div class="row" style="display:flex; gap:15px; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:250px;">
                                <label class="form-label">زمان (روز)</label>
                                <input type="number" name="Service_time" class="form-control" value="<?php echo $product['Service_time']; ?>" required>
                            </div>
                            <div class="form-group" style="flex:1; min-width:250px;">
                                <label class="form-label">نوع کاربر</label>
                                <select name="agent" class="form-control">
                                    <option value="f" <?php if($product['agent']=='f') echo 'selected'; ?>>کاربر عادی</option>
                                    <option value="n" <?php if($product['agent']=='n') echo 'selected'; ?>>نماینده معمولی</option>
                                    <option value="n2" <?php if($product['agent']=='n2') echo 'selected'; ?>>نماینده پیشرفته</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">دسته‌بندی</label>
                            <input type="text" name="category" class="form-control" value="<?php echo $product['category']; ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">یادداشت</label>
                            <textarea name="note" class="form-control" rows="3"><?php echo $product['note']; ?></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-check"></i> ذخیره تغییرات
                        </button>

                    </form>
                    <?php endif; ?>
                </div>

            </section>
        </section>
    </section>

    <script>
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