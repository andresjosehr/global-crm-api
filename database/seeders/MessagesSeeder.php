<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('messages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $messages = [
            [
                'name' => 'Confirmacion de compra',
                'content' => 'CONFIRMACIÓN DE TU COMPRA

                CURSO(S) SAP: {{COURSES}}
                CURSO(S) ADICIONALES: {{FREE_COURSES}}

                LICENCIA SAP Y AULA VIRTUAL: {{LICENSE}}
                INTENTOS PARA EXAMEN: 3
                AULA VIRTUAL CURSO(S) ADICIONAL(ES): 3 MESES

                PAGOS REALIZADOS: {{PAYMENTS_COUNT}}

                {{PAYMENTS}}

                CUOTA 1: 250 MXN 11/09/2023 Reserva de precio promocional
                CUOTA 2: 1.550 MXN 15/09/2023 Con este pago accederá al 50% del material pregrabado del curso, el día de su fecha de inicio
                CUOTA 3: 900 MXN 14/10/2023
                CUOTA 4: 900 MXN 14/11/2023 Con este pago accederá al 100% del material pregrabado del curso, en un plazo de 24 horas hábiles.

                FECHA DE INICIO: {{START_DATE}}

                TOMAR EN CUENTA

                A. Se debe realizar el pago correspondiente para completar el 50% del valor total, el día pautado para que se pueda programar la instalación y creación de su aula virtual, caso contrario se reprogramará su inicio y podría perder el precio promocional.

                B. Recuerde que para acceder al 100% del contenido, debe haber pagado el 100% del costo total de la inversión. De igual forma le indico que si sólo ha pagado el 50% del valor total del curso, sólo tendrá acceso al 50% del material pregrabado. Y el 100% del contenido restante, cuando culmine el 100% de los pagos.

                C. El proceso de instalación de SAP GUI (Versión R/3 ERP para los cursos SAP PP, SAP PM, SAP MM, SAP FI y SAP HCM) o SAP IDES (Versión S/4 HANA para el curso SAP Integral), dependiendo del curso adquirido y su versión, solo será realizado por nuestro personal técnico autorizado, de forma remota, bajo supervisión del alumno el día de su fecha de inicio; no es posible enviar guías para que el alumno instale por sí mismo. Contará con 2 instalaciones gratuitas.

                D. Para instalaciones en computadores o laptops con restricciones empresariales es necesario contar con permisos de administrador de parte de su empresa, no es responsabilidad de la institución, y tenga en cuenta que su tiempo de licencia empezará a correr desde el día en que se envían sus accesos.

                E. Recuerde que el tiempo designado para el curso SAP, está incluido en el precio establecido, si desea una extensión del tiempo brindado, podrá realizar un pago, el monto dependerá de la cantidad de meses que desee extender y se lo brindará su asistente académica. El proceso de extensión se debe realizar con un mínimo de siete (07) días previos a la fecha de fin de curso, de lo contrario, podría perder su avance y no se mantendría el precio brindado por cada mes de extensión.

                F. Una vez culminado tiempo brindado para su curso SAP, tendrá un plazo máximo de una (1) semana para iniciar con los cursos adicionales ofrecidos de ser el caso (EXCEL EMPRESARIAL, POWER BI y MS PROJECT) de acuerdo a las fechas de inicio que le brindarán. De lo contrario, los estaría perdiendo.

                G. Los cursos adicionales como Excel Empresarial, Power BI y MS Project, tienen su propia fecha de inicio y tiempo establecido, en caso de que desee iniciar antes debe notificarlo a su asistente académica y haber realizado el pago del 100% del valor del curso. Tener en cuenta que para estos cursos no brindamos licencias, ni realizamos instalaciones; y para contar con el aval de Credly e insignias digitales, debe haber aprobado el curso SAP, ya que es el curso principal, de lo contrario por cada curso de obsequio aprobado solo recibirá un certificado simple sin aval internacional. Si en dado caso abandona su curso SAP, perderá el acceso a los cursos de obsequio ofrecidos. Al reprobar dos cursos, pierde el acceso a los demás ofrecidos.

                H. Recuerde que para obtener cada certificado se ha de completar el contenido del curso correspondiente, rendir y aprobar un examen de certificación dentro del tiempo establecido para cada curso. No se otorgan certificados solo por participación.

                I. La cantidad de intentos de examen de certificación está incluida en el precio establecido y se otorgarán al completar todo el contenido dentro del tiempo brindado, si desea intentos extras, se deberá realizar un pago adicional, el cual deberá solicitar al área académica, dentro del tiempo designado para el curso.

                EL PROGRAMA DE CAPACITACIÓN DE SAP COMPRENDE:
                50 HORAS TEÓRICO-PRÁCTICAS CERTIFICADAS COMO KEY-USER (USUARIO EXPERTO) EN {{COURSES}}

                Estimado/a:
                {{STUDENT_NAME}}

                A partir de este momento, cualquier duda que tengas respecto a tus certificados, tiempo en la plataforma, instalación del software o suspensión temporal, vas comunicarte con coordinación académica a través del chat que se te asignará y su horario de atención es de lunes a viernes de 9am a 7:30pm, y días sábado de 9am a 6pm.

                Recuerda que te hemos enviado una ficha de matrícula con nuestros TÉRMINOS Y CONDICIONES la cual debes remitir en un plazo no mayor a 24 HORAS con los datos solicitados y firmada, para poder completar tu inscripción y recibir tus accesos e instalación. En la misma tenemos un apartado que indica que si decides retirarte sin completar el 100% del valor del curso, no haremos ningún reembolso.

                Sin más que agregar
                Nuevamente bienvenido/a.'
            ],
            [
                'name' => 'Inicio SAP',
                'content' => "Hola, espero te encuentres bien.

                *Te hemos enviado a tu correo tu usuario y contraseña para que puedas iniciar tu curso, así como una guía con el paso a paso para que puedas visualizar tu primer vídeo y el link de nuestra aula virtual.*

                🚨⚠️ *SI EL CORREO NO LO ENCUENTRAS EN LA BANDEJA DE ENTRADA, POR FAVOR REVISA TU BANDEJA DE CORREOS NO DESEADOS O SPAM.*

                Te recuerdo que tus clases son *pre-grabadas* y se encuentran cargadas en tu aula virtual y esta se encuentra habilitada 24/7, para que puedas avanzar a tu propio ritmo, *es decir que no hay horarios.*

                *En tu aula virtual encontrarás el acceso directo a las sesiones en vivo, con tan solo un click;* estas sesiones son para aclarar dudas o consultas en tiempo real con los consultores, sobre lo que no haya quedado claro en los vídeos, es decir que no son clases.

                *Te adjunto el cronograma de sesiones en vivo de este mes, también podrás descargarlo los primeros días de cada mes, desde tu aula virtual.*

                A través de este WhatsApp, estaremos en contacto para cualquier inquietud que tengas o apoyo que requieras✍️.
                _OJO: 👀 No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_

                *Nuestro HORARIO DE ATENCIÓN comprende de
                Lunes a Viernes de 9am a 7:30pm (Hora Perú).
                Sábados de 9am a 6pm (Hora Perú).
                Te recordamos que los *DOMINGOS NO LABORAMOS.*"
            ],
            [
                'name' => 'Inicio MS Project',
                'content' => "Hola, espero te encuentres bien.

                Te escribo para comentarte que te hemos enviado los accesos de tu *curso MS Project*, si no lo encuentras en la bandeja de entrada *por favor valida tu bandeja de SPAM*.

                Adjunto a tu correo hay una *guía de ingreso*, para apoyarte hasta que visualices tu primer vídeo.

                Recuerda que si tienes dudas o consultas, puedes comunicarte con nosotros por este medio."
            ],
            [
                'name' => 'Inicio Excel Empresarial',
                'description' => "Hola, espero te encuentres bien.

                Te escribo para comentarte que te hemos enviado los accesos de tu *curso Excel Empresarial,* si no lo encuentras en la bandeja de entrada *por favor valida tu bandeja de SPAM.*

                Adjunto a tu correo hay una *guía de ingreso,* para apoyarte hasta que visualices tu primer vídeo.

                *Te pedimos que al finalizar tu curso no elimines los archivos de Excel que vayas creando, ya que los necesitarás para tu curso de Power BI.*

                Recuerda que si tienes dudas o consultas, puedes comunicarte con nosotros por este medio."
            ],
            [
                'name' => 'Inicio Power BI',
                'content' => "Hola, espero te encuentres bien.

                Te escribo para comentarte que te hemos enviado los accesos de tu *curso Power BI*, si no lo encuentras en la bandeja de entrada *por favor valida tu bandeja de SPAM*.

                Adjunto a tu correo hay una *guía de ingreso*, para apoyarte hasta que visualices tu primer vídeo.

                Recuerda que si tienes dudas o consultas, puedes comunicarte con nosotros por este medio."
            ],
            [
                'name' => 'Agendar Instalación SAP',
                'content' => "Buen día, me comunico para enviarle los horarios disponibles *(de acuerdo a su huso horario, es decir, la ciudad donde se encuentra)* para iniciar el proceso para agendar su instalación de SAP para el día: {{SAP_INSTALATION_DATE}}

                {{HOURS}}

                *De igual manera, por favor me responde las siguientes consultas:*
                1. La computadora que va a usar para su capacitación e instalación, ¿utiliza sistema operativo Windows o MAC?

                2. ¿Es personal o pertenece a la empresa en la que trabaja?

                3. Y por último, ¿Tiene una versión de SAP instalada?
                *Es importante que nos indique, ya que no puede haber dos versiones instaladas en el mismo ordenador.*"
            ],
            [
                'name' => 'Horas de preferencia para instalación SAP',
                'content' => "Por favor me indica el *horario de su preferencia, para continuar* con el proceso de agendamiento de su *instalación de SAP.*

                {{HOURS}}

                Quedo atenta a sus respuestas para poder enviarle la *guía de pre-instalación correspondiente,* de acuerdo a las preguntas realizadas.

                ⚠️Sin la respuesta a todas las consultas y el horario de su preferencia, no se completará el agendamiento de SAP y sus accesos empezarán a correr desde su fecha de inicio."
            ],
            [
                'name' => 'Guia Instalación SAP',
                // *TRES (03)*}
                // TeamViewer, WinRar y SAP GUI
                'content' => "Por favor, debe tener descargados los *{{NUMBER}}* archivos que indica la guía, *antes de la hora agendada;* de lo contrario, el personal técnico procederá a *reagendar su instalación.* La PRÓXIMA CITA QUE TENGO DISPONIBLE ES EN DOS DÍAS HÁBILES A PARTIR DE LAS 9AM. *Y su licencia corre desde el día que enviamos los accesos.*

                *El personal técnico se contactará por este medio, a la hora agendada.*

                Le recuerdo que hemos reservado esta cita únicamente para usted, no pudiendo brindarle este horario a ningún otro alumno. Si tuviera algún inconveniente, por favor trate de notificar 30 minutos antes para poder reprogramarlo. Gracias por su comprensión.

                *LE RECUERDO QUE LA GUÍA CONTIENE {{NUMBER}} ARCHIVOS PARA DESCARGAR: {{PROGRAMS}}*"
            ],
            [
                'name' => 'Instalacion SAP agendada',
                'content' => "Se ha agendado su instalación. *Por favor tener los archivos* descargados *antes de su instalación,* los puede encontrar en la guía enviada anteriormente.

                *Me indica si es que no la puede visualizar.*

                El área técnica se comunicará por este medio (de forma escrita), y tienen un *tiempo de tolerancia de 30 minutos únicamente,* antes de pasar al siguiente alumno.

                OJO: si por alguna razón debe *reprogramar* su instalación por una *tercera vez,* ya le estaría contando como instalación. Le recuerdo una vez más que solo dispone de *dos instalaciones gratuitas únicamente.*"
            ],

            [
                'name' => 'Conserva Clave SAP',
                'content' => "Por favor me indica si aún mantiene su usuario y contraseña SAP."
            ],
            [
                'name' => 'Pantallazos SAP',
                'content' => "En este caso, el técnico solo se conectaría a realizar la conexión de SAP de nuestro servidor en su versión de SAP.
                Para continuar con el agendamiento, *necesitaría por favor un pantallazo de la versión de SAP que tiene actualmente,* para que nuestros técnicos confirmen que se pueda llevar a cabo la instalación. ¿Cuándo cree que la pueda enviar? Para evaluar si es necesario un cambio de la fecha de inicio."
            ],
            [
                'name' => 'Restrincciones instalacion SAP',
                'content' => "Necesitaría que consulte al área de IT de su empresa si su ordenador tiene alguna restricción, ya que al ser una computadora empresarial, podría tener restricciones y no permitirá que el técnico realice la instalación, puesto que él debe ingresar a su computadora y crear los parámetros de conexión, y al ser una computadora empresarial, cuando el técnico ingrese por TeamViewer no le permitirá crear la conexión. Y es probable que tampoco pueda descargar ningún programa de los que le vamos a enviar. Por favor valide con el área de IT de su empresa, y me comenta para seguir con el procedimiento de instalación. *Y por favor me indica cuándo tendría esta respuesta, para evaluar si mejor le cambiamos su fecha de inicio.*"
            ]


        ];

        foreach ($messages as &$message) {
            // Verificar si el índice 'content' existe, en caso contrario, buscar 'description'
            $key = isset($message['content']) ? 'content' : 'description';

            // Separamos el contenido en párrafos
            $paragraphs = explode("\n", $message[$key]);

            // Eliminamos los espacios iniciales de cada párrafo
            $trimmedParagraphs = array_map(function ($paragraph) {
                return ltrim($paragraph);
            }, $paragraphs);

            // Volvemos a unir los párrafos
            $message[$key] = implode("\n", $trimmedParagraphs);
        }

        unset($message);

        DB::table('messages')->insert($messages);
    }
}
