<?php

use Illuminate\Database\Seeder;

class HolidaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $holidays = [
            ['name' => 'New Year\'s Day', 'date' => '2024-01-01'],
            ['name' => 'Martin Luther King Jr. Day', 'date' => '2024-01-15'],
            ['name' => 'Presidents\' Day', 'date' => '2024-02-19'],
            ['name' => 'Memorial Day', 'date' => '2024-05-27'],
            ['name' => 'Independence Day', 'date' => '2024-07-04'],
            ['name' => 'Labor Day', 'date' => '2024-09-02'],
            ['name' => 'Veterans Day', 'date' => '2024-11-11'],
            ['name' => 'Thanksgiving Day', 'date' => '2024-11-28'],
            ['name' => 'Christmas Day', 'date' => '2024-12-25'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert([
                'name' => $holiday['name'],
                'date' => $holiday['date'],
                'holiday_type_id' => 1,
                'workstation_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}
