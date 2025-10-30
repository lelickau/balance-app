#### Установка
```terminaloutput
composer install
```
#### Создать .env
```terminaloutput
cp .env.example .env
```
#### Создать ключ
```terminaloutput
php artisan key:generate
```
#### Поднять все сервисы
```terminaloutput
docker-compose up -d --build
```
#### Запустить PHP-сервер (должен быть на 8080)
```terminaloutput
php artisan serve
```

##### Запустить миграции
```terminaloutput
docker compose exec app php artisan migrate
// или 
docker exec -it имя_контейнера php artisan migrate
```

#### Запуск тестов
```terminaloutput
 docker compose exec app php artisan test
```

##### Сидеры (заполнить DB)
- создаст двух пользователей и начальные балансы
- если не нужны начальные балансы - убрать в DatabaseSeeder.php вызов BalanceSeeder
```terminaloutput
docker compose exec app php artisan db:seed
```

#### env db
```terminaloutput
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=balance_db
DB_USERNAME=laravel
DB_PASSWORD=secret
```

##### env.docker
```terminaloutput
APP_NAME=BalanceApp
APP_ENV=local
APP_KEY= НУЖНО_ДОБАВИТЬ_СГЕНЕРИРОВАННЫЙ_КЛЮЧ
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=balance_db
DB_USERNAME=laravel
DB_PASSWORD=secret
```

##### Подключение к db (если требуется)
```terminaloutput
docker exec -it balance-db psql -U laravel -d balance_db
```
