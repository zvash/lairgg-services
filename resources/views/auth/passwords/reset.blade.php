@extends('layouts.master')

@section('content')
    <style>
        *, *:before, *:after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        input, button {
            border: none;
            outline: none;
            background: none;
            font-family: 'Nunito', sans-serif;
        }
        button {
            display: block;
            margin: 0 auto;
            width: 260px;
            height: 50px;
            border-radius: 30px;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }
        h2 {
            width: 100%;
            font-size: 26px;
            text-align: center;
        }
        label {
            display: block;
            width: 80%;
            margin: 25px auto 0;
            text-align: center;
        }
        label span {
            font-size: 12px;
            color: #cfcfcf;
            text-transform: uppercase;
        }
        input {
            display: block;
            width: 100%;
            margin-top: 5px;
            padding-bottom: 5px;
            font-size: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        p {
            line-height: 30px;
        }
        .form {
            width: calc(100vw - 50vw);
            padding: 50px 30px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-direction: column;
            height: 100vh;
        }
        .sidebar {
            background-image: url("https://cdn.virtuleap.com/website/assets/images/banners/enhance-parallax-v2.png");
            background-size: cover;
            background-position-x: right;
            width: calc(100vw - 50vw);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 50px 30px;
        }
        input[type=email] {
            color: #0076ff;
            font-weight: bold;
        }
        input.is-invalid {
            border-color: #fc3053;
        }
        span.is-invalid, .invalid-feedback {
            color: #fc3053;
        }
        .image-text {
            width: 100%;
            text-align: center;
            color: #fff;
        }
        .image-text h2 {
            font-weight: bold;
        }
        .form p, .image-text p {
            font-size: 17px;
            margin-top: 20px;
        }
        .image-button {
            width: 100%;
            margin-top: 70px;
        }
        .image-button a {
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            height: 50px;
            border: 2px solid #fff;
            border-radius: 30px;
            margin: 0 auto;
            width: 260px;
            display: block;
            text-align: center;
            line-height: 46px;
        }
        .submit {
            margin-top: 40px;
            background: #0076ff;
            font-weight: bold;
        }

        @media only screen and (max-width: 990px) {
            label {
                width: 100%;
            }
            .flex-center {
                flex-wrap: wrap;
            }
            .form {
                width: 100vw;
                height: auto;
            }
            /*.sidebar {*/
                /*background-image: url("https://cdn.virtuleap.com/website/assets/images/banners/enhance-parallax-v2.png");*/
                /*background-size: cover;*/
                /*width: 100vw;*/
                /*height: auto;*/
            /*}*/
        }
    </style>

    <form class="form sign-in" method="POST" action="{{ route('users.password.reset.update') }}">
        @csrf

        <h2>{{ __('passwords.form.title') }}</h2>
        <p>{{ __('emails.reset.signature', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}</p>

        {{--Token--}}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="lang" value="{{ app()->getLocale() }}">

        {{--Email--}}
        <label for="email">
            <span @error('email') class="is-invalid" @enderror>{{ __('passwords.form.email') }}</span>
            <input @error('email') class="is-invalid" @enderror
                id="email"
                type="email"
                name="email"
                value="{{ $email ?? old('email') }}"
                autocomplete="email"
                required/>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </label>

        {{--Password--}}
        <label for="password" >
            <span @error('password') class="is-invalid" @enderror>{{ __('passwords.form.password') }}</span>
            <input @error('password') class="is-invalid" @enderror
                id="password"
                type="password"
                name="password"
                autofocus
                required
                autocomplete="new-password"/>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </label>

        {{--Confrim Password--}}
        <label for="password-confirm" >
            <span>{{ __('passwords.form.confirm') }}</span>
            <input
                id="password-confirm"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"/>
        </label>

        <button type="submit" class="submit">{{ __('passwords.form.title') }}</button>
    </form>

    {{--Sidebar--}}
    {{--<div class="sidebar">--}}
        {{--<div class="image-text">--}}
            {{--<h2>{{ config('app.name') }}</h2>--}}
            {{--<p>{{ __('passwords.form.sidebar') }}</p>--}}
        {{--</div>--}}
        {{--<div class="image-button">--}}
            {{--<a href="https://virtuleap.com">{{ __('passwords.form.learn') }}</a>--}}
        {{--</div>--}}
    {{--</div>--}}
@endsection
