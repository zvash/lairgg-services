<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Docs</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">News</a>
                    <a href="https://blog.laravel.com">Blog</a>
                    <a href="https://nova.laravel.com">Nova</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://vapor.laravel.com">Vapor</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
        </div>

        <!-- The core Firebase JS SDK is always required and must be listed first -->
        <script src="https://www.gstatic.com/firebasejs/8.4.3/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/8.4.3/firebase-messaging.js"></script>

        <!-- TODO: Add SDKs for Firebase products that you want to use
             https://firebase.google.com/docs/web/setup#available-libraries -->
        <script src="https://www.gstatic.com/firebasejs/8.4.3/firebase-analytics.js"></script>

        <script>

            // Your web app's Firebase configuration
            // For Firebase JS SDK v7.20.0 and later, measurementId is optional
            var firebaseConfig = {
                apiKey: "AIzaSyDxIauiwwyOpfD1-tQYyY4D5eiV0Atf5ck",
                authDomain: "lairgg-4dcef.firebaseapp.com",
                projectId: "lairgg-4dcef",
                storageBucket: "lairgg-4dcef.appspot.com",
                messagingSenderId: "1007213527470",
                appId: "1:1007213527470:web:839cee836a6647a356483f",
                measurementId: "G-95GLY22F04"
            };
            // Initialize Firebase
            firebase.initializeApp(firebaseConfig);
            firebase.analytics();

            const messaging = firebase.messaging();
            // Get registration token. Initially this makes a network call, once retrieved
            // subsequent calls to getToken will return from cache.
            messaging.getToken({ vapidKey: 'BFSVR-xMVXc40EJJlSi0lMpsYZ_IL4KBLxxYosUAQI__oqV_NyPmmMEfG-NUEl4G7v1JPDyGJg17iho_huFOAXY' }).then((currentToken) => {
                if (currentToken) {
                    console.log(currentToken);
                    // Send the token to your server and update the UI if necessary
                    // ...
                } else {
                    // Show permission request UI
                    console.log('No registration token available. Request permission to generate one.');
                    // ...
                }
            }).catch((err) => {
                console.log('An error occurred while retrieving token. ', err);
                // ...
            });

        </script>

    </body>
</html>
