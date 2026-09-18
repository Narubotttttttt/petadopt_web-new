<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F6F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F4F6F8; padding: 40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="560" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                    <!-- Brand Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #199CA4 0%, #0D5B62 100%); padding: 28px 32px; text-align: center;">
                            <h1 style="color: #FFFFFF; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: 0.5px;">
                                CDO Animal Welfare Society Inc.
                            </h1>
                            <p style="color: #E6F4F5; font-size: 12px; font-weight: 500; margin: 6px 0 0 0; letter-spacing: 0.5px;">
                                Administrative Portal Verification
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 32px 24px 32px; color: #334155; font-size: 14px; line-height: 1.6;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: #0F172A;">
                                Hello {{ $user->name }},
                            </p>

                            @if (!empty($isRegistrationOtp))
                                <p style="margin: 0 0 20px 0; color: #475569;">
                                    Please use the 6-digit verification code below to verify your email address and continue your registration:
                                </p>

                                <!-- 6-Digit Code Box -->
                                <div style="background-color: #F0FBFB; border: 1px solid #BCE5E8; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0;">
                                    <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 800; color: #0D5B62; text-transform: uppercase; letter-spacing: 1px;">
                                        6-Digit Verification Code
                                    </p>
                                    <div style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #199CA4; font-family: Consolas, Monaco, monospace; margin: 10px 0;">
                                        {{ $otp }}
                                    </div>
                                    <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748B;">
                                        Enter this code on your registration screen to proceed to Step 2 (Set Password).
                                    </p>
                                </div>

                                <p style="margin: 20px 0 0 0; font-size: 12px; color: #64748B; line-height: 1.5;">
                                    This verification code will expire in 15 minutes.
                                </p>
                            @else
                                <p style="margin: 0 0 24px 0; color: #475569;">
                                    Welcome to CAWS. Please verify your email address to activate your administrative portal account.
                                </p>

                                <!-- Primary 1-Click Verification Button -->
                                <div style="text-align: center; margin: 28px 0 32px 0;">
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="background-color: #199CA4; color: #FFFFFF; display: inline-block; padding: 14px 32px; font-size: 14px; font-weight: 800; text-decoration: none; border-radius: 10px; box-shadow: 0 2px 6px rgba(25, 156, 164, 0.35);">
                                        Verify Email Address
                                    </a>
                                </div>

                                <!-- Mobile / 6-Digit Code Box -->
                                <div style="background-color: #F0FBFB; border: 1px solid #BCE5E8; border-radius: 12px; padding: 20px; text-align: center; margin: 28px 0 24px 0;">
                                    <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 800; color: #0D5B62; text-transform: uppercase; letter-spacing: 1px;">
                                        6-Digit Verification Code
                                    </p>
                                    <div style="font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #199CA4; font-family: Consolas, Monaco, monospace; margin: 8px 0;">
                                        {{ $otp }}
                                    </div>
                                    <p style="margin: 8px 0 0 0; font-size: 12px; color: #64748B;">
                                        Reading this on your mobile device? Type this code on your computer verification screen.
                                    </p>
                                </div>

                                <p style="margin: 20px 0 0 0; font-size: 12px; color: #64748B; line-height: 1.5;">
                                    This verification link and code will expire in 60 minutes.
                                </p>
                            @endif

                            <p style="margin: 6px 0 0 0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
                                If you did not create or request this account, please ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 18px 32px; text-align: center; font-size: 11px; color: #94A3B8;">
                            CDO Animal Welfare Society Inc. &bull; Cagayan de Oro City &bull; Shelter Management Portal
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
