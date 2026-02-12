<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\EmployeeInformation;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(EmployeeInformation::class, function (Faker $faker) {
    $genders = $faker->randomElement(['male', 'female']);
    $citizenship = $faker->randomElement(['Filipino']);
    $street = $faker->randomElement(['99']);
    $barangay = $faker->randomElement(['Sauyo', 'Sta. Lucia']);
    $city = $faker->randomElement(['Quezon City']);
    $province = $faker->randomElement(['Metro Manila']);
    $country = $faker->randomElement(['Philippines']);
    $zip = $faker->randomElement(['1116']);
    $status = $faker->randomElement(['1']);
    $birthdate = $faker->randomElement(['1996-01-01']);

    return [
        'employee_no' => $faker->randomNumber($nbDigits = NULL, $strict = false),
        'firstname' => $faker->firstNameMale,
        'middlename' => $faker->lastName,
        'lastname' => $faker->lastName,
        'birthdate' => $birthdate,
        'gender' => $genders,
        'citizenship' => $citizenship,
        'phone1' => $faker->phoneNumber,
        'street_1' => $street,
        'barangay_1' => $barangay,
        'city_1' => $city,
        'province_1' => $province,
        'country_1' => $country,
        'zip_1' => $zip,
        'email' => $faker->unique()->email,
        'status' => $status,
        'workstation_id' => $status,
        'created_by' => $status,
        'updated_by' => $status,
    ];
});
