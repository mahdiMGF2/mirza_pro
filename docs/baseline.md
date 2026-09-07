# خط مبنای نسخه‌ی شخصی Mirza Bot

این سند خط مبنای بررسی مخزن `mahdiMGF2/mirzabot` در تاریخ ۲۰۲۶-۰۹-۰۷ است و مبنای تغییرات مرحله‌ای بعدی قرار می‌گیرد. هیچ قابلیت فعلی در این مرحله حذف نشده است.

## معماری و نقاط ورود

| بخش | مسیر | نقش |
|---|---|---|
| وبهوک اصلی | `index.php` | دریافت updateهای ربات اصلی |
| ربات‌های اضافی | `vpnbot/` | اجرای نمونه‌ها/ربات‌های ساخته‌شده |
| Mini App و API | `api/`, `app/` | احراز Telegram Web App، خدمات کاربر و مدیریت |
| پنل مدیریت | `panel/` | ورود، مدیریت کاربر، محصول، پرداخت، تنظیمات و گزارش |
| کارهای زمان‌بندی‌شده | `cronbot/` | فعال‌سازی، انقضا، اعلان، پرداخت، backup و پایش |
| اتصال پنل‌ها | فایل‌های PHP ریشه | Marzban، Marzneshin، Hiddify، X-UI، MikroTik، WGDashboard، IBSng و دیگر adapterها |
| پرداخت | `payment/` | درگاه‌های کارت‌به‌کارت، Zarinpal، Aqayepardakht، IranPay، NowPayments و Plisio |
| داده و migration | `db/` | تعریف جدول‌ها، seed، migration و index |

## نیازمندی‌ها و استقرار

- PHP `>= 8.2`، MySQL، Apache با `mod_rewrite` و HTTPS.
- وابستگی‌های Composer: `endroid/qr-code` و `phpoffice/phpspreadsheet`.
- extensionهای اصلی: `curl`, `pdo_mysql`, `mbstring`, `json`, `openssl`, `gd`, `zip`, `dom`, `xml`, `xmlreader`, `xmlwriter`, `simplexml`, `fileinfo`, `iconv`, `ctype`, `filter`, `zlib`, `session`.
- نصب از مسیر `install.sh` یا installer وب انجام می‌شود؛ `vendor/` در مخزن نیست و از Composer یا bundle انتشار ساخته می‌شود.
- cronها و بعضی قابلیت‌های backup/updater به `shell_exec`/`exec` وابسته‌اند؛ در هاست اشتراکی باید دستی پیکربندی شوند.

## پیکربندی و اسرار

`config.php` در وضعیت فعلی شامل placeholderهای مربوط به اتصال MySQL، توکن Telegram، شناسه مدیر، دامنه و نام ربات است. اعتبار پنل‌ها، درگاه‌ها و botهای اضافی نیز در دیتابیس ذخیره می‌شود. از این مرحله، همین مقادیر می‌توانند بدون تغییر رفتار نصب فعلی از environment نیز خوانده شوند: `MIRZA_DB_HOST`, `MIRZA_DB_NAME`, `MIRZA_DB_USER`, `MIRZA_DB_PASSWORD`, `MIRZA_TELEGRAM_BOT_TOKEN`, `MIRZA_ADMIN_CHAT_ID`, `MIRZA_DOMAIN`, `MIRZA_BOT_USERNAME` و `MIRZA_BRAND_NAME`. مقدار environment فقط در صورت تعریف‌شدن اولویت دارد. `MIRZA_BRAND_NAME` نام نمایشی است و به‌صورت پیش‌فرض `Root Bot` است؛ نام کلاس‌ها، جدول‌ها، مسیرهای legacy و attribution پروژه تغییر نمی‌کند. فایل‌های runtime مانند `error_log`, `storage/`, `api/hash.txt` و خروجی‌های Excel نباید در release یا backup عمومی قرار گیرند.

## کنترل‌های موجود

- PDO با prepared statement، `ERRMODE_EXCEPTION` و `ATTR_EMULATE_PREPARES=false`.
- اعتبارسنجی امضای Telegram Web App در `api/verify.php` و انقضای داده‌ی init.
- session cookieهای `HttpOnly`/`SameSite=Lax` و CSRF در پنل.
- password hashing برای حساب‌های جدید پنل و rate limit محلی ورود.
- محافظت `.htaccess` از فایل‌های JSON/SQL/ZIP و قفل زمان نصب.
- محدودسازی شناسه‌های SQL پویا در schema و استفاده‌ی گسترده از query پارامتری.

## ریسک‌های ثبت‌شده برای مراحل بعد

1. `install.sh` و self-update دسترسی root و اجرای shell دارند و باید pin/checksum و کنترل ورودی قوی‌تری داشته باشند.
2. backup می‌تواند داده‌ی کاربر، config و اطلاعات اتصال را در archive/SQL جمع کند و سیاست رمزگذاری، retention و ارسال آن باید بازطراحی شود.
3. منطق integrationها و queryهای کسب‌وکار در فایل‌های متعدد متمرکز است و قرارداد مشترک adapter ندارد.
4. مسیرهای عمومی API، tokenهای کاربر، احراز Mini App و نشست پنل باید یکپارچه و با timeout/rotation/rate limit قابل کنترل شوند.
5. migration runner خطاها را log می‌کند و در برخی موارد ادامه می‌دهد؛ migrationهای بحرانی باید وضعیت و fail-fast مشخص داشته باشند.
6. تست خودکار رسمی برای وبهوک، پرداخت، adapterها و cronها در مخزن وجود ندارد.
7. migrationهای موفق اکنون در جدول داخلی `mirza_schema_migrations` ثبت می‌شوند و backupهای موقت با نام تصادفی و permission `0600` ساخته و پس از استفاده حذف می‌شوند.
8. installer اکنون با `umask 077` اجرا می‌شود، لاگ مرحله‌ای را خصوصی می‌کند، قبل از self-update نسخه‌ی قبلی را در `/root/install.sh.bak` نگه می‌دارد و config تولیدی را پس از کپی با permission `0640` تنظیم می‌کند.

## معیار پذیرش مراحل بعد

- هیچ مسیر موجود، جدول موجود یا قابلیت اعلام‌شده حذف نشود.
- تغییرات schema دارای migration برگشت‌پذیر یا مسیر restore باشند.
- اسرار در log، response، release و backup بدون محافظت ظاهر نشوند.
- هر تغییر امنیتی با تست/بازبینی مسیر موفق و خطای متناظر همراه باشد.
- سازگاری PHP 8.2 و AGPL-3.0 حفظ شود.
