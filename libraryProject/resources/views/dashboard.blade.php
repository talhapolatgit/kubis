@extends('layouts.base')

@section('title', 'Genel Bakış — KÜBİS')

@section('styles')
    <style>
        .dashboard-content{display:flex;flex-direction:column;gap:22px;max-width:1200px;width:100%;margin:0 auto}
        .page-hero{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px}
        .page-title{font-family:var(--font-serif);font-size:26px;font-weight:700;letter-spacing:-.02em}
        .page-sub{font-size:14px;color:var(--muted-foreground);margin-top:4px;max-width:520px}
        .page-meta{font-size:12px;color:var(--muted-foreground)}
        .kpi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
        .kpi-card{background:var(--card);border:1px solid rgba(217,208,194,.65);border-radius:var(--radius);padding:18px 18px 16px;box-shadow:0 1px 3px rgba(0,0,0,.04);text-decoration:none;color:inherit;display:block;transition:border-color .15s,box-shadow .15s,transform .12s}
        .kpi-card:hover{border-color:rgba(122,92,60,.45);box-shadow:0 4px 14px rgba(61,50,38,.08)}
        .kpi-card--static{cursor:default}
        .kpi-card--static:hover{transform:none;border-color:rgba(217,208,194,.65);box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .kpi-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:6px;display:flex;align-items:center;gap:6px}
        .kpi-label svg{width:15px;height:15px;opacity:.85}
        .kpi-value{font-family:var(--font-serif);font-size:30px;font-weight:700;line-height:1.1}
        .kpi-hint{font-size:12px;color:var(--muted-foreground);margin-top:8px}
        .kpi-value--danger{color:var(--destructive)}
        .kpi-value--ok{color:var(--success)}
        .kpi-value--warn{color:var(--warning)}
        .dash-row{display:flex;flex-wrap:wrap;gap:16px}
        .dash-row .panel{flex:1;min-width:min(100%,300px)}
        .panel{background:var(--card);border:1px solid rgba(217,208,194,.65);border-radius:var(--radius);padding:20px 22px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
        .panel-title{font-family:var(--font-serif);font-size:15px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:10px}
        .panel-title a{font-size:13px;font-weight:600;color:var(--primary);text-decoration:none}
        .panel-title a:hover{text-decoration:underline}
        .time-filter{display:flex;gap:6px;flex-wrap:wrap;margin:-4px 0 12px}
        .time-filter-link{display:inline-flex;align-items:center;padding:4px 10px;border:1px solid var(--border);border-radius:999px;font-size:12px;font-weight:600;color:var(--muted-foreground);text-decoration:none;transition:all .12s}
        .time-filter-link:hover{border-color:rgba(122,92,60,.45);color:var(--foreground);background:rgba(122,92,60,.06)}
        .time-filter-link.active{background:var(--primary);border-color:var(--primary);color:var(--primary-foreground)}
        .time-filter-link.is-loading{opacity:.6;pointer-events:none}
        .bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px;font-size:13px}
        .bar-row:last-child{margin-bottom:0}
        .bar-name{min-width:88px;color:var(--muted-foreground);font-weight:500}
        .bar-track{flex:1;height:10px;background:var(--secondary);border-radius:999px;overflow:hidden;border:1px solid rgba(217,208,194,.5)}
        .bar-fill{height:100%;background:linear-gradient(90deg,var(--primary),#9b6b3f);border-radius:999px;min-width:2px;transition:width .4s ease}
        .bar-fill--secondary{background:linear-gradient(90deg,#5c7a9e,#7a9bb8)}
        .bar-fill--tertiary{background:linear-gradient(90deg,#6b8f6a,#8aab7e)}
        .bar-count{font-variant-numeric:tabular-nums;font-weight:700;min-width:36px;text-align:right;font-size:13px}
        .demo-split{display:grid;grid-template-columns:1fr 1fr;gap:22px}
        @media(max-width:720px){.demo-split{grid-template-columns:1fr}}
        .demo-block-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:10px}
        .panel-hint{font-size:12px;color:var(--muted-foreground);margin-top:10px;line-height:1.45}
        .qlinks{display:flex;flex-direction:column;gap:8px}
        .qlink{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;border:1px solid var(--border);text-decoration:none;color:var(--foreground);font-size:14px;font-weight:500;transition:background .12s,border-color .12s}
        .qlink:hover{background:rgba(122,92,60,.06);border-color:rgba(122,92,60,.35)}
        .qlink svg{width:18px;height:18px;color:var(--primary);flex-shrink:0}
        .mini-table{width:100%;border-collapse:collapse;font-size:13px}
        .mini-table th{text-align:left;padding:8px 10px;border-bottom:1px solid var(--border);color:var(--muted-foreground);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.04em}
        .mini-table td{padding:10px;border-bottom:1px solid rgba(217,208,194,.45);vertical-align:middle}
        .mini-table tr:last-child td{border-bottom:none}
        .mini-table a{color:var(--primary);font-weight:600;text-decoration:none}
        .mini-table a:hover{text-decoration:underline}
        .badge{display:inline-flex;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:700}
        .badge--aktif{background:rgba(37,99,235,.1);color:#1d4ed8}
        .badge--iade{background:rgba(22,163,74,.12);color:var(--success)}
        .badge--kayip{background:rgba(107,114,128,.15);color:#374151}
        .badge--uye-aktif{background:rgba(22,163,74,.12);color:var(--success)}
        .badge--uye-pasif{background:rgba(180,83,9,.12);color:var(--warning)}
        .empty-hint{padding:20px;text-align:center;color:var(--muted-foreground);font-size:13px}
        @media(max-width:768px){.dashboard-content{gap:16px}}
    </style>
@endsection

@section('breadcrumb')
    <nav class="breadcrumb" aria-label="Gezinti">
        <span class="breadcrumb-current">Genel bakış</span>
    </nav>
@endsection

@section('content')
            <div class="dashboard-content">
            <div class="page-hero">
                <div>
                    <h1 class="page-title">Merhaba, {{ auth()->user()->name }}</h1>
                    <p class="page-sub">Kütüphane operasyonlarınızın özeti. Aşağıdaki kartlar yetkilerinize ve erişebildiğiniz kütüphane kapsamına göre hesaplanır.</p>
                </div>
                <div class="page-meta">Veri anı: {{ now()->format('d.m.Y H:i') }}</div>
            </div>

            @if($flags['catalog'] || $flags['loans'] || $flags['members'] || $flags['libraries'])
                <div class="kpi-grid">
                    @if($flags['catalog'])
                        <a href="{{ route('katalog.index') }}" class="kpi-card">
                            <div class="kpi-label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg> Katalog</div>
                            <div class="kpi-value">{{ number_format($stats['katalog_total'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Bu ay +{{ number_format($stats['katalog_bu_ay'] ?? 0, 0, ',', '.') }} · Rafta {{ number_format($stats['katalog_rafa'] ?? 0, 0, ',', '.') }}</div>
                        </a>
                        <div class="kpi-card kpi-card--static">
                            <div class="kpi-label">Ödünçteki kitap</div>
                            <div class="kpi-value">{{ number_format($stats['katalog_odunc'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Durumu «Ödünç» olan kayıtlar</div>
                        </div>
                        <div class="kpi-card kpi-card--static">
                            <div class="kpi-label">Rezervasyonlu</div>
                            <div class="kpi-value">{{ number_format($stats['katalog_rezerve'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Durumu «Rezerve» olan kayıtlar</div>
                        </div>
                        @if(auth()->user()->hasYetki(20))
                            <div class="kpi-card kpi-card--static">
                                <div class="kpi-label">Etiketlenmemiş</div>
                                <div class="kpi-value kpi-value--warn">{{ number_format($stats['katalog_etiketlenmemis'] ?? 0, 0, ',', '.') }}</div>
                                <div class="kpi-hint">Etiket oluşturma için bekleyen</div>
                            </div>
                        @endif
                    @endif

                    @if($flags['loans'])
                        <a href="{{ route('odunc.index') }}" class="kpi-card">
                            <div class="kpi-label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg> Aktif ödünç</div>
                            <div class="kpi-value">{{ number_format($stats['odunc_aktif'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Bu ay verilen: {{ number_format($stats['odunc_bu_ay'] ?? 0, 0, ',', '.') }}</div>
                        </a>
                        <a href="{{ route('odunc.index', ['statu' => 'gecikti']) }}" class="kpi-card">
                            <div class="kpi-label">Geciken</div>
                            <div class="kpi-value kpi-value--danger">{{ number_format($stats['odunc_gecikti'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Planlanan iadesi geçmiş aktif ödünç</div>
                        </a>
                        <div class="kpi-card kpi-card--static">
                            <div class="kpi-label">Bugün iade</div>
                            <div class="kpi-value kpi-value--ok">{{ number_format($stats['odunc_bugun_iade'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Bugün tamamlanan iadeler</div>
                        </div>
                        <a href="{{ route('rezerve.index') }}" class="kpi-card">
                            <div class="kpi-label">Aktif rezervasyon</div>
                            <div class="kpi-value">{{ number_format($stats['rezerve_aktif'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Süresi dolmamış, iptal edilmemiş</div>
                        </a>
                        <div class="kpi-card kpi-card--static">
                            <div class="kpi-label">Kayıp bildirimi</div>
                            <div class="kpi-value">{{ number_format($stats['odunc_kayip'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Ödünç işlemi statüsü</div>
                        </div>
                    @endif

                    @if($flags['members'])
                        <a href="{{ route('uyeler.index') }}" class="kpi-card">
                            <div class="kpi-label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Üyeler</div>
                            <div class="kpi-value">{{ number_format($stats['uye_toplam'] ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Aktif {{ number_format($stats['uye_aktif'] ?? 0, 0, ',', '.') }} · Bu ay +{{ number_format($stats['uye_bu_ay'] ?? 0, 0, ',', '.') }}</div>
                        </a>
                    @endif

                    @if($flags['libraries'])
                        <a href="{{ route('kutuphane.index') }}" class="kpi-card">
                            <div class="kpi-label">Kütüphane</div>
                            <div class="kpi-value">{{ number_format($kutuphaneCount ?? 0, 0, ',', '.') }}</div>
                            <div class="kpi-hint">Kayıtlı kütüphane sayısı</div>
                        </a>
                    @endif
                </div>
            @else
                <div class="panel">
                    <p class="empty-hint" style="padding:8px 0;">Bu hesap için özet kartları gösterecek katalog, ödünç, üye veya kütüphane yetkisi tanımlı değil. Sol menüden erişebildiğiniz modülleri kullanmaya devam edebilirsiniz.</p>
                </div>
            @endif

            @if(($flags['catalog'] && ($kategoriBreakdown->isNotEmpty() || $kutuphaneAktifKatalog->isNotEmpty())) || $flags['members'])
                <div class="dash-row">
                    @if($flags['catalog'] && $kategoriBreakdown->isNotEmpty())
                        <div class="panel">
                            <div class="panel-title">
                                <span>Kategori dağılımı</span>
                                <a href="{{ route('katalog.index') }}">Katalog</a>
                            </div>
                            @foreach($kategoriBreakdown as $row)
                                <div class="bar-row">
                                    <span class="bar-name" title="{{ $row['label'] }}">{{ \Illuminate\Support\Str::limit($row['label'], 22) }}</span>
                                    <div class="bar-track" title="{{ $row['count'] }} kayıt">
                                        <div class="bar-fill" style="width:{{ min(100, round($row['count'] / $kategoriMax * 100)) }}%"></div>
                                    </div>
                                    <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <p class="panel-hint">Kayıtlar, erişiminiz kapsamındaki katalog üzerinden kategori alanına göre gruplanır.</p>
                        </div>
                    @endif

                    @if($flags['members'])
                        <div class="panel">
                            <div class="panel-title">
                                <span>Üye demografisi</span>
                                <a href="{{ route('uyeler.index') }}">Üyeler</a>
                            </div>
                            @if($uyeCinsiyetBreakdown->isEmpty() && $uyeYasBreakdown->isEmpty())
                                <p class="empty-hint" style="padding:12px 0;">Gösterecek üye kaydı bulunmuyor.</p>
                            @else
                                <div class="demo-split">
                                    <div>
                                        <div class="demo-block-title">Cinsiyet</div>
                                        @forelse($uyeCinsiyetBreakdown as $row)
                                            <div class="bar-row">
                                                <span class="bar-name">{{ $row['label'] }}</span>
                                                <div class="bar-track" title="{{ $row['count'] }} üye">
                                                    <div class="bar-fill bar-fill--secondary" style="width:{{ min(100, round($row['count'] / $uyeCinsiyetMax * 100)) }}%"></div>
                                                </div>
                                                <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                                            </div>
                                        @empty
                                            <p class="empty-hint" style="padding:8px 0;font-size:13px;">Cinsiyet verisi girilmemiş.</p>
                                        @endforelse
                                    </div>
                                    <div>
                                        <div class="demo-block-title">Yaş grubu</div>
                                        @forelse($uyeYasBreakdown as $row)
                                            <div class="bar-row">
                                                <span class="bar-name">{{ \Illuminate\Support\Str::limit($row['label'], 20) }}</span>
                                                <div class="bar-track" title="{{ $row['count'] }} üye">
                                                    <div class="bar-fill bar-fill--tertiary" style="width:{{ min(100, round($row['count'] / $uyeYasMax * 100)) }}%"></div>
                                                </div>
                                                <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                                            </div>
                                        @empty
                                            <p class="empty-hint" style="padding:8px 0;font-size:13px;">Yaş verisi oluşturulamıyor.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <p class="panel-hint">Yaşlar doğum tarihine göre; cinsiyet üye kartından (isteğe bağlı) tutulur.</p>
                            @endif
                        </div>
                    @endif

                    @if($flags['catalog'])
                        <div class="panel">
                            <div class="panel-title">
                                <span>Kütüphane — aktif katalog</span>
                                <a href="{{ route('katalog.index') }}">Katalog</a>
                            </div>
                            @if($kutuphaneAktifKatalog->isEmpty())
                                <p class="empty-hint" style="padding:12px 0;">Rafta, ödünç veya rezerve durumunda kayıt yok veya kapsamınızda eşleşen katalog bulunmuyor.</p>
                            @else
                                @foreach($kutuphaneAktifKatalog as $row)
                                    <div class="bar-row">
                                        <span class="bar-name" title="{{ $row['label'] }}">{{ \Illuminate\Support\Str::limit($row['label'], 22) }}</span>
                                        <div class="bar-track" title="{{ $row['count'] }} kayıt">
                                            <div class="bar-fill" style="width:{{ min(100, round($row['count'] / $kutuphaneAktifMax * 100)) }}%"></div>
                                        </div>
                                        <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <p class="panel-hint">Sayılar yalnızca <strong>Rafta</strong>, <strong>Ödünç</strong> ve <strong>Rezerve</strong> durumundaki kayıtları içerir (kayıp / bakım vb. hariç).</p>
                            @endif
                        </div>

                    @endif
                </div>
            @endif

            <div class="dash-row">
                @if($flags['catalog'] && $durumBreakdown->isNotEmpty())
                    <div class="panel">
                        <div class="panel-title">
                            <span>Kitap durum dağılımı</span>
                            <a href="{{ route('katalog.index') }}">Kataloğa git</a>
                        </div>
                        @foreach($durumBreakdown as $row)
                            <div class="bar-row">
                                <span class="bar-name">{{ $row['label'] }}</span>
                                <div class="bar-track" title="{{ $row['count'] }} kayıt">
                                    <div class="bar-fill" style="width:{{ min(100, round($row['count'] / $durumMax * 100)) }}%"></div>
                                </div>
                                <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($flags['catalog'])
                    <div class="panel" id="creatorCatalogPanel">
                        <div class="panel-title">
                            <span>Kullanıcı Katalog Kayıt Sayıları</span>
                            <a href="{{ route('katalog.index') }}">Katalog</a>
                        </div>
                        <div class="time-filter" aria-label="Zaman aralığı seçimi">
                            <a class="time-filter-link js-creator-range {{ ($creatorRange ?? 'all') === 'all' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['creator_range' => 'all']) }}">Tüm Zamanlar</a>
                            <a class="time-filter-link js-creator-range {{ ($creatorRange ?? 'all') === '1y' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['creator_range' => '1y']) }}">1 Yıl</a>
                            <a class="time-filter-link js-creator-range {{ ($creatorRange ?? 'all') === '1m' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['creator_range' => '1m']) }}">1 Ay</a>
                            <a class="time-filter-link js-creator-range {{ ($creatorRange ?? 'all') === '1w' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['creator_range' => '1w']) }}">1 Hafta</a>
                            <a class="time-filter-link js-creator-range {{ ($creatorRange ?? 'all') === 'today' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['creator_range' => 'today']) }}">Bugün</a>
                        </div>
                        @if($topCatalogCreators->isEmpty())
                            <p class="empty-hint" style="padding:12px 0;">Gösterecek kullanıcı kayıt verisi bulunmuyor.</p>
                        @else
                            @foreach($topCatalogCreators as $row)
                                <div class="bar-row">
                                    <span class="bar-name" title="{{ $row['label'] }}">{{ \Illuminate\Support\Str::limit($row['label'], 22) }}</span>
                                    <div class="bar-track" title="{{ $row['count'] }} kitap">
                                        <div class="bar-fill bar-fill--secondary" style="width:{{ min(100, round($row['count'] / $topCatalogCreators->max('count') * 100)) }}%"></div>
                                    </div>
                                    <span class="bar-count">{{ number_format($row['count'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <p class="panel-hint">Liste, kapsamınızdaki katalog kayıtlarında en fazla kitap kaydeden ilk 10 kullanıcıyı gösterir.</p>
                        @endif
                    </div>
                @endif

                <div class="panel">
                    <div class="panel-title"><span>Hızlı işlemler</span></div>
                    <div class="qlinks">
                        @if(auth()->user()->hasYetki(8) || auth()->user()->hasYetki(10))
                            <a class="qlink" href="{{ route('odunc.new') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                                Yeni ödünç ver
                            </a>
                        @endif
                        @if(auth()->user()->hasYetki(3) || auth()->user()->hasYetki(6))
                            <a class="qlink" href="{{ route('katalog.new') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13"/></svg>
                                Yeni kitap kaydı
                            </a>
                        @endif
                        @if(auth()->user()->hasYetki(12))
                            <a class="qlink" href="{{ route('uyeler.new') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                                Yeni üye
                            </a>
                        @endif
                        @if(auth()->user()->hasYetki(7) || auth()->user()->hasYetki(8) || auth()->user()->hasYetki(9) || auth()->user()->hasYetki(10))
                            <a class="qlink" href="{{ route('rezerve.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                Rezervasyonlar
                            </a>
                        @endif
                        @if(auth()->user()->hasYetki(1) || auth()->user()->hasYetki(2) || auth()->user()->hasYetki(4) || auth()->user()->hasYetki(5))
                            <a class="qlink" href="{{ route('katalog.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                Kitap kataloğu
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="dash-row">
                @if($flags['loans'] && $recentLoans->isNotEmpty())
                    <div class="panel">
                        <div class="panel-title">
                            <span>Son ödünç işlemleri</span>
                            <a href="{{ route('odunc.index') }}">Tümü</a>
                        </div>
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Üye / Kitap</th>
                                    <th>Durum</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLoans as $islem)
                                    <tr>
                                        <td>
                                            <div style="font-weight:600;">{{ $islem->uye->ad }} {{ $islem->uye->soyad }}</div>
                                            <div style="font-size:12px;color:var(--muted-foreground);margin-top:2px;">{{ \Illuminate\Support\Str::limit($islem->katalog->kunyeEserAdi ?? '—', 42) }}</div>
                                        </td>
                                        <td>
                                            @if($islem->statu === 'aktif')
                                                <span class="badge badge--aktif">Aktif</span>
                                            @elseif($islem->statu === 'iade_edildi')
                                                <span class="badge badge--iade">İade</span>
                                            @else
                                                <span class="badge badge--kayip">Kayıp</span>
                                            @endif
                                        </td>
                                        <td style="text-align:right;"><a href="{{ route('odunc.show', $islem) }}">Detay</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($flags['members'] && $recentMembers->isNotEmpty())
                    <div class="panel">
                        <div class="panel-title">
                            <span>Son kayıtlı üyeler</span>
                            <a href="{{ route('uyeler.index') }}">Liste</a>
                        </div>
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>Durum</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMembers as $uye)
                                    <tr>
                                        <td style="font-weight:600;">{{ $uye->ad }} {{ $uye->soyad }}</td>
                                        <td>
                                            @if($uye->statu === 'aktif')
                                                <span class="badge badge--uye-aktif">Aktif</span>
                                            @else
                                                <span class="badge badge--uye-pasif">Pasif</span>
                                            @endif
                                        </td>
                                        <td style="text-align:right;"><a href="{{ route('uyeler.edit', $uye) }}">Düzenle</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            </div>
@endsection

@section('scripts')
<script>
    (function () {
        function bindCreatorRangeAjax() {
            document.querySelectorAll('.js-creator-range').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    var href = this.getAttribute('href');
                    if (!href) return;

                    var panel = document.getElementById('creatorCatalogPanel');
                    if (!panel) return;

                    panel.querySelectorAll('.js-creator-range').forEach(function (btn) {
                        btn.classList.add('is-loading');
                    });

                    fetch(href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        credentials: 'same-origin'
                    })
                    .then(function (resp) {
                        if (!resp.ok) throw new Error('HTTP ' + resp.status);
                        return resp.text();
                    })
                    .then(function (html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var nextPanel = doc.getElementById('creatorCatalogPanel');
                        var currentPanel = document.getElementById('creatorCatalogPanel');
                        if (!nextPanel || !currentPanel) throw new Error('Panel bulunamadı');

                        currentPanel.outerHTML = nextPanel.outerHTML;
                        window.history.replaceState({}, '', href);
                        bindCreatorRangeAjax();
                    })
                    .catch(function () {
                        window.location.href = href;
                    });
                });
            });
        }

        bindCreatorRangeAjax();
    })();
</script>
@endsection
