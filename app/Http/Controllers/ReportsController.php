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

        $callStats = $this->getCallStats($user, $start, $end);
        $minutesStats = $this->minutesStats($user, $start, $end);
        $salesStats = $this->getSalesStats($user, $start, $end);
        $categoriesStats = $this->getSalesCategoriesStats($user, $start, $end);

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


    public function getSalesCategoriesStats($user, $start, $end)
    {
        $salesQuery = Order::withCount(['orderCourses' => function ($query) {
            $query->where('type', 'paid');
        }])
            ->whereBetween('created_at', [$start, $end]);

        // Aplicar filtro por user_id solo si el usuario no es administrador (rol 1)
        if ($user->role_id != 1) {
            $salesQuery->where('created_by', $user->id);
        }

        $sales = $salesQuery->get();

        return [
            'orders' => $sales,
            'plus_sales' => $sales->where('order_courses_count', 1)->count(),
            'premium_sales' => $sales->where('order_courses_count', 2)->count(),
            'platinum_sales' => $sales->where('order_courses_count', 5)->count()
        ];
    }




    public function getSalesStats($user, $start, $end)
    {
        // Inicializar las consultas sin filtros por ID
        $salesQuery = Order::query();

        // Si el usuario no es administrador (rol_id != 1), aplicar filtro por ID
        if ($user->role_id != 1) {
            $salesQuery->where('created_by', $user->id);
        }

        // Ventas en el mes actual
        $totalSalesThisMonth = $salesQuery
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Mes con mayor venta y cantidad de ventas mensuales
        $monthlySales = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_sales')
            ->groupBy('year', 'month')
            ->orderByDesc('total_sales')
            ->first();

        $bestMonth = null;
        $bestMonthSales = 0;
        if ($monthlySales) {
            $bestMonth = Carbon::create($monthlySales->year, $monthlySales->month)->format('F');
            $bestMonthSales = $monthlySales->total_sales;
        }

        // Total de ventas del mejor mes
        $totalBestMonthSales = Order::whereYear('created_at', $monthlySales->year)
            ->whereMonth('created_at', $monthlySales->month)
            ->count();

        // Media de ventas mensuales

        $totalMonthlySales = $salesQuery
            ->selectRaw('COUNT(*) as total_sales')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->pluck('total_sales')
            ->sum();

        // Media de ventas mensuales
        $averageMonthlySales = $totalMonthlySales / Carbon::now()->month;


        return [
            // 'objetiveCalls' => $objectiveCalls,
            'totalSalesThisMonth' => $totalSalesThisMonth,
            'bestMonth' => $bestMonth,
            'bestMonthSales' => $bestMonthSales,
            'totalBestMonthSales' => $totalBestMonthSales,
            'averageMonthlySales' => (float) number_format($averageMonthlySales, 2, '.', ''),
        ];
    }

    public function minutesStats($user, $start, $end)
    {
        // Consultas sin filtro de usuario si el rol es 1 (administrador)
        if ($user->role_id == 1) {
            $totalMinutesToday = ZadarmaStatistic::where(DB::raw('DATE(callstart)'), Carbon::now()->format('Y-m-d'))
                ->sum('seconds') / 60;

            $minutesCurrentMonth = ZadarmaStatistic::whereBetween('callstart', [$start, $end])
                ->sum('seconds') / 60;

            $averageMinutesCurrentMonth = ZadarmaStatistic::whereBetween('callstart', [$start, $end])
                ->where('seconds', '>', 0)
                ->selectRaw('AVG(seconds) as average')
                ->first()
                ->average / 60;

            // Si no se encontraron datos, establecer el promedio en 0

        } else {
            // Consultas con filtro de usuario para roles distintos de 1
            $totalMinutesToday = ZadarmaStatistic::where('extension', $user->zadarma_id)
                ->where(DB::raw('DATE(callstart)'), Carbon::now()->format('Y-m-d'))
                ->sum('seconds') / 60;

            $minutesCurrentMonth = ZadarmaStatistic::where('extension', $user->zadarma_id)
                ->whereBetween('callstart', [$start, $end])
                ->sum('seconds') / 60;

            $averageMinutesCurrentMonth = ZadarmaStatistic::where('extension', $user->zadarma_id)
                ->whereBetween('callstart', [$start, $end])
                ->where('seconds', '>', 0)
                ->selectRaw('AVG(seconds) as average')
                ->first()
                ->average / 60;
        }

        return [
            'minutesToday' => (float) number_format($totalMinutesToday, 2, '.', ''),
            'minutesCurrentMonth' => (float) number_format($minutesCurrentMonth, 2, '.', ''),
            'averageMinutesCurrentMonth' => (float) number_format($averageMinutesCurrentMonth, 2, '.', ''),
        ];
    }

    public function getCallStats($user, $start, $end)
    {
        $now = Carbon::now();

        // Consultas sin filtro de usuario si el rol es 1 (administrador)
        if ($user->role_id == 1) {
            // Obtener llamadas del día de hoy
            $totalCallsToday = ZadarmaStatistic::select('DISTINCT(to)')
                ->whereDate('callstart', $now->format('Y-m-d'))
                ->count();

            // Obtener llamadas para el mes actual
            $totalCallsCurrentMonth = ZadarmaStatistic::select('DISTINCT(to)')
                ->whereBetween('callstart', [$start, $end])
                ->count();

            // Obtener llamadas para el mes anterior
            $averageCallsPerDayCurrentMonth = ZadarmaStatistic::selectRaw('DATE(callstart) as date, COUNT(DISTINCT(`destination`)) as total')
                ->whereBetween('callstart', [$start, $end])
                ->groupBy('date')
                ->get()->pluck('total');

            if ($averageCallsPerDayCurrentMonth->count() > 0) {
                $averageCallsPerDayCurrentMonth = $averageCallsPerDayCurrentMonth->sum() / $averageCallsPerDayCurrentMonth->count();
            } else {
                $averageCallsPerDayCurrentMonth = 0;
            }
        } else {
            // Consultas con filtro de usuario para roles distintos de 1
            // Obtener llamadas del día de hoy
            $totalCallsToday = ZadarmaStatistic::select('DISTINCT(to)')
                ->where('extension', $user->zadarma_id)
                ->whereDate('callstart', $now->format('Y-m-d'))
                ->count();

            // Obtener llamadas para el mes actual
            $totalCallsCurrentMonth = ZadarmaStatistic::select('DISTINCT(to)')
                ->where('extension', $user->zadarma_id)
                ->whereBetween('callstart', [$start, $end])
                ->count();

            // Obtener llamadas para el mes anterior
            $averageCallsPerDayCurrentMonth = ZadarmaStatistic::selectRaw('DATE(callstart) as date, COUNT(DISTINCT(`destination`)) as total')
                ->where('extension', $user->zadarma_id)
                ->whereBetween('callstart', [$start, $end])
                ->groupBy('date')
                ->get()->pluck('total');

            if ($averageCallsPerDayCurrentMonth->count() > 0) {
                $averageCallsPerDayCurrentMonth = $averageCallsPerDayCurrentMonth->sum() / $averageCallsPerDayCurrentMonth->count();
            } else {
                $averageCallsPerDayCurrentMonth = 0;
            }
        }

        return [
            'totalCallsToday'                => $totalCallsToday,
            'totalCallsCurrentMonth'         => $totalCallsCurrentMonth,
            'averageCallsPerDayCurrentMonth' => (float) number_format($averageCallsPerDayCurrentMonth, 2, '.', ''),
        ];
    }



    public function getCallsAndSalesPerWeek(Request $request)
    {
        $days = ["Monday" => "Lunes", "Tuesday" => "Martes", "Wednesday" => "Miércoles", "Thursday" => "Jueves", "Friday" => "Viernes", "Saturday" => "Sábado", "Sunday" => "Domingo"];
        $user = $request->has('user_id') ? User::find($request->user_id) : $request->user();
        $callsPerDay = ZadarmaStatistic::select(
            DB::raw('DATE(callstart) as date'),
            DB::raw('COUNT(*) as value')
        )
            ->groupBy(DB::raw('DATE(callstart)')) // Agrupar también por 'to'
            ->whereBetween('callstart', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->where('extension', $user->zadarma_id);
            })
            ->get()
            ->toArray();

        $sellsPerDay = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as value')
        )
            ->when($user->role_id != 1, function ($query) use ($user) {
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

        // created_at

        // Calls per hour
        Log::info("Start: " . $request->start . ' 00:00:00');
        Log::info("End: " . $request->end . ' 23:59:59');
        $callsPerHour = ZadarmaStatistic::select(
            DB::raw('CONCAT(MAX(DATE(callstart)), " ", MAX(HOUR(callstart)), ":00:00") AS datetime'),
            DB::raw('MAX(DATE(callstart)) as date'),
            DB::raw('HOUR(callstart) as hour'),
            DB::raw('COUNT(*) as value')
        )
            ->groupBy(DB::raw('DATE(callstart)'), DB::raw('HOUR(callstart)'))
            ->whereBetween('callstart', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->when($user->role_id != 1, function ($query) use ($user) {
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
            ->whereBetween('callstart', [$request->start . ' 00:00:00', $request->end . ' 23:59:59'])
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->where('extension', $user->zadarma_id);
            })
            ->get()->each(function ($item) {
                $item->datetime = Carbon::parse($item->datetime)->format('Y-m-d H:i:s');
            })->toArray();



        $date = Carbon::parse($request->start);
        for ($i = 0; $i < $daysBetween; $i++) {
            $hours = range(8, 22);
            foreach ($hours as $hour) {
                $datetime = $date->format('Y-m-d') . ' ' . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00:00';
                Log::info($datetime);
                if (!in_array($datetime, array_column($callsPerHour, 'datetime'))) {
                    $callsPerHour[] = ['datetime' => $datetime, 'date' => $date->toDateString(), 'hour' => $hour, 'value' => 0];
                }
                if (!in_array($datetime, array_column($minutesPerHour, 'datetime'))) {
                    $minutesPerHour[] = ['datetime' => $datetime, 'date' => $date->toDateString(), 'hour' => $hour, 'value' => 0];
                }
            }
            Log::info('-------------------');
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





        // Crear el array de datos de respuesta
        $data = [
            'callsPerDay' => $callsPerDay,
            'sellsPerDay' => $sellsPerDay,
            'callsPerHour' => $callsPerHour,
            'minutesPerHour' => $minutesPerHour
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
