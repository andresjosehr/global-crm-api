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
        'daily' => [
            '¡Hola! 👋 Te recuerdo que tienes tu {instalation_type} pendiente de agendar. Para asegurar un servicio óptimo, es importante que termines de agendar a la brevedad. Puedes hacerlo fácilmente a través de nuestro enlace de agendamiento. ¿Hay algo en lo que te pueda ayudar?',
            '¡Saludos! 😊 Quiero asegurarme de que no olvides agendar tu {instalation_type}. Escoge tu horario con anticipación para ayudarnos a prepararnos mejor para brindarte el mejor servicio posible. Ingresa al link de agendamiento hoy mismo. ¿Te puedo asistir en algo?',
            '¡Hola! 🌟 Aún queda por agendar tu {instalation_type}. Recuerda que programar con tiempo es clave para un proceso sin contratiempos. Visita nuestro enlace de agendamiento y selecciona el horario que mejor te convenga. Estamos aquí para ayudarte.',
            '¡Buen día! ☀️ Aún tienes tiempo para agendar tu {instalation_type}. Los mejores horarios se están llenando, así que te recomendamos hacer tu reserva pronto. Accede al enlace de agendamiento y elige el momento ideal para ti. Si necesitas asistencia, aquí estoy para ayudarte.',
            '¡Aviso importante! 📌 Tu {instalation_type} aún está sin agendar y el tiempo se está agotando. Para evitar inconvenientes, por favor agenda tu instalación cuanto antes. Haz clic en el enlace de agendamiento y selecciona tu horario. Estamos a tu disposición para cualquier consulta.',
            'Buenos dias, ¿cómo estás? 🌞 Te recuerdo que aún no has agendado tu {instalation_type}. Es importante que lo hagas hoy para asegurar tu instalación a tiempo. Ingresa al enlace de agendamiento y selecciona tu horario. ¿Puedo ayudarte en algo?',
            '¡Atención! ⚠️ Aún no has agendado tu {instalation_type}. Es crucial que lo hagas hoy para asegurar tu instalación a tiempo. Por favor, selecciona tu horario a través del enlace de agendamiento. Nuestro equipo está listo para apoyarte. ¿Hay algo en lo que podamos asistirte?',
        ],
        'penultimate' => [
            '¡Hola! 👋 He notado que tu {instalation_type} está programada para mañana y aún no has fijado una hora. Es importante que entres al link de agendamiento y fijes una hora y evitar inconvenientes.',
            '¡Hola de nuevo! 🕒 He revisado y veo que aún no has agendado tu {instalation_type} para mañana. Es crucial que puedas escoger una hora para brindarte el mejor servicio. Por favor, toma un momento para confirmar tu horario hoy. ¿Puedo ayudarte en algo?',
            '¡Urgente! ⏳ Acabo de ver que aún no has confirmado tu horario para tu {instalation_type}. Es vital para el funcionamiento de tus sistemas. Si no se agenda hoy, podrías enfrentar retrasos. Nuestro equipo está listo para apoyarte. Por favor, agenda tu horario cuanto antes.',
            '¡Atención! ⚠️ Este es el último recordatorio para tu {instalation_type}. Todavía no has confirmado tu horario. Es esencial hacerlo hoy para evitar problemas. Por favor, agenda ahora para asegurar tu instalación a tiempo. ¿Cómo puedo asistirte?'
        ],
        'last_day' => [
            '¡Última llamada! 🚨 Hoy es el día de tu {instalation_type} y todavía no has confirmado la hora. Es imprescindible que lo hagas de inmediato para asegurar la instalación hoy mismo. Nuestro equipo está listo y esperando tu confirmación. No pierdas esta oportunidad de mejorar tus sistemas. Por favor, selecciona tu horario ahora.',
            '¡Alerta final! ⌛️ Hoy es el día crucial para tu {instalation_type} y aún falta tu confirmación de horario. Es sumamente importante para garantizar una instalación exitosa. Evita contratiempos y confirma tu horario cuanto antes. Nuestro equipo está a la espera de tu decisión. ¿Hay algo en lo que podamos ayudarte para agilizar este proceso?'
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

        GeneralJob::dispatch(SapInstalationRemainder::class, 'sendSapInstalationReminder', ['type' => $type])->onQueue('liveconnect');
    }

    public function sendSapInstalationReminder($type)
    {
        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        $index = [
            'daily' => $this->texts['daily'][rand(0, 6)],
            'penultimate' => $this->messagePenultimateDat(),
            'last_day' => $this->messagesLastDay()
        ];

        SapInstalation::with('lastSapTry', 'order.student')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) use ($nextDay, $type) {
                $query->whereNull('schedule_at')
                    ->when($type == 'daily', function ($query) use ($nextDay) {
                        $query->whereDate('start_datetime', '>', $nextDay->format('Y-m-d'));
                    })
                    ->when($type == 'penultimate', function ($query) use ($nextDay) {
                        $query->whereDate('start_datetime', $nextDay->format('Y-m-d'));
                    })
                    ->when($type == 'last_day', function ($query) {
                        $query->whereDate('start_datetime', Carbon::now()->format('Y-m-d'));
                    });
            })->get()->each(function ($instalation) use ($type, $index) {
                $student_id = $instalation->order->student->id;
                $phone      = $instalation->order->student->phone;

                $message    = $index[$type];

                $instalation_type = $instalation->instalation_type == 'Instalación completa' ? 'instalación SAP' : $instalation->instalation_type;
                $instalation_type = $instalation_type ? $instalation_type : 'instalación SAP';
                $message          = str_replace('{instalation_type}', $instalation_type, $message);

                Log::info([
                    'student_id' => $student_id,
                    'phone'      => $phone,
                    'message'    => $message,
                    'type'       => $type
                ]);
                $liveconnectService = new LiveConnectService();
                $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'SAP_INSTALATION_REMAINDER_DAILY');
                sleep(rand(12, 20));
            });

        sleep(rand(12, 20));
    }


    private function messagePenultimateDat()
    {
        $hour = Carbon::now()->hour;
        $message = '';
        switch ($hour) {
            case Carbon::now()->hour >= 8 && Carbon::now()->hour < 10:
                $message = $this->texts['penultimate'][0];
                break;
            case Carbon::now()->hour >= 10 && Carbon::now()->hour < 12:
                $message = $this->texts['penultimate'][1];
                break;
            case Carbon::now()->hour >= 12 && Carbon::now()->hour < 14:
                $message = $this->texts['penultimate'][2];
                break;
            case Carbon::now()->hour >= 14 && Carbon::now()->hour < 23:
                $message = $this->texts['penultimate'][3];
                break;
            default:
                $message = $this->texts['penultimate'][0];
                break;
        }
        return $message;
    }

    private function messagesLastDay()
    {
        $hour = Carbon::now()->hour;
        $message = '';
        switch ($hour) {
            case Carbon::now()->hour >= 8 && Carbon::now()->hour < 10:
                $message = $this->texts['last_day'][0];
                break;
            case Carbon::now()->hour >= 11 && Carbon::now()->hour < 13:
                $message = $this->texts['last_day'][1];
                break;
            default:
                $message = $this->texts['last_day'][0];
                break;
        }
        return $message;
    }
}
