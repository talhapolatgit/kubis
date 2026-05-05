@extends('layouts.base')

@section('title', 'Kullanici Yetkileri')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        .content-area{display:flex;flex-direction:column;gap:16px}
        .page-title{font-family:var(--font-serif);font-size:22px;font-weight:700}
        .page-sub{font-size:13px;color:var(--muted-foreground);margin-top:2px}

        .card{background:var(--card);border:1px solid rgba(217,208,194,.6);border-radius:var(--radius);box-shadow:0 1px 3px rgba(0,0,0,.04);overflow:hidden}
        .card-h{padding:18px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
        .card-sep{height:1px;background:var(--border)}
        .card-b{padding:18px 20px}

        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:calc(var(--radius) - 2px);font-size:14px;font-weight:500;cursor:pointer;transition:background .15s,opacity .15s;border:none;text-decoration:none}
        .btn svg{width:16px;height:16px}
        .btn-primary{background:var(--primary);color:var(--primary-foreground)}
        .btn-primary:hover{opacity:.9}
        .btn-outline{background:transparent;color:var(--foreground);border:1px solid var(--border)}
        .btn-outline:hover{background:var(--muted)}

        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
        .perm{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border:1px solid rgba(217,208,194,.7);border-radius:10px;background:rgba(237,232,222,.45)}
        .perm:hover{border-color:var(--border)}
        .perm input{margin-top:4px}
        .perm-title{font-weight:700;font-size:13px}
        .perm-desc{font-size:13px;color:var(--muted-foreground);margin-top:2px}
        .badge{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background:rgba(122,92,60,.12);color:var(--primary)}

        @media(max-width:900px){.grid{grid-template-columns:1fr}}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('users.index') }}" class="breadcrumb-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Kullanıcılar
        </a>
        <span class="breadcrumb-sep">/</span>
        <a href="{{ route('users.edit', $user->id) }}" class="breadcrumb-link">{{ $user->name }}</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">Yetkiler</span>
    </nav>
@endsection

@section('content')
        <div class="content-area">
            <div>
                <div class="page-title">Kullanıcı Yetkileri</div>
                <div class="page-sub">
                    <span class="badge">#{{ $user->id }}</span>
                    <span style="margin-left:6px;font-weight:700;">{{ $user->name }}</span>
                    <span style="margin-left:6px;color:var(--muted-foreground);">({{ $user->email }})</span>
                </div>
            </div>

            <form class="card" method="POST" action="{{ route('users.yetkiler.update', $user->id) }}">
                @csrf
                <div class="card-h">
                    <div>
                        <div style="font-family:var(--font-serif);font-size:18px;font-weight:800;">Yetki Tanımla</div>
                        <div style="font-size:13px;color:var(--muted-foreground);margin-top:3px;">
                            Bu sayfada seçilen yetkiler, kullanıcı arayüzünde butonları ve erişimleri etkiler.
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                        <a class="btn btn-outline" href="{{ route('users.edit', $user->id) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            Geri Dön
                        </a>
                        <button class="btn btn-primary" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Kaydet
                        </button>
                    </div>
                </div>
                <div class="card-sep"></div>
                <div class="card-b">
                    <div class="grid">
                        @foreach($yetkiler as $no => $label)
                            @php
                                $col = 'y' . str_pad((string)$no, 2, '0', STR_PAD_LEFT);
                                $checked = (bool)($row->{$col} ?? false);
                            @endphp
                            <label class="perm">
                                <input type="checkbox" name="{{ $col }}" {{ $checked ? 'checked' : '' }} />
                                <div>
                                    <div class="perm-title">{{ $no }}. Yetki</div>
                                    <div class="perm-desc">{{ $label }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
@endsection


