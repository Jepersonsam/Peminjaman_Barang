<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f4ff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <!-- Outer Wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f0f4ff; padding: 40px 0;">
    <tr>
        <td align="center">

            <!-- Container -->
            <table width="580" cellpadding="0" cellspacing="0" role="presentation" style="max-width:580px; width:100%;">

                <!-- ===== HEADER BRAND ===== -->
                <tr>
                    <td align="center" style="padding-bottom: 24px;">
                        <table cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td align="center">
                                    <!-- Icon/Logo Circle -->
                                    <div style="
                                        display:inline-block;
                                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                        width:64px; height:64px;
                                        border-radius:50%;
                                        text-align:center;
                                        line-height:64px;
                                        font-size:28px;
                                        box-shadow: 0 8px 24px rgba(102,126,234,0.45);
                                    ">🔐</div>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding-top:12px;">
                                    <span style="
                                        font-size:22px;
                                        font-weight:700;
                                        color:#2d3748;
                                        letter-spacing:-0.5px;
                                    ">{{ config('app.name') }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ===== MAIN CARD ===== -->
                <tr>
                    <td style="
                        background-color:#ffffff;
                        border-radius:16px;
                        overflow:hidden;
                        box-shadow: 0 4px 32px rgba(102,126,234,0.12), 0 1px 4px rgba(0,0,0,0.08);
                    ">

                        <!-- Card Top Gradient Bar -->
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="
                                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                    height: 6px;
                                    font-size:0;
                                    line-height:0;
                                ">&nbsp;</td>
                            </tr>
                        </table>

                        <!-- Card Content -->
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="padding: 44px 48px 36px 48px;">

                                    <!-- Title -->
                                    <h1 style="
                                        margin:0 0 8px 0;
                                        font-size:26px;
                                        font-weight:800;
                                        color:#1a202c;
                                        letter-spacing:-0.5px;
                                    ">Reset Password Anda</h1>
                                    <p style="
                                        margin:0 0 28px 0;
                                        font-size:14px;
                                        color:#a0aec0;
                                        letter-spacing:0.3px;
                                        text-transform:uppercase;
                                        font-weight:600;
                                    ">Permintaan atur ulang kata sandi</p>

                                    <!-- Divider -->
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                        <tr>
                                            <td style="
                                                height:1px;
                                                background: linear-gradient(to right, #667eea22, #764ba222, transparent);
                                                margin-bottom:28px;
                                                font-size:0;
                                                line-height:0;
                                            ">&nbsp;</td>
                                        </tr>
                                    </table>

                                    <!-- Greeting -->
                                    <p style="
                                        font-size:16px;
                                        color:#4a5568;
                                        line-height:1.7;
                                        margin:24px 0 16px 0;
                                    ">Halo 👋,</p>

                                    <!-- Body Text -->
                                    <p style="
                                        font-size:16px;
                                        color:#4a5568;
                                        line-height:1.7;
                                        margin:0 0 24px 0;
                                    ">
                                        Kami menerima permintaan untuk <strong style="color:#2d3748;">mengatur ulang password</strong>
                                        akun Anda. Klik tombol di bawah ini untuk melanjutkan proses reset password.
                                    </p>

                                    <!-- Info Box -->
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:32px;">
                                        <tr>
                                            <td style="
                                                background: linear-gradient(135deg, #f0f4ff 0%, #f8f0ff 100%);
                                                border-left: 4px solid #667eea;
                                                border-radius: 0 8px 8px 0;
                                                padding: 16px 20px;
                                            ">
                                                <table cellpadding="0" cellspacing="0" role="presentation">
                                                    <tr>
                                                        <td style="vertical-align:top; padding-right:12px; font-size:20px;">⏱️</td>
                                                        <td>
                                                            <p style="margin:0; font-size:14px; color:#553c9a; font-weight:600;">Link berlaku selama 60 menit</p>
                                                            <p style="margin:4px 0 0 0; font-size:13px; color:#6b7280; line-height:1.5;">
                                                                Jika Anda tidak merasa meminta reset password, abaikan email ini. Password Anda tetap aman.
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- CTA Button -->
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:36px;">
                                        <tr>
                                            <td align="center">
                                                <a href="{{ $url }}" target="_blank" style="
                                                    display:inline-block;
                                                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                    color:#ffffff;
                                                    text-decoration:none;
                                                    font-size:16px;
                                                    font-weight:700;
                                                    padding:16px 48px;
                                                    border-radius:50px;
                                                    letter-spacing:0.3px;
                                                    box-shadow: 0 8px 24px rgba(102,126,234,0.5);
                                                ">
                                                    🔑&nbsp; Reset Password Sekarang
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Security Tips -->
                                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:8px;">
                                        <tr>
                                            <td style="
                                                background-color:#fffbeb;
                                                border:1px solid #fde68a;
                                                border-radius:8px;
                                                padding:16px 20px;
                                            ">
                                                <p style="margin:0 0 8px 0; font-size:13px; font-weight:700; color:#92400e;">⚠️ Tips Keamanan</p>
                                                <ul style="margin:0; padding:0 0 0 18px; font-size:13px; color:#78350f; line-height:1.8;">
                                                    <li>Jangan bagikan link ini kepada siapapun.</li>
                                                    <li>Link hanya dapat digunakan satu kali.</li>
                                                    <li>Gunakan password yang kuat dan unik.</li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- ===== FOOTER ===== -->
                <tr>
                    <td align="center" style="padding:28px 0 16px 0;">
                        <p style="
                            margin:0;
                            font-size:12px;
                            color:#a0aec0;
                            line-height:1.7;
                            text-align:center;
                        ">
                            Email ini dikirim secara otomatis oleh sistem<br>
                            <strong style="color:#718096;">{{ config('app.name') }}</strong> &bull; {{ date('Y') }} &bull; Seluruh Hak Dilindungi
                        </p>
                        <p style="
                            margin:8px 0 0 0;
                            font-size:11px;
                            color:#cbd5e0;
                            text-align:center;
                        ">
                            Jika Anda tidak meminta ini, tidak ada tindakan yang perlu dilakukan.
                        </p>
                    </td>
                </tr>

            </table>
            <!-- /Container -->

        </td>
    </tr>
    </table>
    <!-- /Outer Wrapper -->

</body>
</html>
