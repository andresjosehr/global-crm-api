<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\LeadAssignment;
use App\Models\SaleActivity;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\ZadarmaStatistic;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Api;

class ReportsController extends Controller
{
    public function getAssignmentsByHour(Request $request)
    {
        $user = $request->user();
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $roleAsesorId = 2; // ID del rol de asesor

        // Obtener todos los asesores
        $asesores = User::where('role_id', $roleAsesorId)
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('user_id'));
            })->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('id', $request->user()->id);
            })
            ->where('active', true)
            ->with('projects_pivot')
            ->get();


        $reporte = $asesores->map(function ($asesor) use ($start, $end) {
            // Obtener los lead assignments del asesor, agrupados por día y hora
            $assignmentsPorHora = LeadAssignment::where('user_id', $asesor->id)
                ->whereBetween('assigned_at', [$start->startOfDay()->format('Y-m-d H:i:s'), $end->endOfDay()->format('Y-m-d H:i:s')])
                ->selectRaw('DATE(assigned_at) as fecha, HOUR(assigned_at) as hora, COUNT(*) as value')
                ->groupBy('fecha', 'hora')
                ->get()
                ->groupBy('fecha')
                ->mapWithKeys(function ($item) {
                    return [$item[0]->fecha => $item->keyBy('hora')];
                });

            $datos = [];

            // Iterar sobre cada día en el rango
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $fechaFormato = $date->format('Y-m-d');
                foreach (range(8, 23) as $hora) {
                    $horaKey = str_pad($hora, 2, '0', STR_PAD_LEFT);

                    // Verificar si existen datos para la fecha y hora específicas
                    $value = $assignmentsPorHora[$fechaFormato][$horaKey]->value ?? 0;

                    $datos[] = [
                        'datetime' => $fechaFormato . ' ' . $horaKey . ':00:00',
                        'value' => $value
                    ];
                }
            }
            $count = 0;
            foreach ($assignmentsPorHora as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $count += $value2->value;
                }
            }

            $sells = Order::where('created_by', $asesor->id)
                ->whereBetween('created_at', [$start->startOfDay()->format('Y-m-d H:i:s'), $end->endOfDay()->format('Y-m-d H:i:s')])
                ->count();
            $calls = ZadarmaStatistic::whereBetween('callstart', [$start->startOfDay()->format('Y-m-d H:i:s'), $end->endOfDay()->format('Y-m-d H:i:s')])
                ->where('extension', $asesor->zadarma_id)
                ->count();

            return [
                'id'             => $asesor->id,
                'name'           => $asesor->name,
                'email'          => $asesor->email,
                'calling'        => $asesor->calling,
                'last_call'      => $asesor->last_call,
                'active_working' => $asesor->active_working,
                'role_id'        => $asesor->role_id,
                'count'          => $count,
                'projects_pivot' => $asesor->projects_pivot,
                'data'           => $datos,
                'datica'         => $assignmentsPorHora,
                'sells'          => $sells,
                'calls'          => $calls
            ];
        });

        return ApiResponseController::response("Exito", 200, $reporte);
    }

    public function getCallsByHour(Request $request)
    {
        $user = $request->user();
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $roleAsesorId = 2; // ID del rol de asesor

        // Obtener todos los asesores
        $asesores = User::where('role_id', $roleAsesorId)
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('user_id'));
            })->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('id', $request->user()->id);
            })
            ->with('projects_pivot')
            ->get();


        $reporte = $asesores->map(function ($asesor) use ($start, $end) {
            $count = 0;

            $calls = ZadarmaStatistic::whereHas('user', function ($query) use ($asesor) {
                $query->where('id', $asesor->id);
            })
                ->whereBetween('callstart', [$start, $end])
                ->selectRaw('DATE(callstart) as fecha, HOUR(callstart) as hora, COUNT(*) as value')
                ->groupBy('fecha', 'hora')
                ->get()
                ->groupBy('fecha')
                ->mapWithKeys(function ($item) {
                    return [$item[0]->fecha => $item->keyBy('hora')];
                });

            $datos = [];

            // Iterar sobre cada día en el rango
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $fechaFormato = $date->format('Y-m-d');
                foreach (range(0, 23) as $hora) {
                    $horaKey = str_pad($hora, 2, '0', STR_PAD_LEFT);

                    // Verificar si existen datos para la fecha y hora específicas
                    $value = $calls[$fechaFormato][$horaKey]->value ?? 0;
                    $count += $value;

                    $datos[] = [
                        'datetime' => $fechaFormato . ' ' . $horaKey . ':00:00',
                        'value' => $value
                    ];
                }
            }

            return [
                'id'             => $asesor->id,
                'name'           => $asesor->name,
                'email'          => $asesor->email,
                'active_working' => $asesor->active_working,
                'role_id'        => $asesor->role_id,
                'count'          => $count,
                'projects_pivot' => $asesor->projects_pivot,
                'data'           => $datos
            ];
        });

        return ApiResponseController::response("Exito", 200, $reporte);
    }

    public function getMainStats(Request $request)
    {
        $user = $request->user();

        if ($user->role_id != 1) {
            // Si el rol del usuario no es 1, se permite filtrar por user_id si se proporciona
            $user = $request->has('user_id') ? User::find($request->user_id) : $user;
        } else {
            // Si el rol del usuario es 1 (administrador) y no se proporciona user_id, no se aplican filtros por usuario
            $user = $request->has('user_id') ? User::find($request->user_id) : $user;
        }

        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $activities = SaleActivity::where('type', 'Llamada')
            ->whereBetween('created_at', [$start, $end]);

        // Aplicar filtro por user_id solo si el usuario no es administrador (rol 1)
        if ($user->role_id != 1) {
            $activities->where('user_id', $user->id);
        }

        $activities = $activities->get();

        $totalSeconds = 0;

        foreach ($activities as $activity) {
            $_start = Carbon::parse($activity->start);
            $_end = Carbon::parse($activity->end);

            $totalSeconds += $_end->diffInSeconds($_start);
        }

        $hours = $totalSeconds / 3600;
        $hours = number_format($hours, 2, '.', '');
        // Convert to float
        $hours = floatval($hours);

        $leadCounts = LeadAssignment::whereBetween('assigned_at', [$start, $end]);
        $callsCount = SaleActivity::where('type', 'Llamada')->whereBetween('created_at', [$start, $end]);
        $answeredCallsCount = SaleActivity::where('type', 'Llamada')->where('answered', 1)->whereBetween('created_at', [$start, $end]);

        // Aplicar filtro por user_id solo si el usuario no es administrador (rol 1)
        if ($user->role_id != 1) {
            $leadCounts->where('user_id', $user->id);
            $callsCount->where('user_id', $user->id);
            $answeredCallsCount->where('user_id', $user->id);
        }

        $leadCounts = $leadCounts->count();
        $callsCount = $callsCount->count();
        $answeredCallsCount = $answeredCallsCount->count();

        $callStats = $this->getCallStats($user, $start);
        $minutesStats = $this->minutesStats($user, $start);
        $salesStats = $this->getSalesStats($user, $start, $end);



        return ApiResponseController::response("Exito", 200, $callStats);
    }

    public function getSalesStats(Request $request)
    {
        // Inicializar las consultas sin filtros por ID
        $salesQuery = Order::query();

        // Mes con mayor venta y cantidad de ventas mensuales
        $monthlySales = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_sales')
            ->groupBy('year', 'month')
            ->orderByDesc('total_sales')
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('created_by', $request->input('user_id'));
            })
            ->first();

        $bestMonth = null;
        $bestMonthSales = 0;
        if ($monthlySales) {
            $bestMonth = Carbon::create($monthlySales->year, $monthlySales->month)->format('F');
            $bestMonthSales = $monthlySales->total_sales;
        }

        // Total de ventas del mejor mes
        $totalSales = Order::whereYear('created_at', $monthlySales->year)
            ->whereBetween('created_at', [$request->input('start') . ' 00:00:00', $request->input('end') . ' 23:59:59'])
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('created_by', $request->input('user_id'));
            })
            ->count();

        $salesQuery = Order::withCount(['orderCourses' => function ($query) {
            $query->where('type', 'paid');
        }])
            ->whereBetween('created_at', [$request->input('start') . ' 00:00:00', $request->input('end') . ' 23:59:59'])
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('created_by', $request->input('user_id'));
            })->get();


        $data = [
            'totalSales'     => $totalSales,
            'bestMonth'      => $bestMonth,
            'bestMonthSales' => $bestMonthSales,

            'salesTypes' => [
                'plus_sales' => $salesQuery->where('order_courses_count', 1)->count(),
                'premium_sales' => $salesQuery->where('order_courses_count', 2)->count(),
                'platinum_sales' => $salesQuery->where('order_courses_count', 5)->count()

            ]
        ];

        return ApiResponseController::response("Exito", 200, $data);
    }

    public function minutesStats($user, $date)
    {
        $now = Carbon::now();
        // Consultas con filtro de usuario para roles distintos de 1
        $totalMinutesToday = ZadarmaStatistic::when($user, function ($query) use ($user) {
            $query->where('extension', $user->zadarma_id);
        })
            ->where(DB::raw('DATE(callstart)'), $date)
            ->sum('seconds') / 60;

        $minutesCurrentMonth = ZadarmaStatistic::when($user, function ($query) use ($user) {
            $query->where('extension', $user->zadarma_id);
        })
            ->whereMonth('callstart', $now->month)
            ->sum('seconds') / 60;


        $data = [
            'minutesToday' => (float) number_format($totalMinutesToday, 2, '.', ''),
            'minutesCurrentMonth' => (float) number_format($minutesCurrentMonth, 2, '.', ''),
        ];

        return $data;
    }

    public function getCallStats($user, $date)
    {
        $now = Carbon::now();

        // Consultas con filtro de usuario para roles distintos de 1
        // Obtener llamadas del día de hoy
        $totalCallsToday = ZadarmaStatistic::select('DISTINCT(to)')
            ->when($user, function ($query) use ($user) {
                $query->where('extension', $user->zadarma_id);
            })
            ->whereDate('callstart', $date)
            ->count();

        // Obtener llamadas para el mes actual
        $totalCallsCurrentMonth = ZadarmaStatistic::select('DISTINCT(to)')
            ->when($user, function ($query) use ($user) {
                $query->where('extension', $user->zadarma_id);
            })
            ->whereMonth('callstart', $now->month)
            ->count();

        $data = [
            'totalCallsToday'        => $totalCallsToday,
            'totalCallsCurrentMonth' => $totalCallsCurrentMonth,
        ];
        return $data;
    }



    public function getStatsPerDay(Request $request)
    {
        $user = $request->user_id ? User::find($request->user_id) : null;

        $days = ["Monday" => "Lunes", "Tuesday" => "Martes", "Wednesday" => "Miércoles", "Thursday" => "Jueves", "Friday" => "Viernes", "Saturday" => "Sábado", "Sunday" => "Domingo"];
        $user = $request->user_id != 'null' ? User::find($request->user_id) : $request->user();
        $callsPerDay = ZadarmaStatistic::select(
            DB::raw('DATE(callstart) as date'),
            DB::raw('COUNT(*) as value')
        )
            ->groupBy(DB::raw('DATE(callstart)')) // Agrupar también por 'to'
            ->whereBetween('callstart', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->when($user, function ($query) use ($user) {
                return $query->where('extension', $user->zadarma_id);
            })
            ->get()
            ->toArray();

        $sellsPerDay = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as value')
        )
            ->when($user, function ($query) use ($user) {
                return $query->where('created_by', $user->id);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->whereBetween('created_at', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->get()->toArray();



        // Days between the start and end date
        $daysBetween = CarbonPeriod::create($request->start, $request->end);
        $daysBetween = count(iterator_to_array($daysBetween));

        $date = Carbon::parse($request->start);
        for ($i = 0; $i < $daysBetween; $i++) {
            if (!in_array($date->toDateString(), array_column($callsPerDay, 'date'))) {
                $callsPerDay[] = ['date' => $date->toDateString(), 'value' => 0];
            }

            if (!in_array($date->toDateString(), array_column($sellsPerDay, 'date'))) {
                $sellsPerDay[] = ['date' => $date->toDateString(), 'value' => 0];
            }

            // Attach the name of the day in spanish
            $callsPerDay[array_search($date->toDateString(), array_column($callsPerDay, 'date'))]['day'] = $days[$date->format('l')];
            $date->addDay();
        }

        usort($callsPerDay, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        usort($sellsPerDay, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });


        // Crear el array de datos de respuesta
        $data = [
            'callsPerDay' => $callsPerDay,
            'sellsPerDay' => $sellsPerDay
        ];

        // Retornar la respuesta en formato JSON
        return ApiResponseController::response("Éxito", 200, $data);
    }

    public function getStatsPerHour(Request $request)
    {
        $user = $request->user_id ? User::find($request->user_id) : null;



        // Calls per hour
        $callsPerHour = ZadarmaStatistic::select(
            DB::raw('CONCAT(MAX(DATE(callstart)), " ", MAX(HOUR(callstart)), ":00:00") AS datetime'),
            DB::raw('MAX(DATE(callstart)) as date'),
            DB::raw('HOUR(callstart) as hour'),
            DB::raw('COUNT(*) as value')
        )
            ->groupBy(DB::raw('DATE(callstart)'), DB::raw('HOUR(callstart)'))
            ->whereDate('callstart', $request->date)
            ->when($user, function ($query) use ($user) {
                return $query->where('extension', $user->zadarma_id);
            })
            ->orderByRaw('MAX(callstart)')
            ->get()->each(function ($item) {
                $item->datetime = Carbon::parse($item->datetime)->format('Y-m-d H:i:s');
            })->toArray();

        // Sells per hour
        $minutesPerHour = ZadarmaStatistic::select(
            DB::raw('CONCAT(MAX(DATE(callstart)), " ", MAX(HOUR(callstart)), ":00:00") AS datetime'),
            DB::raw('MAX(DATE(callstart)) as date'),
            DB::raw('HOUR(callstart) as hour'),
            DB::raw('SUM(seconds) as value')
        )
            ->groupBy(DB::raw('DATE(callstart)'), DB::raw('HOUR(callstart)'))
            ->whereDate('callstart', $request->date)
            ->when($user, function ($query) use ($user) {
                return $query->where('extension', $user->zadarma_id);
            })
            ->get()->each(function ($item) {
                $item->datetime = Carbon::parse($item->datetime)->format('Y-m-d H:i:s');
            })->toArray();



        $date = Carbon::parse($request->date);
        for ($i = 0; $i < 1; $i++) {
            $hours = range(8, 22);
            foreach ($hours as $hour) {
                $datetime = $date->format('Y-m-d') . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                if (!in_array($datetime, array_column($callsPerHour, 'datetime'))) {
                    $callsPerHour[] = ['datetime' => $datetime, 'date' => $date->toDateString(), 'hour' => $hour, 'value' => 0];
                }
                if (!in_array($datetime, array_column($minutesPerHour, 'datetime'))) {
                    $minutesPerHour[] = ['datetime' => $datetime, 'date' => $date->toDateString(), 'hour' => $hour, 'value' => 0];
                }
            }
            $date->addDay();
        }

        usort($callsPerHour, function ($a, $b) {
            return strtotime($a['datetime']) - strtotime($b['datetime']);
        });

        usort($minutesPerHour, function ($a, $b) {
            return strtotime($a['datetime']) - strtotime($b['datetime']);
        });

        // Conert seonds to minutes
        foreach ($minutesPerHour as $key => $value) {
            // fixed to 2 decimal places
            $minutesPerHour[$key]['value'] = number_format($value['value'] / 60, 2, '.', '');
        }

        $data = [
            'callsPerHour'   => $callsPerHour,
            'minutesPerHour' => $minutesPerHour,
            'callStats'      => $this->getCallStats($user, $request->date),
            'minutesStats'   => $this->minutesStats($user, $request->date)
        ];

        return ApiResponseController::response("Éxito", 200, $data);
    }



    public function getSalesAndCalls($user, $start, $end)
    {
        // Inicializar arrays para almacenar los datos de ventas y llamadas por día
        $salesPerDay = [];
        $callsPerDay = [];

        // Obtener el rango de fechas según los parámetros proporcionados o la semana actual
        if ($start && $end) {
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        }

        // Obtener los registros de llamadas del usuario dentro del rango de fechas
        $calls = LeadAssignment::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Iterar sobre los registros de llamadas y llenar el array con los datos por día
        foreach ($calls as $call) {
            $date = Carbon::parse($call->created_at)->toDateString();
            if (!isset($callsPerDay[$date])) {
                $callsPerDay[$date] = 0;
            }
            $callsPerDay[$date]++;
        }

        // Obtener los registros de ventas del usuario dentro del rango de fechas desde la tabla orders
        $sales = Order::where('created_by', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Iterar sobre los registros de ventas y llenar el array con los datos por día
        foreach ($sales as $sale) {
            $date = Carbon::parse($sale->created_at)->toDateString();
            if (!isset($salesPerDay[$date])) {
                $salesPerDay[$date] = 0;
            }
            $salesPerDay[$date]++;
        }

        // Completar las fechas faltantes con 0 llamadas y 0 ventas
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->toDateString();
            if (!isset($callsPerDay[$dateString])) {
                $callsPerDay[$dateString] = 0;
            }
            if (!isset($salesPerDay[$dateString])) {
                $salesPerDay[$dateString] = 0;
            }
            $currentDate->addDay();
        }

        // Preparar los datos en el formato deseado
        $formattedCalls = [];
        $formattedSales = [];
        foreach ($callsPerDay as $date => $count) {
            $formattedCalls[] = ['x' => $date, 'y' => $count];
        }
        foreach ($salesPerDay as $date => $count) {
            $formattedSales[] = ['x' => $date, 'y' => $count];
        }

        // Ordenar los arrays por fecha
        usort($formattedCalls, function ($a, $b) {
            return strtotime($a['x']) - strtotime($b['x']);
        });
        usort($formattedSales, function ($a, $b) {
            return strtotime($a['x']) - strtotime($b['x']);
        });

        return [
            'calls' => $formattedCalls,
            'sales' => $formattedSales,
        ];
    }
}
