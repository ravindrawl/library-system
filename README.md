# Library System
This project is a Laravel and Vue 3 application. 
## Installation 
Follow these steps to set up and run the project. 
### Prerequisites - PHP ^8.2 - Composer - vue.js & NPM 
### Backend 
LARAVEL v11.30.0  plugin v1.0.5
## Setup and Run The Project
### Setup 1. Clone the repository: ```
        - sh git clone https://github.com/ravindrawl/library-system/
        - cd library-system
### Setup 2. install dependancies
        -  composer install
### Setup 3. Copy .env.example to .env:
        - cp .env.example .env
### Step 4. Generate an application key
        - php artisan key:generate
### Step 5. Set up your database in the .env file:
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=your_database
        DB_USERNAME=your_username
        DB_PASSWORD=your_password
### Run the migrations:
        - php artisan migrate
### Seed the database:
        - php artisan db:seed
### Start the development server:
       - php artisan serve
## Frontend (Vue 3) Setup
### Install Node.js dependencies:
       - npm install
### Compile the assets:
       - npm run dev
### If using Vite (for Vue 3 with Laravel):
       - npm run dev
## Running the Application
       - Open your browser and navigate to http://localhost:8000 to see the application running.
##
### Reguister New user URL
    http://127.0.0.1:8000/register
##
##
Author : Ravindra wimalasiri 
Date   : 2024/11/12 
##

