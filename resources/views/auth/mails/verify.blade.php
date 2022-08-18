<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://d1i1hlbp8uye62.cloudfront.net" />
    <title>Reset Password</title>
    <style>
        html,
        body {
            margin: 0;
            font-family: Arial;
        }

        #main {
            margin-bottom: 24px;
        }

        #main p {
            font-size: 15px;
            line-height: 1.33;
            color: rgba(0, 20, 43, 0.8);
        }

        #main p a {
            color: #ff0030;
            text-decoration: none;
        }

        #main p a.reset-password {
            color: #1890ff;
            text-decoration: underline;
        }

        #main #verify-email {
            display: block;
            width: 238px;
            margin: 16px auto 0;
            padding: 9px 0px 9px;
            border-radius: 5px;
            text-align: center;
            background-color: #ff0030;
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            font-stretch: normal;
            font-style: normal;
            line-height: 1.25;
            letter-spacing: normal;
            text-align: center;
            color: #fff;
            text-decoration: none;
        }

        #footer #line {
            width: 100%;
            height: 1px;
            margin: 8px 0 12px;
            background-color: rgba(177, 188, 192, 0.31);
        }

        #footer p {
            margin: 12px 0 0;
            font-size: 10px;
            line-height: 1.6;
            color: #929292;
        }

        #footer p a {
            color: #1890ff;
            text-decoration: none;
        }

        #footer #socials {
            margin-top: 32px;
            text-align: center;
        }

        #footer #socials a {
            margin: 0 8px;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div style="width: 480px; max-width: 100%;margin:40px auto 0;">
    <div style="padding: 0 14px;">
        <img src="/emails/assets/logo.png" alt="Lair.GG" width="52" style="margin-bottom: 8px;" />

        <div id="main">
            <p>
                Hi {{ $name }},
            </p>
            <p>
                To start using your account, you need to confirm your email address.
            </p>

            <p>
                <a id="verify-email" href="{{ $url }}">Verify Email Address</a>
            </p>
        </div>
    </div>
    <div id="footer">
        <div id="line"></div>

        <p>© 2022 LAIR Inc. All rights reserved</p>
        <div id="socials">
            <a href="https://discord.gg/EaPvWb5CqE">
                <img src="/emails/assets/discord.png" alt="Discord" width="16" />
            </a>
            <a href="https://twitter.com/lair_gg">
                <img src="/emails/assets/twitter.png" alt="Twitter" width="16" />
            </a>
            <!-- <a href="#">
                <img src="/emails/assets/instagram.png" alt="Instagram" width="16" />
            </a> -->
        </div>
    </div>
</div>

</body>

</html>
