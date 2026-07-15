<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial">

    <h2>مرحباً {{ $doctor->name }}</h2>

    <p>
        تم إنشاء حساب لك على منصة الأطباء.
    </p>

    <hr>

    <p>
        <strong>البريد الإلكتروني</strong><br>
        {{ $doctor->email }}
    </p>

    <p>
        <strong>كلمة المرور</strong><br>
        {{ $password }}
    </p>

    <br>

    <a href="{{ route('login') }}">
        تسجيل الدخول
    </a>

    <br><br>

    <p>
        يرجى تغيير كلمة المرور بعد أول تسجيل دخول.
    </p>

</body>
</html>