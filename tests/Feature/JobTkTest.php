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
use App\Jobs\Tk\PochtaJob;
use App\Jobs\Tk\VozovozJob;
use App\Traits\Hash;
use App\Traits\Json;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
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

        // ! debug: удаление прежней записи, для прохождения проверки
        Redis::del($hash);

        $structure = [
            'count' => count($this->request()['companies']),
            'request' => $this->request(),
            'results' => [],
            'begin' => now(),
            'complete' => null,
            'is_complete' => false,
        ];

        Redis::setex($hash, config('custom.expire'), $this->toJson($structure));

        BaikalJob::dispatch($this->request(), $hash); // 2.1, 2.1, 2.3
        // BoxberryJob::dispatch($this->request(), $hash); // ! bocked
        // CdekJob::dispatch($this->request(), $hash);
        DellinJob::dispatch($this->request(), $hash); // 4.0, 4.8, 5.7
        DpdJob::dispatch($this->request(), $hash);
        // JdeJob::dispatch($this->request(), $hash);
        // KitJob::dispatch($this->request(), $hash);
        // NrgJob::dispatch($this->request(), $hash);
        PekJob::dispatch($this->request(), $hash); // 1.8, 1.6, 2.7
        // PochtaJob::dispatch($this->request(), $hash);
        VozovozJob::dispatch($this->request(), $hash); // 3.8, 0.8, 1.8

        $data = $this->toArray(Redis::get($hash));

        dump("Хэш результата: $hash");

        assertNotEmpty($data);

        assertArrayHasKey('results', $data);

        assertArrayHasKey('baikal', $data['results']);
        assertArrayHasKey('ss', $data['results']['baikal']['success']);

        // ! blocked
        // assertArrayHasKey('boxberry', $data['results']);
        // assertArrayHasKey('ss', $data['results']['boxberry']['success']);

        // assertArrayHasKey('cdek', $data['results']);
        // assertArrayHasKey('ss', $data['results']['cdek']['success']);

        assertArrayHasKey('dellin', $data['results']);
        assertArrayHasKey('ss', $data['results']['dellin']['success']);

        assertArrayHasKey('dpd', $data['results']);
        assertArrayHasKey('ss', $data['results']['dpd']['success']);

        // assertArrayHasKey('jde', $data['results']);
        // assertArrayHasKey('ss', $data['results']['jde']['success']);

        // assertArrayHasKey('kit', $data['results']);
        // assertArrayHasKey('ss', $data['results']['kit']['success']);

        // assertArrayHasKey('nrg', $data['results']);
        // assertArrayHasKey('ss', $data['results']['nrg']['success']);

        assertArrayHasKey('pek', $data['results']);
        assertArrayHasKey('ss', $data['results']['pek']['success']);

        // assertArrayHasKey('pochta', $data['results']);
        // assertArrayHasKey('ss', $data['results']['pochta']['success']);

        assertArrayHasKey('vozovoz', $data['results']);
        assertArrayHasKey('ss', $data['results']['vozovoz']['success']);
    }

    private function request(): array
    {
        return [
            "from" => 212,
            "to" => 169,
            "places" => [
                0 => [
                    "weight" => "10",
                    "length" => "100",
                    "width" => "20",
                    "height" => "10",
                ],
                1 => [
                    "weight" => "20",
                    "length" => "60",
                    "width" => "30",
                    "height" => "15",
                ],
            ],
            "companies" => [
                // "baikal",
                // "boxberry", // ! blocked
                // "cdek",
                "dellin",
                // "dpd",
                // "jde",
                "pek",
                // "vozovoz",
            ],
            "delivery_type" => [
                0 => "ss",
                1 => "sd",
                2 => "ds",
                3 => "dd",
            ],
            "shipment_date" => Carbon::now()->addDays(2)->isoFormat('YYYY-MM-DD'),
        ];
    }
}
