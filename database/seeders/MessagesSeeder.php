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

        DB::table('messages')->insert([
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
            ]
        ]);
    }
}
