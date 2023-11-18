<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadAssignment;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    public function assignNextLead(Request $request)
    {
        $userId = $request->user()->id;
        $lastAssignment = LeadAssignment::where('user_id', $userId)
                             ->orderBy('sequence', 'desc')
                             ->first();

        $nextSequence = $lastAssignment ? $lastAssignment->sequence + 1 : 1;
        $totalLeads = Lead::count();

        if ($nextSequence > $totalLeads) {
            $nextSequence = 1; // Vuelve al principio si se llega al final
        }

        $nextLead = Lead::whereDoesntHave('assignments', function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        })
                        ->skip($nextSequence - 1)
                        ->first();

        if (!$nextLead) {
            return response()->json(['message' => 'No hay más leads disponibles.'], 404);
        }

        $assignment = new LeadAssignment();
        $assignment->user_id = $userId;
        $assignment->lead_id = $nextLead->id;
        $assignment->sequence = $nextSequence;
        $assignment->save();

        return ApiResponseController::response('Lead asignado exitosamente', 200, $assignment);
    }

    public function previousLead(Request $request)
    {
        $userId = $request->user()->id;
        $lastAssignment = LeadAssignment::where('user_id', $userId)
                             ->orderBy('sequence', 'desc')
                             ->first();

        if (!$lastAssignment || $lastAssignment->sequence == 1) {
            return ApiResponseController::response('No hay leads anteriores', 200);
        }

        $previousLeadId = LeadAssignment::where('user_id', $userId)
                             ->where('sequence', '<', $lastAssignment->sequence)
                             ->orderBy('sequence', 'desc')
                             ->first()
                             ->lead_id;

        $lead = Lead::find($previousLeadId);
        return ApiResponseController::response('Lead asignado exitosamente', 200, $lead);

    }

    public function nextLead(Request $request)
    {
        $userId = $request->user()->id;
        $nextLeadId = LeadAssignment::where('user_id', $userId)
                            ->orderBy('sequence', 'asc')
                            ->firstWhere('sequence', '>', $request->sequence)
                            ->lead_id ?? null;

        if (!$nextLeadId) {
            return response()->json(['message' => 'No hay más leads.'], 404);
        }

        return $lead = Lead::find($nextLeadId);
        return ApiResponseController::response('Lead asignado exitosamente', 200, $lead);
    }

    public function currentLead(Request $request)
    {
        $userId = $request->user()->id;

        $currentAssignment = LeadAssignment::where('user_id', $userId)
                               ->orderBy('sequence', 'desc')
                               ->first();

        if (!$currentAssignment) {
            return response()->json(['message' => 'Actualmente no tienes asignaciones.'], 404);
        }

        return Lead::find($currentAssignment->lead_id);
    }
}
