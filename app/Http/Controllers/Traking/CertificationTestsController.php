<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\CertificationTest;
use App\Models\Due;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificationTestsController extends Controller
{
    public function update(Request $request)
    {
        foreach ($request->all() as $cert) {
            // return $cert;
            $fillable = new CertificationTest();
            $c = array_intersect_key($cert, array_flip($fillable->getFillable()));

            $certificationTest = CertificationTest::where('id', $cert['id'])->first();
            $certificationTest->update($c);

            if (!$certificationTest->due_id && $certificationTest->premium) {
                // Create due
                $due = new Due();
                $due->payment_reason = 'Examen de certificación';
                $due->order_id = $certificationTest->order_id;
                $due->save();

                $certificationTest->due_id = $due->id;
                $certificationTest->save();
            }

            if ($certificationTest->premium) {
                self::updatePayment($certificationTest->id, $cert);
            }
        }

        return ApiResponseController::response('Exito', 200, $cert);
    }

    public function updatePayment($certification_id, $data)
    {
        $certificationTest = CertificationTest::find($certification_id);

        Log::info($data);
        $d = new Due();
        $d = array_intersect_key($data, array_flip($d->getFillable()));

        Log::info($d);
        $certificationTest->due->update($d);

        return true;
    }
}
