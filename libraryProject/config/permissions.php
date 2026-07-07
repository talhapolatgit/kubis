<?php

/**
 * Sistem yetki tanımları.
 *
 * legacy_no: hasYetki(N) ile geriye dönük uyumluluk (eski y01..y26).
 * slug:      kod içinde anlamlı sabit referans.
 * group:     Üst modül grubu (permissions.groups).
 *
 * label:     Yalnızca seed/migration için; arayüzde permissions.label (veritabanı) kullanılır.
 */
return [

    'groups' => [
        'genel' => [
            'title'       => 'Genel',
            'description' => 'Ana ekran ve genel erişim.',
            'permissions' => [21],
        ],
        'katalog' => [
            'title'       => 'Katalog',
            'description' => 'Kitap kayıtlarının listelenmesi, güncellenmesi ve yeni kayıt oluşturma.',
            'permissions' => [1, 2, 3, 4, 5, 6],
        ],
        'odunc' => [
            'title'       => 'Ödünç & Rezervasyon & Ziyaret',
            'description' => 'Ödünç verme, rezervasyon ve ziyaretçi işlemleri.',
            'permissions' => [7, 8, 9, 10, 35, 27, 28, 29, 30, 31, 32, 33, 34],
        ],
        'uyeler' => [
            'title'       => 'Üyeler',
            'description' => 'Kütüphane üyelerinin listelenmesi ve yönetimi.',
            'permissions' => [11, 12, 13],
        ],
        'tanimlar' => [
            'title'       => 'Tanımlar',
            'description' => 'Kütüphane, yazar ve yayınevi tanımları.',
            'sections'    => [
                'kutuphane' => [
                    'title'       => 'Kütüphaneler',
                    'permissions' => [17, 18, 19],
                ],
                'yazar' => [
                    'title'       => 'Yazarlar',
                    'permissions' => [22, 23],
                ],
                'yayinevi' => [
                    'title'       => 'Yayınevleri',
                    'permissions' => [24, 25],
                ],
            ],
        ],
        'yonetim' => [
            'title'       => 'Yönetim',
            'description' => 'Sistem kullanıcıları, etiketler ve raporlar.',
            'sections'    => [
                'kullanici' => [
                    'title'       => 'Sistem Kullanıcıları',
                    'permissions' => [14, 15, 16],
                ],
                'etiket' => [
                    'title'       => 'Etiket & Parametreler',
                    'permissions' => [20],
                ],
                'rapor' => [
                    'title'       => 'Raporlar',
                    'permissions' => [26],
                ],
            ],
        ],
    ],

    'permissions' => [
        1  => ['slug' => 'katalog.kutuphane.view',   'label' => 'Yetkili olduğu kütüphanelerin kitaplarını görebilir.', 'group' => 'katalog'],
        2  => ['slug' => 'katalog.kutuphane.edit',   'label' => 'Yetkili olduğu kütüphanelerin kitaplarını görebilir ve güncelleyebilir.', 'group' => 'katalog'],
        3  => ['slug' => 'katalog.kutuphane.create', 'label' => 'Yetkili olduğu kütüphanelere yeni kitap kaydedebilir.', 'group' => 'katalog'],
        4  => ['slug' => 'katalog.all.view',         'label' => 'Tüm kütüphanelerin kitaplarını görebilir.', 'group' => 'katalog'],
        5  => ['slug' => 'katalog.all.edit',         'label' => 'Tüm kütüphanelerin kitaplarını görebilir ve güncelleyebilir.', 'group' => 'katalog'],
        6  => ['slug' => 'katalog.all.create',       'label' => 'Tüm kütüphanelere yeni kitap kaydedebilir.', 'group' => 'katalog'],
        7  => ['slug' => 'odunc.kutuphane.view',     'label' => 'Yetkili olduğu kütüphanelerin ödünçlerini görebilir.', 'group' => 'odunc'],
        8  => ['slug' => 'odunc.kutuphane.manage',   'label' => 'Yetkili olduğu kütüphanelerin ödünçlerini görebilir ve yeni ödünç verebilir.', 'group' => 'odunc'],
        9  => ['slug' => 'odunc.all.view',           'label' => 'Tüm kütüphanelerin ödünçlerini görebilir.', 'group' => 'odunc'],
        10 => ['slug' => 'odunc.all.manage',         'label' => 'Tüm kütüphanelerin ödünçlerini görebilir ve yeni ödünç verebilir.', 'group' => 'odunc'],
        11 => ['slug' => 'uye.view',                 'label' => 'Üyeleri görebilir.', 'group' => 'uyeler'],
        12 => ['slug' => 'uye.create',               'label' => 'Yeni üye oluşturabilir.', 'group' => 'uyeler'],
        13 => ['slug' => 'uye.edit',                 'label' => 'Üye güncelleyebilir', 'group' => 'uyeler'],
        14 => ['slug' => 'user.view',                'label' => 'Kullanıcıları görebilir.', 'group' => 'yonetim', 'section' => 'kullanici'],
        15 => ['slug' => 'user.create',              'label' => 'Yeni kullanıcı oluşturabilir.', 'group' => 'yonetim', 'section' => 'kullanici'],
        16 => ['slug' => 'user.edit',                'label' => 'Kullanıcıları görebilir ve güncelleyebilir.', 'group' => 'yonetim', 'section' => 'kullanici'],
        17 => ['slug' => 'kutuphane.view',           'label' => 'Kütüphaneleri görebilir.', 'group' => 'tanimlar', 'section' => 'kutuphane'],
        18 => ['slug' => 'kutuphane.create',         'label' => 'Yeni kütüphane oluşturabilir.', 'group' => 'tanimlar', 'section' => 'kutuphane'],
        19 => ['slug' => 'kutuphane.edit',           'label' => 'Kütüphaneleri görebilir ve güncelleyebilir.', 'group' => 'tanimlar', 'section' => 'kutuphane'],
        20 => ['slug' => 'etiket.manage',            'label' => 'Etiket oluşturabilir.', 'group' => 'yonetim', 'section' => 'etiket'],
        21 => ['slug' => 'dashboard.view',           'label' => 'Dashboard ekranı görme yetkisi.', 'group' => 'genel'],
        22 => ['slug' => 'yazar.view',               'label' => 'Yazarlar ekranına erişebilir.', 'group' => 'tanimlar', 'section' => 'yazar'],
        23 => ['slug' => 'yazar.manage',             'label' => 'Yazar ekleyebilir, güncelleyebilir ve silebilir.', 'group' => 'tanimlar', 'section' => 'yazar'],
        24 => ['slug' => 'yayinevi.view',            'label' => 'Yayınevleri ekranına erişebilir.', 'group' => 'tanimlar', 'section' => 'yayinevi'],
        25 => ['slug' => 'yayinevi.manage',          'label' => 'Yayınevi ekleyebilir, güncelleyebilir ve silebilir.', 'group' => 'tanimlar', 'section' => 'yayinevi'],
        26 => ['slug' => 'rapor.sihirbazi',          'label' => 'Rapor Sihirbazı ekranına erişebilir.', 'group' => 'yonetim', 'section' => 'rapor'],
        27 => ['slug' => 'ziyaret.view',             'label' => 'Ziyaret geçmişini görebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        28 => ['slug' => 'ziyaret.create',           'label' => 'Ziyaret oluşturabilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        29 => ['slug' => 'ziyaret.edit',             'label' => 'Ziyaret geçmişini güncelleyebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        30 => ['slug' => 'ziyaret.delete',           'label' => 'Ziyaret geçmişini silebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        31 => ['slug' => 'ziyaret.all.view',         'label' => 'Tüm kütüphanelerin ziyaret geçmişini görebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        32 => ['slug' => 'ziyaret.all.create',       'label' => 'Tüm kütüphanelere ziyaret oluşturabilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        33 => ['slug' => 'ziyaret.all.edit',         'label' => 'Tüm kütüphanelerin ziyaret geçmişini görebilir ve güncelleyebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        34 => ['slug' => 'ziyaret.all.delete',       'label' => 'Tüm kütüphanelerin ziyaret geçmişini silebilir.', 'group' => 'uyeler', 'section' => 'ziyaret'],
        35 => ['slug' => 'odunc.extend',             'label' => 'Ödünç süresini uzatabilir.', 'group' => 'odunc'],
    ],
];
