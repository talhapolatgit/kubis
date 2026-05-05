@extends('layouts.base')

@section('title', 'Şifre Değiştir — Kütüphane Bilgi Sistemi')

@section('styles')
<style>
    .form-card{border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);background:var(--card);box-shadow:0 1px 3px rgba(0,0,0,.04);max-width:720px}
    .form-card-header{padding:24px 24px 16px}
    .form-card-title{display:flex;align-items:center;gap:10px;font-family:var(--font-serif);font-size:20px;font-weight:700}
    .form-card-title .title-icon{width:38px;height:38px;border-radius:10px;background:rgba(122,92,60,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .form-card-title .title-icon svg{width:20px;height:20px;color:var(--primary)}
    .form-card-desc{font-size:14px;color:var(--muted-foreground);margin-top:4px;margin-left:48px}
    .form-card-separator{height:1px;background:var(--border)}
    .form-card-body{padding:24px;display:flex;flex-direction:column;gap:16px}
    .form-field{display:flex;flex-direction:column}
    .form-label{font-size:14px;font-weight:500;color:var(--foreground);margin-bottom:6px}
    .form-input{width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:calc(var(--radius) - 2px);background:var(--card);color:var(--foreground);font-size:14px;line-height:1.5;transition:border-color .15s,box-shadow .15s;outline:none}
    .form-input:focus{border-color:var(--ring);box-shadow:0 0 0 2px rgba(122,92,60,.15)}
    .form-error{font-size:12px;color:var(--destructive);margin-top:4px}
    .form-ok{padding:10px 12px;border-radius:8px;background:rgba(34,197,94,.1);color:#166534;border:1px solid rgba(34,197,94,.25);font-size:13px}
    .rules{font-size:12px;color:var(--muted-foreground);margin-top:4px}
    .form-actions{display:flex;justify-content:flex-end;gap:12px;padding-top:4px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
    .btn-primary{background:var(--primary);color:var(--primary-foreground)}
    .btn-primary:hover{opacity:.9}
    .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
    .btn-outline:hover{background:var(--muted)}
</style>
@endsection

@section('breadcrumb')
<nav class="breadcrumb">
    <a href="{{ route('katalog.index') }}" class="breadcrumb-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Ana Sayfa
    </a>
    <span class="breadcrumb-sep">›</span>
    <span class="breadcrumb-current">Şifre Değiştir</span>
</nav>
@endsection

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-title">
            <span class="title-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            Şifre Değiştir
        </div>
        <p class="form-card-desc">Mevcut şifrenizi doğrulayıp yeni şifrenizi belirleyin.</p>
    </div>
    <div class="form-card-separator"></div>

    <div class="form-card-body">
        @if(session('success'))
            <div class="form-ok">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="form-error">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.password.update') }}">
            @csrf
            <div class="form-field">
                <label class="form-label" for="current_password">Mevcut Şifre</label>
                <input id="current_password" name="current_password" type="password" class="form-input" autocomplete="current-password" />
            </div>

            <div class="form-field">
                <label class="form-label" for="password">Yeni Şifre</label>
                <input id="password" name="password" type="password" class="form-input" autocomplete="new-password" />
                <div class="rules">En az 8 karakter olmalı ve en az bir küçük harf, bir büyük harf, bir rakam içermelidir.</div>
            </div>

            <div class="form-field">
                <label class="form-label" for="password_confirmation">Yeni Şifre (Tekrar)</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" />
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="{{ url()->previous() }}">Geri</a>
                <button type="submit" class="btn btn-primary">Şifreyi Güncelle</button>
            </div>
        </form>
    </div>
</div>
@endsection
