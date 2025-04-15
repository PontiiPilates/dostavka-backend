<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pochta = Company::where('name', 'pochta')->first();

        // письмо
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter',
            'label' => 'Письмо простое',
            'number' => '2000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_ordered',
            'label' => 'Письмо заказное',
            'number' => '2010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_with_sumoc',
            'label' => 'Письмо с объявленной ценностью',
            'number' => '2020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_with_sumoc_and_cash',
            'label' => 'Письмо с объявленной ценностью и наложенным платежом',
            'number' => '2040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_first_class_ordered',
            'label' => 'Письмо 1 класса заказное',
            'number' => '15010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_first_class_with_sumoc',
            'label' => 'Письмо 1 класса с объявленной ценностью',
            'number' => '15020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_first_class_with_sumoc_and_cash',
            'label' => 'Письмо 1 класса с объявленной ценностью и наложенным платежом',
            'number' => '15040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'track_postcard',
            'label' => 'Трек-открытка',
            'number' => '36000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'track_letter',
            'label' => 'Трек-письмо',
            'number' => '37000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'track_letter',
            'label' => 'Трек-письмо',
            'number' => '37000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        // почтовая карточка, секограмма
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'postcard',
            'label' => 'Почтовая карточка простая',
            'number' => '6000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'postcard_ordered',
            'label' => 'Почтовая карточка заказная',
            'number' => '6010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'secogram',
            'label' => 'Секограмма',
            'number' => '8010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        // бандероль
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel',
            'label' => 'Бандероль простая',
            'number' => '3000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_ordered',
            'label' => 'Бандероль заказная',
            'number' => '3010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_with_sumoc',
            'label' => 'Бандероль с объявленной ценностью',
            'number' => '3020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_with_sumoc_and_cash',
            'label' => 'Бандероль с объявленной ценностью и наложенным платежом',
            'number' => '3040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_first_class_ordered',
            'label' => 'Бандероль 1 класса заказная',
            'number' => '16010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_first_class_with_sumoc',
            'label' => 'Бандероль 1 класса с объявленной ценностью',
            'number' => '16020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_first_class_with_sumoc_and_cach',
            'label' => 'Бандероль 1 класса с объявленной ценностью и наложенным платежом',
            'number' => '16040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_complect',
            'label' => 'Бандероль-комплект',
            'number' => '35010',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        // посылки для населения
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'standatr_package',
            'label' => 'Письмо простое',
            'number' => '27030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'standatr_package_with_sumoc',
            'label' => 'Посылка стандарт с объявленной ценностью',
            'number' => '27020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'standatr_package_with_sumoc_and_cash',
            'label' => 'Посылка стандарт с объявленной ценностью и наложенным платежом',
            'number' => '27040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'unstandart_package',
            'label' => 'Посылка нестандартная',
            'number' => '4030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'unstandart_package_with_sumoc',
            'label' => 'Посылка нестандартная с объявленной ценностью',
            'number' => '4020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'unstandart_package_with_sumoc_and_cash',
            'label' => 'Посылка нестандартная с объявленной ценностью и наложенным платежом',
            'number' => '4040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'package_with_sumoc_and_necessarily_payment',
            'label' => 'Посылка с объявленной ценностью и обязательным платежом',
            'number' => '4060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'first_class_package',
            'label' => 'Посылка 1 класса',
            'number' => '47030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'first_class_package_with_sumoc',
            'label' => 'Посылка 1 класса с объявленной ценностью',
            'number' => '47020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'first_class_package_with_sumoc_and_cash',
            'label' => 'Посылка 1 класса с объявленной ценностью и наложенным платежом',
            'number' => '47040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'first_class_package_with_sumoc_and_necessarily_payment',
            'label' => 'Посылка 1 класса с объявленной ценностью и обязательным платежом',
            'number' => '47060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        // посылки для организаций
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package',
            'label' => 'Посылка онлайн обыкновенная',
            'number' => '23030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_with_sumoc',
            'label' => 'Посылка онлайн с объявленной ценностью',
            'number' => '23020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_with_sumoc_and_cash',
            'label' => 'Посылка онлайн с объявленной ценностью и наложенным платежом',
            'number' => '23040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_with_sumoc_and_necessarily_payment',
            'label' => 'Посылка онлайн с объявленной ценностью и обязательным платежом',
            'number' => '23060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_combine',
            'label' => 'Посылка онлайн комбинированная',
            'number' => '23080',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_package_combine_with_sumoc',
            'label' => 'Посылка онлайн комбинированная с объявленной ценностью',
            'number' => '23090',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_courier',
            'label' => 'Курьер онлайн обыкновенный',
            'number' => '24030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_courier_with_sumoc',
            'label' => 'Курьер онлайн с объявленной ценностью',
            'number' => '24020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_courier_with_sumoc_and_cash',
            'label' => 'Курьер онлайн с объявленной ценностью и наложенным платежом',
            'number' => '24040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'online_courier_with_sumoc_and_necessarily_payment',
            'label' => 'Курьер онлайн с объявленной ценностью и обязательным платежом',
            'number' => '24060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'busines_courier',
            'label' => 'Бизнес курьер',
            'number' => '30030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'busines_courier_with_sumoc',
            'label' => 'Бизнес курьер с объявленной ценностью',
            'number' => '30020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'BusinesCourierExpress',
            'label' => 'Бизнес курьер экспресс',
            'number' => '31030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'BusinesCourierExpressWithSumoc',
            'label' => 'Бизнес курьер экспресс с объявленной ценностью',
            'number' => '31020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'easy_return_package',
            'label' => 'Посылка “Легкий возврат” обыкновенная',
            'number' => '51030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'easy_return_package_with_sumoc',
            'label' => 'Посылка “Легкий возврат” с объявленной ценностью',
            'number' => '51020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ekom_marketplace_with_sumoc',
            'label' => 'ЕКОМ Маркетплейс с объявленной ценностью',
            'number' => '54020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ekom_marketplace_with_sumoc_and_necessarily_payment',
            'label' => 'ЕКОМ Маркетплейс с объявленной ценностью и обязательным платежом',
            'number' => '54060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        // ems отправления
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems',
            'label' => 'EMS',
            'number' => '7030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_with_sumoc',
            'label' => 'EMS с объявленной ценностью',
            'number' => '7020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_with_sumoc_and_cash',
            'label' => 'EMS с объявленной ценностью и наложенным платежом',
            'number' => '7040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_with_sumoc_and_cash',
            'label' => 'EMS с объявленной ценностью и обязательным платежом',
            'number' => '7060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_optium',
            'label' => 'EMS оптимальное',
            'number' => '34030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_optium_with_sumoc',
            'label' => 'EMS оптимальное с объявленной ценностью',
            'number' => '34020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_optium_with_sumoc_and_cash',
            'label' => 'EMS оптимальное с объявленной ценностью и наложенным платежом',
            'number' => '34040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_optium_with_sumoc_and_necessarily_payment',
            'label' => 'EMS оптимальное с объявленной ценностью и обязательным платежом',
            'number' => '34060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_pt',
            'label' => 'EMS PT',
            'number' => '41030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_pt_with_sumoc',
            'label' => 'EMS PT с объявленной ценностью',
            'number' => '41020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_tender',
            'label' => 'EMS Тендер',
            'number' => '52030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_tender_with_sumoc',
            'label' => 'EMS PT с объявленной ценностью',
            'number' => '52020',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_tender_with_sumoc_and_cash',
            'label' => 'EMS Тендер с объявленной ценностью и наложенным платежом',
            'number' => '52040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_tender_with_sumoc_and_necessarily_payment',
            'label' => 'EMS Тендер с объявленной ценностью и обязательным платежом',
            'number' => '52060',
            'sumoc' => true,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'return_accompanying_documents',
            'label' => 'Возврат сопроводительных документов',
            'number' => '10030',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        // международная исходящая письменная корреспонденция
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_outgoing',
            'label' => 'Письмо простое международное исходящее',
            'number' => '2001',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_ordered_outgoing',
            'label' => 'Письмо заказное международное исходящее',
            'number' => '2011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'letter_with_sumoc_outgoing',
            'label' => 'Письмо с объявленной ценностью международное исходящее',
            'number' => '2021',
            'sumoc' => true,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'postcard_outgoing',
            'label' => 'Почтовая карточка простая международная исходящая',
            'number' => '6001',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'postcard_ordered_outgoing',
            'label' => 'Почтовая карточка заказная международная исходящая',
            'number' => '6011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'secogram_ordered_outgoing',
            'label' => 'Секограмма заказная международная исходящая',
            'number' => '8011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_outgoing',
            'label' => 'Бандероль простая международная исходящая',
            'number' => '3001',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'parcel_ordered_outgoing',
            'label' => 'Бандероль заказная международная исходящая',
            'number' => '3011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'bag_m_outgoing',
            'label' => 'Мешок М простой исходящий',
            'number' => '9001',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'bag_m_ordered_outgoing',
            'label' => 'Мешок М заказной исходящий',
            'number' => '9011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        // международные исходящие отправления с товарным вложением
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'package_outgoing',
            'label' => 'Посылка обыкновенная международная исходящая',
            'number' => '4031',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'package_with_sumoc_outgoing',
            'label' => 'Посылка с объявленной ценностью исходящая',
            'number' => '4021',
            'sumoc' => true,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'package_with_sumoc_and_cash_outgoing',
            'label' => 'Посылка с объявленной ценностью и наложенным платежом исходящая',
            'number' => '4041',
            'sumoc' => true,
            'sumnp' => true,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_outgoing',
            'label' => 'EMS обыкновенное исходящее',
            'number' => '7031',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_with_sumoc_outgoing',
            'label' => 'EMS с объявленной ценностью международное исходящее',
            'number' => '7021',
            'sumoc' => true,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_with_cash_outgoing',
            'label' => 'EMS с наложенным платежом международное исходящее',
            'number' => '7041',
            'sumoc' => false,
            'sumnp' => true,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'litle_package_outgoing',
            'label' => 'Мелкий пакет простой исходящий',
            'number' => '5001',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'litle_package_ordered_outgoing',
            'label' => 'Мелкий пакет заказной исходящий',
            'number' => '5011',
            'sumoc' => false,
            'sumnp' => false,
            'international' => true,
        ]);

        // возможно устаревшие, но существуют в документации
        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'kpo_standart',
            'label' => 'КПО-стандарт',
            'number' => '39000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'kpo_econom',
            'label' => 'КПО-эконом',
            'number' => '40000',
            'sumoc' => false,
            'sumnp' => false,
            'international' => false,
        ]);

        DB::table('tariffs')->insert([
            'companies_id' => $pochta->id,
            'name' => 'ems_pt_with_sumoc_and_cash',
            'label' => 'EMS PT с объявленной ценностью и наложенным платежом',
            'number' => '41040',
            'sumoc' => true,
            'sumnp' => true,
            'international' => false,
        ]);
    }
}
