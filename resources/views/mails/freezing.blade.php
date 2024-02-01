<!DOCTYPE html>
<html>
<head>
    <title>Congelación de curso - Global Tecnologías Academy</title>
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
    @php
        // $s = '';
        // if(count($order->orderCourses->where('type', 'paid')) > 1) {
        //     $s = 's';
        // }


        // $s2 = '';
        // if(count($order->dues) > 1) {
        //     $s2 = 's';
        // }


        // $s3 = '';
        // if(count($order->orderCourses->where('type', 'free')) > 1) {
        //     $s3 = 's';
        // }
    @endphp


    <div class="container">
        <!-- BANER DE GLOBAL TECNOLOGÍAS ACADEMY -->
        <div class="banner">
            <!-- Aquí va la imagen del banner, reemplazar 'path_to_banner_image.jpg' con la ruta de la imagen real -->
            <img style="width: 100%" src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/banner-email.png" alt="Global Tecnologías Academy">
        </div>

        <!-- SEGUIMIENTO ACADÉMICO (CUANDO EL ALUMNO REALIZA UN SOLO PAGO) -->
        <div class="seguimiento-academico" style="font-size: 19px">
            <p>¡Hola! 🤓 <span class="important">
                {{$student->name}}
                </span></p>
                <p>Te saludamos del área académica 🤓 de Global Tecnologías Academy, para darte información importante.</p>

                <p>Nos has solicitado poner en pausa tu curso: <b>{{$course->name}}</b>. El cual tiene actualmente la siguiente información.</p>

                    <ul>
                        <li>Fecha de inicio: {{$original_date->start_date}}</li>
                        <li>Fecha de fin: {{$original_date->end_date}}</li>
                        <li>Tiempo de licencia y aula virtual inicial: {{$order_course->license}}</li>
                    </ul>

                <p>El tiempo maximo total que se puede congelar un curso es de 3 meses, y nos has solicitado congelarlo por:</p>

                <ul>
                    <li>Tiempo a congelar: {{$freezing->duration}}</li>
                    <li>Tiempo disponible para volver a congelar: {{$remainFreezingDurationAvaliable}} Meses</li>
                </ul>

                <p>De acuerdo a lo anterior, las nuevas fechas de inicio y fin de tu aula virtual y licencia SAP serían las siguientes:</p>
                <ul>
                    <li>Fecha de inicio: {{$freezing->return_date}}</li>
                    <li>Fecha de fin: {{$freezing->finish_date}}</li>
                    <li>Tiempo de licencia y aula virtual restante: {{$freezing->remain_license}}</li>
                </ul>

                <p><b>CONSIDERACIONES:</b>
                    <ul>
                        <li>Al congelar tu curso, se mantendrá tu avance realizado hasta la fecha, pero no tendrás acceso a tu aula virtual, ni a tu usuario SAP.</li>
                        <li>Debes mantener la conexión a nuestro servidor y recordar la clave de SAP que creaste, de lo contrario tendrías que pagar por el desbloqueo del usuario y si consumiste tus instalaciones gratuitas, pagar por una nueva instalación.</li>
                    </ul>





            <p>Nuestro horario de atención comprende ⏰📅<br>Lunes a Viernes de 9am a 7pm (Hora Perú 🇵🇪)<br>Sábados de 9am a 5pm (Hora Perú 🇵🇪)<br>Los DOMINGOS NO laboramos.</p>

            {{-- <p>¡! 🤩</p> --}}
        </div>
    </div>
</body>
</html>
