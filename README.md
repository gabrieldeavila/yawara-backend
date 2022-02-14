## Instalar

É preciso baixar o [frontend](https://github.com/gabrieldeavila/yawara-frontend)

```
git clone https://github.com/gabrieldeavila/yawara-backend.git;

cd yawara-backend;

composer install;
composer update;

mudar .env.example para .env

se for utilizar email, adicioná-lo no MAIL_MAILER, se for gmail, lembrar de permitir envio de emails no Google.

php artisan key:generate;

php artisan migrate;
php artisan db:seed;
php artisan storage:link; //e jogar imagens do public/fakeImagens no public/storage
php artisan serve;

```

## Frontend

A porta deve ser a 3000

## Backend

A porta deve ser a 8000

## Administrador

Login: administrador@yawara.com
Senha: senhasupersecreta
