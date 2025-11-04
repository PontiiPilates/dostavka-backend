<?php

namespace Database\Seeders\Tk;

use App\Enums\CompanyType;
use App\Enums\LocationType;
use App\Enums\Nrg\NrgUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalNrg;
use App\Traits\Logger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalNrgSeeder extends Seeder
{
    use Logger;

    private array $countryCodes = [
        0 => "RU",
        -1 => "RU",
        86015311036992 => "RU",
        86015311037009 => "KZ",
        86015311037004 => "KG",
        86015311036993 => "BY",
        86015311037003 => "AM",
        86015311037018 => "RU",
    ];

    private string|null $name;
    private string|null $type;
    private string|null $region;
    private string|null $district;
    private bool $federal;

    private array $candidatsToUpdate = [];

    /**
     * Принципы парсинга:
     * несмотря на то, что список имеет подразделение на терминалы внутри локации,
     * извлечению подлежат лишь данные о локации, без терминалов,
     * поскольку апи позволяет работать с идентификаторами локаций, то, что нужно,
     * некоторые наименования локаций содержат региональную принадлежность,
     * всвязи с этим происходит их очистка,
     * качество данных не позволяет применять данный список в качестве образующего,
     * на данном этапе список только регистрирует собственные идентификаторы локаций,
     * и в незначительной степени дополняет общий состав
     */
    public function run(): void
    {
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;
        $token = config('companies.nrg.token');

        TerminalNrg::truncate();

        $iterable = 0;
        $timeStart = Carbon::now();

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        foreach ($response->object()->cityList as $city) {

            // если обнаружено нежелательное наименование
            if (in_array($city->name, [
                'Кокшетау KZ НЕ ВЫБИРАТЬ!',
                '1-е Отделение СНТ Восход',
                '2-е Отделение СНТ Восход',
                '2-й поселок ЛПК',
                '3-е Отделение СНТ Восход',
                '3307км',
                '5-я стройка СНТ "Малинка"',
                '5-я стройка СНТ "Мечта"',
                '5-я стройка СНТ "Портовик"',
                '5-я стройка СНТ "Сетлое"',
                '5-я стройка СНТ "Сторожил"',
                '7-й км',
                '8 Марта',
                '86-й Квартал',
                'АО "Аммоний"',
                'АО "ОЭЗ ППТ "Алабуга"',
                'АО "Химзавод им.Карпова"',
                'Ереван НЕ ПРИНИМАТЬ!!!',
                'Байкальский тракт 20-28 км',
                'Байкальский тракт 5-15 км',
                'Бишкек2',
                'Бяла-Подляска',
                'Гуанчжоу',
                'Маньчжурия',
                'Масловский Совхоз',
                'Муравьинная бухта',
                'Новосибирск2',
                'Пекин',
                'Пионерная база',
                'Плишкинский тракт',
                'Полет ДНТ',
                'Пришахтинск',
                'Промзона БСИ-1',
                'Ростов2',
                'Рябиновый м-н',
                'СНТ Отрадное (Бердск)',
                'Сара-Станция',
                'Сельская (Бердск)',
                'Сортировка',
                'Сутузово',
                'Улак (Ундыткан)',
                'Уралоргсинтез',
                'Урумчи',
                'Усть-Среднекан',
                'Химик',
                'Холмогорской',
                'Шанхай',
                'Шэньчжэнь',
                'Станция Абалаково',
                'Головинская',
                'Горизонт ЖК',
                'Горская (С.Петербург)',
                'ДНП Лаки Парк',
                'Камала',
                'Ключевой',
                'Миловиды ЖК',
                'Гелиос СНТ',
                'ДНТ "Орбита"',
                'Жаворонки ЖК',
                'ЗапСибНефтехим',
                'Магдагачи Адресная НЕ ВЫБИРАТЬ',
                'Мельничный тракт более 4 км',
                'ОбьГЭС',
                'СНТ "Мечта"',
                'СНТ Ротор',
                'Томскнефтехим ООО',
                'Шагаловский 3298 км',
                'Разъезд Абсалямово',
            ])) {
                continue;
            }

            $this->name = $this->normalizeName($city->name);
            $this->type = null;
            $this->region = null;
            $this->district = null;
            $this->federal = false;

            // если обнаружена принадлежность к территиории федерального значения
            if ($city->name == 'Санкт-Петербург' || $city->name == 'Москва' || $city->name == 'Севастополь') {
                $this->region = $city->name;
                $this->federal = true;
            }

            $location = Location::query()
                ->where('name', $this->name)
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $this->countryCodes[$city->idCountry]);
                })->first();

            // если локация не обнаружена, то происходит попытка парсинга
            if (!$location) {

                // continue;

                $territories = explode(',', $city->description);

                foreach ($territories as $key => $territory) {

                    $locationTypes = [
                        LocationType::AgroTown->value,
                        LocationType::Aul->value,
                        LocationType::CottageVillage->value,
                        LocationType::Farmstead->value,
                        LocationType::Hamlet->value,
                        LocationType::Island->value,
                        LocationType::JobVillage->value,
                        LocationType::Locality->value,
                        LocationType::MicroDistrict->value,
                        LocationType::Pgt->value,
                        LocationType::ResidentialComplex->value,
                        LocationType::ResortVillage->value,
                        LocationType::RualVillage->value,
                        LocationType::Sloboda->value,
                        LocationType::SmallTown->value,
                        LocationType::Snt->value,
                        LocationType::Spk->value,
                        LocationType::Stanitsa->value,
                        LocationType::StateFarm->value,
                        LocationType::Town->value,
                        LocationType::Township->value,
                        LocationType::UrbanVillage->value,
                        LocationType::Village->value,
                        LocationType::Zato->value,
                        'поселок',
                        'деверня',
                        'деервня',
                        'поселок городского типа',
                        'р.п.',
                        'пгт.',
                    ];

                    $regionTypes = [
                        LocationType::Area->value,
                        LocationType::AutonomousRegion->value,
                        LocationType::Edge->value,
                        LocationType::Republic->value,
                    ];

                    // определение типа локации и приведение к стандарту
                    if (in_array(mb_strtolower($territory), $locationTypes)) {
                        $this->type = mb_strtolower($territory);
                        if ($this->type == 'поселок') {
                            $this->type = LocationType::Township->value;
                        }
                        if ($this->type == 'деверня' || $this->type == 'деервня') {
                            $this->type = LocationType::Hamlet->value;
                        }
                        if ($this->type == 'поселок городского типа' || $this->type == 'пгт' || $this->type == 'пгт.') {
                            $this->type = LocationType::Pgt->value;
                        }
                        if ($this->type == 'р.п.') {
                            $this->type = LocationType::JobVillage->value;
                        }
                    }

                    // определение региона и приведение его к стандарту
                    foreach ($regionTypes as $regionType) {
                        $normalizeTerritory = mb_strtolower($territory);
                        $normalizeTerritory = str_replace(['республика'], ['Республика'], $normalizeTerritory);
                        $normalizeTerritory = trim($normalizeTerritory);

                        if (str_contains($normalizeTerritory, $regionType)) {
                            $this->region = mb_ucfirst($normalizeTerritory);
                            $this->region = str_replace([
                                1 => 'Республика башкортостан',
                                2 => 'Башкортостан Республика',
                                3 => 'Республика татарстан',
                                4 => 'Республика северная осетия-алания',
                                5 => 'Ингушетия Республика',
                                6 => 'Республика крым',
                                7 => 'Чеченская Республика',
                                8 => 'Деревня. Республика башкортостан',
                                9 => 'Тыва Республика',
                                10 => 'Мордовия Республика',
                                11 => 'Республика северная осетия — алания',
                                12 => 'Республика хакасия',
                                13 => 'Адыгея Республика',
                                14 => 'Кабардино-балкарская Республика',
                                15 => 'Татарстан Республика',
                                16 => 'Хакасия Республика',
                                17 => 'Республика саха (якутия)',
                                18 => 'Республика марий эл',
                                19 => 'Деревня. Республика Башкортостан',
                            ], [
                                1 => 'Республика Башкортостан',
                                2 => 'Республика Башкортостан',
                                3 => 'Республика Татарстан',
                                4 => 'Республика Северная Осетия - Алания',
                                5 => 'Республика Ингушетия',
                                6 => 'Республика Крым',
                                7 => 'Чеченская Республика',
                                8 => 'Республика Башкортостан',
                                9 => 'Республика Тыва',
                                10 => 'Республика Мордовия',
                                11 => 'Республика Северная Осетия - Алания',
                                12 => 'Республика Хакасия',
                                13 => 'Республика Адыгея',
                                14 => 'Кабардино-Балкарская Республика',
                                15 => 'Республика Татарстан',
                                16 => 'Республика Хакасия',
                                17 => 'Республика Саха (Якутия)',
                                18 => 'Республика Марий Эл',
                                19 => 'Республика Башкортостан',
                            ], $this->region);
                        }
                    }

                    // определение района и приведение его к стандарту
                    if (str_contains(mb_strtolower($territory), LocationType::District->value)) {

                        $normalizeDistrict = mb_strtolower($territory);
                        $normalizeDistrict = trim($normalizeDistrict);
                        $normalizeDistrict = mb_ucfirst($normalizeDistrict);

                        $normalizeDistrict = str_replace([
                            'Ленинск-кузнецкий район',
                            'Кинель-черкасский район',
                            'Каа-хемский район',
                            'Усть-таркский район',
                            'Адыге-хабльский район',
                            'Ножай-юртовский район',
                            'Усть-кутский район',
                            'Ачхой-мартановский район',
                            'Пгт килемарский район',
                            'Ац кравинского района',
                            'Посёлок в елизовском районе камчатского края',
                            'Марик-турекский район',
                            'Урус-мартановский район',
                            'Посёлок в иркутском районе иркутской области',
                            'Казачинско-ленинский район',
                            'Петров-забайкальский район',
                            'Эхирит-булатагский район',
                            'Голоустинский тракт. деревня в иркутском районе иркутской области.',
                            '​коченевский район',
                            'Чеди-хольский район',
                        ], [
                            'Ленинск-Кузнецкий район',
                            'Кинель-Черкасский район',
                            'Каа-Хемский район',
                            'Усть-Таркский район',
                            'Адыге-Хабльский район',
                            'Ножай-Юртовский район',
                            'Усть-Кутский район',
                            'Ачхой-Мартановский район',
                            'Килемарский район',
                            'Кравинский район',
                            'Елизовский район',
                            'Мари-Турекский район',
                            'Урус-Мартановский район',
                            'Иркутский район',
                            'Казачинско-Ленский район',
                            'Петровск-Забайкальский район',
                            'Эхирит-Булагатский район',
                            'Иркутский район',
                            'Коченевский район',
                            'Чеди-Хольский район',
                        ], $normalizeDistrict);

                        $this->district = $normalizeDistrict;
                    }

                    // если не удалось распарсить
                    if (!$this->type && !$this->region && !$this->district) {
                        $this->parseFail(CompanyType::Nrg->value, $city->name . ': ' . $city->description);
                    }
                }
            }

            TerminalNrg::create([
                'identifier' => $city->id,
                'name' => $this->name ?? $city->name,
                'type' => $this->type,
                'district' => $this->district,
                'region' => $this->region,
                'federal' => $this->federal,
                'country' => $this->countryCodes[$city->idCountry],
            ]);

            $iterable++;
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);
        $executionTime = number_format((float) $executionTime, 1, '.');

        $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");
    }

    /**
     * Нормализация наименования населённого пункта
     */
    private function normalizeName($name)
    {
        if (str_contains($name, '(')) {
            $name = strstr($name, '(', true);
        }

        if (str_contains($name, ',')) {
            $name = explode(',', $name)[0];
        }

        $name = trim($name);

        $name = str_replace([
            'ВладиКАВКАЗ',
            'Гороное ЛОО',
            'ДальнеГОРСК',
            'ЗеленоДОЛЬСК',
            'КрасноДАР',
            'КрасноКАМСК',
            'КрасноЯРСК',
            'НЕФТЕкамск',
            'НефтеЮГАНСК',
            'НижнеКАМСК',
            'Ново-Ленино/Западный',
            'Совхоз Боровский',
            'Совхоз Победа',
            'Совхоз Ревдинский',
            'Совхоз Сибиряк',
            'Усть-КАМЕНОГОРСК',
        ], [
            'Владикавказ',
            'Горное Лоо',
            'Дальнегорск',
            'Зеленодольск',
            'Краснодар',
            'Краснокамск',
            'Красноярск',
            'Нефтекамск',
            'Нефтеюганск',
            'Нижнекамск',
            'Ново-Ленино',
            'Боровский',
            'Победа',
            'Ревдинский',
            'Сибиряк',
            'Усть-Каменогорск',

        ], $name);

        return $name;
    }
}
