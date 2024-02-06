<!DOCTYPE html>
<html>
<head>
    <title>Correo de Bienvenida - Global Tecnologías Academy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .banner {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }
        .important {
            /* color: #ff0000; */
        }
        .whatsapp-info {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- BANER DE GLOBAL TECNOLOGÍAS ACADEMY -->
        <div class="banner">
            <!-- Aquí va la imagen del banner, reemplazar 'path_to_banner_image.jpg' con la ruta de la imagen real -->
            <img style="width: 100%" src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/banner-email.png" alt="Global Tecnologías Academy">
        </div>

        <div style="text-align: center">
            <h1 style='margin: 0px'>Retoma tu curso:</h1>
            <h1 style='margin: 0px'>{{$freezing->orderCourse->course->name}}</h1>
        </div>

        <!-- SEGUIMIENTO ACADÉMICO (CUANDO EL ALUMNO REALIZA UN SOLO PAGO) -->
        <div class="seguimiento-academico" style="font-size: 19px">
            <p>
                <div> Estimado(a): </div>
                <div>{{$freezing->orderCourse->order->student->name}}</div>
            </p>

            <p>
                <b>¡Te damos nuevamente la bienvenida a tu curso de {{$freezing->orderCourse->course->name}}</b>
            </p>

            <p>El programa de capacitación de sap comprende <b> 50 horas teórico-prácticas certificadas como Key User (usuario experto) {{$freezing->orderCourse->course->name}}</b></p>


            @if(count($freeCourses) > 0)

            <p>Te recordamos la información de los siguientes cursos de obsequio que tienes disponibles:</p>
            <table style="width: 100%; border: 1px solid #333; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #333; padding: 5px;">Curso Obsequio</th>
                        <th style="border: 1px solid #333; padding: 5px;">Tiempo de aula virtual</th>
                        <th style="border: 1px solid #333; padding: 5px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($freeCourses as $orderCourse)
                    <tr>
                        <td style="border: 1px solid #333; padding: 5px;">{{$orderCourse->course->name}}</td>
                        <td style="border: 1px solid #333; padding: 5px;">{{$orderCourse->license}}</td>
                        <td style="border: 1px solid #333; padding: 5px;">{{$orderCourse->classroom_status}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Adicionalmente, te comento que si aun no has empezado alguno de estos cursos y quieres iniciarlos, debes indicarle a tu asistente académica para que te indique las fechas de inicio disponible. Si decides iniciar con los cursos de obsequio, sin haber culminado SAP, sólo recibirás una certificación simple sin aval internacional, hasta que te certifiques en SAP.  Si en dado caso repruebas o no culminas SAP o DOS cursos en total, pierdes el acceso a los demás ofrecidos.</p>
            @endif

            <p><b>Si por alguna razón no confirmas la llegada de este correo, no te eximirá de tus pagos si los tuvieras pendientes, ni del comienzo de tu licencia SAP o tiempo de aula virtual.</b></p>

            <p style='text-align: center; margin-top:40px'><u><b>PROCESO DE INICIO DE SESIÓN</b></u></p>

            <b> Acceso al <u>AULA VIRTUAL:</u></b>
            <li style="margin-left: 30px">Usuario: {{$freezing->orderCourse->order->student->classroom_user}}</li>
            <li style="margin-left: 30px">Contraseña: {{$freezing->orderCourse->order->student->classroom_user}}</li>

            <div style="margin:50px 0px;">
                <a target="_blank" style="background: #2c7379; padding: 10px 20px; text-decoration: none; color: white; font-weight: 700; border-radius: 10px;" href="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/Guia-para-uso-del-aula-virtual.pdf">
                Click aqui para descargar tu guía para uso del aula virtual
                </a>
             </div>

             <div style="margin-bottom: 40px">
                <b> Acceso al <u>SOFTWARE DE SAP:</u></b>
                <li style="margin-left: 30px">Usuario: 'POR DEFINIR'</li>
                <li style="margin-left: 30px">Contraseña: CREADA POR TI, desde el inicio de tu capacitación. La misma te indicamos que debías conservar hasta el momento de tu retorno.</li>
             </div>

             <div style="margin-bottom: 40px">
                <b> <B>Tendrás habilitada nuevamente la licencia original de SAP y tu aula virtual:</B></b>
                <li style="margin-left: 30px">Desde el dia: {{$freezing->return_date}}</li>
                <li style="margin-left: 30px">Hasta el dia: {{$freezing->finish_date}}</li>
             </div>

             <p>El tiempo de licencia no depende del cumplimiento del agendamiento por tu parte, ya que se agenda de acuerdo al tiempo y aceptación previa que nos brindes, los cambios posteriores por tu parte, son temas ajenos a la institución. Si tienes un ordenador empresarial, el cual tiene restricciones, no podrás recibir la instalación sin aprobación previa de tu empresa. Por lo que se recomienda tomar las previsiones antes de aceptar la instalación, por lo mencionado anteriormente.</p>
             <p>El acceso a nuestra conexión, es <b> imperativo </b> para el uso del curso, ya que, para realizar las prácticas y el examen de certificación, todo está configurado en nuestro servidor. Si en dado caso no logramos agendar tu instalación, pero recibiste este correo, desde este momento empieza a correr el tiempo de licencia y aula virtual.</p>

             <p>Debes culminar los cursos y dar tu examen de certificación en el tiempo brindado para cada uno, el cual te detallo líneas arriba. Si no lo pudieras terminar en el plazo establecidos, podrás pagar por una extensión de tiempo. El costo dependerá de la cantidad de meses adicionales que desee y será brindado por el área académica. Este pago es totalmente opcional, por lo que no estás obligado a pagarlo, si no deseas, pero no recibirás un certificado por participación de ningún curso.</p>


             <div style='color: red; text-decoration: underline; text-align: center; margin: 40px 0; font-size:30px; font-weight:800'>AVISO IMPORTANTE: </div>

             <div style='display: flex; gap: 30px'>
                <div style="width: 25%">
                    <img src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/unnamed.gif" name="Imagen2" align="left" width="100%" height="100%" border="0" />
                </div>
                <div style="width: 75%">Cuando nuestro personal técnico se conecte e ingrese la clave generada por defecto, el sistema te pedirá que <b> CREES UNA NUEVA CONTRASEÑA</b>, para <b> mayor seguridad</b>. Esta contraseña la debes <b><u>anotar o guardar</u></b>, ya que si realizas <b>DOS O MÁS intentos</b>, el usuario se <b><u>BLOQUEARÁ</u></b> y el <b>reseteo del usuario y/o asignación de nuevas credenciales tiene un costo adicional, el cual tendrás que pagar</b>, tal como se te indicó en la ficha de alumno que firmaste al momento de matricularte; así que es importante que <b>no la olvides o que la guardes muy bien, para evitar estos inconvenientes; por temas de seguridad y ética de SAP Open Ecosystem no estamos autorizados a saber ni guardar las claves que crean los alumnos en nuestro servidor</b>. El desbloqueo o asignación de nuevas credenciales, <u>toma al menos 48horas desde el momento en el que hayas realizado el pago</u> correspondiente.</div>

             </div>

             <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>MODALIDAD DE ESTUDIO Y SESIONES EN VIVO</b></u></p>

             <p style="margin-top: 50px"> Recuerda que tus clases son pregrabadas y que puedes avanzar a tu propio ritmo. Tendrás al menos 30 horas de sesiones en vivo, distribuidas a lo largo de tu capacitación, pero son para aclarar dudas o consultas en tiempo real con los consultores, cuando algo no te haya quedado muy claro en los vídeos.  No son de asistencia obligatoria   y tampoco tocan un tema en específico. Los alumnos se conectan de todos los niveles y hacen todas las consultas que tengan al momento.  Tú puedes hacer lo mismo, no importa el nivel en el que te encuentres, ya que los consultores están en la disposición de responder todas las dudas.   Estas sesiones se graban y se cuelgan en tu aula para que quienes no hayan podido asistir, las puedan ver luego y se retroalimenten de las preguntas de sus compañeros. Además, tendrás acceso a las sesiones que han sido realizadas durante el año 2021, hasta la actualidad. Te servirán como banco de consulta. </p>
             <p style="margin-top: 50px"><b>Tu aula está habilitada 24 horas al día los 7 días de la semana. Sigue las instrucciones detalladas en la guía adjunta al correo, si tienes algún inconveniente al momento de ingresar a tu aula virtual.</b></p>
             <p style="margin-top: 50px">El curso brindado, está diseñado para ser llevado en una computadora, ya que es un curso totalmente práctico y el software sólo puede ser instalado en un ordenador. Si deseas ver clases en una Tablet o celular, podrás, pero no es responsabilidad de la institución cómo reproduzca los vídeos o que realices tus prácticas en el software.</p>
             <p style="margin-top: 50px">Las sesiones en vivo serán a través de CISCO WEBEX, puedes descargar el app en tu computadora o en tu celular y conectarte, o puedes realizarlo a través del navegador de tu computadora. Estaré enviando el calendario de sesiones en vivo vía WhatsApp para que puedas organizarte y programar tu asistencia vía correo aceptando la invitación, ya que, si no confirman al menos 6 alumnos, la sesión se reprograma. </p>
             <p style="margin-top: 50px">Encontrarás en tu aula virtual los accesos directos para cada sesión en vivo , siempre y cuando se hayan programado de acuerdo a la disponibilidad de los consultores. Adicionalmente, 15 minutos antes de la sesión en vivo, recibirás un recordatorio para que procedas a conectarte. Por otro lado encontrarás en tu aula virtual, un botón de descarga del calendario programado para el mes en curso y se actualizará cada mes.​</p>


             <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>CERTIFICACIÓN Y CÓMO OBTENERLA:</b></u></p>
            <p style="margin-top: 40px">El curso SAP cuenta con un examen de certificación, el cual es teórico práctico disponiendo de dos (02) horas para realizarlo; el mismo se lleva a cabo en una plataforma diferente a tu aula virtual, por lo que debes agendarlo con tiempo, y es imperativo que cuentes con nuestra conexión SAP habilitada (debido a que el ambiente del examen está creado en nuestro servidor) y tu usuario de SAP habilitado.</p>
            <p style="margin-top: 50px">Los intentos de examen para certificarte, serán indicados en tu confirmación de compra, y estarán disponibles siempre y cuando completes todo el contenido del curso dentro del tiempo establecido indicado al inicio de este correo, de requerir un intento adicional, podrías optar por el mismo, pero con un costo adicional el cual variará si es presentado antes o después del tiempo de licencia y aula ofrecido, y será brindado por el área de asistencia académica de presentarse el caso.</p>
            <p style="margin-top: 50px">Todos los intentos de examen gratuitos, deben realizarse dentro del tiempo de aula y licencia otorgado, de lo contrario los estarás perdiendo, no son acumulativos. En el caso de que estés optando por la certificación Master, para poder obtenerla tendrás que culminar y aprobar el examen de certificación de cada uno de los cursos especializados a los que accediste según la certificación Master correspondiente; teniendo en cuenta que primero debes culminar un curso, antes de iniciar el otro, no se pueden llevar en simultáneo.</p>
            <p style="margin-top: 50px">Si no culminas el curso de SAP ofrecido dentro del tiempo brindado o no apruebas tu examen de certificación, por los cursos de obsequio ofrecidos y aprobados, sólo recibirás una certificación simple, sin aval internacional. Con respecto a los cursos de obsequio ofrecidos, dispondrás de un lapso de 6 MESES para escoger una fecha de inicio que será brindada por el área de asistencia académica, de no recibir respuesta del alumno lo estaría perdiendo. A su vez, si llegases a reprobar tu curso SAP y uno de los cursos obsequio, perderás el acceso al resto de los cursos que se hubiesen ofrecido inicialmente, aprovecho para comentarte que no brindamos certificados por participación. Al adquirir más de 1 curso SAP, dispones de quince (15) días para iniciar el siguiente, al término del primero. De lo contrario, pasarás a ser alumno en abandono y tendrás que matricularse nuevamente.</p>



            <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>INSTALACIONES:</b></u></p>
            <p style="margin-top: 40px">Nuestro personal técnico realizará la instalación necesaria del software SAP GUI (Únicamente en sistema operativo Windows o MAC), como máximo en dos (02) ocasiones. Si requieres una instalación en una tercera ocasión, tendrá un costo adicional. Las instalaciones gratuitas no se renuevan cuando extiendes tu curso. La instalación se realiza a través del programa TeamViewer, bajo tu supervisión.</p>
            <p style="margin-top: 50px">La conexión la realizará únicamente nuestro personal técnico vía remota, no es posible enviar una guía o manual de conexión, ni tampoco realizar la instalación vía llamada telefónica.</p>
            <p style="margin-top: 50px">Tener en cuenta que, para los cursos de EXCEL EMPRESARIAL, POWER BI o MS PROJECT no brindamos licencias, ni realizamos instalaciones, sin embargo, al inicio del curso encontrarás un pequeño tutorial para obtener una versión del programa.</p>
            <p style="margin-top: 50px">Si tienes más dudas o consultas, puedes contactarme por WhatsApp y con gusto podemos apoyarte.</p>
            <p style="margin-top: 50px">Posteriormente, estarás recibiendo al menos una llamada/WhatsApp semanal de mi parte, para ver tu seguimiento académico y recordarte las sesiones en vivo mensualmente.</p>
            <p style="margin-top: 50px">Nuevamente te doy la bienvenida a la familia ¡GLOBAL TECNOLOGÍAS ACADEMY!</p>







            <!-- Puntos a tener en cuenta -->
            <p>Nos encantaría que pases por nuestras redes sociales:

            </p>
            <ul>
                <li>✅ Sitio web: <a href="https://globaltecnologiasacademy.com">globaltecnologiasacademy.com</a></li>
                <li>✅ Instagram: <a href="https://instagram.com/globaltecnologiasacademy">instagram.com/globaltecnologiasacademy?utm_medium=copy_link</a></li>
                <li>✅ Youtube: <a href="https://youtube.com/@GlobalTecnologiasAcademy">youtube.com/@GlobalTecnologiasAcademy</a></li>
                <li>✅ Fanpage de facebook: <a href="https://facebook.com/globaltecnologiasacademy">facebook.com/globaltecnologiasacademy</a></li>
                <li>✅ Fanpage de facebook Int: <a href="https://facebook.com/globaltecnologiasacademyint">facebook.com/globaltecnologiasacademyint</a></li>
                <li>✅ Fanpage de facebook LATAM: <a href="https://facebook.com/globaltecnologiasacademylatam/reviews">facebook.com/globaltecnologiasacademylatam/reviews</a></li>
                <li>✅ Fanpage de facebook SAP: <a href="https://facebook.com/globaltecnologiasacademysap/reviews">facebook.com/globaltecnologiasacademysap/reviews</a></li>
                <li>✅ Perfil de CREDLY: <a href="https://credly.com/organizations/gacaam-global-tecnologias-academy-s…">credly.com/organizations/gacaam-global-tecnologias-academy-s…</a></li>
                <li>✅ Perfil de LINKEDIN: <a href="https://linkedin.com/in/global-tecnolog%C3%ADas-academy">linkedin.com/in/global-tecnolog%C3%ADas-academy</a></li>
            </ul>


            <p class="whatsapp-info">A través de nuestro número oficial de WhatsApp: +51 935355105, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️ <br>OJO: 👀 No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado</p>

            <p>Nuestro horario de atención comprende ⏰📅<br>Lunes a Viernes de 9am a 7pm (Hora Perú 🇵🇪)<br>Sábados de 9am a 5pm (Hora Perú 🇵🇪)<br>Los DOMINGOS NO laboramos.</p>


            <p>¡Bienvenido/a de nuevo a la familia Global Tecnologías Academy! 🤩</p>
        </div>
    </div>
</body>
</html>
