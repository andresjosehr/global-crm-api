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
            <h1>Notificación de extension de curso</h1>
        </div>
        <div class="content">
            <p>¡Hola! 👋 {{$extension->order->student->name}}



            <p>Nos complace informarle que el proceso administrativo de extensión de <b>{{$orderCourse->course->name}}</b>, ha sido completado satisfactoriamente.  </p>
            <p>La nueva fecha fin de tu curso es el <b>{{$orderCourse->end}}</b></p>
            <p style="margin-top: 30px">Asimismo recordarle que antes de esta fecha, debe realizar el examen de certificación correspondiente e informarme su aprobación dentro de mi horario laboral:</p>
            <p>Lunes a Viernes de 9am a 7pm (Hora Perú 🇵🇪)<br>Sábados de 9am a 5pm (Hora Perú 🇵🇪)<br>Los DOMINGOS NO laboramos.</p>
            <p>Caso contrario, no admitiremos capturas de pantalla, como se ha indicado en sus términos y condiciones previamente aceptados.</p>
        </div>



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



    </div>
</body>
</html>
