# راهنمای توسعه

## پیش‌نیازها

نسخه‌ی PHP مطابق `composer.json` (حداقل 8.2)، Composer و extensionهای بررسی‌شده در installer لازم هستند. برای اجرای smoke test دیتابیس، توکن Telegram یا سرویس خارجی لازم نیست.

## بررسی محلی

```bash
composer validate --no-check-publish --no-interaction
composer test
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
bash -n install.sh
git diff --check
```

هشدارهای deprecation نسخه‌ی PHP جدیدتر از نسخه‌ی پشتیبانی‌شده باید جدا از خطاهای syntax بررسی شوند. در حال حاضر برخی warningهای legacy در `jdf.php` وجود دارد.

## توسعه‌ی integration

برای پنل یا درگاه جدید، ابتدا contract مربوط را پیاده کنید و خروجی نرمال‌شده، خطای HTTP و خطای transport را با fixture تست کنید. مسیر legacy و fallback را تا پایان rollout حفظ کنید؛ token، cookie و پاسخ خام را در log ثبت نکنید.

## AGPL-3.0

این پروژه تحت AGPL-3.0-or-later است. تغییرات مشتق‌شده باید notice مجوز و attribution را حفظ کنند و هنگام ارائه‌ی نسخه‌ی قابل استفاده از شبکه، متن متناظر سورس تغییرکرده طبق AGPL در دسترس کاربران قرار گیرد.
