<?php

namespace App\Console\Commands\crm;

use App\Models\OrderCourse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCourseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        OrderCourse::where('end', '>', now())->with('freezings')->get()->each(function ($orderCourse) {
            $now = Carbon::now();
            $orderCourse->freezings->each(function ($freezing) use ($orderCourse, $now) {
                $start_freezing = Carbon::parse($freezing->start_date);
                $return_freezing = Carbon::parse($freezing->return_date);

                if ($now->between($start_freezing, $return_freezing) && !$freezing->new_return_date) {
                    $orderCourse->classroom_status = 'Congelado';
                    $orderCourse->save();
                }
            });

            if ($orderCourse->classroom_status === 'Congelado') {
                return;
            }

            $start = Carbon::parse($orderCourse->start);
            $end = Carbon::parse($orderCourse->end);

            if ($now->between($start, $end)) {
                $orderCourse->classroom_status = 'Cursando';
                $orderCourse->save();
            }


            if ($now->gt($end)) {
                $orderCourse->classroom_status = 'Finalizado';
                $orderCourse->save();
            }

            if ($now->lt($start)) {
                $orderCourse->classroom_status = 'Por habilitar';
                $orderCourse->save();
            }
        });

        // Log::channel('processes')->info('UpdateCourseStatus executed successfully.');

        return Command::SUCCESS;
    }
}
