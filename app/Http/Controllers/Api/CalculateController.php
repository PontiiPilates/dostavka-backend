<?php

namespace App\Http\Controllers\Api;

use App\Enums\CompanyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateRequest;
use App\UseCases\TK\BaikalsrCase;
use App\UseCases\TK\BoxberryCase;
use App\UseCases\TK\DPDCase;
use App\UseCases\TK\PochtaCase;

class CalculateController extends Controller
{
    private array $allResponses = [];

    public function __construct(
        private PochtaCase $pochta,
        private BaikalsrCase $baikal,
        private DPDCase $dpd,
        private BoxberryCase $boxberry,
    ) {}

    public function handle(CalculateRequest $request)
    {
        foreach ($request->companies as $company) {
            match ($company) {
                CompanyType::Pochta->value => $this->pochta($request),
                CompanyType::Baikal->value => $this->baikal($request),
                CompanyType::DPD->value => $this->dpd($request),
                CompanyType::Boxberry->value => $this->boxberry($request),
            };
        }

        return response()->json([
            'success' => true,
            'message' => "",
            'data' => $this->allResponses
        ]);
    }

    private function pochta(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Pochta->value => $this->pochta->handle($request)
        ];
    }

    private function baikal(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Baikal->value => $this->baikal->handle($request)
        ];
    }

    private function dpd(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::DPD->value => $this->dpd->handle($request)
        ];
    }

    private function boxberry(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Boxberry->value => $this->boxberry->handle($request)
        ];
    }
}
