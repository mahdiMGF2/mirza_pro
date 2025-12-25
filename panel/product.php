<?php
session_start();
require_once __DIR__ . '/../config.php';
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

// دریافت لیست محصولات و پنل‌ها
$query = $pdo->prepare("SELECT * FROM product ORDER BY id ASC");
$query->execute();
$listinvoice = $query->fetchAll(); // متغیر محصولات طبق کد شما

$query = $pdo->prepare("SELECT * FROM marzban_panel");
$query->execute();
$listpanel = $query->fetchAll();

// --- لاجیک افزودن محصول ---
$nameProduct = $_POST['nameproduct'] ?? null;
if(!empty($nameProduct)){
    $randomString = bin2hex(random_bytes(2));
    $userdata['data_limit_reset'] = "no_reset";
    
    // بررسی تکراری بودن
    $product_count = select("product","*","name_product",$nameProduct,"count");
    if($product_count != 0){
        echo "<script>alert('محصول از قبل وجود دارد'); window.location.href='product.php';</script>";
        return;
    }
    
    $hidepanel = "{}";
    // دریافت مقادیر فرم
    $priceProduct   = $_POST['price_product']   ?? '';
    $volumeProduct  = $_POST['volume_product']  ?? '';
    $serviceTime    = $_POST['time_product']    ?? '';
    $location       = $_POST['namepanel']       ?? '';
    $agentProduct   = $_POST['agent_product']   ?? '';
    $category       = $_POST['cetegory_product'] ?? '';
    $note           = $_POST['note_product']    ?? '';
    $dataLimitReset = $userdata['data_limit_reset'];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO product (name_product,code_product,price_product,Volume_constraint,Service_time,Location,agent,data_limit_reset,note,category,hide_panel,one_buy_status) VALUES (:name_product,:code_product,:price_product,:Volume_constraint,:Service_time,:Location,:agent,:data_limit_reset,:note,:category,:hide_panel,'0')");
    
    $stmt->bindParam(':name_product', $nameProduct, PDO::PARAM_STR);
    $stmt->bindParam(':code_product', $randomString);
    $stmt->bindParam(':price_product', $priceProduct, PDO::PARAM_STR);
    $stmt->bindParam(':Volume_constraint', $volumeProduct, PDO::PARAM_STR);
    $stmt->bindParam(':Service_time', $serviceTime, PDO::PARAM_STR);
    $stmt->bindParam(':Location', $location, PDO::PARAM_STR);
    $stmt->bindParam(':agent', $agentProduct, PDO::PARAM_STR);
    $stmt->bindParam(':data_limit_reset', $dataLimitReset);
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindParam(':note', $note, PDO::PARAM_STR);
    $stmt->bindParam(':hide_panel', $hidepanel);
    $stmt->execute();
    
    header("Location: product.php");
    exit;
}

// --- لاجیک جابجایی محصول ---
if(isset($_GET['oneproduct'], $_GET['toweproduct']) && $_GET['oneproduct'] !== '' && $_GET['toweproduct'] !== ''){
    update("product", "id", 10000, "id", $_GET['oneproduct']);
    update("product", "id", intval($_GET['oneproduct']), "id", intval($_GET['toweproduct']));
    update("product", "id", intval($_GET['toweproduct']), "id", 10000);
    header("Location: product.php");
    exit;
}

// --- لاجیک حذف محصول ---
if(isset($_GET['removeid']) && $_GET['removeid'] !== ''){
    // توجه: در کد اصلی شما از $connect استفاده شده بود، اینجا با $pdo هماهنگ شد
    $stmt = $pdo->prepare("DELETE FROM product WHERE id = :id");
    $stmt->bindParam(':id', $_GET['removeid']);
    $stmt->execute();
    header("Location: product.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت محصولات | ربات میرزا</title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/font-face.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <style>
        /* --- استایل‌های پایه --- */
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
            --input-bg: rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] {
            --bg-body: #f0f2f5;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(0, 0, 0, 0.05);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-hover: rgba(255, 255, 255, 1);
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
            flex-wrap: wrap; gap: 15px;
        }

        .page-title { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .page-title i { color: var(--color-primary); }

        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .btn-add {
            background: linear-gradient(135deg, var(--color-primary), #059669);
            color: white; padding: 8px 15px; border-radius: 10px; text-decoration: none;
            font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;
            border: none; cursor: pointer; transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4); }

        .btn-move {
            background: linear-gradient(135deg, var(--color-purple), #7c3aed);
            color: white; padding: 8px 15px; border-radius: 10px; text-decoration: none;
            font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;
            border: none; cursor: pointer; transition: 0.3s;
        }
        .btn-move:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4); }

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

        .action-btns { display:flex; gap:5px; }
        .action-btns a {
            display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center;
            border-radius: 8px; color: white; transition: 0.3s; text-decoration:none;
        }
        .btn-edit { background: var(--color-info); }
        .btn-delete { background: var(--color-rose); }
        .action-btns a:hover { transform: translateY(-2px); opacity: 0.9; }

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

        /* --- مودال اختصاصی --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
            z-index: 2000; display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s;
        }
        .modal-box {
            background: var(--glass-bg); border: 1px solid var(--glass-border);
            padding: 30px; border-radius: 20px; width: 90%; max-width: 600px;
            transform: scale(0.9); transition: transform 0.3s;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            max-height: 90vh; overflow-y: auto;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-overlay.active .modal-box { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .modal-title { font-size: 18px; font-weight: bold; }
        .close-modal { cursor: pointer; font-size: 24px; color: var(--text-muted); }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted); }
        .form-control {
            width: 100%; padding: 12px; border-radius: 10px;
            border: 1px solid var(--glass-border); background: var(--input-bg);
            color: var(--text-main); outline: none; transition: 0.3s;
        }
        
        /* اصلاح رنگ متن گزینه‌های سلکت */
        select.form-control option { color: #000; background: #fff; }
        
        .form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
        
        .btn-submit {
            width: 100%; padding: 12px; border-radius: 10px; border: none;
            background: var(--color-primary); color: white; cursor: pointer; font-weight: bold;
        }
        .btn-submit:hover { filter: brightness(1.1); }

        /* دکمه تم */
        .theme-toggle {
            position: fixed; bottom: 30px; left: 30px; width: 50px; height: 50px;
            background: var(--color-purple); border-radius: 50%; display: flex; align-items: center;
            justify-content: center; cursor: pointer; box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
            z-index: 3000; transition: transform 0.3s; color: white; font-size: 20px; border: none;
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
                            <i class="fa-solid fa-box-open"></i>
                            لیست محصولات
                        </div>
                        <div class="header-actions">
                            <button onclick="openModal('modal-add-product')" class="btn-add">
                                <i class="fa-solid fa-plus"></i> افزودن محصول
                            </button>
                            <button onclick="openModal('modal-move-product')" class="btn-move">
                                <i class="fa-solid fa-arrow-down-up-across-line"></i> جابجایی
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="productsTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>شناسه</th>
                                    <th>نام محصول</th>
                                    <th>قیمت (تومان)</th>
                                    <th>حجم (GB)</th>
                                    <th>زمان (روز)</th>
                                    <th>لوکیشن</th>
                                    <th>گروه کاربری</th>
                                    <th>دسته‌بندی</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($listinvoice as $list): 
                                    $category = ($list['category'] == null) ? 'ندارد' : $list['category'];
                                    
                                    // تعیین متن برای نوع ایجنت
                                    $agent_type = 'عادی';
                                    $agent_badge = 'badge-gray';
                                    if($list['agent'] == 'n') { $agent_type = 'نماینده'; $agent_badge = 'badge-purple'; }
                                    if($list['agent'] == 'n2') { $agent_type = 'نماینده ویژه'; $agent_badge = 'badge-warning'; }
                                ?>
                                <tr>
                                    <td><?php echo $list['id']; ?></td>
                                    <td><?php echo $list['name_product']; ?></td>
                                    <td><span class="badge badge-success"><?php echo number_format($list['price_product']); ?></span></td>
                                    <td><span class="badge badge-info"><?php echo $list['Volume_constraint']; ?></span></td>
                                    <td><span class="badge badge-warning"><?php echo $list['Service_time']; ?></span></td>
                                    <td><span class="badge badge-cyan"><?php echo $list['Location']; ?></span></td>
                                    <td><span class="badge <?php echo $agent_badge; ?>"><?php echo $agent_type; ?></span></td>
                                    <td><?php echo $category; ?></td>
                                    <td class="action-btns">
                                        <a href="productedit.php?id=<?php echo $list['id']; ?>" class="btn-edit" title="ویرایش"><i class="fa-solid fa-pen"></i></a>
                                        <a href="product.php?removeid=<?php echo $list['id']; ?>" class="btn-delete" title="حذف" onclick="return confirm('آیا از حذف این محصول مطمئن هستید؟')"><i class="fa-solid fa-trash"></i></a>
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

    <div id="modal-add-product" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">افزودن محصول جدید</span>
                <span class="close-modal" onclick="closeModal('modal-add-product')">&times;</span>
            </div>
            <form action="product.php" method="POST">
                <div class="form-group">
                    <label class="form-label">نام محصول</label>
                    <input type="text" name="nameproduct" class="form-control" placeholder="نام محصول را وارد کنید" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">پنل (موقعیت)</label>
                    <select name="namepanel" class="form-control" required>
                        <option value="/all">تمامی پنل ها</option>
                        <?php foreach($listpanel as $panel): ?>
                            <option value="<?php echo $panel['name_panel']; ?>"><?php echo $panel['name_panel']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row" style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">قیمت (تومان)</label>
                        <input type="number" name="price_product" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">حجم (GB)</label>
                        <input type="number" name="volume_product" class="form-control" required>
                    </div>
                </div>

                <div class="row" style="display:flex; gap:10px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">زمان (روز)</label>
                        <input type="number" name="time_product" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">نوع کاربر</label>
                        <select name="agent_product" class="form-control" required>
                            <option value="f">کاربر عادی</option>
                            <option value="n">نماینده</option>
                            <option value="n2">نماینده پیشرفته</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">دسته‌بندی</label>
                    <input type="text" name="cetegory_product" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <input type="text" name="note_product" class="form-control" required>
                </div>

                <button type="submit" class="btn-submit">افزودن محصول</button>
            </form>
        </div>
    </div>

    <div id="modal-move-product" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <span class="modal-title">جابجایی ردیف محصولات</span>
                <span class="close-modal" onclick="closeModal('modal-move-product')">&times;</span>
            </div>
            <form action="product.php" method="GET">
                <div class="form-group">
                    <label class="form-label">شناسه محصول اول</label>
                    <input type="number" name="oneproduct" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">شناسه محصول دوم</label>
                    <input type="number" name="toweproduct" class="form-control" required>
                </div>
                <button type="submit" class="btn-submit" style="background: var(--color-purple);">جابجایی</button>
            </form>
        </div>
    </div>

    <script src="js/jquery.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json"
                },
                "order": [[ 0, "asc" ]],
                "pageLength": 10,
                "dom": '<"top"lf>rt<"bottom"ip><"clear">'
            });
        });

        // توابع باز و بسته کردن مودال‌ها
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


    </script>
</body>
</html>