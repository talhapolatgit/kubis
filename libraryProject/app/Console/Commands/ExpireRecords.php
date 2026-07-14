<?php

namespace App\Console\Commands;

use App\Models\UyeRezerve;
use App\Models\UyeBekleme;
use App\Models\Katalog;
use App\Models\Uye;
use App\Services\WebhookService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireRecords extends Command
{
    protected $signature = 'records:expire';
    protected $description = 'Süresi geçen rezervasyonları günceller';

    private $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        parent::__construct();
        $this->webhookService = $webhookService;
    }

    public function handle()
    {
        Log::info('ExpireRecords başladı.', ['zaman' => now()]);

        $rezerveler = UyeRezerve::where('rezerve_bitis', '<=', now())
            ->where('oduncAldiMi', '!=', 'true')
            ->where('iptalMi', '!=', 'true')
            ->whereNull('deleted_at')
            ->whereNull('catalogStatuReset');

        $rezerveKoleksiyonu = $rezerveler->get();

        $kataloglar = $rezerveler->pluck('katalog_id')->toArray();

        if (empty($kataloglar)) {
            Log::info('ExpireRecords: Güncellenecek rezervasyon bulunamadı.');
            return;
        }

        DB::transaction(function () use ($rezerveler, $kataloglar) {
            Katalog::whereIn('id', $kataloglar)
                ->update(['kunyeDurum' => 'Rafta']);

            $rezerveler->update([
                'catalogStatuReset' => 'true',
                'suresiDolduMu' => 'true',
                'updated_at' => now(),
            ]);
        });

        foreach($kataloglar as $katalogId) {

            $katalog = Katalog::where('id', $katalogId)->first();

            $beklemeList = UyeBekleme::where('katalog_id', $katalog->id)
                    ->with('uye')
                    ->get()
                    ->pluck('uye.tc_kimlik')
                    ->toArray();

                    Log::info('Bekleme listesi oluşturuldu', [
                        'bekleme_sayisi' => count($beklemeList),
                    ]);        

                    if (!empty($beklemeList)) {
                        try {
                            $this->webhookService->sendBildirim(
                                tcList:  $beklemeList,
                                title:   'Beklediğiniz kitap artık müsait!',
                                message: $katalog->kunyeEserAdi . ' isimli kitap artık müsait. Kaçırmamak için tıkla ve hemen rezerve et 😊',
                            );
                
                            UyeBekleme::where('katalog_id', $katalogId)
                                ->update(['bildirim' => DB::raw('COALESCE(bildirim, 0) + 1')]);
                
                        } catch (\Exception $e) {
                            Log::error('Bildirim gönderilemedi.', [
                                'katalog_id' => $katalogId,
                                'hata'       => $e->getMessage(),
                            ]);
                        }
                    }
                }

            foreach($rezerveKoleksiyonu as $rezerve) {
                $uye = Uye::find($rezerve->uye_id);
                $katalog = Katalog::find($rezerve->katalog_id);

                if ($uye != null && $katalog != null) {
                    try {
                        $this->webhookService->sendBildirim(
                            tcList:  [$uye->tc_kimlik],
                            title:   'Rezervasyonun iptal edildi 😢',
                            message: $katalog->kunyeEserAdi . ' isimli kitap için yaptığın rezervasyonun süresi dolması sebebiyle iptal edilmiştir. Unutma; rezervasyon yaptıktan sonra 24 saat içerisinde kütüphanemize gelerek kitabı ödünç almalısın.',
                        );
            
                    } catch (\Exception $e) {
                        Log::error('Rezervasyon iptal bildirimi gönderilemedi.', [
                            'katalog_id' => $katalog->id,
                            'hata'       => $e->getMessage(),
                        ]);
                    }
                }

            }
                
        

        Log::info('ExpireRecords tamamlandı.', [
            'guncellenen_katalog_sayisi' => count($kataloglar),
            'katalog_idler' => $kataloglar,
        ]);
    }
}