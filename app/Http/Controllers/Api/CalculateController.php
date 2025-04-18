<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\UseCases\TK\BaikalsrCase;
use App\UseCases\TK\PochtaCase;
use Illuminate\Http\Request;

class CalculateController extends Controller
{
    public function __construct(
        private PochtaCase $pochta,
        private BaikalsrCase $baikal,
    ) {}

    public function handle(Request $request)
    {
        $pochta = $this->pochta($request);
        $baikal = $this->baikal($request);

        return response()->json(['some' => 'data']);
    }

    private function pochta(Request $request)
    {
        $this->pochta->handle($request);
    }

    private function baikal(Request $request)
    {
        $this->pochta->handle($request);
    }
}
