<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadProjectsController extends Controller
{
    public function importData(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->storeAs('csv', $file->getClientOriginalName());

            $handle = fopen(storage_path("app/$path"), 'r');

            fgetcsv($handle);

            // Obtener todos los leads con números de teléfono que se crearon hoy


            $project_id = null;
            if ($request->project_type == 'Nuevo') {
                $leadProject = LeadProject::create([
                    'name' => $request->project_name
                ]);
                $project_id = $leadProject->id;
            } else {
                $project_id = $request->project_id == 'Base' ? null : $request->project_id;
            }

            $existingLeads = Lead::whereDate('created_at', Carbon::today())->where('lead_project_id', $project_id)->pluck('phone')->toArray();

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
                        'phone' => $phone,
                        'lead_project_id' => $project_id
                    ]);

                    // Añadir el número de teléfono al conjunto de datos para futuras comprobaciones
                    $existingLeads[] = $phone;
                }
            }

            fclose($handle);
        }

        return ApiResponseController::response('Leads importados exitosamente', 200);
    }

    public function getProjects(Request $request)
    {


        $user = $request->user();
        // Get projects with leads count
        $projects = LeadProject::
        withCount('leads')
        ->with('users')
        ->when($user->role_id != 1, function ($query) use ($user) {
            return $query->whereHas('users', function ($q) use ($user) {
                return $q->where('email', $user->email);
            });
        })
        ->get();

        $base = [
            'id'          => 'Base',
            'name'        => 'Base',
            'leads_count' => Lead::whereNull('lead_project_id')->count()
        ];

        // $projects->push($base);
        if($user->role_id == 1){
            $projects->prepend($base);
        }
        return ApiResponseController::response("Exito", 200, $projects);
    }

    public function updateUserProjects(Request $request)
    {

        $user = User::find($request->user_id);
        // Remove projects
        $user->projects()->detach();

        // Add projects
        foreach ($request->projects as $project) {
            DB::table('user_lead_projects')->insert([
                'user_id' => $user->id,
                'lead_project_id' => $project,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
        return ApiResponseController::response("Exito", 200);
    }
}
