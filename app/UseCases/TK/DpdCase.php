<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Enums\DPD\DpdUrlType;
use Illuminate\Http\Request;
use SoapClient;

class DpdCase extends BaseCase
{
    private string $uri;
    private string $clientNumber;
    private string $clientKey;

    public function __construct()
    {
        $this->uri = config('companies.dpd.uri');
        $this->clientNumber = config('companies.dpd.client_number');
        $this->clientKey = config('companies.dpd.client_key');
    }

    public function handle(Request $request)
    {
        // todo: подготовка данных, возможно лучше обернуть в метод prepare, а переменные сделать свойствами
        $selectedFrom = $this->findcity($request->from);
        $selectedTo = $this->findCity($request->to);
        $selectedRegimes = $request->regimes;
        $selectedPlaces = $request->places;
        $selectedSumoc = $request->sumoc;
        $selectedSumnp = $request->sumnp;
        $selectedShipmentDate = $request->shipment_date;
        $selectedInternationals = $this->isInternational($request->to);

        // режим является главной конструкцией, которая разделяет логику
        foreach ($selectedRegimes as $regime) {

            $regimeSwitchers = $this->regimeSwitchers($regime);

            // todo: переписать в метод build Dto и собирать в нем DTO
            $dto['request'] = [
                'declaredValue' => $selectedSumoc, // объявленная ценность (итоговая)
                'parcel' => $selectedPlaces,
                'pickup' => [
                    'cityId' => $selectedFrom->cityId, // откуда
                    'cityName' => $selectedFrom->cityName, // откуда
                ],
                'delivery' => [
                    'cityId' => $selectedTo->cityId, // куда
                    'cityName' => $selectedTo->cityName, // куда
                ],
                'pickupDate' => $selectedShipmentDate, // дата сдачи груза
                'selfPickup' => $regimeSwitchers['selfPickup'],
                'selfDelivery' => $regimeSwitchers['selfDelivery'],
                'auth' => [
                    'clientNumber' => $this->clientNumber,
                    'clientKey' => $this->clientKey,
                ]
            ];
        }

        dd($dto);

        $this->calculate($dto);
    }

    /**
     * Возвращает переключатели в соответствии с режимом доставки.
     * 
     * selfPickup: true - отправитель довозит до терминала / false - курьер забирает у отправителя
     * selfDelivery: true - получатель забирает сам / false - курьер доставляет получателю
     */
    private function regimeSwitchers($selectedRegime): array
    {
        switch ($selectedRegime) {
            case 'ss': // (склад-склад)
                return [
                    'selfPickup' => true,
                    'selfDelivery' => true,
                ];
            case 'sd': // (склад-дверь)
                return [
                    'selfPickup' => true,
                    'selfDelivery' => false,
                ];
            case 'ds': // (дверь-склад)
                return [
                    'selfPickup' => false,
                    'selfDelivery' => true,
                ];
            default: // dd (дверь-дверь)
                return [
                    'selfPickup' => false,
                    'selfDelivery' => false,
                ];
        }
    }

    // {
    //   +"cityId": 48951627
    //   +"countryCode": "RU"
    //   +"countryName": "Россия"
    //   +"regionCode": 42
    //   +"regionName": "Кемеровская область - Кузбасс"
    //   +"cityCode": "42000009000"
    //   +"cityName": "Кемерово"
    //   +"abbreviation": "г"
    //   +"indexMin": "650000"
    //   +"indexMax": "650992"
    // }

    /**
     * Возвращает спецификацию населённых пунктов.
     */
    private function cityes(): object
    {
        $parameters['request'] = [
            'auth' => [
                'clientNumber' => $this->clientNumber,
                'clientKey' => $this->clientKey,
            ]
        ];

        // todo: обернуть в send-метод и поместить в BaseCase
        $client = new SoapClient($this->uri . DpdUrlType::Geography->value); // установка соединения
        $cityes = $client->getCitiesCashPay($parameters); // отправка запроса

        return $cityes;
    }

    /**
     * Возвращает объект населённого пункта.
     */
    private function findCity($needle): object|null
    {
        $cityes = $this->cityes();

        foreach ($cityes->return as $item) {
            if ($item->cityName == $needle) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Расчёт доставки.
     */
    private function calculate($dto)
    {
        // todo: обернуть в send-метод и поместить в BaseCase
        $client = new SoapClient($uri = $this->uri . DpdUrlType::Calculator->value); // установка подключения SOAP
        $response = $client->getServiceCostByParcels2($dto); // отправка запроса

        dd($response);
    }













    /**
     * Примеры из стрёмной документации 🙈
     */
    public function access()
    {

        $MY_NUMBER     = '1204000542';
        $MY_KEY        = '632B142BD21B8A341D9252CCF40A89B98B0E2990';

        $server = array(
            0 => 'http://ws.dpd.ru/services/', //обычный сервер
            1 => 'http://wstest.dpd.ru/services/' //тестовый сервер
        );
    }


    /**
     * Здесь происходит поиск города, чтобы оперировать этими данными дальше.
     */
    public function findCityy($idcity)
    { //делаем функцию по поиску ключа города в DPD передавая город. (Пример - Калуга)
        include "setings.php";
        $client = new SoapClient("$server[0]geography2?wsdl");

        $arData['auth'] = array(
            'clientNumber' => $MY_NUMBER,
            'clientKey' => $MY_KEY
        );
        $arRequest['request'] = $arData; //помещаем наш масив авторизации в масив запроса request.
        $ret = $client->getCitiesCashPay($arRequest); //обращаемся к функции getCitiesCashPay  и получаем список городов.

        function stdToArray($obj)
        {
            $rc = (array)$obj;
            foreach ($rc as $key => $item) {
                $rc[$key] = (array)$item;
                foreach ($rc[$key] as $keys => $items) {
                    $rc[$key][$keys] = (array)$items;
                }
            }
            return $rc;
        }
        //функция отвечает за преобразования объекта в масив


        $mass = stdToArray($ret); //вызываем эту самую функцию для того чтобы можно было перебрать масив

        foreach ($mass as $key => $key1) {
            foreach ($key1 as $cityid => $city) {
                if (in_array($idcity, $city)) {
                    $id = $city['cityId'];
                    return $id;
                } // если мы находим этот город в масиве (который мы искали) мы заносим его в переменную $ID и возвращаем наш ответ.
            }
        }
    }

    public function getFindCity()
    {
        // Пример запроса
        $city = 'Калуга';
        $findcity = $this->findCityy($city); //так мы запишем номер города из DPD в нашу переменную.
    }



    /**
     * Пример из стрёмной документации 🙈
     */
    public function streamScenarie()
    {
        $city = 'Калуга';
        $findcity = $this->findCityy($city);
        $sposob = 'home';
        // Массив tovars 
        $a[] = array(
            0 => 'тут id',
            1 => 'тут количество этого товара'
        ); // и так можно дублировать до скольких вам нужно. Или же использовать отправление с помощью AJAX
        $tovars = $_POST['tovars']; //принимаем масив товаров
        $spec = $_POST['tovars'];
        for ($g = 0; $g <= count($tovars) - 1; $g++) { //перебираем масив(можно через foreach)
            $all[] = $tovars[$g][0]; //id товара
            $cout[] = $tovars[$g][1]; //количество товаров
        }

        sort($cout); // сортируем количество 
        $tovar = array_unique($all); //удаляем тот товар который повторяеться
        $tovar = implode(",", $tovar); // записываем товары через ‘,’ 

        // $mysql_query = mysql_query("SELECT * FROM items WHERE id IN ($tovar)"); //таблица items имеет структуру id(тот который мы искали),name(название товара),mesto(количество мест),width,height,weight,length,price
        // $mysql_array = mysql_fetch_assoc($mysql_query);
        $mysql_array = [];

        $server = array(
            0 => 'http://ws.dpd.ru/services/', //обычный сервер
            1 => 'http://wstest.dpd.ru/services/' //тестовый сервер
        );


        $client = new SoapClient("$server[0]calculator2?wsdl"); //создаем подключение soap
        $arData = array(
            'delivery' => array(            // город доставки
                'cityId' => $findcity, //id города
                'cityName' => $city, //сам город
            ),
        );
        $arData['auth'] = array(
            'clientNumber' => '1204000542',
            'clientKey' => '632B142BD21B8A341D9252CCF40A89B98B0E2990',
        ); //данные авторизации
        if ($sposob == 'home') { //если отправляем до дома то ставим значение false
            $arData['selfDelivery'] = false; // Доставка ДО дома
        } else { // если же мы хотим отправить до терминала то true
            $arData['selfDelivery'] = true; // Доставка ДО терминала
        }
        $arData['pickup'] = array(
            'cityId' => 195733465,
            'cityName' => 'Калуга',
        ); // где забирают товар

        // что делать с терминалом
        $arData['selfPickup'] = true; // Доставка ОТ терминала // если вы сами довозите до терминала то true если вы отдаёте от двери то false
        $i = 0;
        do { //перебираем массив запроса в БД 
            if ($mysql_array['mesto'] > 1) { //если мест больше чем 1
                $ves = explode(",", $mysql_array["weight"]); //в бд всё храниться в одном столбике но через ‘,’ для этого используем команду explode(где указываем что у нас стоит ‘,');
                $length = explode(",", $mysql_array["length"]);
                $width = explode(",", $mysql_array["width"]);
                $height = explode(",", $mysql_array["height"]);
            } else {
                $ves[] = $mysql_array["weight"];
                $length[] = $mysql_array["length"];
                $width[] = $mysql_array["width"];
                $height[] = $mysql_array["height"]; //если у нас место 1 то мы просто заносим в массив
            }
            for ($s = 0; $s <= $mysql_array['mesto'] - 1; $s++) { //создаем цикл помещаем в масив parcel информацию о товарах
                $arData['parcel'][] = array('weight' => $ves[$s], 'length' => $length[$s], 'width' => $width[$s], 'height' => $height[$s], 'quantity' => $cout[$i]);
            }
            $i++;
            $cena[] = $mysql_array['price']; // указываем цену за товар из БД

        } while ($mysql_array = [] /* mysql_fetch_assoc($mysql_query) */); //повторяем тело цикла

        for ($c = 0; $c <= count($cena); $c++) {
            $a = $a + ($cena[$c] * $cout[$c]);
        } //сумируем цену и умножаем на количество
        $arData['declaredValue'] = $a; //Объявленная ценность (итоговая)
        $arRequest['request'] = $arData; // помещаем в массив запроса 
        $ret = $client->getServiceCostByParcels2($arRequest); //делаем сам запрос

        $echo = stdToArray($ret); // функция из объекта в массив (в 1 пункте она есть).
        $all = array();
        for ($j = 0; $j <= count($echo['return']) - 1; $j++) {
            $all[] = array('serviceName' => $echo['return'][$j]['serviceName'], 'cost' => $echo['return'][$j]['cost'], 'tarif' => $echo['return'][$j]['serviceCode']);
        } //помещаем в массив all – указывает название тарифа, код тарифа, стоимость.

        echo json_encode($all); // выводим для JS в json формате.

    }
}
