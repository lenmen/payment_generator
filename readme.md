# Payments generator

This project uses the framework `Symfony v4`.

### Sales department payment generator 

This is a console utility will help companies to generate the payment
days when the company needs to pay the salaries to his employees.

The utility will determine the next payment days:
* Normal salaries will be payed on the last day of the month except
if the last day is on a saturday or sunday. Then the payment day will
be on the wednesday before.
* The bonus of employees will be payed on the 15th of the month. If
the 15th is on a saturday or sunday then the bonus will be payed on the
next wednesday.

## Requirements
* Docker 

## Setup

Follow the next steps to run the [commands](#Commands)

* Copy the file `./.env.test` to `./.env`
* Build the docker images. `docker-compose build`
* Install the dependencies. `docker-compose run --rm composer install  --ignore-platform-reqs`


## Commands
Execute the command with the next line:

`docker-compose run --rm console generate:sales_payment_days [filename]`.

*This will store the file in the folder `./payments/[filename]`*

## Unit tests

This project contains some unit tests. You can run the unit tests with the 
next command.

`docker-compose run --rm phpunit`


## Libraries

* (Nesbot/Carbon ^2.10)
**Used for doing the calculations on the dates** 
* (roromix/spreadsheetbundle ^1.0)
**Used to save the dates into a csv file**