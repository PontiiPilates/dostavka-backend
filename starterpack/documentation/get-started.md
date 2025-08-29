Get started
===========

[Главная страница](/README.md) > Get started

## Содержание
* **[Клонирование репозитория](#клонирование-репозитория)**
* **[Запуск проекта в Docker](#запуск-проекта-в-docker)**
* **[Инициализация проекта](#инициализация-проекта)**
* **[Выдача прав доступа](#выдача-прав-доступа)**
* **[Импорт начальных данных](#импорт-начальных-данных)**
* **[Тестирование](#тестирование)**
* **[Возможные ошибки](#возможные-ошибки)**

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

## Выдача прав доступа

Войти в php-контейнер

```shell
docker-compose exec php sh
```

Разрешить www-data запись в файлы

```shell
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## Импорт начальных данных

Снабжает проект гео-данными. Это данные самого верхнего уровня, собранные вручную, имеют наивысший уровень доверия.

```
├── Страны
│   ├── Регионы / Города Федерального Значения
```

**Распаковать `starterpack\assets\assets.zip` в `storage\app`**

Эти данные также необходимы для выполнения сидеров.

Наполнение базы данными

```shell
docker-compose run --remove-orphans artisan migrate --seed
```

После успешного выполнения, данные вырастут до следующей структуры:

```
├── Страны
│   ├── Регионы / Города Федерального Значения
│   |   ├── Районы
│   |   |   ├── Локации
│   |   |   |   ├── Терминалы Baikal
│   |   |   |   ├── Терминалы Cdek
│   |   |   |   ├── Терминалы Dellin
│   |   |   |   ├── Терминалы Dpd
│   |   |   |   ├── Терминалы Jde
│   |   |   |   ├── Терминалы Kit
│   |   |   |   ├── Терминалы Nrg
│   |   |   |   ├── Терминалы Pek
│   |   |   |   ├── Терминалы Pochta
│   |   |   |   ├── Терминалы Vozovoz
```

## Тестирование

Проверяет результат калькуляции всех транспортных компаний

```shell
docker-compose run --remove-orphans artisan test --filter JobTkTest
```

## Возможные ошибки

При появлении ошибок типа `already exists` необходимо полностью очистить базу и снова выполнить ее сборку:

```shell
docker-compose run --remove-orphans artisan db:wipe
docker-compose run --remove-orphans artisan migrate --seed
```

***
[▲ Наверх](#get-started)