# BaksDev UsersTable

![Version](https://img.shields.io/badge/version-6.3.0-blue) ![php 8.1+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль табельного учета пользователя

## Установка

``` bash
$ composer require baks-dev/users-table
```

## Дополнительно

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

Установка файловых ресурсов в публичную директорию (javascript, css, image ...):

``` bash
$ php bin/console baks:assets:install
```

Роли администратора с помощью Fixtures

``` bash
$ php bin/console doctrine:fixtures:load --append
```

Тесты

``` bash
$ php bin/phpunit --group=users-table
```

## Журнал изменений ![Changelog](https://img.shields.io/badge/changelog-yellow)

О том, что изменилось за последнее время, обратитесь к [CHANGELOG](CHANGELOG.md) за дополнительной информацией.

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.


