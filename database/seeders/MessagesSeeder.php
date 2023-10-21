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
                'content' => "Buen día, me comunico para enviarle los horarios disponibles *(de acuerdo a su huso horario: {{LOCATION}} )* para iniciar el proceso para agendar su instalación de SAP para el día: {{SAP_INSTALATION_DATE}}

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

                Quedo atenta a sus respuestas para poder enviarle la *guía de pre-instalación correspondiente,* de acuerdo a las preguntas realizadas.

                ⚠️Sin la respuesta a todas las consultas y el horario de su preferencia, no se completará el agendamiento de SAP y sus accesos empezarán a correr desde su fecha de inicio."
            ],
            [
                'name' => 'Guia Instalación SAP',

                'content' => "Por favor ingrese al siguiente link para encontrar la GUÍA DE PRE INSTALACIÓN SAP:

                {{GUIDE_LINK}}

                Encontrará *{{NUMBER}}* enlaces, los cuales debe tener descargados antes de la hora agendada; de lo contrario, el personal técnico procederá a reagendar su instalación.

                La PRÓXIMA CITA QUE TENGO DISPONIBLE ES EN DOS DÍAS HÁBILES A PARTIR DE LAS 9AM. Y su licencia corre desde el día que enviamos los accesos.

                *El personal técnico se contactará por este medio, a la hora agendada.*

                Le recuerdo que hemos reservado esta cita únicamente para usted, no pudiendo brindarle este horario a ningún otro alumno.

                Si tuviera algún inconveniente, por favor trate de notificar 30 minutos antes (dentro de mi horario laboral) para poder reprogramarlo. Gracias por su comprensión.

                *LE RECUERDO QUE LA GUÍA CONTIENE {{NUMBER}} ARCHIVOS PARA DESCARGAR: {{PROGRAMS}}*. El último puede demorar hasta 3h en descargarse, tomar previsiones."

            ],
            [
                'name' => 'Instalacion SAP agendada',
                'content' => "Se ha agendado su instalación para el {{SAP_INSTALATION_DATE}} a las {{TIME}} hora {{LOCATION}}. *Por favor tener los archivos* descargados *antes de su instalación,* los puede encontrar en la guía enviada anteriormente.

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
            ],
            [
                'name' => 'Bienvenida cuotas 1 curso',
                'content' => '¡Hola! 🤓
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tech Academy*, para darte la bienvenida a tu curso de:
                {{PAID_COURSES}}

                Has realizado el pago de:
                {{PAYMENTS_PAID}}


                Y tus próximos pagos han quedado de la siguiente manera:
                {{PAYMENTS_PENDING}}

                Siendo tu fecha de inicio de clases el:
                {{START_DATE}}	Siempre y cuando hayas mantenido las fechas puntuales en los primeros pagos indicados anteriormente.

                *Recuerda que te matriculaste con un precio PROMOCIONAL, el cual está sujeto a tus pagos dentro de las fechas acordadas por ti mismo.*

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *La instalación se realizará el mismo día de la fecha de inicio,* y será agendada con unos días de anticipación, por lo tanto es *importante tu pago puntual.*
                ✅ El no cumplir con el agendamiento de la instalación, no te eximirá de los pagos acordados previamente, ni del inicio de tu licencia SAP.
                ✅ *El tiempo de licencia y aula virtual de tu curso, es de:*
                {{LICENSE}}
                ✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.
                ✅ Tus *cursos gratuitos* los podrás *habilitar* una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu *confirmación de compra.*
                ✅ Te recuerdo que al 5to día de retraso, tus accesos serán bloqueados.
                ✅ Te recuerdo que a partir del 2do día de retraso, empieza a correr la mora indicada en tu ficha de matrícula. Evita los retrasos o podrías perder el precio promocional.
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes,
                no te eximirá de los pagos acordados previamente.

                {{FREE_COURSES}}

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*


                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*


                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_'
            ],
            [
                'name' => 'Bienvenida cuotas varios cursos',
                'content' => '¡Hola!
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tech Academy*, para darte la bienvenida a tus cursos:
                {{PAID_COURSES}}


                Has realizado el pago de:
                {{PAYMENTS_PAID}}


                Y tu(s) próximo(s) pagos han quedado de la siguiente manera:
                {{PAYMENTS_PENDING}}


                Siendo tu fecha de inicio de clases el:
                {{START_DATE}}	Siempre y cuando hayas mantenido las fechas puntuales en los primeros pagos indicados anteriormente.

                *Recuerda que te matriculaste con un precio PROMOCIONAL, el cual está sujeto a tus pagos dentro de las fechas acordadas por ti mismo, para que puedas mantener el precio acordado*.

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *La instalación se realizará el mismo día de la fecha de inicio,* y será agendada con unos días de anticipación, por lo tanto es *importante tu pago puntual.*
                ✅ El no cumplir con el agendamiento de la instalación, no te eximirá de los pagos acordados previamente, ni del inicio de tu licencia SAP.
                ✅ *El tiempo de licencia y aula virtual de tu curso, es de:* {{LICENSE}} Conmutados desde el inicio de cada curso.
                ✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.
                ✅ Tus *cursos gratuitos* los podrás *habilitar* una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu *confirmación de compra.*
                ✅ Te recuerdo que al 5to día de retraso, tus accesos serán bloqueados.
                ✅ Te recuerdo que a partir del 2do día de retraso, empieza a correr la mora indicada en tu ficha de matrícula. Evita los retrasos o podrías perder el precio promocional.
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes,
                no te eximirá de los pagos acordados previamente.

                Además, recuerda que como obsequio tendrás acceso a los siguientes cursos:
                CURSOS GRATIS

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*


                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*

                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_										'
            ],
            [
                'name' => 'Bienvenida SAP Integral',
                'content' => '¡Hola! 🤓
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tecnologías Academy,* para darte la bienvenida a tu curso de:
                SAP INTEGRAL

                Has realizado el pago de:
                {{PAYMENTS_PAID}}

                Y tus próximos pagos han quedado de la siguiente manera:
                {{PAYMENTS_PENDING}}

                Siendo tu fecha de inicio de clases el:
                {{START_DATE}}	Siempre y cuando hayas mantenido las fechas puntuales en los primeros pagos indicados anteriormente.

                *Recuerda que te matriculaste con un precio PROMOCIONAL, el cual está sujeto a tus pagos dentro de las fechas acordadas por ti mismo.*

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *La instalación se realizará el mismo día de la fecha de inicio,* y será agendada con unos días de anticipación, por lo tanto es *importante tu pago puntual.*
                ✅ El no cumplir con el agendamiento de la instalación, no te eximirá de los pagos acordados previamente, ni del inicio de tu licencia SAP.
                ✅ *El tiempo de licencia y aula virtual de tu curso, es de:* {{LICENSE}}
                ✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.
                ✅ Tus *cursos gratuitos* los podrás *habilitar* una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu *confirmación de compra.*
                ✅ Te recuerdo que al 5to día de retraso, tus accesos serán bloqueados.
                ✅ Te recuerdo que a partir del 2do día de retraso, empieza a correr la mora indicada en tu ficha de matrícula. Evita los retrasos o podrías perder el precio promocional.
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes,
                no te eximirá de los pagos acordados previamente.

                Además, recuerda que como obsequio tendrás acceso a los siguientes cursos:
                {{FREE_COURSES}}

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*

                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*

                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_'
            ],


            [
                'name' => 'Bienvenida (2)',
                'description' => 'Adicionalmente le consulto: ¿Desea que nuestra comunicación sea únicamente por este medio o está de acuerdo con llamadas? También me puede indicar si desea ambas vías o sólo una. Quedo al pendiente de su respuesta.'
            ],
            [
                'name' => 'Bienvenida al contado 1 curso',
                'description' => '¡Hola! 🤓
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tech Academy,* para darte la bienvenida a tu curso de:
                {{PAID_COURSES}}

                Siendo tu fecha de inicio de clases el:
                {{START_DATE}}

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *La instalación se realizará el mismo día de la fecha de inicio,* y será agendada con unos días de anticipación.
                ✅ El no cumplir con el agendamiento de la instalación, no te eximirá del inicio de tu licencia SAP.
                ✅ *El tiempo de licencia y aula virtual de tu curso, es de:* {{LICENSE}}
                ✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.
                ✅ De tener inconvenientes para avanzar en tu curso, podemos congelarlo por única vez, por un máximo de 3 meses (únicamente SAP). Tus *cursos gratuitos* los podrás *habilitar* una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu *confirmación de compra.*
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                ✅ Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes,no te eximirá del tiempo de aula y licencia que dispones.
                ✅ Si finaliza el tiempo de tu aula virtual y licencia SAP, y no logras culminar el contenido para certificarte, podrás obtener más tiempo, por un pago adicional.



                {{FREE_COURSES}}
                *SI DESEAS INICIAR EL MISMO DÍA DE SAP, CON ESTOS CURSOS, DEBES INDICARME EN ESTE MOMENTO.*

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*


                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*

                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_			'
            ],
            [
                'name' => 'Bienvenida al contado mas de un curso',
                'content' => '¡Hola!
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tech Academy*, para darte la bienvenida a tus cursos de:
                {{PAID_COURSES}}


                Siendo tu fecha de inicio de clases el:
                {{COURSES_DATE}}

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *La instalación se realizará el mismo día de la fecha de inicio,* y será agendada con unos días de anticipación.
                ✅ El no cumplir con el agendamiento de la instalación, no te eximirá del inicio de tu licencia SAP.
                ✅ *El tiempo de licencia y aula virtual de tu curso, es de:*
                TIEMPO DE LICENCIA		Conmutados desde el inicio de cada curso.
                ✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.
                ✅ De tener inconvenientes para avanzar en tu curso, podemos congelarlo por única vez, por un máximo de 3 meses en total para todos los cursos en conjunto (únicamente SAP). Tus *cursos gratuitos* los podrás *habilitar* una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu *confirmación de compra.*
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                ✅ Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes,no te eximirá del tiempo de aula y licencia que dispones.
                ✅ Si finaliza el tiempo de tu aula virtual y licencia SAP, y no logras culminar el contenido para certificarte, podrás obtener más tiempo, por un pago adicional.



                {{FREE_COURSES}}

                *SI DESEAS INICIAR EL MISMO DÍA DE SAP, CON ESTOS CURSOS, DEBES INDICARME EN ESTE MOMENTO.*

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*


                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*

                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_																				'
            ],
            [
                'name' => 'Bienvenida al contado cursos gratis',
                'content' => '¡Hola! 🤓
                {{STUDENT_NAME}}

                Te saludamos del área académica de *Global Tech Academy,* para darte la bienvenida a tu curso de:
                {{FREE_COURSE}}

                Siendo tu fecha de inicio de clases el:
                {{START_DATE}}

                PUNTOS A TENER EN CUENTA:
                ✅ *Te enviaremos un correo con tus accesos el día de tu fecha de inicio.*
                ✅ *El tiempo de aula virtual de tu curso, es de:* {{LICENSE}}
                ✅ Dentro de este tiempo, debes realizar todo el contenido y realizar el/los cuestionario/os para poder certificarte.
                ✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio *previamente acordada:*
                ✅ Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes, no te eximirá del tiempo de aula que dispones.
                ✅ Si finaliza el tiempo de tu aula virtual, y no logras culminar el contenido para certificarte, podrás obtener más tiempo, por un pago adicional.



                {{FREE_COURSES}}
                *SI DESEAS INICIAR EL MISMO DÍA DE TU CURSO PRINCIPAL, CON ESTOS CURSOS, DEBES INDICARME EN ESTE MOMENTO.*

                A través de este WhatsApp, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️
                OJO: 👀 *_No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado_*


                *Nuestro horario de atención comprende* ⏰📅
                Lunes a Viernes de 9am a 7:30pm (Hora Perú 🇵🇪 )
                Sabados de 9am a 6pm (Hora Perú 🇵🇪 )
                Los *DOMINGOS no laboramos*

                ¡Bienvenido/a a la familia Global Tech Academy! 🤩
                _Éste es el único número autorizado del que recibirá información, por favor guárdalo como contacto_'
            ],
            [
                'name' => 'Congelación',
                'content' => 'Estimado,
                Nos has solicitado poner en pausa tu curso: {{COURSE_NAME}}. El cual tiene actualmente la siguiente información:

                Fecha de inicio: {{START_DATE}}
                Fecha de fin: {{END_DATE}}
                Tiempo de licencia y aula virtual inicial: {{LICENSE}}

                Dispones de la posibilidad de congelar tu curso por 3 meses, y nos has solicitado congelarlo por:

                Tiempo a congelar: {{DURATION}}
                Tiempo disponible para volver a congelar: {{REMAIN_FREEZING}}

                De acuerdo a lo anterior, las nuevas fechas de inicio y fin de tu aula virtual y licencia SAP serían las siguientes:

                *Fecha de inicio:* {{RETURN_DATE}}
                *Fecha de fin:* {{FINISH_DATE}}
                *Tiempo de licencia y aula virtual restante:*  {{REMAIN_LICENSE}}

                CONSIDERACIONES:

                Al congelar tu curso, se mantendrá tu avance realizado hasta la fecha, pero no tendrás acceso a tu aula virtual, ni a tu usuario SAP.

                Debes mantener la conexión a nuestro servidor y recordar la clave de SAP que creaste, de lo contrario tendrías que pagar por el desbloqueo del usuario y si consumiste tus instalaciones gratuitas, pagar por una nueva instalación. '
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
