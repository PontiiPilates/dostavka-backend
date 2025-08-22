Get started
===========

[Главная страница](/README.md) > Get started

## Содержание
* **[Клонирование репозитория](#клонирование-репозитория)**
* **[Запуск проекта в Docker](#запуск-проекта-в-docker)**
* **[Инициализация проекта](#инициализация-проекта)**
* **[Импорт начальных данных](#импорт-начальных-данных)**
* **[Миграции и посев данных](#миграции-и-посев-данных)**
* **[Проверка работоспособности](#проверка-работоспособности)**

## Клонирование репозитория

```shell
git clone https://github.com/PontiiPilates/dostavka-backend.git
```

## Запуск проекта в Docker

Перед запуском контейнеров следует сконфигурировать .env-файлы:

- `docker\mysql\.env`
- `.env`

> актуальные настройки по запросу: [@pontiipilates](https://t.me/pontiipilates)

```shell
docker-compose up -d
```

## Инициализация проекта

Инициализация вендорной части проекта

```shell
docker-compose run --remove-orphans composer install
```

## Импорт начальных данных

Снабжает проект гео-данными. Это данные самого верхнего уровня, собранные вручную, имеют наивысший уровень доверия.

```
├── Страны
│   ├── Регионы / Города Федерального Значения
```

**Распаковать `starterpack\assets\assets.zip` в `storage\app`**

Эти данные также необходимы для выполнения сидеров.

## Миграции и посев данных

При первом запуске достаточно выполнить:

```shell
docker compose run --remove-orphans artisan migrate --seed
```

Если выполнение происходит повторно, то следует выполнить:

```shell
docker compose run --remove-orphans artisan migrate:refresh --seed
```

При появлении ошибок типа `already exists` необходимо полностью очистить базу и снова выполнить ее сборку:

```shell
docker compose run --remove-orphans artisan db:wipe
docker compose run --remove-orphans artisan migrate --seed
```

После успешного выполнения, данные вырастут до следующей структуры:

```
├── Страны
│   ├── Регионы / Города Федерального Значения
│   |   ├── Районы
│   |   |   ├── Локации
│   |   |   |   ├── Терминалы Vozovoz
│   |   |   |   ├── Терминалы Pek
```

> **В настоящий момент доступна работа с данными только Vozovoz и Pek. Остальные данные подготавливаются.**

## Проверка работоспособности

После выполненных пунктов можно [выполнить](http://localhost/api/v1/calculate?from=212&to=256&places[0][weight]=20&places[0][length]=100&places[0][width]=50&places[0][height]=20&places[1][weight]=10&places[1][length]=75&places[1][width]=15&places[1][height]=10&companies[]=pek&companies[]=vozovoz&delivery_type[]=ss&delivery_type[]=dd&insurance=3200&shipment_date=2025-10-10) запрос на расчёт стоимости и сроков доставки.

А также получить результат [выполненной](http://localhost/api/v1/calculate-result?get=e26fa965bcc89068c5dac7cf233dd2fc) калькуляции.

***
[▲ Наверх](#get-started)