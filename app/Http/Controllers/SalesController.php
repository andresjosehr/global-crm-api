<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Zadarma_API\Api;
use App\Models\Lead;
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

    public function getLeads(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;

        $searchString = $request->input('searchString') ? $request->input('searchString') : '';
        $searchString = $request->input('searchString') != 'null' ? $request->input('searchString') : '';

        $leads = Lead::when($searchString, function ($q) use ($searchString) {
            $q->where('name', 'LIKE', "%$searchString%")
                ->orWhere('courses', 'LIKE', "%$searchString%")
                ->orWhere('phone', 'LIKE', "%$searchString%");
        })
            ->orderByDesc('id')
            ->paginate($perPage);

        return ApiResponseController::response('Consulta Exitosa', 200, $leads);
    }

    public function getZadarmaInfo(){
        // Get from .env
        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);
        $sip = $api->getWebrtcKey('328959-101');


        return ApiResponseController::response('Consulta Exitosa', 200, [$sip]);
    }
}
