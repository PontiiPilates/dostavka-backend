Начало работы
=============

[Главная страница](/README.md) > Get started

## Содержание
* **[Запуск проекта](#запуск-проекта)**
* **[Тестирование](#тестирование)**

## Запуск проекта

### Клонировать репозиторий

```shell
git clone https://github.com/PontiiPilates/dostavka-backend.git
```

### Войти в директорию проекта и инициализировать его

```shell
composer install
```

### Добавить актуальные настройки

> актуальные настройки по запросу: [@pontiipilates](https://t.me/pontiipilates)


### Поднять проект в докере

```shell
docker-compose up -d --build
```

### Выполнить init-скрипт

```shell
docker-compose exec app ./init.sh
```

> раздаёт права

> предоставляет начальные данные

> пересобирает базу

## Тестирование

### Доступность

Приложение доступно на 88 порту: http://localhost:88/

### Проверка калькуляции (без ассинхронности)

```shell
docker-compose exec app php artisan test --filter JobTkTest
```

### Проверка работы ассинхронности

[Посетить данный маршрут](http://localhost:88/test-assynch)

В результате посещения маршрута будет выполнено 3 задания. Успешность выполнения будет определена по записям в логах `storage/logs/laravel.log`

```
[2025-09-19 09:31:20] local.INFO: Start 4 seconds job  
[2025-09-19 09:31:20] local.INFO: Start 3 seconds job  
[2025-09-19 09:31:23] local.INFO: Start 5 seconds job  
[2025-09-19 09:31:23] local.INFO: End 3 seconds job  
[2025-09-19 09:31:24] local.INFO: End 4 seconds job  
[2025-09-19 09:31:28] local.INFO: End 5 seconds job  
```

Данная запись означает, что выполнение заданий было начато одновременно и параллельно (последовательные записи Start), а их завершение произошло независимо друг от друга (последовательные записи End).

***
[▲ Наверх](#get-started)