<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://d1i1hlbp8uye62.cloudfront.net" />
    <title>Verification</title>
    <style>
        html,
        body {
            margin: 0;
            font-family: Arial;
        }

        #title {
            font-size: 16px;
            font-weight: 500;
            line-height: 1.25;
            color: #00142b;
        }

        #box {
            margin-top: 16px;
            padding: 19px 0;
            background-color: rgba(0, 20, 43, 0.05);
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
            color: #00142b;
        }

        #bottom-texts {
            font-size: 15px;
            line-height: 1.33;
            text-align: center;
            color: #929292;
        }
    </style>
</head>

<body>
<div style="width: 480px; max-width: 100%;margin:40px auto 0;">
    <div style="padding: 0 14px;">
        <img src="/emails/assets/logo.png" alt="Lair.GG" width="52" style="display: block;margin: 0 auto 24px;" />

        <h2 id="title" style="text-align: center">Your password reset code is:</h2>
    </div>
    <div id="box">
        <div>{{ $code }}</div>
    </div>
    <div id="bottom-texts" style="padding: 0 14px;">
        <p>If you didn’t try to reset your password, you can safely ignore this email</p>
        <p>LAIR, Esports tournament platform</p>
    </div>
</div>
</body>

</html>
