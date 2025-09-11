<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(Request $request)
    {
        $user = $request->user();

        // если пользователь уже авторизован
        if ($user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь уже авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        }

        $validator = Validator::make($request->all(), [
            'surname' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'middle_name' => ['string', 'max:100'],
            'phone' => ['regex:/^(\+?\d{1,3})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{1,4}[\s\-]?\d{1,4}[\s\-]?\d{1,9}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '',
                'errors' => $validator->errors(),
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $request->surname . ' ' . $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'surname' => $request->surname,
                'name' => $request->name,
                'middle_name' => $request->middle_name,
                'phone' => $request->phone,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('errors')->error('Ошибка при регистрации нового пользователя:', [$th]);
            $message = 'Регистрация не удалась, попробуйте позже или свяжитесь с администратором.';
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => [],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $token = $user->createToken('auth_token', ['auth'])->plainTextToken;

        event(new Registered($user));

        $message = 'Спасибо за регистрацию! Мы отправили ссылку на указанную почту. Пожалуйста, перейдите по ней для завершения регистрации.';
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'token' => $token
            ],
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Авторизация пользователя.
     */
    public function login(Request $request)
    {
        $user = $request->user();

        // если пользователь уже авторизован
        if ($user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь уже авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', Rules\Password::defaults()],
        ]);

        // если есть ошибки валидации
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '',
                'errors' => $validator->errors(),
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::where('email', $request->email)->first();

        // если пользователь не обнаружен либо пароли не совпадают
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с такими данными не обнаружен.',
                'data' => []
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        // проверки успешно пройдены, создание токена
        $token = $user->createToken('auth_token', ['auth'])->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно авторизован.',
            'data' => [
                'token' => $token
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Данные авторизованного пользователя.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // если пользователь не авторизован
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'message' => '',
            'errors' => [],
            'data' => [
                $user->currentAccessToken(),
            ],
        ]);
    }

    /**
     * Отправка ссылки на Email пользователя для подтверждения.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verificationNotice(Request $request): JsonResponse
    {
        $user = $request->user();

        // если пользователь не авторизован
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // если почта уже подтверждена
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Eail уже подтверждён. Можно приступать к работе.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Ссылка для подтверждения почты отправлена на указанный email.',
            'errors' => [],
            'data' => [],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Подтверждение Email, на который была отправлена ссылка.
     * 
     * @param EmailVerificationRequest $request
     * @return JsonResponse
     */
    public function verificationVerify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Ваша почта уже подтверждена. Можете приступать к работе.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        }

        $request->user()->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Ваша почта успешно подтверждена.',
            'errors' => [],
            'data' => [],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Генерация ссылки на сброс пароля.
     */
    public function passwordForgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => true,
                'message' => '',
                'errors' => $validator->errors(),
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status != Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => __($status),
                'errors' => $validator->errors(),
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
            'errors' => $validator->errors(),
            'data' => [],
        ]);
    }

    /**
     * Изменение пароля.
     */
    public function passwordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => '',
                'errors' => $validator->errors(),
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $status = Password::reset(
            $request->only('token', 'email', 'password', 'password_confirmation'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(64),
                ])->save();
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => __($status),
                'errors' => $validator->errors(),
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'message' => __($status),
            'errors' => $validator->errors(),
            'data' => [],
        ]);
    }

    /**
     * Завершение сеанса.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if ($user) {
            $request->user()->tokens()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Сеанс успешно завершен.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        }
    }

    /**
     * Удаление пользователя.
     */
    public function delete(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user->tokens()->delete();

        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'Профиль пользователя успешно удалён.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_OK);
        } catch (\Throwable $th) {
            $message = 'Не удалось удалить профиль пользователя.';
            Log::channel('errors')->error('Ошибка при удалении профиля пользователя:', [$th]);
            return response()->json([
                'success' => true,
                'message' => $message,
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR)->withHeaders([]);
        }
    }

    /**
     * Выпуск API-токена.
     */
    public function apiTokenCreate(Request $request)
    {
        $user = $request->user();

        // если пользователь не авторизован
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // если пользователь не верифицирован
        if (!$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не верифицирован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $apiTokens = $user->tokens()->where('name', 'api_token');

        // если у пользователя есть выпущенные токены
        if ($apiTokens) {
            $apiTokens->delete();
        }

        $token = $user->createToken('api_token', ['calculate'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'API-токен успешно создан. Это единственный раз, когда вы можете его увидеть. Сохраните токен для дальнейшего использования.',
            'errors' => [],
            'data' => [
                'token' => $token
            ],
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Отзыв токена.
     */
    public function apiTokenRemove(Request $request)
    {
        $user = $request->user();

        // если пользователь не авторизован
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // если пользователь не верифицирован
        if (!$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не верифицирован.',
                'errors' => [],
                'data' => [],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->tokens()->where('name', 'api_token')->delete();

        return response()->json([
            'success' => true,
            'message' => 'API-токен успешно удалён.',
            'errors' => [],
            'data' => [],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Тестирование SMTP-сервера.
     */
    public function testEmailSend()
    {
        try {
            Mail::raw('Test email', function ($message) {
                $message->to('s.m.leshukov@yandex.ru')
                    ->subject('Test Mail');
            });
            return 'Email sent successfully';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
