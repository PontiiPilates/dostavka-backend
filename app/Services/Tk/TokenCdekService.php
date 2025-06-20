<?php

namespace App\Services\Tk;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Tk\TokenCdek;
use Illuminate\Support\Facades\Http;

/**
 * Наличие данного сервиса обусловлено системой авторизации в API СДЕК.
 * Она предполагает выдачу токена для клиента.
 * Длительность токена - 1 час.
 */
class TokenCdekService
{
    public string $url;

    private string $account;
    private string $secure;

    public string $access_token;
    public int $expires_in;

    public function __construct()
    {
        $this->url = config('companies.cdek.url');
        $this->account = config('companies.cdek.account');
        $this->secure = config('companies.cdek.secure');
    }

    /**
     * Возвращает новый токен, полученный от API СДЕК.
     * 
     * @return $this
     */
    public function getNewToken()
    {
        $response = Http::post($this->url . CdekUrlType::Auth->value . '?' . "grant_type=client_credentials&client_id=$this->account&client_secret=$this->secure");
        $response = $response->object();

        $this->access_token = $response->access_token;
        $this->expires_in = $response->expires_in;

        return $this;
    }

    /**
     * Возвращает имеющийся в приложении токен. А если он истёк, то обновляет его перед возвращением.
     * 
     * @return TokenCdek
     */
    public function getActualToken(): string
    {
        $tokenCdek = TokenCdek::first();

        // на случай, если в базе всё же не оказалось токена
        if (!$tokenCdek) {
            $tokenCdek = $this->createToken();
        }

        $expirationTime = $tokenCdek->updated_at->addSeconds($tokenCdek->expires);

        if ($expirationTime <= now()) {
            $this->updateToken($tokenCdek);
            $tokenCdek = TokenCdek::first();
        }

        return $tokenCdek->token;
    }

    private function createToken(): TokenCdek
    {
        $this->getNewToken();

        return TokenCdek::create([
            'token' => $this->access_token,
            'expires' => $this->expires_in
        ]);
    }

    private function updateToken(TokenCdek $tokenCdek): bool
    {
        $this->getNewToken();

        return $tokenCdek->update([
            'token' => $this->access_token,
            'expires' => $this->expires_in
        ]);
    }
}
