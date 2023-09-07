# Death Stranding tracker

Progress tracker for Death Stranding

## Packages

The following packages and plugins have been used:

- [Filament PHP](https://filamentphp.com/docs) v3 - The perfect starting point for your next app.

### Dev Tooling

- [PHPUnit](https://docs.phpunit.de/en/10.3/) v10 - PHPUnit provides a framework for writing tests as well as a
  command-line tool for running these tests. PHPUnit is installed by default with Laravel.

## Requirements

This is a Laravel 10 project. The requirements are the same as a
new [Laravel 10 project](https://laravel.com/docs/10.x).

- [PHP 8.1+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org)
- [Node](https://nodejs.org/en/download)

Recommended:

- [Git](https://git-scm.com/downloads)

## Clone

Clone the project repository.

e.g.

```sh
git clone git@github.com:Pen-y-Fan/stranding.git
```

## Install

Install all the dependencies using composer.

```sh
cd stranding
composer install
```

## Create .env

Create an `.env` file from `.env.example`

```shell script
cp .env.example .env
```

## Configure Laravel

This project uses models and seeders to generate the tables for the database. Tests will use the seeded data. Configure
the Laravel **.env** file with the **database**, updating **username** and**password** as per you local setup.

```text
APP_NAME="Death Stranding tracker"

APP_URL=https://stranding.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stranding
DB_USERNAME=YourDatabaseUserName (root)
DB_PASSWORD=YourDatabaseUserPassword
```

## Generate APP_KEY

Generate an APP_KEY using the artisan command

```shell script
php artisan key:generate
```

## Create the database

The database will need to be manually created e.g.

```shell
mysql -u YourDatabaseUserName (root)
CREATE DATABASE stranding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

## Install Database

This project uses models and seeders to generate the tables for the database. Tests will use the seeded data.

```shell
php artisan migrate --seed
# or if previously migrated: 
php artisan migrate:fresh --seed 
```

## Vite

The first time you pull the project run install:

```shell
npm install
```

Compile your CSS / JavaScript for development and recompile on change:

```shell
npm run dev
```

When ready to deploy, compile your CSS / JavaScript for production:

```shell
npm run build
```

## Run tests

Tests have been configured to use **sqlite** in memory database, enable the PHP **pdo_sqlite** extension or adjust
**phpunit.xml** if MySQL is preferred. To make it easy to run all the PHPUnit tests a composer script has been created
in **composer.json**. From the root of the projects, run:

```shell script
composer tests
```

You should see the results in testDoc format:

```text
PHPUnit 10.3.3 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.1.16
Configuration: F:\laragon\www\stranding\phpunit.xml

..                                                                  2 / 2 (100%)

Time: 00:00.114, Memory: 24.00 MB

Example (Tests\Feature\Example)
 ✔ The application returns a successful response

Example (Tests\Unit\Example)
 ✔ That true is true

OK (2 tests, 2 assertions)
```

## TODO

Database:

district ✅

- name (West, Central, East) ✅

location ✅

- name (Capital Knot City etc.) ✅
- district_id ✅
- is_physical ✅

delivery_category ✅

- name (Delivery Time, Delivery Volume, Cargo Condition, Miscellaneous) ✅

order

- number
- name
- client_id (locations)
- destination_id (locations)
- delivery_category_id
- max_likes
- weight
- status (unavailable, available, standby, in progress, complete)

delivery

- order_id
- start_date (Date time)
- end_date (Date time)
- status (in progress, failed, complete, stashed)
- location_id current location of the delivery, default is 'In progress' + Other
    - access via is_physical false on location 
- comment, if the order is stashed at a pill box leave the location or comment on a failure.

## Logic

- The default status for all orders is unavailable
- A user can list orders by client location and update the available orders
- A user can filter by destination location to attempt to 'batch up' orders
- A user can find an order, update the status to in progress, which will trigger the progress start_date
- A user can find an order, update the status to failed, which will mark the progress to failed (for stats) and update
  the status to standby
- A user can find an order, update the status to complete, which will mark the progress to complete (for stats) and
  update the status to complete
- A user can filter large orders which require Delivery Bot or Floating carrier ( > 200 kg < 600 kg)
- A user can filter orders for trucks ( > 600 kg)
- A user can stash a delivery in a private locker at a location or in a pill box, these can be 'other' with a comment on
  the location. Null is on person / truck etc
- A user can multi filter by status (e.g. unavailable & available) to allow delivery planning 
- A user can view update a delivery to failed and give a reason for failure (e.g. must be raining)
- A user can view past failed deliveries with the comment(s) why each delivery failed 
- A user can filter deliveries by district (West / Central) so they may view deliveries in progress including stashed deliveries
- A user can filter orders by district (West / Central) so they may view orders
