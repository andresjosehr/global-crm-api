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
            <h1 style='margin: 0px'>Bienvenido(a) a tu curso:</h1>
            <h1 style='margin: 0px'>Fundamentos de MS Project 2019</h1>
        </div>

        <!-- SEGUIMIENTO ACADÉMICO (CUANDO EL ALUMNO REALIZA UN SOLO PAGO) -->
        <div class="seguimiento-academico" style="font-size: 19px">
            <p>
                <div> Estimado(a): </div>
                <div>{{$orderCourse->order->student->name}}</div>
            </p>

            <p>
                <b>¡Te damos la bienvenida a tu curso MS Project 2019!</b>
            </p>

            <div style="margin-bottom: 40px">
                <li style="margin-left: 30px">Tu fecha de inicio es el dia: {{$orderCourse->start}}</li>
                <li style="margin-left: 30px">Tu fecha de fin de curso es el dia: {{$orderCourse->end}}</li>
             </div>

            <p>Dispondrás de 3 MESES para culminar este curso. Si no logras culminar dentro del tiempo establecido, puedes realizar un pago adicional para extender el tiempo, siendo esto totalmente opcional; de lo contrario, no recibirás certificación alguna, ya que no entregamos certificado por participación.</p>

            <p>Al iniciar sin terminar SAP, si aprueba el curso, su certificado será emitido cuando termine SAP; siempre y cuando lo apruebe. Si no aprueba SAP, no recibirá el certificado del curso aprobado, y si aún está cursando, perderá el acceso.</p>

            <p>Debes indicarle a tu asistente académica si culminas el contenido y examen de certificación encontrado en tu aula, antes de la fecha de fin de curso, para que proceda a emitir tu certificado correspondiente. Si esperas a la fecha de fin de curso para indicar que habías culminado y requieres tu certificado, podría perderlo, ya que, hay que realizar una validación de tu aula virtual dentro del horario laboral, el cual te detallamos desde el primer día. Si no le avisas a tu asistente que culminaste y vence el tiempo de tu aula, aunque envíes capturas, no se tomará en cuenta para solicitar tu certificado.</p>

            <p>Adicionalmente, te comento que cuando desees iniciar alguno de los otros cursos disponibles, debes indicarle a tu asistente académica para que te indique las fechas de inicio disponible.</p>

            <p>Si repruebas SAP y este curso de obsequio, pierdes el acceso a los demás cursos. En total, solo puedes reprobar un curso, con dos cursos reprobados, pasas a abandono.</p>

            <p>Para los cursos de obsequio no otorgamos ningún tipo de licencia, ni realizamos instalaciones, pero encontrarás un pequeño tutorial sobre cómo obtener alguno de ellos, en los vídeos pregrabados. </p>

            <p><b>Tendrás habilitada tu plataforma las 24 horas del día, los 7 días de la semana, recuerda que tus sesiones ya están pregrabadas y cargadas en tu aula virtual.</b></p>

            <p>Si inicias el curso y luego no puedes continuarlo, no es posible congelarlo o pausarlo.</p>

            <p>Si por alguna razón no confirmas la llegada de este correo, no te eximirá del tiempo de inicio de tu aula virtual.</p>



            <p style='text-align: center; margin-top:40px'><u><b>PROCESO DE INICIO DE SESIÓN</b></u></p>

            <b> Acceso al <u>AULA VIRTUAL:</u></b>
            <li style="margin-left: 30px">Usuario: {{$orderCourse->order->student->classroom_user}}</li>
            <li style="margin-left: 30px">Contraseña: {{$orderCourse->order->student->classroom_user}}</li>

            <div style="margin:50px 0px;">
                <a target="_blank" style="background: #2c7379; padding: 10px 20px; text-decoration: none; color: white; font-weight: 700; border-radius: 10px;" href="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/Guia-para-uso-del-aula-virtual.pdf">
                Click aqui para descargar tu guía para uso del aula virtual
                </a>
             </div>



             <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>MODALIDAD DE ESTUDIO</b></u></p>

             <p style="margin-top: 50px">Recuerda que tus clases son pregrabadas y que puedes avanzar a tu propio ritmo. Tendrás una sesión en vivo cada 15 días, pero son para aclarar dudas o consultas en tiempo real con el docente a cargo del curso, cuando algo no te haya quedado muy claro en los vídeos. No son de asistencia obligatoria y tampoco tocan un tema en específico. Los alumnos se conectan de todos los niveles y hacen todas las consultas que tengan al momento. <b> Tú puedes hacer lo mismo, no importa el nivel en el que te encuentres, ya que el docente está en la disposición de responder todas las dudas </b>. Estas sesiones se graban y se cuelgan en tu aula para que quienes no hayan podido asistir, las puedan ver luego y se retroalimenten de las preguntas de sus compañeros. Además, tendrás acceso a las sesiones que han sido realizadas durante el año en curso, hasta la actualidad. Te servirán como banco de consulta. </p>
             <p style="margin-top: 50px"><b>Tu aula está habilitada 24 horas al día los 7 días de la semana. Sigue las instrucciones detalladas en la guía adjunta al correo, si tienes algún inconveniente al momento de ingresar a tu aula virtual.</b></p>


             <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>SESIONES EN VIVO:</b></u></p>
             <p style="margin-top: 40px">Las sesiones en vivo serán a través de CISCO WEBEX, puedes descargar el app en tu computadora o en tu celular y conectarte, o puedes realizarlo a través del navegador de tu computadora. Encontrarás en tu aula virtual los accesos directos para cada sesión en vivo, siempre y cuando se hayan programado de acuerdo a la disponibilidad de los consultores. Adicionalmente, 15 minutos antes de la sesión en vivo, recibirás un recordatorio para que procedas a conectarte. Por otro lado encontrarás en tu aula virtual, un botón de descarga del calendario programado para el mes en curso y se actualizará cada mes.</p>


             <p style='text-align: center; margin-top:70px; font-size:30px'><u><b>EVALUACIONES:</b></u></p>
             <li style="margin-top: 40px">En tu aula virtual, encontrarás el examen de certificación. En él se encuentran las instrucciones sobre cómo debe ser resuelto.</Ñ>
             <p>Si tienes más dudas o consultas, puedes contactarme por WhatsApp o respondiendo a este mismo correo y con gusto podemos agendar una llamada para darte un paseo guiado por tu aula virtual y en tu primera sesión en vivo, podemos programar una explicación con el consultor encargado de tu curso.</p>
             <p>Posteriormente, estarás recibiendo al menos una llamada/WhatsApp mensual de mi parte.</p>


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


            <p>¡Bienvenido/a a la familia Global Tecnologías Academy! 🤩</p>
        </div>
    </div>
</body>
</html>
