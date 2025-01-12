# BaksDev UsersTable

[![Version](https://img.shields.io/badge/version-7.1.13-blue)](https://github.com/baks-dev/users-table/releases)
![php 8.3+](https://img.shields.io/badge/php-min%208.3-red.svg)

Модуль табельного учета пользователя

## Установка

``` bash
$ composer require baks-dev/users-table
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
$ php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
$ php bin/phpunit --group=users-table
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

[README.md](README.md)
The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
