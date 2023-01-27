<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CertificateName;
use App\Models\CertificateRating;
use App\Models\CertificateSource;
use App\Models\Employee;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyCertificatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->certificatesSources(5);
        $this->certificatesNames(5);
        $this->assignCertificates();
    }
    public function certificatesSources($count=1)
    {
        CertificateSource::factory($count)->create();
    }
    public function certificatesNames($count=1)
    {
        CertificateName::factory($count)->create();
    }
    public function assignCertificates()
    {
        $faker=Factory::create();
        $employees_ids=Employee::query()->pluck('id');
        $certificates_names_ids=CertificateName::query()->pluck('id');
        $certificate_sources_ids=CertificateSource::query()->pluck('id');
        $certificateRating_ratings_ids=CertificateRating::query()->pluck('id');
        foreach($employees_ids as $employee_id)
        {
            Certificate::insert([
                'employee_id'=>$employee_id,
                'certificate_name_id'=>$faker->randomElement($certificates_names_ids),
                'certificate_source_id'=>$faker->randomElement($certificate_sources_ids),
                'certificate_rating_id'=>$faker->randomElement($certificateRating_ratings_ids),
                'certificate_date'=>$faker->dateTimeBetween('1970-01-01','2022-01-01')
            ]);
        }
    }
}
