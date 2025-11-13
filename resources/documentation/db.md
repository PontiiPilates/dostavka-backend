База данных
===========

[Главная страница](/README.md) > API

## Содержание

* **[Подготовка первичных данных](#подготовка-первичных-данных)**
* **[Обновление исходных файлов некоторых транспортных компаний](#обновление-исходных-файлов-некоторых-транспортных-компаний)**
* **[Наполнение базы первичными данными](#наполнение-базы-первичными-данными)**
* **[Наполнение базы данными транспортных компаний](#наполнение-базы-данными-транспортных-компаний)**
* **[Сведение данных транспортных компаний в единую таблицу](#сведение-данных-транспортных-компаний-в-единую-таблицу)**

## Подготовка первичных данных

Распаковать файл `resources/assets.zip` в `storage/app/assets/`.

## Обновление исходных файлов некоторых транспортных компаний

```shell
docker-compose exec app php artisan app:create-data-files-cdek
```
```shell
docker-compose exec app php artisan app:create-data-files-vozovoz
```

```shell
docker-compose exec app php artisan app:create-data-files-dellin
```

```shell
docker-compose exec app php artisan app:create-data-files-dpd
```

## Наполнение базы первичными данными

```shell
docker-compose exec app php artisan migrate:refresh --seed
```

## Наполнение базы данными транспортных компаний

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalVozovozSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalDpdSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalKitSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalDellinSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalNrgSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalJdeSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalCdekSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalBaikalSeeder
```

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\Tk\\TerminalPekSeeder
```

## Сведение данных транспортных компаний в единую таблицу

```shell
docker-compose exec app php artisan db:seed --class=Database\\Seeders\\FinalizerLocationSeeder
```

***
[▲ Наверх](#api)