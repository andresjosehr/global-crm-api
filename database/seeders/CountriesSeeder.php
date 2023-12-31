<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('countries')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('countries')->insert([
            ['id' => 1, 'name' => 'Australia', 'timezone' => 'Australia/Sydney'],
            ['id' => 2, 'name' => 'Austria', 'timezone' => 'Europe/Vienna'],
            ['id' => 3, 'name' => 'Azerbaiyán', 'timezone' => 'Asia/Baku'],
            ['id' => 4, 'name' => 'Anguilla', 'timezone' => 'America/Anguilla'],
            ['id' => 5, 'name' => 'Argentina', 'timezone' => 'America/Argentina/Buenos_Aires'],
            ['id' => 6, 'name' => 'Armenia', 'timezone' => 'Asia/Yerevan'],
            ['id' => 7, 'name' => 'Bielorrusia', 'timezone' => 'Europe/Minsk'],
            ['id' => 8, 'name' => 'Belice', 'timezone' => 'America/Belize'],
            ['id' => 9, 'name' => 'Bélgica', 'timezone' => 'Europe/Brussels'],
            ['id' => 10, 'name' => 'Bermudas', 'timezone' => 'Atlantic/Bermuda'],
            ['id' => 11, 'name' => 'Bulgaria', 'timezone' => 'Europe/Sofia'],
            ['id' => 12, 'name' => 'Brasil', 'timezone' => 'America/Sao_Paulo'],
            ['id' => 13, 'name' => 'Reino Unido', 'timezone' => 'Europe/London'],
            ['id' => 14, 'name' => 'Hungría', 'timezone' => 'Europe/Budapest'],
            ['id' => 15, 'name' => 'Vietnam', 'timezone' => 'Asia/Ho_Chi_Minh'],
            ['id' => 16, 'name' => 'Haiti', 'timezone' => 'America/Port-au-Prince'],
            ['id' => 17, 'name' => 'Guadalupe', 'timezone' => 'America/Guadeloupe'],
            ['id' => 18, 'name' => 'Alemania', 'timezone' => 'Europe/Berlin'],
            ['id' => 19, 'name' => 'Países Bajos, Holanda', 'timezone' => 'Europe/Amsterdam'],
            ['id' => 20, 'name' => 'Grecia', 'timezone' => 'Europe/Athens'],
            ['id' => 21, 'name' => 'Georgia', 'timezone' => 'Asia/Tbilisi'],
            ['id' => 22, 'name' => 'Dinamarca', 'timezone' => 'Europe/Copenhagen'],
            ['id' => 23, 'name' => 'Egipto', 'timezone' => 'Africa/Cairo'],
            ['id' => 24, 'name' => 'Israel', 'timezone' => 'Asia/Jerusalem'],
            ['id' => 25, 'name' => 'India', 'timezone' => 'Asia/Kolkata'],
            ['id' => 26, 'name' => 'Irán', 'timezone' => 'Asia/Tehran'],
            ['id' => 27, 'name' => 'Irlanda', 'timezone' => 'Europe/Dublin'],
            ['id' => 28, 'name' => 'España', 'timezone' => 'Europe/Madrid'],
            ['id' => 29, 'name' => 'Italia', 'timezone' => 'Europe/Rome'],
            ['id' => 30, 'name' => 'Kazajstán', 'timezone' => 'Asia/Almaty'],
            ['id' => 31, 'name' => 'Camerún', 'timezone' => 'Africa/Douala'],
            ['id' => 32, 'name' => 'Canadá', 'timezone' => 'America/Toronto'],
            ['id' => 33, 'name' => 'Chipre', 'timezone' => 'Asia/Nicosia'],
            ['id' => 34, 'name' => 'Kirguistán', 'timezone' => 'Asia/Bishkek'],
            ['id' => 35, 'name' => 'China', 'timezone' => 'Asia/Shanghai'],
            ['id' => 36, 'name' => 'Costa Rica', 'timezone' => 'America/Costa_Rica'],
            ['id' => 37, 'name' => 'Kuwait', 'timezone' => 'Asia/Kuwait'],
            ['id' => 38, 'name' => 'Letonia', 'timezone' => 'Europe/Riga'],
            ['id' => 39, 'name' => 'Libia', 'timezone' => 'Africa/Tripoli'],
            ['id' => 40, 'name' => 'Lituania', 'timezone' => 'Europe/Vilnius'],
            ['id' => 41, 'name' => 'Luxemburgo', 'timezone' => 'Europe/Luxembourg'],
            ['id' => 42, 'name' => 'México', 'timezone' => 'America/Mexico_City'],
            ['id' => 43, 'name' => 'Moldavia', 'timezone' => 'Europe/Chisinau'],
            ['id' => 44, 'name' => 'Mónaco', 'timezone' => 'Europe/Monaco'],
            ['id' => 45, 'name' => 'Nueva Zelanda', 'timezone' => 'Pacific/Auckland'],
            ['id' => 46, 'name' => 'Noruega', 'timezone' => 'Europe/Oslo'],
            ['id' => 47, 'name' => 'Polonia', 'timezone' => 'Europe/Warsaw'],
            ['id' => 48, 'name' => 'Portugal', 'timezone' => 'Europe/Lisbon'],
            ['id' => 49, 'name' => 'Reunión', 'timezone' => 'Indian/Reunion'],
            ['id' => 50, 'name' => 'Rusia', 'timezone' => 'Europe/Moscow'],
            ['id' => 51, 'name' => 'El Salvador', 'timezone' => 'America/El_Salvador'],
            ['id' => 52, 'name' => 'Eslovaquia', 'timezone' => 'Europe/Bratislava'],
            ['id' => 53, 'name' => 'Eslovenia', 'timezone' => 'Europe/Ljubljana'],
            ['id' => 54, 'name' => 'Surinam', 'timezone' => 'America/Paramaribo'],
            ['id' => 55, 'name' => 'Estados Unidos', 'timezone' => 'America/New_York'],
            ['id' => 56, 'name' => 'Tadjikistan', 'timezone' => 'Asia/Dushanbe'],
            ['id' => 57, 'name' => 'Turkmenistan', 'timezone' => 'Asia/Ashgabat'],
            ['id' => 58, 'name' => 'Islas Turcas y Caicos', 'timezone' => 'America/Grand_Turk'],
            ['id' => 59, 'name' => 'Turquía', 'timezone' => 'Europe/Istanbul'],
            ['id' => 60, 'name' => 'Uganda', 'timezone' => 'Africa/Kampala'],
            ['id' => 61, 'name' => 'Uzbekistán', 'timezone' => 'Asia/Tashkent'],
            ['id' => 62, 'name' => 'Ucrania', 'timezone' => 'Europe/Kiev'],
            ['id' => 63, 'name' => 'Finlandia', 'timezone' => 'Europe/Helsinki'],
            ['id' => 64, 'name' => 'Francia', 'timezone' => 'Europe/Paris'],
            ['id' => 65, 'name' => 'República Checa', 'timezone' => 'Europe/Prague'],
            ['id' => 66, 'name' => 'Suiza', 'timezone' => 'Europe/Zurich'],
            ['id' => 67, 'name' => 'Suecia', 'timezone' => 'Europe/Stockholm'],
            ['id' => 68, 'name' => 'Estonia', 'timezone' => 'Europe/Tallinn'],
            ['id' => 69, 'name' => 'Corea del Sur', 'timezone' => 'Asia/Seoul'],
            ['id' => 70, 'name' => 'Japón', 'timezone' => 'Asia/Tokyo'],
            ['id' => 71, 'name' => 'Croacia', 'timezone' => 'Europe/Zagreb'],
            ['id' => 72, 'name' => 'Rumanía', 'timezone' => 'Europe/Bucharest'],
            ['id' => 73, 'name' => 'Hong Kong', 'timezone' => 'Asia/Hong_Kong'],
            ['id' => 74, 'name' => 'Indonesia', 'timezone' => 'Asia/Jakarta'],
            ['id' => 75, 'name' => 'Jordania', 'timezone' => 'Asia/Amman'],
            ['id' => 76, 'name' => 'Malasia', 'timezone' => 'Asia/Kuala_Lumpur'],
            ['id' => 77, 'name' => 'Singapur', 'timezone' => 'Asia/Singapore'],
            ['id' => 78, 'name' => 'Taiwan', 'timezone' => 'Asia/Taipei'],
            ['id' => 79, 'name' => 'Bosnia y Herzegovina', 'timezone' => 'Europe/Sarajevo'],
            ['id' => 80, 'name' => 'Bahamas', 'timezone' => 'America/Nassau'],
            ['id' => 81, 'name' => 'Chile', 'timezone' => 'America/Santiago'],
            ['id' => 82, 'name' => 'Colombia', 'timezone' => 'America/Bogota'],
            ['id' => 83, 'name' => 'Islandia', 'timezone' => 'Atlantic/Reykjavik'],
            ['id' => 84, 'name' => 'Corea del Norte', 'timezone' => 'Asia/Pyongyang'],
            ['id' => 85, 'name' => 'Macedonia', 'timezone' => 'Europe/Skopje'],
            ['id' => 86, 'name' => 'Malta', 'timezone' => 'Europe/Malta'],
            ['id' => 87, 'name' => 'Pakistán', 'timezone' => 'Asia/Karachi'],
            ['id' => 88, 'name' => 'Papúa-Nueva Guinea', 'timezone' => 'Pacific/Port_Moresby'],
            ['id' => 89, 'name' => 'Perú', 'timezone' => 'America/Lima'],
            ['id' => 90, 'name' => 'Filipinas', 'timezone' => 'Asia/Manila'],
            ['id' => 91, 'name' => 'Arabia Saudita', 'timezone' => 'Asia/Riyadh'],
            ['id' => 92, 'name' => 'Tailandia', 'timezone' => 'Asia/Bangkok'],
            ['id' => 93, 'name' => 'Emiratos árabes Unidos', 'timezone' => 'Asia/Dubai'],
            ['id' => 94, 'name' => 'Groenlandia', 'timezone' => 'America/Godthab'],
            ['id' => 95, 'name' => 'Venezuela', 'timezone' => 'America/Caracas'],
            ['id' => 96, 'name' => 'Zimbabwe', 'timezone' => 'Africa/Harare'],
            ['id' => 97, 'name' => 'Kenia', 'timezone' => 'Africa/Nairobi'],
            ['id' => 98, 'name' => 'Algeria', 'timezone' => 'Africa/Algiers'],
            ['id' => 99, 'name' => 'Líbano', 'timezone' => 'Asia/Beirut'],
            ['id' => 100, 'name' => 'Botsuana', 'timezone' => 'Africa/Gaborone'],
            ['id' => 101, 'name' => 'Tanzania', 'timezone' => 'Africa/Dar_es_Salaam'],
            ['id' => 102, 'name' => 'Namibia', 'timezone' => 'Africa/Windhoek'],
            ['id' => 103, 'name' => 'Ecuador', 'timezone' => 'America/Guayaquil'],
            ['id' => 104, 'name' => 'Marruecos', 'timezone' => 'Africa/Casablanca'],
            ['id' => 105, 'name' => 'Ghana', 'timezone' => 'Africa/Accra'],
            ['id' => 106, 'name' => 'Siria', 'timezone' => 'Asia/Damascus'],
            ['id' => 107, 'name' => 'Nepal', 'timezone' => 'Asia/Kathmandu'],
            ['id' => 108, 'name' => 'Mauritania', 'timezone' => 'Africa/Nouakchott'],
            ['id' => 109, 'name' => 'Seychelles', 'timezone' => 'Indian/Mahe'],
            ['id' => 110, 'name' => 'Paraguay', 'timezone' => 'America/Asuncion'],
            ['id' => 111, 'name' => 'Uruguay', 'timezone' => 'America/Montevideo'],
            ['id' => 112, 'name' => 'Congo (Brazzaville)', 'timezone' => 'Africa/Brazzaville'],
            ['id' => 113, 'name' => 'Cuba', 'timezone' => 'America/Havana'],
            ['id' => 114, 'name' => 'Albania', 'timezone' => 'Europe/Tirane'],
            ['id' => 115, 'name' => 'Nigeria', 'timezone' => 'Africa/Lagos'],
            ['id' => 116, 'name' => 'Zambia', 'timezone' => 'Africa/Lusaka'],
            ['id' => 117, 'name' => 'Mozambique', 'timezone' => 'Africa/Maputo'],
            ['id' => 119, 'name' => 'Angola', 'timezone' => 'Africa/Luanda'],
            ['id' => 120, 'name' => 'Sri Lanka', 'timezone' => 'Asia/Colombo'],
            ['id' => 121, 'name' => 'Etiopía', 'timezone' => 'Africa/Addis_Ababa'],
            ['id' => 122, 'name' => 'Túnez', 'timezone' => 'Africa/Tunis'],
            ['id' => 123, 'name' => 'Bolivia', 'timezone' => 'America/La_Paz'],
            ['id' => 124, 'name' => 'Panamá', 'timezone' => 'America/Panama'],
            ['id' => 125, 'name' => 'Malawi', 'timezone' => 'Africa/Blantyre'],
            ['id' => 126, 'name' => 'Liechtenstein', 'timezone' => 'Europe/Vaduz'],
            ['id' => 127, 'name' => 'Bahrein', 'timezone' => 'Asia/Bahrain'],
            ['id' => 128, 'name' => 'Barbados', 'timezone' => 'America/Barbados'],
            ['id' => 130, 'name' => 'Chad', 'timezone' => 'Africa/Ndjamena'],
            ['id' => 131, 'name' => 'Man, Isla de', 'timezone' => 'Europe/Isle_of_Man'],
            ['id' => 132, 'name' => 'Jamaica', 'timezone' => 'America/Jamaica'],
            ['id' => 133, 'name' => 'Malí', 'timezone' => 'Africa/Bamako'],
            ['id' => 134, 'name' => 'Madagascar', 'timezone' => 'Indian/Antananarivo'],
            ['id' => 135, 'name' => 'Senegal', 'timezone' => 'Africa/Dakar'],
            ['id' => 136, 'name' => 'Togo', 'timezone' => 'Africa/Lome'],
            ['id' => 137, 'name' => 'Honduras', 'timezone' => 'America/Tegucigalpa'],
            ['id' => 138, 'name' => 'República Dominicana', 'timezone' => 'America/Santo_Domingo'],
            ['id' => 139, 'name' => 'Mongolia', 'timezone' => 'Asia/Ulaanbaatar'],
            ['id' => 140, 'name' => 'Irak', 'timezone' => 'Asia/Baghdad'],
            ['id' => 141, 'name' => 'Sudáfrica', 'timezone' => 'Africa/Johannesburg'],
            ['id' => 142, 'name' => 'Aruba', 'timezone' => 'America/Aruba'],
            ['id' => 143, 'name' => 'Gibraltar', 'timezone' => 'Europe/Gibraltar'],
            ['id' => 144, 'name' => 'Afganistán', 'timezone' => 'Asia/Kabul'],
            ['id' => 145, 'name' => 'Andorra', 'timezone' => 'Europe/Andorra'],
            ['id' => 147, 'name' => 'Antigua y Barbuda', 'timezone' => 'America/Antigua'],
            ['id' => 149, 'name' => 'Bangladesh', 'timezone' => 'Asia/Dhaka'],
            ['id' => 151, 'name' => 'Benín', 'timezone' => 'Africa/Porto-Novo'],
            ['id' => 152, 'name' => 'Bután', 'timezone' => 'Asia/Thimphu'],
            ['id' => 154, 'name' => 'Islas Virgenes Británicas', 'timezone' => 'America/Tortola'],
            ['id' => 155, 'name' => 'Brunéi', 'timezone' => 'Asia/Brunei'],
            ['id' => 156, 'name' => 'Burkina Faso', 'timezone' => 'Africa/Ouagadougou'],
            ['id' => 157, 'name' => 'Burundi', 'timezone' => 'Africa/Bujumbura'],
            ['id' => 158, 'name' => 'Camboya', 'timezone' => 'Asia/Phnom_Penh'],
            ['id' => 159, 'name' => 'Cabo Verde', 'timezone' => 'Atlantic/Cape_Verde'],
            ['id' => 164, 'name' => 'Comores', 'timezone' => 'Indian/Comoro'],
            ['id' => 165, 'name' => 'Congo (Kinshasa)', 'timezone' => 'Africa/Kinshasa'],
            ['id' => 166, 'name' => 'Cook, Islas', 'timezone' => 'Pacific/Rarotonga'],
            ['id' => 168, 'name' => 'Costa de Marfil', 'timezone' => 'Africa/Abidjan'],
            ['id' => 169, 'name' => 'Djibouti, Yibuti', 'timezone' => 'Africa/Djibouti'],
            ['id' => 171, 'name' => 'Timor Oriental', 'timezone' => 'Asia/Dili'],
            ['id' => 172, 'name' => 'Guinea Ecuatorial', 'timezone' => 'Africa/Malabo'],
            ['id' => 173, 'name' => 'Eritrea', 'timezone' => 'Africa/Asmara'],
            ['id' => 175, 'name' => 'Feroe, Islas', 'timezone' => 'Atlantic/Faroe'],
            ['id' => 176, 'name' => 'Fiyi', 'timezone' => 'Pacific/Fiji'],
            ['id' => 178, 'name' => 'Polinesia Francesa', 'timezone' => 'Pacific/Tahiti'],
            ['id' => 180, 'name' => 'Gabón', 'timezone' => 'Africa/Libreville'],
            ['id' => 181, 'name' => 'Gambia', 'timezone' => 'Africa/Banjul'],
            ['id' => 184, 'name' => 'Granada', 'timezone' => 'America/Grenada'],
            ['id' => 185, 'name' => 'Guatemala', 'timezone' => 'America/Guatemala'],
            ['id' => 186, 'name' => 'Guernsey', 'timezone' => 'Europe/Guernsey'],
            ['id' => 187, 'name' => 'Guinea', 'timezone' => 'Africa/Conakry'],
            ['id' => 188, 'name' => 'Guinea-Bissau', 'timezone' => 'Africa/Bissau'],
            ['id' => 189, 'name' => 'Guyana', 'timezone' => 'America/Guyana'],
            ['id' => 193, 'name' => 'Jersey', 'timezone' => 'Europe/Jersey'],
            ['id' => 195, 'name' => 'Kiribati', 'timezone' => 'Pacific/Tarawa'],
            ['id' => 196, 'name' => 'Laos', 'timezone' => 'Asia/Vientiane'],
            ['id' => 197, 'name' => 'Lesotho', 'timezone' => 'Africa/Maseru'],
            ['id' => 198, 'name' => 'Liberia', 'timezone' => 'Africa/Monrovia'],
            ['id' => 200, 'name' => 'Maldivas', 'timezone' => 'Indian/Maldives'],
            ['id' => 201, 'name' => 'Martinica', 'timezone' => 'America/Martinique'],
            ['id' => 202, 'name' => 'Mauricio', 'timezone' => 'Indian/Mauritius'],
            ['id' => 205, 'name' => 'Myanmar', 'timezone' => 'Asia/Yangon'],
            ['id' => 206, 'name' => 'Nauru', 'timezone' => 'Pacific/Nauru'],
            ['id' => 207, 'name' => 'Antillas Holandesas', 'timezone' => 'America/Curacao'],
            ['id' => 208, 'name' => 'Nueva Caledonia', 'timezone' => 'Pacific/Noumea'],
            ['id' => 209, 'name' => 'Nicaragua', 'timezone' => 'America/Managua'],
            ['id' => 210, 'name' => 'Níger', 'timezone' => 'Africa/Niamey'],
            ['id' => 212, 'name' => 'Norfolk Island', 'timezone' => 'Pacific/Norfolk'],
            ['id' => 213, 'name' => 'Omán', 'timezone' => 'Asia/Muscat'],
            ['id' => 215, 'name' => 'Isla Pitcairn', 'timezone' => 'Pacific/Pitcairn'],
            ['id' => 216, 'name' => 'Qatar', 'timezone' => 'Asia/Qatar'],
            ['id' => 217, 'name' => 'Ruanda', 'timezone' => 'Africa/Kigali'],
            ['id' => 218, 'name' => 'Santa Elena', 'timezone' => 'Atlantic/St_Helena'],
            ['id' => 219, 'name' => 'San Cristobal y Nevis', 'timezone' => 'America/St_Kitts'],
            ['id' => 220, 'name' => 'Santa Lucía', 'timezone' => 'America/St_Lucia'],
            ['id' => 221, 'name' => 'San Pedro y Miquelón', 'timezone' => 'America/Miquelon'],
            ['id' => 222, 'name' => 'San Vincente y Granadinas', 'timezone' => 'America/St_Vincent'],
            ['id' => 223, 'name' => 'Samoa', 'timezone' => 'Pacific/Apia'],
            ['id' => 224, 'name' => 'San Marino', 'timezone' => 'Europe/San_Marino'],
            ['id' => 225, 'name' => 'San Tomé y Príncipe', 'timezone' => 'Africa/Sao_Tome'],
            ['id' => 226, 'name' => 'Serbia y Montenegro', 'timezone' => 'Europe/Belgrade'],
            ['id' => 227, 'name' => 'Sierra Leona', 'timezone' => 'Africa/Freetown'],
            ['id' => 228, 'name' => 'Islas Salomón', 'timezone' => 'Pacific/Guadalcanal'],
            ['id' => 229, 'name' => 'Somalia', 'timezone' => 'Africa/Mogadishu'],
            ['id' => 232, 'name' => 'Sudán', 'timezone' => 'Africa/Khartoum'],
            ['id' => 234, 'name' => 'Swazilandia', 'timezone' => 'Africa/Mbabane'],
            ['id' => 235, 'name' => 'Tokelau', 'timezone' => 'Pacific/Fakaofo'],
            ['id' => 236, 'name' => 'Tonga', 'timezone' => 'Pacific/Tongatapu'],
            ['id' => 237, 'name' => 'Trinidad y Tobago', 'timezone' => 'America/Port_of_Spain'],
            ['id' => 239, 'name' => 'Tuvalu', 'timezone' => 'Pacific/Funafuti'],
            ['id' => 240, 'name' => 'Vanuatu', 'timezone' => 'Pacific/Efate'],
            ['id' => 241, 'name' => 'Wallis y Futuna', 'timezone' => 'Pacific/Wallis'],
            ['id' => 242, 'name' => 'Sáhara Occidental', 'timezone' => 'Africa/El_Aaiun'],
            ['id' => 243, 'name' => 'Yemen', 'timezone' => 'Asia/Aden'],
            ['id' => 246, 'name' => 'Puerto Rico', 'timezone' => 'America/Puerto_Rico']
        ]);

    }
}
