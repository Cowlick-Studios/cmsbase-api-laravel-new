# CMS Base API (Laravel)

This is the API for CMS Base

## Base application configuration

``` sh
composer install
cp .env.example .env
php artisan key:generate --show
```

## Run laravel sail (docker)

``` sh
./vendor/bin/sail up -d

# First startup
./vendor/bin/sail artisan migrate:fresh --seed

# Stop sail (add -v flag to remove all volumes)
./vendor/bin/sail down
```

## Connections

You can access the running application on port 80 of your local system, this can be done by simply going to [http://localhost/](http://localhost/)

The connection details for the docker environment is taken from the project .env file. Meaning whatever connections you set in your .env file will automatically be used in your docker container. You can access the following services with the below default config. 

**NOTE:** By default sail uses the default ports of the service, so if you are running another instance of the service it will be unable to start until the port is free. You can change the port mapping to another port, or stop the other instance to free the port.


| Service      | Local Port | Username | Password | NOTES             |
| ------------ | ---------- | -------- | -------- | ----------------- |
| App          | 80         |          |          |                   |
| pgsql        | 5432       | sail     | password | DB_NAME = cmsbase |
| redis        | 6379       |          |          |                   |
| mailpit-SMTP | 1025       |          |          |                   |
| mailpit-UI   | 8025       |          |          |                   |