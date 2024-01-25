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

            @php
            $paidDues = $order->dues->where('paid', 1);
            $s4 = '';
            if(count($paidDues) > 1) {
                $s4 = 's';
            }reserva
            @endphp

            <p>Has realizado {{$s4 ? 'los' : 'el'}} siguiente{{$s4}} pago{{$s4}}</p>
            <table style="width: 100%; border: 1px solid #333; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #333; padding: 5px;">Fecha</th>
                        <th style="border: 1px solid #333; padding: 5px;">Monto</th>
                        <th style="border: 1px solid #333; padding: 5px;">Estado</th>
                        <th style="border: 1px solid #333; padding: 5px;">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $acc = 0;
                    $fif = false;
                    @endphp
                    @foreach($paidDues as $due)
                    <tr>
                        <td style="border: 1px solid #333; padding: 5px;">{{ DateTime::createFromFormat('Y-m-d', $due->date)->format('d/m/Y')}} </td>
                        <td style="border: 1px solid #333; padding: 5px;">{{$due->amount}} {{$order->currency->iso_code}}</td>
                        <td style="border: 1px solid #333; padding: 5px;">
                            <span
                            style="

                        background: {{$due->paid ? '#79c970' : '#c58d8d'}};
                        color: {{$due->paid ? '#092d05' : '#3b0606'}};
                        padding: 5px 14px;
                        border-radius: 8px;"
                            "
                            >{{$due->paid ? 'Pagada' : 'Pendiente por pagar'}}</span></td>
                            <td style="width: 30%">
                                Pago para la reserva de precio promocional, el cual se mantiene hasta la siguiente cuota de pago.
                                @php
                                $acc += $due->amount;
                                @endphp
                                @if($acc >= $order->price_amount / 2 && !$fif)
                                 / Con este podras acceder al 50% del contenido pregrabado del curso, el dia de su fecha de inicio
                                @endif
                            </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>


            @php
            $pendingDues = $order->dues->where('paid', 0);
            $s5 = '';
            if(count($pendingDues) > 1) {
                $s5 = 's';
            }
            @endphp

            <p>Y tu{{$s5}} proximo{{$s5}} pago{{$s5}} queda{{$s5 ? 'n' : ''}} de la siguiente manera</p>
            <table style="width: 100%; border: 1px solid #333; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #333; padding: 5px;">Fecha</th>
                        <th style="border: 1px solid #333; padding: 5px;">Monto</th>
                        <th style="border: 1px solid #333; padding: 5px;">Estado</th>
                        <th style="border: 1px solid #333; padding: 5px;">Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i=0; @endphp
                    @foreach($pendingDues as $due)
                    @php
                    $acc += $due->amount;
                    @endphp
                    <tr>
                        <td style="border: 1px solid #333; padding: 5px;">{{ DateTime::createFromFormat('Y-m-d', $due->date)->format('d/m/Y')}} </td>
                        <td style="border: 1px solid #333; padding: 5px;">{{$due->amount}} {{$order->currency->iso_code}}</td>
                        <td style="border: 1px solid #333; padding: 5px;">
                            <span
                            style="

                        background: {{$due->paid ? '#79c970' : '#c58d8d'}};
                        color: {{$due->paid ? '#092d05' : '#3b0606'}};
                        padding: 5px 14px;
                        border-radius: 8px;"
                            "
                            >{{$due->paid ? 'Pagada' : 'Pendiente por pagar'}}</span></td>
                            <td style="border: 1px solid #333; padding: 5px;width: 30%">
                                @if($acc >= $order->price_amount / 2 && !$fif)
                                @php $fif = true; @endphp
                                Con este podras acceder al 50% del contenido pregrabado del curso, el dia de su fecha de inicio
                                @endif
                                @if($i == count($pendingDues)-1)
                                Con este ultimo pago podras acceder a 100% del material pregrabado del curso, el dia de su fecha de inicio
                                {{-- CON ESTE ULTIMO PAGO ACCEDERÁ AL 100% DEL MATERIAL PREGRABADO DEL CURSO, EL DÍA DE SU FECHA DE INICIO --}}
                                @endif
                            </td>
                    </tr>
                    @php $i++; @endphp
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

                Siempre y cuando hayas mantenido las fechas puntuales en los primeros pagos indicados anteriormente
            </p>

            <p>Recuerda que te matriculaste con un precio PROMOCIONAL, el cual está sujeto a tus pagos dentro de las fechas acordadas por ti mismo.</p>



            <!-- Puntos a tener en cuenta -->
            <p>PUNTOS A TENER EN CUENTA:</p>
            <ul>
                <li>✅ Te enviaremos un correo con tus accesos el día de tu fecha de inicio.</li>
                <li>✅ La instalación se realizará el mismo día de la fecha de inicio, y será agendada con unos días de anticipación, por lo tanto es importante tu pago puntual.</li>
                <li>✅ El no cumplir con el agendamiento de la instalación, no te eximirá de los pagos acordados previamente, ni del inicio de tu licencia SAP.</li>
                <li>✅ El tiempo de licencia y aula virtual de tu curso, es de {{$order->orderCourses->first()->license}}.</li>
                <li>✅ Dentro de este tiempo, debes realizar y aprobar tu examen de certificación teórico-práctico. Las condiciones para rendirlo, se encuentran en tu aula virtual.</li>
                <li>✅ Tus cursos gratuitos los podrás habilitar una vez hayas completado tus pagos, y los tiempos de cada uno se han detallado en tu confirmación de compra.</li>
                <li>✅ Te recuerdo que, al 5to día de retraso, tus accesos serán bloqueados.</li>
                <li>✅ Te recuerdo que, a partir del 2do día de retraso, empieza a correr la mora indicada en tu ficha de matrícula. Evita los retrasos o podrías perder el precio promocional.</li>

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

            <p>Nuestro horario de atención comprende ⏰📅<br>Lunes a Viernes de 9am a 7pm (Hora Perú 🇵🇪)<br>Sábados de 9am a 5pm (Hora Perú 🇵🇪)<br>Los DOMINGOS NO laboramos.</p>

            <p>¡Bienvenido/a a la familia Global Tecnologías Academy! 🤩</p>
        </div>
    </div>
</body>
</html>
