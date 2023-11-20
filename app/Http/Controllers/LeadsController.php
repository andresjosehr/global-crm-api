<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadObservation;
use App\Models\User;
use Illuminate\Http\Request;

class LeadsController extends Controller
{

    public function getCurrentLead(Request $request)
    {
        $user = $request->user();

        $lead = $user->leadAssignments()->latest('order')->where('active', true)->first();


        if (!$lead) {
            self::assignNextLead($user);
        }

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();
        // ATTATCH observations.user



        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getNextLead(Request $request)
    {
        $user = $request->user();

        self::assignNextLead($user);

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getPreviousLead(Request $request)
    {
        $user = $request->user();

        $previousAssignment = self::assignPreviousLead($user);

        if (!$previousAssignment) {
            return ApiResponseController::response("No hay leads previos", 200);
        }

        $lead = $previousAssignment->lead;
        // $lead = Lead::with('observations.user')->find($lead->id);
        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function assignNextLead(User $user)
    {
        // Obtener el último lead asignado al asesor y el orden actual
        $lastAssignment = $user->leadAssignments()->latest('order')->where('active', true)->first();
        $nextOrder = $lastAssignment ? $lastAssignment->order + 1 : 1;

        // Verificar si ya existe un lead asignado con el siguiente orden
        $nextAssignment = $user->leadAssignments()->where('order', $nextOrder)->first();

        if ($nextAssignment) {
            // Reactivar el lead siguiente si existe
            $nextAssignment->update(['active' => true]);
        } else {
            // Si no existe, determinar el próximo lead a asignar
            $data = $this->findNextLeadId($user, $lastAssignment);

            // Crear la nueva asignación
            $nextAssignment = new LeadAssignment([
                'lead_id' => $data['nextLeadId'],
                'round' => $data['round'],
                'order' => $nextOrder,
                'active' => true, // El lead se marca como activo por defecto
                'assigned_at' => now(),
            ]);

            $user->leadAssignments()->save($nextAssignment);
        }

        // Marcar el lead anterior como inactivo
        if ($lastAssignment) {
            $lastAssignment->update(['active' => false]);
        }

        return $nextAssignment;
    }

    public function assignPreviousLead(User $user)
    {
        // Obtener el lead actualmente asignado y disminuir el orden
        $currentAssignment = $user->leadAssignments()->latest('order')->where('active', true)->first();

        if (!$currentAssignment || $currentAssignment->order <= 1) {
            // No hay lead previo o es el primer lead
            return null;
        }

        $previousOrder = $currentAssignment->order - 1;
        $previousAssignment = $user->leadAssignments()->where('order', $previousOrder)->first();

        // Marcar el lead actual como inactivo y el anterior como activo
        if ($previousAssignment) {
            $currentAssignment->update(['active' => false]);
            $previousAssignment->update(['active' => true]);
        }

        return $previousAssignment;
    }

    private function findNextLeadId()
    {
        // Obtener todos los leads ya asignados al usuario
        $activeAssignedLeadIds = LeadAssignment::where('active', true)->pluck('lead_id');

        // Get max activeAssignedLeadIds
        $round = LeadAssignment::max('round') ?? 1;
        $maxActiveAssignedLeadId = LeadAssignment::where('round', $round)->max('lead_id') ?? 0;

        // Encuentra el próximo lead no asignado al usuario
        $nextLeadId = Lead::whereNotIn('id', $activeAssignedLeadIds)
            ->where('id', '>', $maxActiveAssignedLeadId)
            ->orderBy('id', 'ASC')
            ->first();

        if(!$nextLeadId){
            $round++;
            $nextLeadId = $this->startNewRound();
        } else{
            $nextLeadId = $nextLeadId->id;
        }

        return [
            'round' => $round,
            'nextLeadId' => $nextLeadId
        ];
    }

    private function startNewRound()
    {
        // Comienza una nueva vuelta asignando el primer lead disponible
        return Lead::orderBy('id')->first()->id;
    }


    public function saveBasicData(Request $request, $id)
    {
        $lead = Lead::where('id', $id)->update([
            'name' => $request->name,
            'courses' => $request->courses,
            'phone' => $request->phone,
            'status' => $request->status,
            'email' => $request->email,
            'origin' => $request->origin,
            'document' => $request->document
        ]);

        $lead = Lead::with('observations.user')->find($id);

        return ApiResponseController::response("Exito", 200, $lead);

    }

    public function saveObservation(Request $request, $leadId, $leadAssignamentId)
    {
        // Get current lead assignment


        $leadObservation = LeadObservation::create([
            'user_id'            => $request->user()->id,
            'lead_id'            => $leadId,
            'call_status'        => $request->call_status,
            'observation'        => $request->observation,
            'lead_assignment_id' => $leadAssignamentId
        ]);

        $leadObservation = LeadObservation::with('user')->find($leadObservation->id);



        return ApiResponseController::response("Exito", 200, $leadObservation);
    }
}
