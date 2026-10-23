<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تأكيد البريد الإلكتروني</title>
</head>
<body style="margin:0;padding:0;background:#faf6ee;font-family:Tahoma,Arial,sans-serif;color:#292524;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf6ee;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #eee7da;border-radius:18px;overflow:hidden;">
                    <tr>
                        <td style="background:#151310;padding:24px 28px;">
                            <div style="font-size:18px;font-weight:bold;color:#facc15;">{{ $appName }}</div>
                            <div style="font-size:12px;color:#a8a29e;margin-top:4px;">نظام إدارة المخزون</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px;">
                            <h1 style="margin:0 0 12px;font-size:22px;color:#1c1917;">مرحبًا {{ $user->name }}</h1>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.9;color:#57534e;">
                                تم إنشاء حساب مدير النظام الخاص بك. يرجى تأكيد بريدك الإلكتروني للمتابعة واستخدام لوحة إدارة المخزون.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td style="border-radius:10px;background:#facc15;">
                                        <a href="{{ $url }}" style="display:inline-block;padding:13px 26px;font-size:14px;font-weight:bold;color:#151310;text-decoration:none;">تأكيد البريد الإلكتروني</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;font-size:12px;line-height:1.8;color:#78716c;">
                                إذا لم يعمل الزر، انسخ الرابط التالي والصقه في المتصفح:
                            </p>
                            <p style="margin:0;font-size:11px;line-height:1.7;word-break:break-all;color:#0f766e;">{{ $url }}</p>
                            <p style="margin:24px 0 0;font-size:11px;line-height:1.8;color:#a8a29e;">
                                ينتهي هذا الرابط خلال 60 دقيقة. إذا لم تطلب إنشاء الحساب، يمكنك تجاهل هذه الرسالة.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #f2ebdd;padding:18px 28px;font-size:11px;color:#a8a29e;text-align:center;">
                            {{ $appName }} — جميع الحقوق محفوظة
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
