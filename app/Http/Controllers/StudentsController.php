<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Jobs\GeneralJob;
use App\Models\Car;
use App\Models\DocumentType;
use App\Models\Order;
use App\Models\Student;
use App\Models\User;
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
        // return $user;
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
                })
                    ->when($user->role_id == 2, function ($q) use ($user) {
                        $q->whereHas('orders', function ($q) use ($user) {
                            return $q->where('created_by', $user->id);
                        });
                    });
            })
            ->with(['orders' => function ($q) {
                return $q->withCount(['orderCourses' => function ($query) {
                    $query->where('type', 'paid');
                }]);
            }])
            ->with('lead')
            ->when($user->role_id == 3 || $user->role_id == 4, function ($q) use ($user) {
                return $q->where('user_id', $user->id);
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
        // $student = Student::with('orders.orderCourses.course', 'orders.orderCourses.extensions', 'orders.orderCourses.certificationTests', 'orders.orderCourses.sapInstalations.staff', 'orders.orderCourses.freezings', 'orders.orderCourses.dateHistory', 'orders.currency', 'orders.dues', 'orders.user', 'orders.invoice')->find($id)->attachCertificationTest();
        $student = Student::with('orders')->find($id);
        if (!$student) {
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
        $user = $request->user();
        $student                       = Student::find($id);

        if (!$student) {
            return ApiResponseController::response('Alumno no encontrado', 404);
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

        // check if user_id comming from request is different from the one in the database
        if ($request->input('user_id') && $request->input('user_id') != $student->user_id) {
            if ($user->role_id == 1) {
                $student->user_id = $request->input('user_id');
            }
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
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 401);
        }

        $student = Student::where('id', $id)->with('orders')->first();

        if (!$student) {
            return ApiResponseController::response('Alumno no encontrado', 404);
        }

        $orderController = new OrdersController();
        foreach ($student->orders as $order) {
            $orderController->destroy($order->id);
        }

        DB::table('user_student')->where('student_id', $id)->delete();

        $student->delete();

        return ApiResponseController::response('Alumno eliminado con éxito', 200);
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



        $params = [
            'order'   => $order,
            'student' => $student
        ];

        GeneralJob::dispatch(StudentsController::class, 'dipatchNotification', $params)->onQueue('default');


        return ApiResponseController::response('Consulta exitosa', 200, $order);
    }

    public function dipatchNotification($order, $student)
    {

        $mailTemplate = [
            'Contado' => 'terms-contado',
            'Cuotas'  => 'terms-cuotas'
        ];

        $pdfFileName  = 'orden_' . Carbon::parse($order->created_at)->format('YmdHis') . $order->id . '.pdf';
        $urlTerm      = env('APP_URL');
        $urlTerm     .= '/storage/terminos-aceptados/' . $pdfFileName;

        $content = view("mails." . $mailTemplate[$order->payment_mode])->with(['order' => $order, 'urlTerm' => $urlTerm])->render();

        CoreMailsController::sendMail(
            'finanzas@globaltecnologiasacademy.com',
            'Has aceptado los términos y condiciones | Bienvenido a tu curso',
            $content
        );

        $student = Student::where('id', $order->student->id)->with('users')->first();

        CoreMailsController::sendMail(
            $student->email,
            'Has aceptado los términos y condiciones | Bienvenido a tu curso',
            $content
        );
        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => 'Ficha de matriculada confirmada | ' . $order->student->name,
            'body'       => 'El alumno ' . $order->student->name . ' ha confirmado su ficha de matrícula de manera satisfactoria',
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => $student->users[0]->id,
            'use_router' => false,
            'custom_data' => [
                'type'     => 'terms_confirmed_by_student',
                'student'  => $student,
                'order_id' => $order->id
            ]
        ]);

        self::assignStudentToUser($order->student->id);
        $processesController = new ProcessesController();
        $processesController->updateSellsExcel($order->id);
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



    public function assignStudentToUser($student_id, $created_by = null, $orderCriteria = null)
    {

        // return self::getUserWithCount(null, [3, 4]);


        if (!$orderCriteria) {
            $orderCriteria =  [
                ['students_assigned_date_count', 'asc'],
                ['students_assigned_count', 'asc']
            ];
        }


        return Student::where('id', $student_id)
            ->with('orders')->get()->filter(function ($student) {
                return $student->orders->count() > 0;
            })->values()->map(function ($student) {
                $student->role = $student->orders[0]->dues->where('paid', 1)->sum('amount') == $student->orders[0]->price_amount ? 4 : 3;
                $student->role_name = $student->role == 4 ? 'Seguimiento' : 'Cobranza';
                return $student;
            })
            ->filter(function ($student) {
                return $student->start_date;
            })->values()
            ->map(function ($student) use ($created_by, $orderCriteria) {

                $user = self::getUserWithCount($student->start_date, [$student->role], $orderCriteria)->first();
                $student->user_id = $user->id;
                Student::where('id', $student->id)->update(['user_id' => $user->id]);
                DB::table('user_student')->insert([
                    'student_id' => $student->id,
                    'user_id'    => $user->id,
                    'created_by' => $created_by,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                return $student;
            });
    }

    public function getUserWithCount($date = null, $roles = [], $orderCriteria)
    {
        return User::when($roles, function ($query, $roles) {
            return $query->whereIn('role_id', $roles);
        })
            ->withCount('studentsAssigned')->with('students.orders.orderCourses')
            // ->where('role_id', $student->role)
            ->get()->map(function ($user) use ($date) {


                $user->students_assigned_date_count = $user->students->filter(function ($student) use ($date) {
                    if (!$date) {
                        return true;
                    }
                    if ($student->orders->count() > 0) {
                        return $student->orders[0]->orderCourses[0]->start == $date;
                    }
                    return false;
                })->count();
                $user->date = $date;
                unset($user->students);


                return $user;
            })
            ->values()
            ->sortBy($orderCriteria)->values();
    }

    public function delegateAcademicArea(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role_id != 3 && $user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 422);
        }

        $student = Student::with('orders')->where('id', $id)->first();
        if (!$student) {
            return ApiResponseController::response('Alumno no encontrado', 404);
        }


        // Get last order with its dues
        $order = $student->orders->last();
        $dues = $order->dues;


        $duesAmount = $dues->where('paid', 1)->sum('amount');


        if ($duesAmount < $order->price_amount) {
            return ApiResponseController::response('El Alumno no ha pagado la totalidad de la matrícula, no se puede delegar', 400);
        }

        $start_date = $order->orderCourses[0]->start;

        $orderCriteria = null;
        if ($start_date < Carbon::now()->format('Y-m-d')) {
            $orderCriteria = [['students_assigned_count', 'asc']];
        }

        self::assignStudentToUser($id, $user->id, $orderCriteria);



        return ApiResponseController::response('Alumno delegado con éxito', 200);
    }
}
