<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\Car;
use App\Models\DocumentType;
use App\Models\Order;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;

        $searchString = $request->input('searchString') ? $request->input('searchString') : '';
        $searchString = $request->input('searchString') != 'null' ? $request->input('searchString') : '';

        $users = Student::when($searchString, function ($q) use ($searchString) {
            $q->where('name', 'LIKE', "%$searchString%")
                ->orWhere('country_id', 'LIKE', "%$searchString%")
                ->orWhere("email", 'LIKE', "%$searchString%")
                ->orWhere('phone', 'LIKE', "%$searchString%")
                ->orWhere('document', 'LIKE', "%$searchString%");
        })
            ->when($request->input('Matriculados') == '1', function ($q) use ($user) {
                $q->whereHas('lead', function ($q) {
                    $q->where('status', 'Matriculado');
                })->where('user_id', $user->id)->with('lead');
            })
            ->with('orders')
            ->when($user->role_id != 1, function ($q) use ($user) {
                return $q->whereHas('orders', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return ApiResponseController::response('Consulta Exitosa,pero no hay matriculados', 200, $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Crear el estudiante
        $student                   = new Student();
        $student->name             = $request->input('name');
        $student->country_id       = $request->input('country_id');
        $student->state_id         = $request->input('state_id');
        $student->city_id          = $request->input('city_id');
        $student->document_type_id = $request->input('document_type_id');
        $student->phone            = $request->input('phone');
        $student->document         = $request->input('document');
        $student->email            = $request->input('email');

        // Asignar user_id dinámicamente

        $student->save();

        // Obtener el ID del estudiante creado


        return ApiResponseController::response('Usuario creado con éxito', 200, $student);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$student = Student::with('orders.orderCourses.course', 'orders.orderCourses.extensions', 'orders.orderCourses.certificationTests', 'orders.orderCourses.sapInstalations', 'orders.orderCourses.freezings', 'orders.orderCourses.dateHistory', 'orders.currency', 'orders.dues', 'orders.user', 'orders.invoice')->find($id)->attachCertificationTest()) {
            return ApiResponseController::response('', 204);
        }


        return ApiResponseController::response('Consulta exitosa', 200, $student);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $student                       = Student::find($id);

        if (!$student) {
            return ApiResponseController::response('Estudiante no encontrado', 404);
        }

        $student->name       = $request->input('name');
        $student->country_id = $request->input('country_id');
        $student->state_id   = $request->input('state_id') ?? null;
        $student->city_id    = $request->input('city_id') ?? null;
        $student->phone      = $request->input('phone');
        $student->email      = $request->input('email');



        if ($request->input('document_type_id') == 'otro') {
            // Check if exists
            if (!DocumentType::where('name', $request->input('document_type_name'))->exists()) {
                $documentType = new DocumentType();
                $documentType->name = $request->input('document_type_name');
                $documentType->custom = true;
                $documentType->country_id = $request->input('country_id');
                $documentType->save();
                $student->document_type_id = $documentType->id;
            }
        } else {
            $student->document_type_id     = $request->input('document_type_id');
        }


        $student->save();

        return ApiResponseController::response('Usuario actualizado con exito', 200, $student);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    function checkTermsAccess(Request $request, $key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', 0)->first();

        if (!$order) {
            return ApiResponseController::response('Unauthorized', 401);
        }

        return ApiResponseController::response('Authorized', 200);
    }


    function getTermsInfo($key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', false)->with('student', 'orderCourses.course', 'dues', 'currency', 'student')->first();

        if (!$order) {
            return ApiResponseController::response('No se encontró la orden', 404);
        }

        return ApiResponseController::response('Consulta exitosa', 200, $order);
    }


    function confirmTermsInfo(Request $request, $key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', false)->first();

        if (!$order) {
            return ApiResponseController::response('No se encontró la orden', 404);
        }


        $student = Student::find($order->student->id);

        $student->name       = $request->input('name');
        $student->country_id = $request->input('country_id');
        $student->phone      = $request->input('phone');
        $student->phone      = $request->input('phone');
        $student->city_id    = $request->input('city_id');
        $student->state_id   = $request->input('state_id');
        $student->email      = $request->input('email');

        $student->save();

        $student = Student::find($student->id)->with('user')->first();



        if ($request->input('document_type_id') == 'otro') {
            // Check if exists
            if (!DocumentType::where('name', $request->input('document_type_name'))->exists()) {
                $documentType = new DocumentType();
                $documentType->name = $request->input('document_type_name');
                $documentType->custom = true;

                $student->document_type_id = $documentType->id;
                $documentType->save();
            }
        } else {
            $student->document_type_id     = $request->input('document_type_id');
        }

        $order->terms_confirmed_by_student = true;
        $order->save();

        $order = Order::where('id', $order->id)->with('orderCourses.course', 'dues', 'student.users', 'currency')->first();

        $mailTemplate = [
            'Contado' => 'terms-contado',
            'Cuotas' => 'terms-cuotas'
        ];

        $pdfFileName  = 'orden_' . Carbon::parse($order->created_at)->format('YmdHis') . $order->id . '.pdf';
        $urlTerm      = env('APP_URL');
        $urlTerm     .= '/storage/terminos-aceptados/' . $pdfFileName;

        $content = view("mails." . $mailTemplate[$order->payment_mode])->with(['order' => $order, 'urlTerm' => $urlTerm])->render();

        CoreMailsController::sendMail(
            $student->user->email,
            'Has aceptado los términos y condiciones | Bienvenido a tu curso',
            $content
        );

        CoreMailsController::sendMail(
            'andresjosehr@gmail.com',
            'Has aceptado los términos y condiciones | Bienvenido a tu curso',
            $content
        );

        CoreMailsController::sendMail(
            'llazayanaalex@gmail.com',
            'Has aceptado los términos y condiciones | Bienvenido a tu curso',
            $content
        );

        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => 'Ficha de matriculada confirmada | ' . $order->student->name,
            'body'       => 'El alumno ' . $order->student->name . ' ha confirmado su ficha de matrícula de manera satisfactoria',
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => $order->student->users[0]->id,
            'use_router' => false,
            'custom_data' => [
                'type'     => 'terms_confirmed_by_student',
                'student'  => $student,
                'order_id' => $order->id
            ]
        ]);

        $processesController = new ProcessesController();
        $processesController->updateSellsExcel($order->id);


        return ApiResponseController::response('Consulta exitosa', 200, $order);
    }

    public function saveTermsPdfTemplate(Request $request, $order_id)
    {
        $base64String = $request->input('base64'); // Asegúrate de enviar la cadena base64 como parte de tu solicitud

        // Decodificar la cadena base64
        $pdfDecoded = base64_decode($base64String);

        $order = Order::find($order_id);

        // Definir el nombre del archivo PDF
        $pdfFileName = 'orden_' . Carbon::parse($order->created_at)->format('YmdHis') . $order->id . '.pdf';

        // Guardar el archivo en la carpeta storage/app/public/terminos-aceptados
        $path = storage_path('app/public/terminos-aceptados/' . $pdfFileName);
        file_put_contents($path, $pdfDecoded);

        // Devolver alguna respuesta
        return response()->json(['mensaje' => 'PDF guardado con éxito', 'ruta' => $path]);
    }


    public function downloadTermsPdfTemplate($order_id)
    {

        $order = Order::find($order_id);

        $filename = 'orden_' . Carbon::parse($order->created_at)->format('YmdHis') . $order->id . '.pdf';
        // return $filename;

        if (!Storage::disk('local')->exists("public/terminos-aceptados/$filename")) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $path = storage_path("app/public/terminos-aceptados/$filename");
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::download($path, $filename, $headers);
    }

    public function downloadTermsPdfTemplate2($order_key)
    {

        $order = Order::where('key', $order_key)->first();

        $filename = 'orden_' . Carbon::parse($order->created_at)->format('YmdHis') . $order->id . '.pdf';

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists("public/terminos-aceptados/$filename")) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        // Ruta al archivo en el sistema de archivos
        $path = storage_path("app/public/terminos-aceptados/$filename");

        // Descargar el archivo
        return response()->download($path);
    }
}
