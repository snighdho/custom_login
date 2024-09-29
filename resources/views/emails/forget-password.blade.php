<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <p>Hello,</p>
    <p>To reset your password, click the link below:</p>
    <a href="{{ route('reset.password', ['token' => $token]) }}">Reset Password</a>
    <p>If you didn't request a password reset, please ignore this email.</p>
</body>

</html>
