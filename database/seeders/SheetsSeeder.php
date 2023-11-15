<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SheetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sheets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('sheets')->insert([
            [
                'sheet_id'      => '1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8',
                'base_tab_id'   => 0,
                'course_tab_id' => 366956743,
                'link'          => 'https://docs.google.com/spreadsheets/d/1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8/edit#gid=366956743',
                'type'          => 'test'
            ],
            [
                'sheet_id'      => '1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo',
                'base_tab_id'   => 0,
                'course_tab_id' => 2038070467,
                'link'          => 'https://docs.google.com/spreadsheets/d/1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo/edit#gid=2038070467',
                'type'          => 'test'
            ],
            [
                'sheet_id'      => '10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec',
                'base_tab_id'   => 0,
                'course_tab_id' => 1091629913,
                'link'          => 'https://docs.google.com/spreadsheets/d/10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec/edit#gid=1091629913',
                'type'          => 'test'
            ],
            [
                'sheet_id'      => '1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA',
                'base_tab_id'   => 0,
                'course_tab_id' => 1543574426,
                'link'          => 'https://docs.google.com/spreadsheets/d/1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA/edit#gid=1543574426',
                'type'          => 'test'
            ],







            // Prod
            [
                'sheet_id'      => '14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs',
                'base_tab_id'   => 0,
                'course_tab_id' => 810305363,
                'link'          => 'https://docs.google.com/spreadsheets/d/14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs/edit#gid=810305363',
                'type'          => 'prod'
            ],
            [
                'sheet_id'      => '1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo',
                'base_tab_id'   => 0,
                'course_tab_id' => 378979069,
                'link'          => 'https://docs.google.com/spreadsheets/d/1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo/edit#gid=378979069',
                'type'          => 'prod'
            ],
            [
                'sheet_id'      => '17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA',
                'base_tab_id'   => 0,
                'course_tab_id' => 404045194,
                'link'          => 'https://docs.google.com/spreadsheets/d/17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA/edit#gid=404045194',
                'type'          => 'prod'
            ],
            [
                'sheet_id'      => '1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI',
                'base_tab_id'   => 0,
                'course_tab_id' => 283027112,
                'link'          => 'https://docs.google.com/spreadsheets/d/1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI/edit#gid=283027112',
                'type'          => 'prod'
            ],
            [
                'sheet_id'      => '15IgSGsDjfrJMLaVRwkpxkusiyNHc0nSaFRpuRJ1ywWk',
                'base_tab_id'   => 0,
                'course_tab_id' => 378979069,
                'link'          => 'https://docs.google.com/spreadsheets/d/15IgSGsDjfrJMLaVRwkpxkusiyNHc0nSaFRpuRJ1ywWk/edit#gid=378979069',
                'type'          => 'prod'
            ],


        ]);

    }
}
