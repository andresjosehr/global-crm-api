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
            <p><b>Fecha: {{$sap->start_datetime}}</b></p>

            <p>Te recordamos los siguientes puntos:</p>

            <ul>
                <li>Debes tener descargados los archivos señalados en tu pre-guia de instalación antes del momento en el que el tecnico realizara la instalacion del software de tu computadora. Esta guia puedes descargarla en el boton de mas abajo</li>
                <li>Debes estar presente en la fecha y hora señalada, ya que nuestro personal técnico se conectará a tu computadora para realizar la instalación.</li>
                <li>Si por alguna razón no puedes estar presente en la fecha y hora señalada, por favor comunícate con nosotros para reagendar tu instalación 24 horas antes del momento de instalación.</li>
                <li>Solo estas permitido a reagendar tu instalación una 3 veces, si necesitas reagendar por segunda vez, tendrá un costo adicional.</li>
                <li>Esta es tu {{count($sap->sapTries)}}° agendamiento de instalación, si necesitas una instalación adicional, tendrá un costo adicional, por lo que te restan {{3-count($sap->sapTries)}} reagendamientos disponibles.</li>
            </ul>

            <p><b>Si por alguna razón no confirmas la llegada de este correo, no te eximirá de la responsabilidad de estar presente en la fecha y hora señalada.</b></p>

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


            {{-- <p>¡Bienvenido/a de nuevo a la familia Global Tecnologías Academy! 🤩</p> --}}
        </div>
    </div>
</body>
</html>
