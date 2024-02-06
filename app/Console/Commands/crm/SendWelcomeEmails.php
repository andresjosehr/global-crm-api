<?php

namespace App\Console\Commands\crm;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\Car;
use App\Models\Holiday;
use App\Models\OrderCourse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmails extends Command
{
    public $emailTemplates = [
        1  => 'sap-welcome',
        2  => 'sap-welcome',
        3  => 'sap-welcome',
        4  => 'sap-welcome',
        5  => 'integral-welcome',
        6  => 'excel-welcome',
        7  => 'powerbi-welcome',
        8  => 'powerbi-welcome',
        9  => 'msproject-welcome',
        10 => 'sap-welcome',
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-welcome-emails';

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

        $sendDate = Carbon::tomorrow();


        $holidays = Holiday::all()->pluck('date')->toArray();
        while (in_array($sendDate->toDateString(), $holidays) || $sendDate->dayOfWeek === 0) {
            $sendDate->addDay();
        }

        OrderCourse::where('start', $sendDate->format('Y-m-d'))
            ->with('course', 'certificationTests', 'freezings', 'sapInstalations', 'dateHistory', 'order.dues', 'order.student', 'order.currency', 'order.price', 'order.student')
            ->get()->each(function ($orderCourse) {

                $course_id = $orderCourse->course->id;
                $subject = '-Bienvenido(a) a tu curso de ' . $orderCourse->course->name . ' ¡Global Tecnologías Academy!';
                $content = view("mails." . $this->emailTemplates[$course_id])->with(['orderCourse' => $orderCourse])->render();
                $scheduleTime = Carbon::parse($orderCourse->start)->format('m/d/Y');

                $message = CoreMailsController::sendMail($orderCourse->order->student->email, $subject, $content, $scheduleTime);
                // Conver stdclass to array
                OrderCourse::where('id', $orderCourse->id)->update(['welcome_mail_id' => $message->messageId]);
            });

        return Command::SUCCESS;
    }
}
