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
            <h1 style='margin: 0px'>Notificación de {{$retry ? 're' : ''}}agendamiento de instalación SAP:</h1>
            {{-- <h1 style='margin: 0px'></h1> --}}
        </div>

        <!-- SEGUIMIENTO ACADÉMICO (CUANDO EL ALUMNO REALIZA UN SOLO PAGO) -->
        <div class="seguimiento-academico" style="font-size: 19px">
            <p>
                <div> Estimado(a): </div>
                <div>{{$sap->student->name}}</div>
            </p>

            <p>
                <b>¡Has {{$retry ? 're' : ''}}agendado correctamente tu instalación SAP!</b>
            </p>

            <p>La fecha y hora de agendamiento que has escogido es: </p>
            <p><b>Fecha:
                {{-- {{$sap->start_datetime_target_timezone}} --}}
                {{-- format: YYYY-MM-DD HH:MM --}}
                {{date('d/m/Y h:i A', strtotime($sap->start_datetime_target_timezone))}}

            </b></p>
            <p><b>Zona horaria: {{$sap->timezone}}</b></p>

            <p><b>Te recordamos los siguientes puntos:</p></b>

            <ul>
                <li>La instalación es personal, no pudiendo recibirla un tercero. Debe estar conectado en su ordenador a la fecha y hora antes señalada.</li>
                <li>Debe tener descargados lo {{$sap->operating_system === 'Windows' ? 'TRES (03)' : 'CUATRO (04)'}} archivos que indica la guía, <b>antes de la hora agendada</b>; de lo contrario, el personal técnico procederá a <b>reagendar su instalación</b>. La PRÓXIMA CITA QUE SE TIENE DISPONIBLE ES EN DOS DÍAS HÁBILES A PARTIR DE LAS 9AM. </li>
                <li>El personal técnico se contactará por WhatsApp a la hora agendada. <b>NO REALIZAMOS LLAMADAS NI NOS CONECTAMOS VÍA MEET O ZOOM. Asimismo, solo tendrá una tolerancia de respuesta de 30min, es decir, que de no recibir respuesta de su parte en esos 30min, procederá a reprogramar la instalación. </b></li>
                <li>Le recuerdo que hemos reservado esta cita únicamente para usted, no pudiendo brindarle este horario a ningún otro alumno. Si tuviera algún inconveniente, por favor <b> notifique con un mínimo de CUATRO (04) HORAS antes de su cita </b> (dentro de mi horario laboral) para poder reprogramarlo dentro del mismo día.</li>

                <li> Le recuerdo una vez más que solo dispone de dos instalaciones gratuitas únicamente. Por tal motivo, si consume DOS (02) instalaciones, así hayan sido solo con reagendamientos, para un agendamiento más, tendrá que pagar, ya que, sería su tercera instalación.</li>


            </ul>



            <p><b>Consecuencias del reagendamiento de la instalación:</p></b>

            <ul>
                {{-- Primera instalacion --}}
                <li>Su licencia SAP y accesos al curso estarán activos desde el día que enviamos los accesos. Es decir, desde el día de la fecha de inicio, aunque no haya recibido la instalación.</li>


                <li>Si por alguna razón debe <b> reprogramar </b>su instalación por una <b>tercera vez</b>, ya le estaría contando como otra instalación. Le recuerdo una vez más que solo dispone de dos instalaciones gratuitas únicamente. </li>
                <li>Por tal motivo, si consume DOS (02) instalaciones, así hayan sido solo con reagendamientos, para un agendamiento más, tendrá que pagar, ya que, sería su tercera instalación.</li>


                <li>Este sería su {{count($sap->sapTries)}}° agendamiento, por lo que te restan {{3-count($sap->sapTries)}} reagendamientos disponibles de su instalación.</li>
            </ul>




            <p><b>CONSIDERACIONES PARA EL MOMENTO DE LA INSTALACIÓN:</p></b>

            <ul>
                <li>El técnico le pedirá que cree una contraseña, se recomienda que sea fácil de recordar.</li>
                <li>Debe tener un mínimo de 6 caracteres. </li>
                <li>No necesariamente debe contener mayúsculas o minúsculas.</li>
                <li>No necesariamente debe ser una combinación de letras y números.</li>
                <li>No podrá ver la contraseña que estará creando, ya que, estará protegida por ******************* por ética y protección de datos de SAP Open Ecosystem, por tal motivo, se recomienda que primero la cree en un bloc de notas de su ordenador y luego la copie y pegue en SAP.</li>
                <li>Posteriormente, deberá escribir nuevamente la contraseña creada en la parte inferior, para CONFIRMAR que es la misma que ha escrito anteriormente, de manera que si NO es la misma, no podrá ingresar a SAP. Si es la misma, ingresará SATISFACTORIAMENTE.</li>
                <li>Se recomienda que deje la contraseña guardada en el bloc de notas de su ordenador, para evitar que la olvide y deba pagar para el desbloqueo o restablecimiento de su contraseña.</li>
                <li>Al colocar satisfactoriamente la clave en los dos espacios solicitados, procederá a aceptar los términps y condiciones de SAP y posteriormente, ingresará a la pantalla principal del programa SAP, concluyendo satisfactoriamente su instalación dejándole un mensaje en su WhatsApp con su usuario SAP. </li>
                <li>En los primeros vídeos de su curso, encontrará cómo ingresar a SAP. Le pedimos que antes de ver los vídeos, maneje SAP con cuidado, para que no elimine nuestra conexión.</li>

            </ul>


            <p><b>Si por alguna razón no confirmas la llegada de este correo, no te eximirá de la responsabilidad de estar presente en la fecha y hora señalada.</b></p>


               <p>Nos encantaría que pases por nuestras redes sociales:</p>

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


            {{-- <p>¡Bienvenido/a de nuevo a la familia Global Tecnologías Academy! 🤩</p> --}}
        </div>
    </div>
</body>
</html>
