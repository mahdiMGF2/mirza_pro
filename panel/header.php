<style>
    /* --- هدر (نوار بالا) --- */
    .header {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: 70px;
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--glass-border);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 30px; z-index: 1001; transition: all 0.3s ease;
    }

    /* لوگو */
    .logo {
        font-size: 24px; font-weight: 800; color: var(--text-main);
        text-decoration: none; display: flex; align-items: center; gap: 10px;
    }
    .logo span { color: var(--color-primary); }

    /* دکمه باز/بسته کردن منو */
    .sidebar-toggle-box {
        cursor: pointer; font-size: 22px; color: var(--text-muted);
        transition: 0.3s; display: flex; align-items: center;
        width: 40px; height: 40px; justify-content: center; border-radius: 10px;
    }
    .sidebar-toggle-box:hover { background: rgba(255,255,255,0.05); color: var(--text-main); }

    /* --- بخش پروفایل کاربر --- */
    .top-nav .nav-list {
        list-style: none; display: flex; align-items: center; gap: 20px; margin: 0; padding: 0;
    }

    .dropdown { position: relative; }

    .user-profile {
        display: flex; align-items: center; gap: 12px; cursor: pointer;
        padding: 6px 12px; border-radius: 50px;
        transition: 0.3s; border: 1px solid transparent;
    }

    .user-profile:hover, .dropdown.active .user-profile {
        background: rgba(255, 255, 255, 0.05); border-color: var(--glass-border);
    }

    .user-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        object-fit: cover; border: 2px solid var(--color-primary); padding: 2px;
    }

    .user-info {
        display: flex; flex-direction: column; align-items: flex-start; line-height: 1.3;
    }

    .user-title { font-size: 14px; font-weight: 700; color: var(--text-main); }
    .user-role { font-size: 11px; color: var(--text-muted); }

    /* --- منوی کشویی --- */
    .dropdown-menu {
        position: absolute; top: 130%; left: 0;
        background: rgba(15, 23, 42, 0.95); 
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px); border-radius: 16px; width: 220px;
        padding: 10px; display: none; flex-direction: column;
        box-shadow: 0 15px 40px rgba(0,0,0,0.5);
        transform-origin: top left; animation: dropdownFade 0.2s ease;
    }

    [data-theme="light"] .dropdown-menu {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-10px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .dropdown.active .dropdown-menu { display: flex; }
    .dropdown-menu li { list-style: none; width: 100%; }

    .dropdown-header {
        text-align: center; padding: 15px 10px;
        background: rgba(255, 255, 255, 0.05); border-radius: 12px; margin-bottom: 8px;
    }
    [data-theme="light"] .dropdown-header { background: rgba(0, 0, 0, 0.05); }

    .dropdown-header strong { display: block; font-size: 16px; margin-bottom: 2px; color: var(--text-main); }
    .dropdown-header span { font-size: 12px; color: var(--text-muted); }

    .dropdown-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 5px 0; border: none; width: 100%; }
    [data-theme="light"] .dropdown-divider { background: rgba(0,0,0,0.1); }

    .dropdown-menu a {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 15px; color: var(--text-main); text-decoration: none;
        font-size: 14px; border-radius: 10px; transition: 0.2s; cursor: pointer;
    }

    .dropdown-menu a:hover {
        background: rgba(255,255,255,0.08); color: var(--color-primary); padding-right: 20px;
    }
    [data-theme="light"] .dropdown-menu a:hover { background: rgba(0,0,0,0.05); }

    .btn-logout:hover { background: rgba(239, 68, 68, 0.15) !important; color: #ef4444 !important; }

    @media (max-width: 768px) {
        .user-info, .fa-chevron-down { display: none; }
        .user-profile { padding: 0; border: none; }
        .user-profile:hover { background: transparent; }
        .dropdown-menu { left: -10px; width: 200px; }
    }

    /* --- سایدبار --- */
    aside {
        width: 260px; position: fixed; top: 70px; bottom: 0; right: 0;
        background: var(--glass-bg); backdrop-filter: blur(12px);
        border-left: 1px solid var(--glass-border); overflow-y: auto;
        z-index: 1002; /* بالاتر از هدر و اورلی */
        transition: transform 0.3s ease; padding: 20px 15px;
    }

    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .sidebar-menu li { margin-bottom: 8px; }

    .sidebar-menu a {
        display: flex; align-items: center; gap: 15px; padding: 12px 15px;
        color: var(--text-muted); text-decoration: none; border-radius: 12px;
        transition: all 0.3s; font-size: 14px; font-weight: 500;
    }

    .sidebar-menu i { font-size: 18px; width: 25px; text-align: center; transition: 0.3s; }

    .sidebar-menu a:hover, .sidebar-menu a.active {
        background: rgba(16, 185, 129, 0.15); color: var(--color-primary);
    }
    .sidebar-menu a:hover i { transform: scale(1.1); }

    aside::-webkit-scrollbar { width: 5px; }
    aside::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* --- اورلی (لایه سیاه پشت منو) --- */
    .sidebar-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); /* سیاه نیمه شفاف */
        backdrop-filter: blur(3px); /* کمی تاری */
        z-index: 1000; /* زیر سایدبار، بالای محتوا */
        display: none; opacity: 0; transition: opacity 0.3s;
    }

    /* --- ریسپانسیو --- */
    @media (max-width: 992px) {
        aside { transform: translateX(100%); }
        
        /* وقتی منو باز است */
        body.sidebar-open aside { transform: translateX(0); }
        
        /* نمایش اورلی وقتی منو باز است */
        body.sidebar-open .sidebar-overlay { display: block; opacity: 1; }
        
        .header { padding: 0 15px; }
        #main-content { margin-right: 0 !important; }
    }

    @media (min-width: 993px) {
        #main-content { margin-right: 260px; }
        body.sidebar-closed aside { transform: translateX(100%); }
        body.sidebar-closed #main-content { margin-right: 0; }
        
        /* در دسکتاپ اورلی لازم نیست */
        .sidebar-overlay { display: none !important; }
    }
</style>

<header class="header">
    <div style="display: flex; align-items: center; gap: 20px;">
        <div class="sidebar-toggle-box" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
        
        <a href="index.php" class="logo">
            <i class="fa-solid fa-robot" style="color: var(--color-primary);"></i>
            ربات <span>میرزا</span>
        </a>
    </div>

    <div class="top-nav">
        <ul class="nav-list">
            <li class="dropdown" onclick="toggleProfileMenu(this)">
                
                <div class="user-profile">
                    <img alt="avatar" src="img/avatar1_small.jpg" class="user-avatar">
                    <div class="user-info">
                        <span class="user-title">حساب کاربری</span>
                        <span class="user-role"><?php echo $_SESSION["user"]; ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 12px; color: var(--text-muted);"></i>
                </div>
                
                <ul class="dropdown-menu">
                    <li class="dropdown-header">
                        <strong><?php echo $_SESSION["user"]; ?></strong>
                        <span>مدیر کل</span>
                    </li>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a href="#">
                            <i class="fa-solid fa-gear"></i> تنظیمات
                        </a>
                    </li>
                    
                    <li>
                        <a onclick="toggleThemeHeader(event)">
                            <i class="fa-solid fa-moon" id="theme-icon-header"></i>
                            <span id="theme-text-header">حالت شب</span>
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a href="login.php" class="btn-logout" style="color: #ef4444;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> خروج
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>

<aside>
    <div id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge-high"></i><span>صفحه اصلی</span></a></li>
            <li><a href="users.php"><i class="fa-solid fa-users"></i><span>کاربران</span></a></li>    
            <li><a href="invoice.php"><i class="fa-solid fa-cart-shopping"></i><span>سفارشات</span></a></li>   
            <li><a href="service.php"><i class="fa-solid fa-server"></i><span>سرویس ها</span></a></li>   
            <li><a href="product.php"><i class="fa-solid fa-box-open"></i><span>محصولات</span></a></li>
            <li><a href="payment.php"><i class="fa-solid fa-credit-card"></i><span>تراکنش ها</span></a></li>   
            <li><a href="cancelService.php"><i class="fa-solid fa-trash-can"></i><span>حذف سرویس</span></a></li> 
            <li><a href="keyboard.php"><i class="fa-solid fa-keyboard"></i><span>چیدمان کیبورد</span></a></li>
        </ul>
    </div>
</aside>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<script>
    function toggleSidebar() {
        if (window.innerWidth > 992) {
            document.body.classList.toggle('sidebar-closed');
        } else {
            document.body.classList.toggle('sidebar-open');
        }
    }

    function toggleProfileMenu(element) {
        element.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        const profileDropdown = document.querySelector('.dropdown');
        if (profileDropdown && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove('active');
        }
    });

    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.sidebar-menu a');
    const menuLength = menuItem.length;
    for (let i = 0; i < menuLength; i++) {
        if (menuItem[i].href === currentLocation) { menuItem[i].classList.add("active"); }
        if(currentLocation.includes('user.php') || currentLocation.includes('useredit.php')) {
            if(menuItem[i].href.includes('users.php')) menuItem[i].classList.add("active");
        }
        if(currentLocation.includes('productedit.php')) {
            if(menuItem[i].href.includes('product.php')) menuItem[i].classList.add("active");
        }
    }

    // --- مدیریت تم (شب/روز) ---
    const themeIconHeader = document.getElementById('theme-icon-header');
    const themeTextHeader = document.getElementById('theme-text-header');
    
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
        document.documentElement.setAttribute('data-theme', storedTheme);
        updateThemeUI(storedTheme);
    }

    function toggleThemeHeader(e) {
        e.stopPropagation();
        let current = document.documentElement.getAttribute('data-theme');
        let newTheme = (current === 'light') ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    }

    function updateThemeUI(theme) {
        if (theme === 'light') {
            themeIconHeader.classList.replace('fa-moon', 'fa-sun');
            themeTextHeader.innerText = 'حالت روز';
        } else {
            themeIconHeader.classList.replace('fa-sun', 'fa-moon');
            themeTextHeader.innerText = 'حالت شب';
        }
    }
</script>