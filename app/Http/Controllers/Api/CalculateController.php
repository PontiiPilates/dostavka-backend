<?php

namespace App\Http\Controllers\Api;

use App\Enums\CompanyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateRequest;
use App\UseCases\TK\BaikalsrCase;
use App\UseCases\TK\PochtaCase;

class CalculateController extends Controller
{
    private array $allResponses = [];

    public function __construct(
        private PochtaCase $pochta,
        private BaikalsrCase $baikal,
    ) {}

    public function handle(CalculateRequest $request)
    {
        foreach ($request->companies as $company) {
            match ($company) {
                CompanyType::Pochta->value => $this->pochta($request),
                CompanyType::Baikal->value => $this->baikal($request),
            };
        }

        return response()->json(['data' => $this->allResponses]);
    }

    private function pochta($request)
    {
        $this->allResponses[] = [
            CompanyType::Pochta->value => $this->pochta->handle($request)
        ];
    }

    private function baikal($request)
    {
        $this->allResponses[] = [
            CompanyType::Baikal->value => $this->baikal->handle($request)
        ];
    }
}
