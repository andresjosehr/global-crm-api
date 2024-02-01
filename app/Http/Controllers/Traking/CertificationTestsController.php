<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\CertificationTest;
use Illuminate\Http\Request;

class CertificationTestsController extends Controller
{
    public function update(Request $request)
    {
        foreach($request->all() as $cert) {
            // return $cert;
            CertificationTest::where('id', $cert['id'])->update($cert);
        }

        return ApiResponseController::response('Exito', 200, $cert);
    }
}
