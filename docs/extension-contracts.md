# قراردادهای توسعه

این قراردادها یک مرز تدریجی برای استخراج integrationها هستند و در این مرحله رفتار adapterهای فعلی را تغییر نمی‌دهند.

## HTTP client

`src/Contracts/HttpClientInterface.php` قرارداد حداقلی client فعلی را تعریف می‌کند. `CurlRequest` همچنان implementation پیش‌فرض و سازگار است؛ کدهای موجود همچنان می‌توانند آن را با همان متدهای `get`, `post`, `put`, `delete` و `PATCH` استفاده کنند. متد `patch` نام canonical قرارداد است و alias قبلی `PATCH` عمداً حفظ شده است.

پاسخ client یک آرایه با این شکل است:

- موفق: `status` کد HTTP و `body` متن پاسخ
- خطای transport: `status` و `body` برابر `null` و `error` شامل پیام curl

هر implementation جدید باید timeout، وضعیت TLS، headerهای احراز هویت و redaction لاگ را صریحاً تعریف کند و نباید token یا cookie را در خطاها ثبت کند.

## Panel adapter

`src/Contracts/PanelAdapterInterface.php` شکل هدف adapterهای پنل را مشخص می‌کند:

- `createUser`: ساخت سرویس و بازگرداندن داده‌ی نرمال‌شده
- `getUser`: خواندن وضعیت سرویس
- `deleteUser`: حذف سرویس

وضعیت فعلی هنوز از توابع legacy در فایل‌های ریشه و dispatch بزرگ `ManagePanel` استفاده می‌کند. استخراج هر پنل باید جداگانه، با contract test برای پاسخ موفق/خطای HTTP و بدون تغییر mapping خروجی انجام شود.

## روش مهاجرت

1. یک adapter را انتخاب و آن را پشت این interface قرار دهید.
2. پاسخ‌های موفق، خطای API و خطای transport را با fixture ثابت مقایسه کنید.
3. dispatch قدیمی را به‌عنوان fallback نگه دارید.
4. پس از تأیید staging، adapter بعدی را استخراج کنید.
