# Тестовое задание

## Как запустить. Вариант 1:

1) Склонировать в нужную папку:
```
git clone https://github.com/yaroslavzinchenko/FpDbTest.git
```

2) Запустить (должен быть установлен php версии 8.3)
```
php test.php
```
Если тесты прошли успешно, выведется 
```
OK
```
Также должна быть локально поднята БД, так как к ней идёт подключение 
в файле `test.php`


## Как запустить. Вариант 2:

1) Склонировать в нужную папку:
```
git clone https://github.com/yaroslavzinchenko/FpDbTest.git
```

2) Сбилдить
```
docker compose build app
```

3) Запустить
```
docker compose up -d
```

4) Зайти в контейнер
```
docker compose exec app bash
```
5) Запустить скрипт
```
php test.php
```

В случае использования докер окружения и запуска из контейнера,
соединение с БД устанавливать следующим образом:
```php
$mysqli = @new mysqli('db', 'root', 'password', 'database', 3306);
```

Если запускаем снаружи контейнера, то 
```php
$mysqli = @new mysqli('127.0.0.1', 'root', 'password', 'database', 3306);
```