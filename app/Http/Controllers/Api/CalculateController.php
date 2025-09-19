<?php

namespace App\Http\Controllers\Api;

use App\Enums\CompanyType;
use App\Enums\EnvironmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateRequest;
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
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CalculateController extends Controller
{
    use Json, Hash;

    private array $allResponses = [];
    private string $hash;

    public function __construct() {}

    public function handle(CalculateRequest $request)
    {
        Log::channel('requests')->info("Пользовательский ввод: ", $request->all());

        $hash = $this->arrayToHash($request->all());

        // если hash запроса обнаружен в качестве ключа в redis
        // то происходит выдача результата по этому ключу
        // в локальной среде проверка работоспособности требуется чаще чем получение результата
        // поэтому происходит предварительная очистка redis от данного результата
        if (config('app.env') == EnvironmentType::Local->value) {
            Redis::del($hash);
        }

        // todo: преобразовать структуру в DTO, это уже сложившийся концепт
        $structure = [
            'count' => count($request->companies),
            'request' => $request->all(),
            'results' => [],
            'begin' => now(),
            'complete' => null,
            'is_complete' => false,
        ];

        // если результат уже существует, то выполнение завершается с выдачей этого результата
        try {
            $this->checkHashExists($hash);
        } catch (\Throwable $th) {
            return response()->json($this->responseStructure($hash));
        }

        Redis::setex($hash, config('custom.expire'), $this->toJson($structure));

        foreach ($request->companies as $company) {
            match ($company) {
                CompanyType::Baikal->value => BaikalJob::dispatch($request->all(), $hash),
                CompanyType::Boxberry->value => BoxberryJob::dispatch($request->all(), $hash),
                CompanyType::Cdek->value => CdekJob::dispatch($request->all(), $hash),
                CompanyType::Dellin->value => DellinJob::dispatch($request->all(), $hash),
                CompanyType::DPD->value => DpdJob::dispatch($request->all(), $hash),
                CompanyType::Jde->value => JdeJob::dispatch($request->all(), $hash),
                CompanyType::Kit->value => KitJob::dispatch($request->all(), $hash),
                CompanyType::Nrg->value => NrgJob::dispatch($request->all(), $hash),
                CompanyType::Pek->value => PekJob::dispatch($request->all(), $hash),
                CompanyType::Pochta->value => PochtaJob::dispatch($request->all(), $hash),
                CompanyType::Vozovoz->value => VozovozJob::dispatch($request->all(), $hash),
            };
        }

        return response()->json($this->responseStructure($hash));
    }

    /**
     * Проверяет наличие результата калькуляции по данному запросу в Redis.
     */
    private function checkHashExists($hash)
    {
        if (Redis::exists($hash)) {
            throw new Exception("Результат колькуляции по данному запросу уже существует", 302);
        }
    }

    private function responseStructure($hash): array
    {
        return [
            'success' => true,
            'message' => "",
            'data' => [
                'transaction' => $hash
            ]
        ];
    }
}
