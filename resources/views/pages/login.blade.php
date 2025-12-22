<!DOCTYPE html>
<html
    lang="en"
    class="light-style customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('public/sneat/') }}"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8"/>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>{{ __('menu.auth.login') }} | {{ config('app.name') }}</title>

    <meta name="description" content=""/>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('sneat/img/favicon/favicon.ico')}}"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet"
    />

    <!-- Font Awesome CDN -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    <!-- Core CSS -->
    <link rel="stylesheet" class="template-customizer-core-css" href="{{asset('sneat/vendor/css/core.css')}}"/>
    <link rel="stylesheet" class="template-customizer-theme-css"
          href="{{asset('sneat/vendor/css/theme-default.css')}}"/>
    <link rel="stylesheet" href="{{asset('sneat/css/demo.css')}}"/>

    <!-- Page -->
    <link rel="stylesheet" href="{{asset('sneat/vendor/css/pages/page-auth.css')}}"/></head>

<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="welcome">
                <div>
                    <h2>Selamat Datang</h2>
                    <p>
                        Silakan masuk ke akun Anda untuk <br />
                        mengakses dashboard admin <br />
                        {{ config('app.name') }}
                    </p>
                </div>
            </div>
            <div class="login-form">
                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <!-- Logo -->
                    <div class="app-brand mb-4 -bottom-30 text-center">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('assets/arsera-logo.png') }}" alt="{{ config('app.name') }}" width="75px">
                        </a>
                    </div>

                    <div class="input-group">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                        />
                        <label for="email">{{ __('model.user.email') }}</label>
                        <i class="fa-solid fa-envelope"></i>
                    </div>

                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                        />
                        <label for="password">{{ __('model.user.password') }}</label>
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <button class="btn" type="submit">{{ __('menu.auth.login') }}</button>

                    <!-- Google OAuth Button -->
                    <div class="mt-3">
                        <a href="{{ route('auth.google') }}" class="btn btn-google w-100">
                            <i class="fab fa-google me-2"></i> {{ __('menu.auth.login_with_google') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
