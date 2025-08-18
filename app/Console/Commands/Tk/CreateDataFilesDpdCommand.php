<?php

namespace App\Console\Commands\Tk;

use App\Enums\DPD\DpdFileType;
use App\Services\Clients\Tk\SoapDpdClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateDataFilesDpdCommand extends Command
{
    private SoapDpdClient $client;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-data-files-dpd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Обновляет файлы с данными о локациях для DPD. Сидер соответствующей тк использует эти файлы для наполнения базы даннах.
     */
    public function handle()
    {
        $this->client = new SoapDpdClient();

        // города с доставкой наложенным платежом
        $response = $this->client->citiesCashPay();
        $result = Storage::put(DpdFileType::CitiesCashPay->value, json_encode($response));
        $this->line("DPD: обновление data-файла cities_cash_pay");

        // пункты выдачи с информацией об ограничениях
        $response = $this->client->parcelShops();
        $result = Storage::put(DpdFileType::ParcelShops->value, json_encode($response));
        $this->line("DPD: обновление data-файла parcel_shops_request");

        // пункты выдачи без ограничений по габаритам
        $response = $this->client->terminalsSelfDelivery2();
        $result = Storage::put(DpdFileType::TerminalsSelfDelivery2->value, json_encode($response));
        $this->line("DPD: обновление data-файла terminals_self_delivery_2");

        $this->info("DPD: обновление data-файлов успешно завершено");
    }
}
