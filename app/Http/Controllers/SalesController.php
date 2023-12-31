<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Zadarma_API\Api;
use App\Models\Lead;
use App\Models\LeadTraking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesController extends Controller
{

    public function importData(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->storeAs('csv', $file->getClientOriginalName());

            $handle = fopen(storage_path("app/$path"), 'r');

            fgetcsv($handle);

            // Obtener todos los leads con números de teléfono que se crearon hoy
            $existingLeads = Lead::whereDate('created_at', Carbon::today())->pluck('phone')->toArray();

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $name = $data[0];
                $courses = $data[1];
                $phone = $data[2];

                if (!$name && !$courses && !$phone) continue;

                // Verificar si el lead ya existe en el conjunto de datos cargado en memoria
                if (!in_array($phone, $existingLeads)) {
                    Lead::create([
                        'name' => $name,
                        'courses' => $courses,
                        'phone' => $phone
                    ]);

                    // Añadir el número de teléfono al conjunto de datos para futuras comprobaciones
                    $existingLeads[] = $phone;
                }
            }

            fclose($handle);
        }

        return ApiResponseController::response('Leads importados exitosamente', 200);
    }



    public function getZadarmaInfo(Request $request){

        $user = $request->user();

        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);
        $sip = $api->getWebrtcKey($user->zadarma_id);

        $data = [
            'key'        => $sip->key,
            'zadarma_id' => $user->zadarma_id,
        ];
        return ApiResponseController::response('Consulta Exitosa', 200, $data);
    }
}
