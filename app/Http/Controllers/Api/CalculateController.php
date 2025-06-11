<?php

namespace App\Http\Controllers\Api;

use App\Enums\CompanyType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateRequest;
use App\UseCases\TK\BaikalsrCase;
use App\UseCases\TK\BoxberryCase;
use App\UseCases\TK\CdekCase;
use App\UseCases\TK\DellinCase;
use App\UseCases\TK\DPDCase;
use App\UseCases\TK\JdeCase;
use App\UseCases\TK\KitCase;
use App\UseCases\TK\PekCase;
use App\UseCases\TK\PochtaCase;
use App\UseCases\TK\VozovozCase;

class CalculateController extends Controller
{
    private array $allResponses = [];

    public function __construct(
        private PochtaCase $pochta,
        private BaikalsrCase $baikal,
        private DPDCase $dpd,
        private BoxberryCase $boxberry,
        private VozovozCase $vozovoz,
        private DellinCase $dellin,
        private JdeCase $jde,
        private KitCase $kit,
        private PekCase $pek,
        private CdekCase $cdek,
    ) {}

    public function handle(CalculateRequest $request)
    {
        foreach ($request->companies as $company) {
            match ($company) {
                CompanyType::Pochta->value => $this->pochta($request),
                CompanyType::Baikal->value => $this->baikal($request),
                CompanyType::DPD->value => $this->dpd($request),
                CompanyType::Boxberry->value => $this->boxberry($request),
                CompanyType::Vozovoz->value => $this->vozovoz($request),
                CompanyType::Dellin->value => $this->dellin($request),
                CompanyType::Jde->value => $this->jde($request),
                CompanyType::Kit->value => $this->kit($request),
                CompanyType::Pek->value => $this->pek($request),
                CompanyType::Cdek->value => $this->cdek($request),
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

    private function vozovoz(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Vozovoz->value => $this->vozovoz->handle($request)
        ];
    }

    private function dellin(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Dellin->value => $this->dellin->handle($request)
        ];
    }

    private function jde(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Jde->value => $this->jde->handle($request)
        ];
    }

    private function kit(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Kit->value => $this->kit->handle($request)
        ];
    }

    private function pek(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Pek->value => $this->pek->handle($request)
        ];
    }

    private function cdek(CalculateRequest $request)
    {
        $this->allResponses[] = [
            CompanyType::Cdek->value => $this->cdek->handle($request)
        ];
    }
}
