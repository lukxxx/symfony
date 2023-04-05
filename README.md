# Simple Symfony Blog

This is a simple blog page made in PHP framework symfony for the job application in Pixel Federation.

## Getting Started

These instructions will give you a copy of the project up and running on
your local machine for development and testing purposes.

### Prerequisites

Requirements for the software and other tools to build this application
- [XAMPP](https://www.apachefriends.org/download.html)
- [Symfony-CLI](https://symfony.com/download)
- [Composer](https://getcomposer.org/download)

### Installing

A step by step series of examples that tell you how to get a development
environment running

First install all Composer dependencies

    composer install

Next we need to install all npm dependencies

    npm install

After that run this command to compile frontend with Webpack

    npm run dev

Then open XAMPP and run MySQL server on your local machine.
After that get ready MySQL database with these commands:

    symfony console doctrine:database:create

    //When prompted type "yes" for successful migration
    symfony console doctrine:migrations:migrate

If finished start an application by running this command

    symfony server:start

Then application should be available on 

    localhost:8000
