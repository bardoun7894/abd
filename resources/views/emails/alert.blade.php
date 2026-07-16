<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $alertTitle }}</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;padding:24px;">
        <h2 style="color:#111111;margin-top:0;">{{ $alertTitle }}</h2>
        <p style="color:#333333;line-height:1.8;white-space:pre-line;">{{ $alertBody }}</p>
        <hr style="border:none;border-top:1px solid #eeeeee;margin:24px 0;">
        <p style="color:#999999;font-size:12px;">هذه رسالة تنبيه تلقائية من النظام، الرجاء عدم الرد عليها.</p>
    </div>
</body>
</html>
