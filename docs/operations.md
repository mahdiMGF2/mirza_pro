# عملیات نصب و rollback

installer با `umask 077` اجرا می‌شود؛ لاگ موقت `/tmp/mirza_install.log` با permission `0600` ساخته می‌شود و فایل‌های config تولیدی با permission `0640` تنظیم می‌شوند.

در self-update، فایل جدید ابتدا اعتبارسنجی syntax می‌شود، سپس در فایل موقت مقصد نوشته و با `mv` جایگزین می‌شود. نسخه‌ی قبلی در `/root/install.sh.bak` باقی می‌ماند تا در صورت نیاز مدیر سیستم بتواند آن را به `/root/install.sh` برگرداند و لینک `/usr/local/bin/mirza` را دوباره همگام کند.

این تغییرات اعتبارسنجی منبع release را جایگزین امضای cryptographic نمی‌کنند؛ نصب production باید از release/tag مورد اعتماد و HTTPS استفاده کند. فایل backup updater و لاگ installer حاوی اطلاعات عملیاتی هستند و نباید عمومی یا در issueها منتشر شوند.
