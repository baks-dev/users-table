
#### Если вы измените файл конфигурации службы, вам необходимо перезагрузить демон:

``` bash

systemctl daemon-reload

```


####  Название файла в директории /etc/systemd/system

``` text

baks-users-table@.service

```


#### Содержимое файла

``` text
[Unit]
Description=Baks UsersTable Messenger %i

[Service]
ExecStart=php /.......PATH_TO_PROJECT......../bin/console messenger:consume users-table --memory-limit=128m --time-limit=3600 --limit=1000
Restart=always
RestartSec=3

[Install]
WantedBy=default.target

```


#### Команды для выполнения


``` bash

systemctl daemon-reload

systemctl enable baks-users-table@1.service
systemctl start baks-users-table@1.service

systemctl disable baks-users-table@1.service
systemctl stop baks-users-table@1.service

```

#### Запуск из консоли на 1 минуту

``` bash

php bin/console messenger:consume users-table --time-limit=60 -vv

```
