php artisan migrate:fresh
php artisan db:seed --class=BulitInSeeder
php artisan db:seed --class=DummyClinics
php artisan db:seed --class=EmployeeSeeder
php artisan db:seed --class=WorkingHourSeeder
php artisan db:seed --class=PatientSeeder
php artisan db:seed --class=DummyCertificatesSeeder
php artisan db:seed --class=BlogSeeder
php artisan db:seed --class=UsersSeeder
php artisan db:seed --class=WaitingSedder
php artisan passport:install
php artisan serve
dont forget remove comment to date validation in session seeder

dont run those!!!!!
php artisan schedule:work
php artisan queue:work
