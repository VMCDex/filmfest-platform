@extends('layouts.app')

@section('content')
<div class="form-container">
    <div class="card">
        <div class="card-header">Авторизация</div>
        <div class="card-body">
            @if(session('error'))
                <div style="background:#fadbd8; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid #f5c6cb;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Логин (Email)</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-block">Войти</button>
            </form>

            <div class="text-center">
                <a href="/register" style="color: #3498db; text-decoration: none; font-size: 14px;">Нет аккаунта? Зарегистрируйтесь</a>
            </div>
        </div>
    </div>
</div>
@endsection