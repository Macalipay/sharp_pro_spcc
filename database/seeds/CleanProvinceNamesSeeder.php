<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanProvinceNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provinces')->update([
            'name' => DB::raw("REPLACE(name, '(Not a Province)', '')")
        ]);
    }
}
