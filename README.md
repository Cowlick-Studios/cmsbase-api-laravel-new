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

## Managing Tenants

Below are the commands created to manage working with tenants, we also have a setup script which will manage migrating and seeding for the entire system including tenants in the next section.

``` sh
# Create tenant (automatically migrate)
./vendor/bin/sail artisan tenant:create tenant admin@tenant.com password

# Seed tenants (NOTE: you must migrate and seed tenants separately, do not use combination command)
./vendor/bin/sail artisan tenants:seed

# Delete tenant (removes tenant records, schema and files)
./vendor/bin/sail artisan tenant:delete tenant

# Delete ALL tenants (removes tenant records, schemas and files)
./vendor/bin/sail artisan tenant:delete --all

# List tenant
./vendor/bin/sail artisan tenant:list
```

## Total system refresh

This is the process you can take to completely reset the system and add data for testing.

``` bash
# Remove all existing tenants
./vendor/bin/sail artisan tenant:delete --all

# Refresh and seed main application
./vendor/bin/sail artisan migrate:fresh --seed

# Create test tenant (You can create as many tenants you wish at this point)
./vendor/bin/sail artisan tenant:create tenant admin@tenant.com password

# Seed all created tenants
./vendor/bin/sail artisan tenants:seed
```

## Easy setup

This single command will scaffold the system by migrating, creating tenants as well as seeding the main system and all tenants.

``` sh
./vendor/bin/sail artisan app:dev_setup
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