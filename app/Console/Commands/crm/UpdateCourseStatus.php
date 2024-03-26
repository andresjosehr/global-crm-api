<?php

namespace App\Console\Commands\crm;

use App\Models\OrderCourse;
use App\Models\Wordpress\WpLearnpressUserItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        OrderCourse::with('freezings')->get()->each(function ($orderCourse) {

            if ($orderCourse->start === null || $orderCourse->end === null) {
                $orderCourse->classroom_status = 'Por habilitar';
                $orderCourse->save();
                return;
            }

            if ($orderCourse->classroom_status == 'Abandonó' || $orderCourse->classroom_status == 'No se habilitó') {
                return;
            }

            if (Carbon::now()->lt(Carbon::parse($orderCourse->start))) {
                $orderCourse->classroom_status = 'Por habilitar';
                $orderCourse->save();
                return;
            }


            // if start date is after now and course status is congelado
            if(Carbon::now()->lt(Carbon::parse($orderCourse->start)) && $orderCourse->classroom_status == 'Congelado') {
                return;
            }

            if (Carbon::now()->between(Carbon::parse($orderCourse->start), Carbon::parse($orderCourse->end))) {

                if ($orderCourse->freezings->count() > 0) {
                    $lastFreezing = $orderCourse->freezings->last();
                    if (Carbon::now()->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->return_date))) {
                        $orderCourse->classroom_status = 'Congelado';
                        $orderCourse->save();
                        return;
                    }
                }


                if ($orderCourse->freezings->count() == 0) {
                    $orderCourse->classroom_status = 'Cursando';
                    $orderCourse->save();
                    return;
                }
            }

            if (Carbon::now()->gt(Carbon::parse($orderCourse->end))) {
                $orderCourse->classroom_status = 'Finalizado';
                $orderCourse->save();
                return;
            }


            if(Carbon::now()->between(Carbon::parse($orderCourse->start), Carbon::parse($orderCourse->end))){
                $orderCourse->classroom_status = 'Cursando';
                $orderCourse->save();
                return;
            }
        });

        // Log::channel('processes')->info('UpdateCourseStatus executed successfully.');

        return Command::SUCCESS;
    }
}
