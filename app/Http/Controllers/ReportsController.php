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

            return [
                'id'             => $asesor->id,
                'name'           => $asesor->name,
                'email'          => $asesor->email,
                'active_working' => $asesor->active_working,
                'role_id'        => $asesor->role_id,
                'count'          => $count,
                'projects_pivot' => $asesor->projects_pivot,
                'data'           => $datos,
                'datica'         => $assignmentsPorHora
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

            $calls = SaleActivity::where('user_id', $asesor->id)
                ->where('type', 'Llamada')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as fecha, HOUR(created_at) as hora, COUNT(*) as value')
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
        if ($request->user_id) {
            $user = User::find($request->user_id);
        } else {
            $user = $request->user();
        }

        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $activities = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->where('type', 'Llamada')
            ->whereBetween('created_at', [$start, $end])
            ->get();

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



        $leadCounts = LeadAssignment::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id == 1, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->whereBetween('assigned_at', [$start, $end])
            ->count();

        $callsCount = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->where('type', 'Llamada')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $answeredCallsCount = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->where('type', 'Llamada')
            ->where('answered', 1) // Filtrar solo las llamadas contestadas
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $callStats = $this->getCallStats($user);

        $minutesStats = $this->minutesStats($user, $start, $end);

        $salesStats = $this->getSalesStats($user);

        $categoriesStats = $this->getSalesCategoriesStats($user);

        $data = [
            'hoursInCall'        => $hours,
            'leadCounts'         => $leadCounts,
            'callsCount'         => $callsCount,
            'answeredCallsCount' => $answeredCallsCount,
            'categoriesSales'    => $categoriesStats,
            'salesStats'         => $salesStats,
            'minutesStats'       => $minutesStats,
            'callsStats'         => $callStats,
            'user_id'            => $user->id
        ];



        return ApiResponseController::response("Exito", 200, $data);
    }

    public function getSalesCategoriesStats($user)
    {
        $sales = Order::withCount(['orderCourses' => function ($query) {
            $query->where('type', 'paid');
        }])
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('created_by', $user->id)
            ->get();


        return [
            'orders' => $sales,
            'plus_sales' => $sales->where('order_courses_count', 1)->count(),
            'premium_sales' => $sales->where('order_courses_count', 2)->count(),
            'platinum_sales' => $sales->where('order_courses_count', 5)->count(),

        ];
    }



    public function getSalesStats($user)
    {
        // Ventas en el mes actual
        $totalSalesThisMonth = Order::where('created_by', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Mes con mayor venta y cantidad de ventas mensuales
        $monthlySales = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_sales')
            ->where('created_by', $user->id)
            ->groupBy('year', 'month')
            ->orderByDesc('total_sales')
            ->first();

        $bestMonth = null;
        $bestMonthSales = 0;
        if ($monthlySales) {
            $bestMonth = Carbon::create($monthlySales->year, $monthlySales->month)->format('F');
            $bestMonthSales = $monthlySales->total_sales;
        }

        $totalMonthlySales = Order::where('created_by', $user->id)
            ->selectRaw('COUNT(*) as total_sales')
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->pluck('total_sales')
            ->sum();

        // Media de ventas mensuales
        $averageMonthlySales = $totalMonthlySales / Carbon::now()->month;

        $initialObjective = 40;
        $maxObjective = 60;
        $objectiveCalls = $initialObjective;

        // Verificar si se ha alcanzado el objetivo actual y no hemos alcanzado el máximo
        if ($totalSalesThisMonth >= $objectiveCalls && $objectiveCalls < $maxObjective) {
            // Incrementar el objetivo para el próximo nivel, pero asegúrate de no pasar del máximo
            $objectiveCalls += 10;
            if ($objectiveCalls > $maxObjective) {
                $objectiveCalls = $maxObjective;
            }
        }

        return [
            'objetiveCalls' => $objectiveCalls,
            'totalSalesThisMonth' => $totalSalesThisMonth,
            'bestMonth' => $bestMonth,
            'bestMonthSales' => $bestMonthSales,
            'averageMonthlySales' => (float) number_format($averageMonthlySales, 2, '.', ''),
        ];
    }


    public function minutesStats($user, $start, $end)
    {
        $totalMinutesToday = ZadarmaStatistic::where('extension', $user->zadarma_id)
            ->where(DB::raw('DATE(callstart)'), Carbon::now()->format('Y-m-d'))
            ->sum('billseconds') / 60;

        $minutesCurrentMonth = ZadarmaStatistic::where('extension', $user->zadarma_id)
            ->whereBetween('callstart', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('billseconds') / 60;

        $averageMinutesCurrentMonth = ZadarmaStatistic::where('extension', $user->zadarma_id)
            ->whereBetween('callstart', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->where('billseconds', '>', 0)
            ->selectRaw('AVG(billseconds) as average')->first()->average / 60;

        return [
            'minutesToday' => (float) number_format($totalMinutesToday, 2, '.', ''),
            'minutesCurrentMonth' => (float) number_format($minutesCurrentMonth, 2, '.', ''),
            'averageMinutesCurrentMonth' => (float) number_format($averageMinutesCurrentMonth, 2, '.', ''),
        ];
    }


    public function getCallStats($user)
    {
        $now = Carbon::now();

        // Obtener llamadas del día de hoy
        $totalCallsToday = ZadarmaStatistic::select('DISTINCT(to)')
            ->where('extension', $user->zadarma_id)
            ->whereDate('callstart', $now->format('Y-m-d'))
            ->count();

        // Obtener llamadas para el mes actual
        $totalCallsCurrentMonth = ZadarmaStatistic::select('DISTINCT(to)')
            ->where('extension', $user->zadarma_id)
            ->whereBetween('callstart', [$now->startOfMonth()->format('Y-m-d H:i:s'), $now->endOfMonth()->format('Y-m-d H:i:s')])
            ->count();

        // Obtener llamadas para el mes anterior
        $averageCallsPerDayCurrentMonth = ZadarmaStatistic::selectRaw('DATE(callstart) as date, COUNT(DISTINCT(`to`)) as total')
            ->where('extension', $user->zadarma_id)
            ->whereBetween('callstart', [$now->startOfMonth()->format('Y-m-d H:i:s'), $now->endOfMonth()->format('Y-m-d H:i:s')])
            ->groupBy('date')
            ->get()->pluck('total');

        $averageCallsPerDayCurrentMonth = $averageCallsPerDayCurrentMonth->sum() / $averageCallsPerDayCurrentMonth->count();


        return [
            'totalCallsToday'                => $totalCallsToday,
            'totalCallsCurrentMonth'         => $totalCallsCurrentMonth,
            'averageCallsPerDayCurrentMonth' => (float) number_format($averageCallsPerDayCurrentMonth, 2, '.', ''),
        ];
    }

    public function getCallsAndSalesPerWeek(Request $request)
    {

        $days = ["Monday" => "Lunes", "Tuesday" => "Martes", "Wednesday" => "Miércoles", "Thursday" => "Jueves", "Friday" => "Viernes", "Saturday" => "Sábado", "Sunday" => "Domingo"];
        $user = $request->user();

        if ($user->role_id == 1) {
            $user = User::find($request->user_id);
        }

        $lastweek = Carbon::now()->subWeek();
        $callsPerDay = ZadarmaStatistic::select(
            DB::raw('DATE(callstart) as date'),
            DB::raw('COUNT(*) as value'),
            // 'to' // Asegúrate de incluir la columna 'to' aquí
        )
            ->where('extension', $user->zadarma_id)
            ->distinct('to') // Usa 'distinct' en la columna 'to'
            ->groupBy(DB::raw('DATE(callstart)')) // Agrupa también por 'to'
            ->whereBetween('callstart', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->get()
            ->toArray();

        $sellsPerDay = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as value'))
            ->where('created_by', $user->id)
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
            'sellsPerDay' => $sellsPerDay,
        ];

        // Retornar la respuesta en formato JSON
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
