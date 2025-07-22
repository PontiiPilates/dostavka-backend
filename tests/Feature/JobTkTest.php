<?php

namespace Tests\Feature;

use App\Jobs\Tk\BaikalJob;
use App\Jobs\Tk\BoxberryJob;
use App\Jobs\Tk\CdekJob;
use App\Jobs\Tk\DellinJob;
use App\Jobs\Tk\DpdJob;
use App\Jobs\Tk\JdeJob;
use App\Jobs\Tk\KitJob;
use App\Jobs\Tk\NrgJob;
use App\Jobs\Tk\PekJob;
use App\Traits\Hash;
use App\Traits\Json;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertNotEmpty;

/**
 * Тест основан на проверки результата, который появляется после выполнения заданий.
 * А именно, задания записывают результат своей работы в redis.
 * Тест проверяет работу этого сценария.
 */
class JobTkTest extends TestCase
{
    use Json, Hash;

    /**
     * A basic feature test example.
     */
    public function test_allTk(): void
    {
        $hash = $this->arrayToHash($this->request());

        // ! для отладки: удаление прежней записи, для прохождения проверки
        Redis::del($hash);

        // todo: преобразовать структуру в DTO, это уже сложившийся концепт
        $structure = [
            'count' => count($this->request()['companies']),
            'request' => $this->request(),
            'results' => [],
            'begin' => now(),
            'complete' => null,
            'is_complete' => false,
        ];

        Redis::setex($hash, config('custom.expire'), $this->toJson($structure));

        BaikalJob::dispatch($this->request(), $hash);
        BoxberryJob::dispatch($this->request(), $hash);
        CdekJob::dispatch($this->request(), $hash);
        DellinJob::dispatch($this->request(), $hash);
        DpdJob::dispatch($this->request(), $hash);
        JdeJob::dispatch($this->request(), $hash);
        KitJob::dispatch($this->request(), $hash);
        NrgJob::dispatch($this->request(), $hash);
        PekJob::dispatch($this->request(), $hash);

        $data = $this->toArray(Redis::get($hash));

        dump("Хэш результата: $hash");

        assertNotEmpty($data);

        assertArrayHasKey('results', $data);

        assertArrayHasKey('baikal', $data['results']);
        assertArrayHasKey('ss', $data['results']['baikal']);

        assertArrayHasKey('boxberry', $data['results']);
        assertArrayHasKey('ss', $data['results']['boxberry']);

        assertArrayHasKey('cdek', $data['results']);
        assertArrayHasKey('ss', $data['results']['cdek']);

        assertArrayHasKey('dellin', $data['results']);
        assertArrayHasKey('ss', $data['results']['dellin']);

        assertArrayHasKey('dpd', $data['results']);
        assertArrayHasKey('ss', $data['results']['dpd']);

        assertArrayHasKey('jde', $data['results']);
        assertArrayHasKey('ss', $data['results']['jde']);

        assertArrayHasKey('kit', $data['results']);
        assertArrayHasKey('ss', $data['results']['kit']);

        assertArrayHasKey('nrg', $data['results']);
        assertArrayHasKey('ss', $data['results']['nrg']);

        assertArrayHasKey('pek', $data['results']);
        assertArrayHasKey('ss', $data['results']['pek']);
    }

    private function request(): array
    {
        return [
            "from" => "Красноярск, Красноярский край",
            "to" => "Москва, Москва",
            "places" => [
                0 => [
                    "weight" => "10",
                    "length" => "100",
                    "width" => "20",
                    "height" => "10",
                    "volume" => "0.2",
                ],
                1 => [
                    "weight" => "20",
                    "length" => "60",
                    "width" => "30",
                    "height" => "15",
                    "volume" => "0.027",
                ],
            ],
            "companies" => [
                0 => "baikal",
                1 => "boxberry",
                2 => "cdek",
                3 => "dellin",
                4 => "dpd",
                5 => "jde",
                6 => "pek",
            ],
            "delivery_type" => [
                0 => "ss",
                1 => "sd",
                2 => "ds",
                3 => "dd",
            ],
            "shipment_date" => "2025-09-06",
        ];
    }
}
