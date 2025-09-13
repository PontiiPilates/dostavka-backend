API
===

[Главная страница](/README.md) > API

## Содержание

* **[Заголовки](#заголовки)**
* **[Регистрация](#регистрация)**
* **[Авторизация](#авторизация)**
* **[Данные пользователя](#данные-пользователя)**
* **[Запрос ссылки на подтверждение](#запрос-ссылки-на-подтверждение)**
* **[Подтверждение почты](#подтверждение-почты)**
* **[Запрос ссылки на сброс пароля](#запрос-ссылки-на-сброс-пароля)**
* **[Изменение пароля](#изменение-пароля)**
* **[Logout](#logout)**
* **[Delete](#delete)**

## Заголовки

#### Описание процесса

Токен выдается сразу после регистрации или авторизации. Это единственные случаи, когда демонстрация токена происходит в открытой форме. Права ограничены до тех пор, пока пользователь не подтвердит свою электронную почту. Без подтверждения обладатель токена не может создавать и удалять токены для интеграции с API калькулятора. Токен следует передавать в каждом запросе.

```javascript
"Authorization": "Bearer 1|PDUGd5hJTCil6QVFIQVEyzNILIedYJmv6fQxb8pId5e8d205"
```

## Регистрация

#### Описание процесса

Маршрут принимает регистрационные данные пользователя и возвращает аутентификационный токен.

GET: [http://localhost/api/v1/register](http://localhost/api/v1/register?surname=Лукашенко&name=Александр&middle_name=Григорьевич&phone=9336663388&email=a.g.lukashenko@gov.by&password=cool_bulba&password_confirmation=cool_bulba)

#### Пример запроса

```javascript
{
    "surname": "Лукашенко"
    "name": "Александр"
    "middle_name": "Григорьевич"
    "phone": "9336663388"
    "email": "a.g.lukashenko@gov.by"
    "password": "cool_bulba"
    "password_confirmation": "cool_bulba"
}
```

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ссылка для подтверждения регистрации отправлена на почту пользователя.",
    "errors": []
    "data": {
        "token": "1|PDUGd5hJTCil6QVFIQVEyzNILIedYJmv6fQxb8pId5e8d205"
    }
}
```

## Авторизация

#### Описание процесса

Маршрут принимает авторизационные данные пользователя и возвращает аутентификационный токен.


POST: ["http://localhost/api/v1/login](http://localhost/api/v1/login?email=a.g.lukashenko@gov.by&password=cool_bulba)

#### Пример запроса

```javascript
{
    "email": "a.g.lukashenko@gov.by"
    "password": "cool_bulba"
}
```

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Пользователь успешно авторизован.",
    "data": {
        "token": "2|n2aVwOMfLbDQlKELsPdPAYr57vcDHJvQ32YsgZLYc55b0daf"
    }
}
```


## Данные пользователя

#### Описание процесса

Возвращает данные авторизованного пользователя. 

GET: [http://localhost/api/v1/me](http://localhost/api/v1/me)

#### Пример ответа

```javascript
{
    "success": true,
    "message": "",
    "errors": [],
    "data": [
        {
            "id": 10,
            "tokenable_type": "App\\Models\\User",
            "tokenable_id": 7,
            "name": "auth_token",
            "abilities": [
                "auth"
            ],
            "last_used_at": "2025-09-13T04:49:48.000000Z",
            "expires_at": null,
            "created_at": "2025-09-12T11:57:53.000000Z",
            "updated_at": "2025-09-13T04:49:48.000000Z",
            "tokenable": {
                "id": 7,
                "name": "Лукашенко Александр",
                "email": "a.g.lukashenko@gov.by",
                "email_verified_at": "2025-09-12T11:58:34.000000Z",
                "created_at": "2025-09-12T11:57:53.000000Z",
                "updated_at": "2025-09-12T11:58:34.000000Z",
                "tokens": [
                    {
                        "id": 10,
                        "tokenable_type": "App\\Models\\User",
                        "tokenable_id": 7,
                        "name": "auth_token",
                        "abilities": [
                            "auth"
                        ],
                        "last_used_at": "2025-09-13T04:49:48.000000Z",
                        "expires_at": null,
                        "created_at": "2025-09-12T11:57:53.000000Z",
                        "updated_at": "2025-09-13T04:49:48.000000Z"
                    },
                    {
                        "id": 12,
                        "tokenable_type": "App\\Models\\User",
                        "tokenable_id": 7,
                        "name": "api_token",
                        "abilities": [
                            "calculate"
                        ],
                        "last_used_at": null,
                        "expires_at": null,
                        "created_at": "2025-09-13T04:44:23.000000Z",
                        "updated_at": "2025-09-13T04:44:23.000000Z"
                    }
                ]
            }
        }
    ]
}
```

## Запрос ссылки на подтверждение

#### Описание процесса

В результате посещения маршрута авторизованным пользователем происходит отправка ссылки на его email для подтвержения.

GET: [http://localhost/api/v1/verification-email](http://localhost/api/v1/verification-email)

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ссылка для подтверждения почты отправлена на указанный email.",
    "errors": [],
    "data": []
}
```

## Подтверждение почты

Маршрут принимает параметры ссылки, которая была отправлена на email авторизованного пользователя.

#### Описание процесса

GET: [http://localhost/api/v1/verification-email](http://localhost/api/v1/verification-email/5/cf6ce79065a13dd0d0a263c3ac3d2cac8bfb8373?expires=1757679578&signature=caf6ae3e243084330a2636a4f8faadc41f2f3b8d5e655ffe45ef92f9c9bdd101)

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ваша почта успешно подтверждена.",
    "errors": [],
    "data": []
}
```

## Запрос ссылки на сброс пароля

POST: [http://localhost/api/v1/password-forgot](http://localhost/api/v1/password-forgot)

#### Описание процесса

Маршрут принимает email-адрес и высылает на него ссылку для сброса пароля.

#### Пример запроса

```javascript
{
    "email": "a.g.lukashenko@gov.by",
}
```

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ссылка для сброса пароля отправлена на указанный email.",
    "errors": [],
    "data": []
}
```

## Изменение пароля

#### Описание процесса

Мапшрут принимает параметры ссылки, которая была отправлена на указанный email, а также новый пароль.

POST: [http://localhost/api/v1/password-reset](http://localhost/api/v1/password-reset?token=9854bd53ac8fb8d4383d9937d4c164b4fb28087805f59bf50d68426c3681b941&email=a.g.lukashenko@gov.by)

#### Пример запроса

```javascript
{
    "token": "9854bd53ac8fb8d4383d9937d4c164b4fb28087805f59bf50d68426c3681b941",
    "email": "a.g.lukashenko@gov.by",
    "password": "just_bulba",
    "password_confirmation": "just_bulba",
}
```

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ваш пароль успешно изменён.",
    "errors": [],
    "data": []
}
```

## Logout

POST: [http://localhost/api/v1/logout](http://localhost/api/v1/logout)

#### Описание процесса

В результате посещения маршрута происходит разрушение токена авторизованного пользователя.

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Сеанс успешно завершен.",
    "errors": [],
    "data": []
}
```

## Delete

POST: [http://localhost/api/v1/delete](http://localhost/api/v1/delete)

#### Описание процесса

В результате посещения маршрута происходит физическое удаление данных авторизованного пользователя.

#### Пример ответа

```javascript
{
    "success": true,
    "message": "Ваш профиль успешно удалён.",
    "errors": [],
    "data": []
}
```

***
[▲ Наверх](#api)