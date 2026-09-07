# rollout و rollback

## ترتیب rollout

1. از دیتابیس و مسیر runtime backup خصوصی بگیرید و restore آن را در staging امتحان کنید.
2. bundle/tag مورد اعتماد را در staging نصب کنید و `composer test`، lint PHP، `bash -n install.sh` و smoke مسیرهای وبهوک/API را اجرا کنید.
3. migrationها را بررسی کنید؛ جدول `mirza_schema_migrations` باید migrationهای موفق را ثبت کند.
4. یک خرید آزمایشی، callback پرداخت، تمدید، یک cron حیاتی و یک درخواست پنل را در staging اجرا کنید.
5. در production ابتدا maintenance کوتاه، سپس update، health check، webhook check و بررسی log انجام دهید.

## rollback

- در صورت خطای installer، نسخه‌ی قبلی self-update در `/root/install.sh.bak` قرار دارد؛ آن را به `/root/install.sh` برگردانید و `/usr/local/bin/mirza` را به همان فایل لینک کنید.
- در صورت خطای application، bundle قبلی را deploy کنید؛ migrationهای ثبت‌شده را حذف یا دستکاری نکنید مگر با migration برگشتی سازگار.
- در صورت آسیب داده، backup SQL را روی staging restore و سپس با توقف writeهای production، restore کنترل‌شده انجام دهید.
- پس از rollback، webhook، cron، login پنل، endpoint احراز هویت و ارسال اعلان را جداگانه بررسی کنید.

هیچ rollback نباید با حذف تصادفی کل پوشه‌ی production یا حذف جدول migration انجام شود. فایل‌های backup و log حاوی داده‌ی حساس‌اند و باید permission محدود داشته باشند.
