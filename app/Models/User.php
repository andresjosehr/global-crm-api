<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function checkPassword($password)
    {
        return Hash::check($password, $this->password);
    }



    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function getModules()
    {
        $modules = $this->role->modules;

        $parentModules = $modules->filter(function ($module) {
            return is_null($module->parent_id);
        });

        $childModules = $modules->filter(function ($module) {
            return !is_null($module->parent_id);
        });

        $organizedModules = $parentModules->map(function ($parentModule) use ($childModules) {
            $parentModule->childs = $childModules->filter(function ($childModule) use ($parentModule) {
                return $childModule->parent_id == $parentModule->id;
            })->values();  // Reset array keys

            return $parentModule;
        })->toArray();

        return array_values($organizedModules);
    }


    function sap_instalation()
    {
        return $this->hasMany(SapInstalation::class, 'staff_id');
    }


    public function getUnavailableTimesAttribute()
    {
        $availability = $this->availabilitySlots()
            ->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');

        $unavailableTimes = [];

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            if ($availability->has($day)) {
                $unavailableTimes[$day] = [];

                $previousEnd = Carbon::parse('00:00:00');

                foreach ($availability[$day] as $slot) {
                    $start = Carbon::parse($slot->start_time);
                    $end = Carbon::parse($slot->end_time);

                    if ($previousEnd->lt($start)) {
                        $unavailableTimes[$day][] = [
                            'start_time' => $previousEnd->toTimeString(),
                            'end_time' => $start->toTimeString(),
                        ];
                    }

                    $previousEnd = $end->gt($previousEnd) ? $end : $previousEnd;
                }

                if ($previousEnd->lt(Carbon::parse('23:59:59'))) {
                    $unavailableTimes[$day][] = [
                        'start_time' => $previousEnd->toTimeString(),
                        'end_time' => Carbon::parse('23:59:59')->toTimeString(),
                    ];
                }
            }
        }

        return $unavailableTimes;
    }

    public function getBussyTimesAttribute()
    {
        // Obtén las asignaciones de este usuario
        $assignments = $this->hasMany(SapInstalation::class, 'staff_id')
            ->select([
                DB::raw('DATE(start_datetime) as date'),
                DB::raw('TIME(start_datetime) as start_time'),
                DB::raw('TIME(end_datetime) as end_time')
            ])
            ->get()
            ->groupBy('date');

        // Formatea los resultados
        $busyTimes = [];
        foreach ($assignments as $date => $times) {
            // Agrupa los tiempos por bloques de media hora
            $timeBlocks = $times->groupBy(function ($date) {
                return $date->start_time . '-' . $date->end_time;
            });

            // Filtra los bloques de tiempo que tienen menos de 2 asignaciones
            $filteredTimeBlocks = $timeBlocks->filter(function ($block) {
                return count($block) >= 2;
            });

            // Si hay bloques de tiempo ocupados para esta fecha, los añadimos al array resultante
            if (!$filteredTimeBlocks->isEmpty()) {
                $busyTimes[$date] = $filteredTimeBlocks->map(function ($block) {
                    return [
                        'start_time' => $block->first()->start_time,
                        'end_time' => $block->first()->end_time,
                    ];
                })->values()->toArray();
            }
        }

        return $busyTimes;
    }

    public function getBussyTimesForCalculateAttribute()
    {
        // Obtén las asignaciones de este usuario
        $assignments = SapTry::where('staff_id', $this->id)
            ->select([
                DB::raw('MAX(id) as id')
            ])
            ->groupBy('sap_instalation_id')
            ->get()->pluck('id')->toArray();

        $assignments = SapTry::whereIn('id', $assignments)->get();


        $busyTimes = $assignments->reduce(function ($carry, $block) {
            $date = Carbon::parse($block->start_datetime)->format('Y-m-d');
            if (!isset($carry[$date])) {
                $carry[$date] = [];
            }
            $start_time = Carbon::parse($block->start_datetime)->format('H:i:s');
            $end_time = Carbon::parse($block->end_datetime)->format('H:i:s');
            $carry[$date][] = [
                'start_time' => $start_time,
                'end_time' => $end_time,
            ];
            return $carry;
        }, []);

        return $busyTimes;
    }


    public function getAvailableTimesForDate($date, $datesBussy = [])
    {
        // Convertir la fecha a un objeto Carbon
        $date = Carbon::parse($date);

        // Obtener el nombre del día en inglés en minúscula para ese día
        $dayName = strtolower($date->format('l'));

        // Iniciar con todos los intervalos de 30 minutos del día como disponibles
        $intervals = [];
        for ($time = Carbon::parse('00:00:00'); $time < Carbon::parse('24:00:00'); $time->addMinutes(30)) {
            $intervals[] = [
                'start_time' => $time->toTimeString(),
                'end_time' => (clone $time)->addMinutes(30)->toTimeString(),
            ];
        }


        // Obtener los tiempos ocupados y no disponibles para ese día
        $busyTimesForDay = data_get($this->append('bussyTimesForCalculate')->bussyTimesForCalculate, $date->format('Y-m-d'), []);
        $unavailableTimesForDay = data_get($this->unavailableTimes, $dayName, []);


        Log::info($this->append('bussyTimesForCalculate')->bussyTimesForCalculate);

        // Adicionar los tiempos ocupados enviados desde el frontend
        $additionalBusyTimes = data_get($datesBussy, $date->format('Y-m-d'), []);

        // Fusionar y contar las ocurrencias de cada intervalo de tiempo
        $mergedBusyTimes = array_merge(array_column($busyTimesForDay, 'start_time'), $additionalBusyTimes);

        Log::info($mergedBusyTimes);



        $timeOccurrences = array_count_values($mergedBusyTimes);

        // Filtrar los intervalos de tiempo donde las ocurrencias son mayores o iguales a 2
        $finalBusyTimes = array_filter($timeOccurrences, function ($occurrence) {
            return $occurrence >= 2;
        });

        // Función para verificar si un intervalo de tiempo está ocupado o no disponible
        $isIntervalUnavailable = function ($interval) use ($finalBusyTimes, $unavailableTimesForDay) {
            $startTime = Carbon::parse($interval['start_time']);
            $endTime = Carbon::parse($interval['end_time']);
            if (isset($finalBusyTimes[$interval['start_time']])) {
                return true;
            }
            foreach ($unavailableTimesForDay as $unavailableInterval) {
                $unavailableStart = Carbon::parse($unavailableInterval['start_time']);
                $unavailableEnd = Carbon::parse($unavailableInterval['end_time']);
                if ($startTime->lt($unavailableEnd) && $endTime->gt($unavailableStart)) {
                    return true;
                }
            }
            return false;
        };

        // Filtrar los intervalos de tiempo para quedarse solo con los disponibles
        $availableIntervals = array_filter($intervals, function ($interval) use ($isIntervalUnavailable) {
            return !$isIntervalUnavailable($interval);
        });

        return array_values($availableIntervals);
    }


    public function availabilitySlots()
    {
        return $this->hasMany(StaffAvailabilitySlot::class);
    }

    public function leadAssignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function projects()
    {
        return $this->belongsToMany(LeadProject::class, 'user_lead_projects', 'user_id', 'lead_project_id');
    }

    public function projects_pivot()
    {
        return $this->hasMany(UserLeadProject::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'user_student', 'user_id', 'student_id');
    }

    public function zadarmaStatistics()
    {
        return $this->hasMany(ZadarmaStatistic::class, 'extension', 'extension');
    }
}
