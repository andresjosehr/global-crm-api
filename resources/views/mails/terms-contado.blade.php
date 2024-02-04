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
    @php
        $s = '';
        if(count($order->orderCourses->where('type', 'paid')) > 1) {
            $s = 's';
        }


        $s2 = '';
        if(count($order->dues) > 1) {
            $s2 = 's';
        }


        $s3 = '';
        if(count($order->orderCourses->where('type', 'free')) > 1) {
            $s3 = 's';
        }
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
                {{$order->student->name}}
                </span></p>
            <p>Te saludamos del área académica🤓 de Global Tecnologías Academy, para darte la bienvenida a tu{{$s}} curso{{$s}} de SAP:

                <ul>
                    @foreach($order->orderCourses as $orderCourse)
                    @if($orderCourse->type == 'paid')
                        <li>{{$orderCourse->course->name}}</li>
                    @endif
                    @endforeach
                </ul>

                {{-- @foreach($order->dues as $due)
                <li>
                    {{$due->amount}} {{$order->currency->iso_code}} - {{$due->created_at->format('d/m/Y')}}
                </li>
            @endforeach --}}

            <p>Tu{{$s2}} pago{{$s2}} ha{{$s2 ? 'n' : ''}} quedado de la siguiente manera
            <table style="width: 100%; border: 1px solid #333; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #333; padding: 5px;">Fecha</th>
                        <th style="border: 1px solid #333; padding: 5px;">Monto</th>
                        <th style="border: 1px solid #333; padding: 5px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->dues as $due)
                    <tr>
                        <td style="border: 1px solid #333; padding: 5px;">{{ DateTime::createFromFormat('Y-m-d', $due->date)->format('d/m/Y')}} </td>
                        <td style="border: 1px solid #333; padding: 5px;">
                            @if($order->currency->iso_code=='PEN')
                            {{$order->currency->symbol}}.{{$due->amount}}
                            @endif
                            @if($order->currency->iso_code!='PEN')
                            {{$due->amount}} {{$order->currency->iso_code}}
                            @endif
                        </td>
                        <td style="border: 1px solid #333; padding: 5px;">
                            <span
                            style="

                        background: {{$due->paid ? '#79c970' : '#c58d8d'}};
                        color: {{$due->paid ? '#092d05' : '#3b0606'}};
                        padding: 5px 14px;
                        border-radius: 8px;"
                            "
                            >{{$due->paid ? 'Pagada' : 'Pendiente por pagar'}}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p>Siendo tu fecha de inicio de clases:
                @php
                    // get min date of all order courses
                    $minDate = $order->orderCourses->min('start');
                    $dateObj = DateTime::createFromFormat('Y-m-d', $minDate);
                    $date = $dateObj->format('d/m/Y');
                @endphp

                {{$date}}


                @php
            $pendingDue = false
            @endphp
            @foreach($order->dues as $due){
                if(!$due->paid){
                    $pendingDue = true;
                }

            }
            @endforeach

            <!-- Puntos a tener en cuenta -->
            <p>PUNTOS A TENER EN CUENTA:</p>
            <ul>
                <li>✅ Te enviaremos un correo con tus accesos el día de tu fecha de inicio.</li>
                <li>✅ La instalación se realizará el mismo día de la fecha de inicio, y será agendada con unos días de anticipación
                    @if($pendingDue)
                    , por lo tanto es importante tu pago puntual.
                    @endif
                     </li>
                <li>✅ El no cumplir con el agendamiento de la instalación,
                    @if($pendingDue)
                    no te eximirá de los pagos acordados previamente, ni del inicio de tu licencia SAP.
                    @endif
                    @if(!$pendingDue)
                    no te eximirá del inicio de tu licencia SAP.
                    @endif

                </li>
                <li>✅ El tiempo de licencia y aula virtual de tu curso, es de {{$order->orderCourses->first()->license}}.</li>
                <li>✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.</li>
                <li>✅ Tus cursos gratuitos los podrás habilitar
                    @if($pendingDue)
                    una vez hayas completado tus pagos
                    @endif
                     y los tiempos de cada uno se han detallado en tu confirmación de compra.</li>

                     @if($pendingDue)
                     <li>✅ Te recuerdo que, al 5to día de retraso, tus accesos serán bloqueados.</li>
                     <li>✅ Te recuerdo que, a partir del 2do día de retraso, empieza a correr la mora indicada en tu ficha de matrícula. Evita los retrasos o podrías perder el precio promocional.</li>
                     @endif
                <li>✅ Te estaremos avisando por este medio que tus accesos han sido enviados al correo en la fecha de inicio previamente acordada:</li>
                <li>✅ Si por alguna razón ajena a nosotros, no ingresas a tu curso o no lo revisas a pesar de haberte enviado los accesos correspondientes, no te eximirá de los pagos acordados previamente.</li>
            </ul>

            @php
            $freeCourses = $order->orderCourses->where('type', 'free');
            @endphp
             @if(count($freeCourses) > 0)
            <p>Además, recuerda que como obsequio tendrás acceso a {{$s3 ? 'los' : 'el'}} siguiente{{$s3}} curso{{$s3}}:
            <ul>
                @foreach($order->orderCourses as $orderCourse)
                @if($orderCourse->type == 'free')
                    <li>{{$orderCourse->course->name}}</li>
                @endif
                @endforeach
            </ul>
            @endif

            <p class="whatsapp-info">A través de nuestro número oficial de WhatsApp: +51 935355105, estaremos en contacto sobre cualquier inquietud que tengas o apoyo que requieras✍️ <br>OJO: 👀 No está habilitado para llamadas por ningún medio, debido a que pertenece a un sistema computarizado</p>

            <p>Con este boton puedes descargar los términos y condiciones que has aceptado anteriormente</p>
            <a href="{{$urlTerm}}" style="background: #0d6efd; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Descargar Términos y Condiciones</a>

            <p>Nuestro horario de atención comprende ⏰📅<br>Lunes a Viernes de 9am a 7pm (Hora Perú 🇵🇪)<br>Sábados de 9am a 5pm (Hora Perú 🇵🇪)<br>Los DOMINGOS NO laboramos.</p>

            <p>¡Bienvenido/a a la familia Global Tecnologías Academy! 🤩</p>
        </div>
    </div>
</body>
</html>
