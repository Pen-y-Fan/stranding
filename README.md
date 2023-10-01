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
CREATE DATABASE electricity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

## Install Database

This project uses models and seeders to generate the tables for the database. Tests will use the seeded data.

```shell
php artisan migrate --seed
# or if previously migrated: 
php artisan migrate:fresh --seed 
```

The seeder will populate:

- Users (2): admin@example.com & user@example.com
- Districts (3): Western, Central and Eastern
- Locations (40): All the delivery locations in East and Central. From Capital Knot City to Wind Farm
    - also locations for deliveries In progress and Other (e.g. a Pill box or private room)
- DeliveryCategories (4): Delivery Time, Delivery Volume, Cargo Condition and Miscellaneous
- Orders (540) - all the standard orders, which count towards game completion

To generate 20 test deliveries run:

```shell
php artisan db:seed DeliverySeeder
```

This can be run multiple times, as required.

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

.......................................                           39 / 39 (100%)

Time: 00:01.599, Memory: 54.00 MB

OK (39 tests, 214 assertions)
```

## TODO

### Database:

#### district ✅

- name (West, Central, East) ✅

#### location ✅

- name (Capital Knot City etc.) ✅
- district_id ✅
- is_physical ✅

Note: Used for physical locations, clients and destinations, also used for delivery and drop off points, which can be
pill boxes, use isPhysical() for the physical locations.

#### delivery_category ✅

- name (Delivery Time, Delivery Volume, Cargo Condition, Miscellaneous) ✅

#### order ✅

- number
- name
- client_id (locations)
- destination_id (locations)
- delivery_category_id
- max_likes
- weight

#### order_user ✅

- status OrderStatus Enum (unavailable, available, standby, in progress, complete) - this is the status of an order.
- order_id }
- user_id } unique

#### delivery ✅

- order_id
- user_id
- start_date (Date time)
- end_date (Date time)
- status (in progress, failed, complete, stashed) - this is the status of a delivery, not exactly the same as an order.
- location_id current location of the delivery, default is 'In progress' + Other
    - access via is_physical false on location
- comment, if the order is stashed at a pill box leave the location or comment on a failure.

### Admin panel

#### Delivery

```shell
php artisan make:filament-resource Delivery --generate
```

Allow a delivery to be viewed and edited.

Starting a delivery will be from the order page, with 'begin delivery' action. Possibly 'Complete delivery' will be on
the Order view too.

This view will allow deliveries to be filtered, by default 'In progress' and 'Stashed' deliveries will be shown.

#### Order

```shell
php artisan make:filament-resource Order --generate --view
```

Allow an order to be viewed and edited. Orders can be filtered District (East and Central), Client, Destination. ✅

TODO: Start new delivery action 🚧

- check if there is already a delivery 'In Progress' or 'Stashed' and give the option to mark it lost or complete or
  cancel
- create a new delivery with the status of 'In Progress'

Add view with relationship on Delivery and status 🚧

#### Delivery Category

```shell
php artisan make:filament-resource DeliveryCategory --generate --view
```

Basic CRUD operations ✅

#### District

```shell
php artisan make:filament-resource District --generate --view
```

Basic CRUD operations. ✅

#### Location

```shell
php artisan make:filament-resource Location --generate --view
```

Basic CRUD operations.

### Logic

- The default status for all orders is unavailable ✅
- A user can list orders by client location and update the available orders ✅
- A user can filter by destination location to attempt to 'batch up' orders ✅
- A user can find an order, update the status to 'In progress', which will trigger the progress start_date ✅
- A user can find an order, update the status to failed, which will update the delivery progress to failed (for stats)
  and location to the order's client location. The order_user status will be changed to standby, as the user will not be
  able to re-take the order immediately ✅
- A user can find an order, update the status to complete, which will mark the progress to complete (for stats) and
  update the status to complete ✅
- A user can filter large orders which require Delivery Bot or Floating carrier ( > 200 kg < 600 kg)
- A user can filter orders for trucks ( > 600 kg)
- A user can stash a delivery in a private locker at a location or in a pill box, these can be 'other' with a comment on
  the location.
- A user can multi filter by status (e.g. unavailable & available) to allow delivery planning ✅
- A user can view update a delivery to failed and give a reason for failure (e.g. must be raining)
- A user can view past failed deliveries with the comment(s) why each delivery failed to help remind the reason and help
  to succeed next time
- A user can filter deliveries by district (Eastern / Central) so they may view deliveries in progress including stashed
  deliveries ✅
- A user can filter orders by district (Eastern / Central) so they may view orders ✅
- Orders: Number, description, max likes and weight, then other stuff.... ✅

## TODO

- Correct Region
    - East > Western ✅
    - West > Eastern ✅
- Delivery list:
    - order number add url to Order show page
    - Add Actions
- Order list:
    - Update wording of action, bulk action and confirmation ✅
        - take on orders - take on standard delivery orders ✅
        - make delivery - deliver requested cargo ✅
    - make delivery badges url to delivery show (if possible) or url to filter delivery list by order.
    - add visibility column as a dropdown
    - add kg to weight and ≈ to Likes
    - Add stash action, with location
    - Add fail Action with option to add a comment for the reason
    - Add region filter (Eastern and Central)
    - Investigate delivery filters for Order - change from tick to select or radio buttons
    - Maybe add 'undelivered' filters = delivery date null.
- Order show:
    - Add actions
    - show delivery history
- Add stats / graph to Dashboard - deliveries per client with total (try stacked bar) ✅ 
- Add extra toggle column options (action?) 
    - minimal (order, description, weight, likes),
    - deliveries (order, description, weight, likes, client, destination)
    - all
- Move Actions to the relent Resource folder (App > Actions >... to App > Resources > OrderResource > Actions > ... ) 
- Add source and licence information 
- Add Notification on actions and listeners to Widgets for changes in orders and deliveries.
