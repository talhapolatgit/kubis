{{-- resources/views/partials/sidebar.blade.php --}}
<style>
    .sidebar-reserve-badge {
        margin-left: auto;
        flex-shrink: 0;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        line-height: 22px;
        text-align: center;
        letter-spacing: -0.03em;
        font-variant-numeric: tabular-nums;
    }
    .sidebar-reserve-badge--active {
        color: #fffbeb;
        background: linear-gradient(145deg, #f97316, #ea580c);
        box-shadow:
            0 1px 3px rgba(194, 65, 12, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.15) inset,
            0 0 12px rgba(234, 88, 12, 0.45);
        animation: sidebarReserveGlow 2.2s ease-in-out infinite;
    }
    .sidebar-reserve-badge--zero {
        color: var(--sidebar-foreground);
        opacity: 0.45;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    @keyframes sidebarReserveGlow {
        0%, 100% {
            box-shadow:
                0 1px 3px rgba(194, 65, 12, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.15) inset,
                0 0 10px rgba(234, 88, 12, 0.35);
        }
        50% {
            box-shadow:
                0 2px 8px rgba(194, 65, 12, 0.65),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset,
                0 0 18px rgba(251, 146, 60, 0.55);
        }
    }
</style>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
        </div>
        <div>
            <div class="sidebar-brand-name">KÜBİS</div>
            <div class="sidebar-brand-sub">Kütüphane Bilgi Sistemi</div>
        </div>
    </div>
    <div class="sidebar-separator"></div>
    <div class="sidebar-content">

        <div class="sidebar-group">
            <div class="sidebar-group-label">Genel</div>
            <ul class="sidebar-menu">
                @if(auth()->user()->hasYetki(21))
                <li><a href="{{ route('dashboard.index') }}" class="sidebar-menu-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        Dashboard</a></li>
                @endif
            </ul>
        </div>

        <div class="sidebar-group">
            <div class="sidebar-group-label">Katalog</div>
            <ul class="sidebar-menu">
                @if(auth()->user()->hasYetki(1) || auth()->user()->hasYetki(2) ||  auth()->user()->hasYetki(4) || auth()->user()->hasYetki(5))
                <li><a href="{{ route('katalog.index') }}" class="sidebar-menu-item {{ request()->routeIs('katalog.index') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                        Katalog Listesi</a></li>
                @endif
                @if(auth()->user()->hasYetki(3) || auth()->user()->hasYetki(6))
                <li><a href="{{ route('katalog.new') }}" class="sidebar-menu-item {{ request()->routeIs('katalog.new') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Yeni Katalog</a></li>
                @endif
                
            </ul>
        </div>

        <div class="sidebar-group">
            <div class="sidebar-group-label">Ödünç & Rezervasyon</div>
            <ul class="sidebar-menu">
                @if(auth()->user()->hasYetki(7) || auth()->user()->hasYetki(8) || auth()->user()->hasYetki(9) || auth()->user()->hasYetki(10))
                <li><a href="{{ route('odunc.index') }}" class="sidebar-menu-item {{ request()->routeIs('odunc.index') || request()->routeIs('odunc.show') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                        Ödünç İşlemleri</a></li>
                @endif
                @if(auth()->user()->hasYetki(8) || auth()->user()->hasYetki(10))  
                <li><a href="{{ route('odunc.new') }}" class="sidebar-menu-item {{ request()->routeIs('odunc.new') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15h6"/></svg>
                        Yeni Ödünç Ver</a></li>
                @endif
                @if(auth()->user()->hasYetki(7) || auth()->user()->hasYetki(8) || auth()->user()->hasYetki(9) || auth()->user()->hasYetki(10))
                <li><a href="{{ route('rezerve.index') }}" class="sidebar-menu-item {{ request()->routeIs('rezerve.*') ? 'active' : '' }}" title="Aktif rezervasyon sayısı">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/></svg>
                        <span style="flex:1;min-width:0;">Rezervasyon İşlemleri</span>
                        <span class="sidebar-reserve-badge {{ ($sidebarAktifRezerveSayisi ?? 0) > 0 ? 'sidebar-reserve-badge--active' : 'sidebar-reserve-badge--zero' }}" aria-label="Aktif rezervasyon: {{ $sidebarAktifRezerveSayisi ?? 0 }}">{{ $sidebarAktifRezerveSayisi ?? 0 }}</span>
                    </a></li>
                @endif
            </ul>
        </div>

        <div class="sidebar-group">
            <div class="sidebar-group-label">Üyeler</div>
            <ul class="sidebar-menu">
                @if(auth()->user()->hasYetki(11) || auth()->user()->hasYetki(13))
                <li><a href="{{ route('uyeler.index') }}" class="sidebar-menu-item {{ request()->routeIs('uyeler.index') || request()->routeIs('uyeler.edit') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Üye Listesi</a></li>
                @endif
                @if(auth()->user()->hasYetki(12))
                <li><a href="{{ route('uyeler.new') }}" class="sidebar-menu-item {{ request()->routeIs('uyeler.new') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Yeni Üye Kayıt</a></li>
                @endif
            </ul>
        </div>

        <div class="sidebar-group">
            <div class="sidebar-group-label">Tanımlar</div>
            <ul class="sidebar-menu">
                @if(auth()->user()->hasYetki(17) || auth()->user()->hasYetki(19))
                <li><a href="{{ route('kutuphane.index') }}" class="sidebar-menu-item {{ request()->routeIs('kutuphane.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Kütüphaneler</a></li>
                @endif
                @if(auth()->user()->hasYetki(22))
                <li><a href="{{ route('yazarlar.index') }}" class="sidebar-menu-item {{ request()->routeIs('yazarlar.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Yazarlar</a></li>
                @endif
                @if(auth()->user()->hasYetki(24))
                <li><a href="{{ route('yayinevleri.index') }}" class="sidebar-menu-item {{ request()->routeIs('yayinevleri.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Yayınevleri</a></li>
                @endif
                @if(auth()->user()->hasYetki(20))
                        <!-- <li><a href="{{ route('dil.index') }}" class="sidebar-menu-item {{ request()->routeIs('dil.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                Diller</a></li> -->
                        <li><a href="{{ route('katalog_parametre.index') }}" class="sidebar-menu-item {{ request()->routeIs('katalog_parametre.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v6H3z"/><path d="M3 15h18v6H3z"/><path d="M7 6h.01"/><path d="M7 18h.01"/></svg>
                                Parametreler</a></li>
                @endif
            </ul>
        </div>

        @auth
            @if(auth()->user()->hasYetki(14) || auth()->user()->hasYetki(15) || auth()->user()->hasYetki(16) || auth()->user()->hasYetki(20))
                <div class="sidebar-group">
                    <div class="sidebar-group-label">Yönetim</div>
                    <ul class="sidebar-menu">
                        @if(auth()->user()->hasYetki(14) || auth()->user()->hasYetki(16))
                        <li><a href="{{ route('users.index') }}" class="sidebar-menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                Sistem Kullanıcıları</a></li>
                        @endif
                        @if(auth()->user()->hasYetki(20))
                        <li><a href="{{ route('etiket.index') }}" class="sidebar-menu-item {{ request()->routeIs('etiket.*') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#ffffff" stroke="#ffffff" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M14.635 2.217a.74.74 0 0 0-1.048 0l-10.37 10.37a.74.74 0 0 0-.217.524v5.185a.741.741 0 0 0 .217.524l2.286 2.286c-.247.6-.513.881-.722.881-.429 0-.846-.58-.982-.86l-.086-.18-1.037.493.085.18c.029.063.728 1.518 2.02 1.518a1.853 1.853 0 0 0 1.608-1.215.732.732 0 0 0 .315.077h5.185a.741.741 0 0 0 .524-.217l10.37-10.37a.74.74 0 0 0 0-1.048zM11.782 21H6.81l-.043-.043a10.076 10.076 0 0 0 .258-1.005 2.533 2.533 0 1 0-1.056-.488c-.022.079-.05.152-.066.235-.023.115-.048.216-.072.322L4 18.189v-4.97L14.11 3.106l7.783 7.782zM6 17.5A1.5 1.5 0 1 1 7.5 19c-.021 0-.04-.005-.062-.006a2.873 2.873 0 0 1 .61-.647l.16-.114-.649-.946-.165.115A4.018 4.018 0 0 0 6.39 18.5a1.489 1.489 0 0 1-.39-1zm2.542-5.792l5.922-5.922.707.707-5.922 5.923zm2.021 2.022l5.922-5.922.707.707-5.922 5.922zm2.021 2.021l5.923-5.922.707.707-5.922 5.922z"></path><path fill="none" d="M0 0h24v24H0z"></path></g></svg>
                                Etiket Oluştur</a></li>
                        @endif
                        
                    </ul>
                </div>
            @endif
        @endauth
    </div>

    <div class="sidebar-footer">
        @auth
            <div class="sidebar-user">
                <div class="sidebar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div style="flex:1;min-width:0;">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ auth()->user()->getRoleLabel() }}</div>
                </div>
                <a href="{{ route('auth.password.form') }}" title="Şifre Değiştir" style="background:none;border:none;cursor:pointer;padding:6px;border-radius:6px;color:var(--sidebar-foreground);opacity:0.55;display:flex;align-items:center;transition:opacity .15s,background .15s;text-decoration:none;" onmouseover="this.style.opacity='1';this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.opacity='0.55';this.style.background='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </a>
                <form method="POST" action="{{ route('auth.logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" title="Çıkış Yap" style="background:none;border:none;cursor:pointer;padding:6px;border-radius:6px;color:var(--sidebar-foreground);opacity:0.55;display:flex;align-items:center;transition:opacity .15s,background .15s;" onmouseover="this.style.opacity='1';this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.opacity='0.55';this.style.background='none'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</aside>
