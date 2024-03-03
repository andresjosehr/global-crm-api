<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            margin: 20px auto;
            padding: 20px 80px;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .header {
            color: black;
            padding: 10px;
            text-align: center;
        }
        .content {
            text-align: left;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #188859;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="banner">
            <!-- Aquí va la imagen del banner, reemplazar 'path_to_banner_image.jpg' con la ruta de la imagen real -->
            <img style="width: 100%" src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/banner-email.png" alt="Global Tecnologías Academy">
        </div>

        <div class="header">
            <h1>¡Tu agendamiento es para mañana!
        </div>
        <div class="content">
            <p>¡Hola! 👋 {{$sap->order->student->name}}

               @php
                $instalation_type = $sap->instalation_type == 'Instalación completa' ? 'instalación sap' : $sap->instalation_type;
                $instalation_type = $instalation_type ? $instalation_type : 'instalación sap';
                @endphp

            <p>Mañana es el día para tu <strong>{{$instalation_type}}</strong>, sin embargo aún no has escogido la hora para tu instalación. Te recordamos que es importante que termines de agendar a la brevedad.</p>
            <p>Asegúrate de tener todo listo para que nuestro equipo pueda brindarte el mejor servicio posible.</p>
            <p>Puedes escoger la hora de tu instalacionh haciendo clic en el botón de abajo para seleccionar la hora que mejor se ajuste a tus necesidades.</p>
            <a href="https://terminos.globaltecnoacademy.com/agendamiento-instalacion-sap/{{$sap->key}}" class="button">Agendar Ahora</a>
            <p style="margin-top: 30px">¿Hay algo más en lo que te podamos ayudar? No dudes en contactarnos si tienes preguntas o necesitas asistencia adicional.</p>
        </div>

        @php
            $url = '';
            if($sap->previus_sap_instalation){
                $url = $sap->operating_system === 'Windows' ? env('APP_URL').'/guia_teamviewer_windows.pdf' : env('APP_URL').'/guia_mac_teamviewer.pdf';
            }else{
                $url = $sap->operating_system === 'Windows' ? 'https://cdn.liveconnect.chat/421/lc/1144/usuarios/4408/files/guia_pre_instalacion_sap_windows.pdf' : 'https://cdn.liveconnect.chat/421/lc/1144/usuarios/4408/files/guia_mac_1.pdf';
            }
            // echo $url;
            @endphp



            <p>Para descargar la guía de instalación, haga clic en el siguiente botón:</p>

            <a href="{{$url}}" style="display: inline-block; padding: 10px 20px; background-color: #188859; color: #fff; text-decoration: none; border-radius: 5px;">Descargar guía de instalación</a>


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

    </div>
</body>
</html>
