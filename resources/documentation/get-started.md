Get started
===========

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

***
[▲ Наверх](#get-started)