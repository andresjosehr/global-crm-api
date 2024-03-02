<?php

namespace App\Console\Commands\Liveconnect;

use App\Http\Services\LiveConnectService;
use App\Jobs\GeneralJob;
use App\Models\Holiday;
use App\Models\SapInstalation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SapInstalationRemainder extends Command
{


    private $texts = [
        'last_day' => [
            '¡Hola! 👋 He notado que tu {instalation_type} está programada para mañana y aún no has fijado una hora. Es importante que entres al link de agendamiento y fijes una hora y evitar inconvenientes.',
            '¡Hola de nuevo! 🕒 He revisado y veo que aún no has agendado tu {instalation_type} para mañana. Es crucial que puedas escoger una hora para brindarte el mejor servicio. Por favor, toma un momento para confirmar tu horario hoy. ¿Puedo ayudarte en algo?',
            '¡Urgente! ⏳ Acabo de ver que aún no has confirmado tu horario para tu {instalation_type}. Es vital para el funcionamiento de tus sistemas. Si no se agenda hoy, podrías enfrentar retrasos. Nuestro equipo está listo para apoyarte. Por favor, agenda tu horario cuanto antes.',
            '¡Atención! ⚠️ Este es el último recordatorio para tu {instalation_type}. Todavía no has confirmado tu horario. Es esencial hacerlo hoy para evitar problemas. Por favor, agenda ahora para asegurar tu instalación a tiempo. ¿Cómo puedo asistirte?'
        ],
        'daily' => [
            '¡Hola! 👋 Te recuerdo que tienes tu {instalation_type} pendiente de agendar. Para asegurar un servicio óptimo, es importante que termines de agendar a la brevedad. Puedes hacerlo fácilmente a través de nuestro enlace de agendamiento. ¿Hay algo en lo que te pueda ayudar?',
            '¡Saludos! 😊 Quiero asegurarme de que no olvides agendar tu {instalation_type}. Escoge tu horario con anticipación para ayudarnos a prepararnos mejor para brindarte el mejor servicio posible. Ingresa al link de agendamiento hoy mismo. ¿Te puedo asistir en algo?',
            '¡Hola! 🌟 Aún queda por agendar tu {instalation_type}. Recuerda que programar con tiempo es clave para un proceso sin contratiempos. Visita nuestro enlace de agendamiento y selecciona el horario que mejor te convenga. Estamos aquí para ayudarte.',
            '¡Buen día! ☀️ Aún tienes tiempo para agendar tu {instalation_type}. Los mejores horarios se están llenando, así que te recomendamos hacer tu reserva pronto. Accede al enlace de agendamiento y elige el momento ideal para ti. Si necesitas asistencia, aquí estoy para ayudarte.',
            '¡Aviso importante! 📌 Tu {instalation_type} aún está sin agendar y el tiempo se está agotando. Para evitar inconvenientes, por favor agenda tu instalación cuanto antes. Haz clic en el enlace de agendamiento y selecciona tu horario. Estamos a tu disposición para cualquier consulta.'
        ]
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Set arguments and options
    protected $signature = 'liveconnect:send-sap-instalation-remainders {type}';

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
        // Get type
        $type = $this->argument('type');

        if (!$type) {
            $this->error('Type is required');
            return;
        }



        if ($type == 'last_day') {

            GeneralJob::dispatch(SapInstalationRemainder::class, 'lastDay', [])->onQueue('liveconnect');
        }

        if ($type == 'daily') {

            GeneralJob::dispatch(SapInstalationRemainder::class, 'daily', [])->onQueue('liveconnect');
        }
    }


    public function daily()
    {
        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        SapInstalation::with('lastSapTry', 'order.student')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) use ($nextDay) {
                $query->whereNull('schedule_at')
                    ->whereDate('start_datetime', '>', $nextDay->format('Y-m-d'));
            })
            ->get()->each(function ($instalation) {
                $student_id = $instalation->order->student->id;
                $phone      = $instalation->order->student->phone;
                $message    = self::messageDaily(Carbon::now()->hour);

                $instalation_type = $instalation->type == 'Instalación completa' ? 'instalación de SAP' : $instalation->type;
                $message          = str_replace('{instalation_type}', $instalation_type, $message);

                $liveconnectService = new LiveConnectService();
                $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'SAP_INSTALATION_REMAINDER_DAILY');
                sleep(rand(6, 12));
            });

        sleep(rand(8, 15));
    }

    public function lastDay()
    {

        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        SapInstalation::with('lastSapTry', 'order.student')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) use ($nextDay) {
                $query->whereIsNull('schedule_at')
                    ->whereDate('start_datetime', $nextDay->format('Y-m-d'));
            })
            ->get()->each(function ($instalation) {
                $student_id = $instalation->order->student->id;
                $phone      = $instalation->order->student->phone;
                $message    = self::messageLastDay(Carbon::now()->hour);

                $instalation_type = $instalation->type == 'Instalación completa' ? 'instalación de SAP' : $instalation->type;
                $message          = str_replace('{instalation_type}', $instalation_type, $message);

                $liveconnectService = new LiveConnectService();
                $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'SAP_INSTALATION_REMAINDER_LAST_DAY');
                sleep(rand(6, 12));
            });

        sleep(rand(8, 15));
    }

    private function messageLastDay($hour)
    {
        $message = '';
        switch ($hour) {
            case Carbon::now()->hour >= 8 && Carbon::now()->hour < 10:
                $message = $this->texts['last_day'][0];
                break;
            case Carbon::now()->hour >= 10 && Carbon::now()->hour < 12:
                $message = $this->texts['last_day'][1];
                break;
            case Carbon::now()->hour >= 12 && Carbon::now()->hour < 14:
                $message = $this->texts['last_day'][2];
                break;
            case Carbon::now()->hour >= 14 && Carbon::now()->hour < 23:
                $message = $this->texts['last_day'][3];
                break;
        }
        return $message;
    }
}
