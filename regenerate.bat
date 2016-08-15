@echo off
php artisan ide-helper:generate
php artisan ide-helper:meta
echo no | php artisan ide-helper:model
composer dump-autoload
