<?php

namespace App\Providers;

use App\Models\UyeRezerve;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.sidebar', function ($view) {
            $count = 0;
            $user  = auth()->user();
            if ($user && ($user->hasYetki(7) || $user->hasYetki(8) || $user->hasYetki(9) || $user->hasYetki(10))) {
                $q = UyeRezerve::query();
                if (! $user->hasYetki(9) && ! $user->hasYetki(10)) {
                    $ids = $user->yetkiliKutuphaneIds();
                    $q->whereHas('katalog', function ($k) use ($ids) {
                        $k->whereIn('kutuphaneId', $ids ?: [-1]);
                    });
                }
                $count = $q->where('iptalMi', 'false')
                    ->where('oduncAldiMi', 'false')
                    ->where('rezerve_bitis', '>', now())
                    ->count();
            }
            $view->with('sidebarAktifRezerveSayisi', $count);
        });
    }
}
