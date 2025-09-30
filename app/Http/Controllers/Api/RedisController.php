<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RedisController extends Controller
{
    /**
     * Возвращает конкретный результат по ключу
     */
    public function handle(Request $request)
    {
        $get = json_decode(Redis::get($request->get), true);

        return [
            'success' => true,
            'message' => "",
            'errors' => [],
            'data' => $get,
        ];
    }

    /**
     * Возвращает все записи
     */
    public function viewAll()
    {
        $all = Redis::keys('*');

        return response()->json($all);
    }

    /**
     * Очищает от всех записей
     */
    public function clear()
    {
        $all = Redis::keys('*');

        foreach ($all as $key) {
            Redis::del($key);
            return response()->json('Deleted', 200);
        }
    }
}
