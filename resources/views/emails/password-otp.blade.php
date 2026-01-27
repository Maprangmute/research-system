<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รหัส OTP สำหรับรีเซ็ตรหัสผ่าน</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <p>สวัสดีคุณ {{ $user->name ?? '' }},</p>
    <p>ระบบได้รับคำขอรีเซ็ตรหัสผ่านของคุณ รหัส OTP คือ:</p>
    <p style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ $otp }}</p>
    <p>รหัสนี้มีอายุ {{ $ttlMinutes }} นาที หากคุณไม่ได้ทำรายการนี้ กรุณาเพิกเฉยอีเมลฉบับนี้</p>
    <p>ขอบคุณครับ/ค่ะ</p>
</body>
</html>
