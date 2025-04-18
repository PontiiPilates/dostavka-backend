<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateRequest;
use App\UseCases\TK\BaikalsrCase;
use App\UseCases\TK\PochtaCase;

class CalculateController extends Controller
{
    public function __construct(
        private PochtaCase $pochta,
        private BaikalsrCase $baikal,
    ) {}

    public function handle(CalculateRequest $request)
    {
        dd($request->all());

        $pochta = $this->pochta($request);
        $baikal = $this->baikal($request);

        return response()->json(['some' => 'data']);
    }

    private function pochta($request)
    {
        $this->pochta->handle($request);
    }

    private function baikal($request)
    {
        $this->pochta->handle($request);
    }
}
