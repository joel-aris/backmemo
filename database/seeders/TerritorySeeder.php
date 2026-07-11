<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Commune;
use App\Models\Province;
use Illuminate\Database\Seeder;

final class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        $territories = [
            'Kinshasa' => [
                'cities' => [
                    'Kinshasa' => ['Gombe', 'Lingwala', 'Limete', 'Barumbu', 'Bumbu', 'Kasa-Vubu', 'Kimbanseke', 'Kintambo', 'Lukunga', 'Mont-Ngafula', 'Ndjili', 'Ngaliema', 'Nsele', 'Selembao'],
                ],
                'code' => 'KIN',
            ],
            'Kongo-Central' => [
                'cities' => [
                    'Matadi' => ['Matadi', 'Nsundi', 'Mokolo', 'Mvungi'],
                    'Boma' => ['Boma', 'Feshi', 'Kimbangu', 'Lufa', 'Maseko'],
                ],
                'code' => 'KCO',
            ],
            'Kwango' => [
                'cities' => [
                    'Kenge' => ['Kenge', 'Kenge Sud', 'Kenge Ouest'],
                    'Popokabaka' => ['Popokabaka', 'Kanti'],
                ],
                'code' => 'KWG',
            ],
            'Kwilu' => [
                'cities' => [
                    'Bandundu' => ['Bandundu', 'Kikwit', 'Masaka', 'Moya'],
                    'Kikwit' => ['Kikwit', 'Kikuyi', 'Kelo'],
                ],
                'code' => 'KWI',
            ],
            'Mai-Ndombe' => [
                'cities' => [
                    'Inongo' => ['Inongo', 'Bolobo', 'Kutu', 'Songo'],
                    'Kutu' => ['Kutu', 'Mushie'],
                ],
                'code' => 'MND',
            ],
            'Tanganyika' => [
                'cities' => [
                    'Kalemie' => ['Kalemie', 'Kabeya', 'Kilumba', 'Nkulu'],
                    'Manono' => ['Manono', 'Kongolo', 'Moba'],
                ],
                'code' => 'TAN',
            ],
            'Haut-Lomami' => [
                'cities' => [
                    'Kamina' => ['Kamina', 'Bukama', 'Kazenga', 'Lubao', 'Luburu'],
                    'Malemba-Nkulu' => ['Malemba-Nkulu', 'Mulongo'],
                ],
                'code' => 'HLO',
            ],
            'Lualaba' => [
                'cities' => [
                    'Kolwezi' => ['Kolwezi', 'Lubudi', 'Mutshatsha'],
                    'Likasi' => ['Likasi', 'Kisanga', 'Kipushi'],
                ],
                'code' => 'LUA',
            ],
            'Haut-Katanga' => [
                'cities' => [
                    'Lubumbashi' => ['Lubumbashi', 'Kamalondo', 'Kenya', 'Kamisato', 'Luanga', 'Mumbunda', 'Rwashi'],
                    'Likasi' => ['Likasi', 'Kikula', 'Panda'],
                ],
                'code' => 'HKA',
            ],
            'Sud-Kivu' => [
                'cities' => [
                    'Bukavu' => ['Bukavu', 'Kabare', 'Kalehe', 'Walungu', 'Shabunda'],
                    'Uvira' => ['Uvira', 'Fizi', 'Nundu'],
                ],
                'code' => 'SKI',
            ],
            'Nord-Kivu' => [
                'cities' => [
                    'Goma' => ['Goma', 'Karisimbi', 'Nyiragongo', 'Rutshuru'],
                    'Beni' => ['Beni', 'Mabalako', 'Beni-ville'],
                ],
                'code' => 'NKI',
            ],
            'Ituri' => [
                'cities' => [
                    'Bunia' => ['Bunia', 'Biringi', 'Mambasa', 'Nyarambo'],
                    'Mahagi' => ['Mahagi', 'Boga', 'Doruma'],
                ],
                'code' => 'ITU',
            ],
            'Tshopo' => [
                'cities' => [
                    'Kisangani' => ['Kisangani', 'Isangi', 'Poko'],
                    'Bafwasende' => ['Bafwasende', 'Basoko'],
                ],
                'code' => 'TSH',
            ],
            'Haut-Uele' => [
                'cities' => [
                    'Isiro' => ['Isiro', 'Faradje', 'Niangara', 'Wamba'],
                    'Dungu' => ['Dungu', 'Bilio', 'Faradje'],
                ],
                'code' => 'HUE',
            ],
            'Bas-Uele' => [
                'cities' => [
                    'Buta' => ['Buta', 'Aketi'],
                    'Poko' => ['Poko', 'Wamba'],
                ],
                'code' => 'BUE',
            ],
            'Mongala' => [
                'cities' => [
                    'Lisala' => ['Lisala', 'Bumba', 'Basankusu'],
                    'Bumba' => ['Bumba', 'Mobayi-Mbongo'],
                ],
                'code' => 'MON',
            ],
            'Nord-Ubangi' => [
                'cities' => [
                    'Gbadolite' => ['Gbadolite', 'Bondo', 'Zongo'],
                    'Bosobolo' => ['Bosobolo', 'Businga'],
                ],
                'code' => 'NUB',
            ],
            'Sud-Ubangi' => [
                'cities' => [
                    'Gemena' => ['Gemena', 'Libenge', 'Zongo'],
                    'Budjala' => ['Budjala', 'Businga'],
                ],
                'code' => 'SUB',
            ],
            'Équateur' => [
                'cities' => [
                    'Mbandaka' => ['Mbandaka', 'Bolomba', 'Kwamouth', 'Monkoto'],
                    'Bumba' => ['Bumba', 'Aketi'],
                ],
                'code' => 'EQU',
            ],
            'Tshuapa' => [
                'cities' => [
                    'Boende' => ['Boende', 'Bokungu', 'Ikela'],
                    'Lomela' => ['Lomela', 'Boende'],
                ],
                'code' => 'TPA',
            ],
            'Sankuru' => [
                'cities' => [
                    'Lomela' => ['Lomela', 'Kisenga', 'Wembi'],
                    'Lubefu' => ['Lubefu', 'Lokilo'],
                ],
                'code' => 'SAN',
            ],
            'Kasaï' => [
                'cities' => [
                    'Luebo' => ['Luebo', 'Mweka'],
                    'Tshikapa' => ['Tshikapa', 'Mweka'],
                ],
                'code' => 'KAS',
            ],
            'Kasaï-Central' => [
                'cities' => [
                    'Kananga' => ['Kananga', 'Dibumba', 'Luiza'],
                    'Mweka' => ['Mweka', 'Luebo'],
                ],
                'code' => 'KCE',
            ],
            'Kasaï-Oriental' => [
                'cities' => [
                    'Mbuji-Mayi' => ['Mbuji-Mayi', 'Dibindi', 'Kisanfu'],
                    'Kabeya-Kamwanga' => ['Kabeya-Kamwanga', 'Kakenge'],
                ],
                'code' => 'KOR',
            ],
            'Lomami' => [
                'cities' => [
                    'Kabinda' => ['Kabinda', 'Lukelenge'],
                    'Kamina' => ['Kamina', 'Kabongo'],
                ],
                'code' => 'LOM',
            ],
            'Maniema' => [
                'cities' => [
                    'Kindu' => ['Kindu', 'Alunguli', 'Kailo', 'Pombe'],
                    'Kibombo' => ['Kibombo', 'Lubutu', 'Mwenga'],
                ],
                'code' => 'MAN',
            ],
        ];

        foreach ($territories as $provinceName => $data) {
            $province = Province::query()->updateOrCreate(
                ['name' => $provinceName],
                ['code' => $data['code']]
            );

            foreach ($data['cities'] as $cityName => $communes) {
                $city = City::query()->updateOrCreate(
                    ['province_id' => $province->id, 'name' => $cityName],
                    ['code' => strtoupper(substr($cityName, 0, 5))]
                );

                foreach ($communes as $communeName) {
                    Commune::query()->updateOrCreate(
                        ['city_id' => $city->id, 'name' => $communeName],
                        ['province_id' => $province->id]
                    );
                }
            }
        }
    }
}
