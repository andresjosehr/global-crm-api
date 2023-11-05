<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title></title>
        <meta name="generator" content="LibreOffice 7.5.5.2 (Windows)" />
        <meta name="created" content="2023-10-31T13:58:09.841000000" />
        <meta name="changed" content="2023-10-31T13:58:33.956000000" />
        <style type="text/css">
            @page {
                size: 21.59cm 27.94cm;
                margin: 2cm;
            }
            p {
                line-height: 115%;
                margin-bottom: 0.25cm;
                background: transparent;
            }
            td p {
                orphans: 0;
                widows: 0;
                background: transparent;
            }
            a:link {
                color: #000080;
                text-decoration: underline;
            }
        </style>
    </head>
    <body lang="es-US" link="#000080" vlink="#800000" dir="ltr">
        <p style="line-height: 100%; margin-bottom: 0cm;">
            <img
                src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/banner-email.png"
                name="Imagen1"
                align="bottom"
                width="100%"
                height="auto"
                border="0"
            />
        </p>
        <p align="center" style="line-height: 100%; margin-bottom: 0cm;">
            <font face="trebuchet ms, arial, helvetica, sans-serif, sans-serif">
                <font size="6" style="font-size: 24pt;"><b>Retoma tu curso:</b></font>
            </font>
        </p>
        <p align="center" style="line-height: 100%; margin-bottom: 0.5cm;">
            <font face="trebuchet ms, arial, helvetica, sans-serif, sans-serif">
                <font size="6" style="font-size: 24pt;"><b>SAP MM (Logística y Materiales)</b></font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;"><span style="background: #ffffff;">Estimado(a):</span></font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;"><span style="background: #ffffff;">{{{$student['NOMBRE COMPLETO CLIENTE']}}}</span></font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="4" style="font-size: 14pt;">
                        <b><span style="background: #ffffff;">¡Te damos nuevamente la bienvenida a tu curso de
                            {{-- Find course with access "CORREO CONGELAR" --}}
                            @foreach ($student['courses'] as $course)
                                @if ($course['access'] == 'CORREO CONGELAR')
                                    {{$course['name']}}
                                @endif
                            @endforeach
                            !</span></b>
                    </font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;"><span style="background: #ffffff;">EL PROGRAMA DE CAPACITACIÓN DE SAP COMPRENDE:</span></font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;"><span style="background: #ffffff;">50 HORAS TEÓRICO-PRÁCTICAS CERTIFICADAS COMO KEY USER (USUARIO EXPERTO) SAP MM.</span></font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p align="justify" style="margin-bottom: 0cm;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;">
                        <b><span style="background: #ffffff;">Te recordamos la información de los siguientes cursos de obsequio que tienes disponibles:</span></b>
                    </font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm;"><br /></p>
        <table cellpadding="2" cellspacing="2">
            <tr>
                <td style="border-top: 1px double #808080; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding: 0.05cm;">
                    <p align="center">
                        <b>CURSO DE OBSEQUIO</b>
                    </p>
                </td>
                <td style="border-top: 1px double #808080; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding: 0.05cm;">
                    <p align="center">
                        <b>TIEMPO DE AULA VIRTUAL</b>
                    </p>
                </td>
                <td style="border-top: 1px double #808080; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding: 0.05cm;">
                    <p align="center">
                        <b>ESTADO</b>
                    </p>
                </td>
            </tr>
            {{-- Foreach free courses --}}
            @foreach ($student['courses'] as $course)
                @if ($course['type'] == 'free')

            <tr>
                <td style="border-top: none; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding-top: 0cm; padding-bottom: 0.05cm; padding-left: 0.05cm; padding-right: 0.05cm;">
                    <p>
                        <font face="trebuchet ms, arial, helvetica, sans-serif, sans-serif"><font size="3" style="font-size: 12pt;">{{$course['name']}}</font></font>
                    </p>
                </td>
                <td style="border-top: none; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding-top: 0cm; padding-bottom: 0.05cm; padding-left: 0.05cm; padding-right: 0.05cm;">
                    <p>
                        3 meses para culminarlo.
                    </p>
                </td>
                <td style="border-top: none; border-bottom: 1px double #808080; border-left: 1px double #808080; border-right: 1px double #808080; padding-top: 0cm; padding-bottom: 0.05cm; padding-left: 0.05cm; padding-right: 0.05cm;">
                    <p>
                        {{$course['course_status']}}
                    </p>
                </td>
            </tr>
            @endif
            @endforeach
            {{-- End foreach free courses --}}
        </table>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;"><br /></p>
        <p>
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;">
                        Adicionalmente, te comento que cuando desees iniciar alguno de estos cursos, debes indicarle a tu asistente académica para que te indique las fechas de inicio disponible. Si decides iniciar con los cursos de
                        obsequio, sin haber culminado SAP, sólo recibirás una certificación simple sin aval internacional, hasta que te certifiques en SAP.&nbsp;
                    </font>
                </font>
            </font>
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif"><span style="background: #ffffff;">Si en dado caso repruebas o no culminas SAP o DOS cursos en total, pierdes el acceso a los demás ofrecidos.</span></font>
            </font>
        </p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000"><span style="background: #ffffff;">&nbsp;</span></font>
        </p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="4" style="font-size: 14pt;">
                        <b>Si por alguna razón no confirmas la llegada de este correo, no te eximirá de tus pagos si los tuvieras pendientes, ni del comienzo de tu licencia SAP o tiempo de aula virtual.</b>
                    </font>
                </font>
            </font>
        </p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">&nbsp;</p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff; text-decoration: none;">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="center" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="4" style="font-size: 14pt;">
                        <u><b>PROCESOS DE INICIO DE SESIÓN</b></u>
                    </font>
                </font>
            </font>
        </p>
        <p style="margin-bottom: 0cm; background: #ffffff;"><font color="#000000">&nbsp;</font></p>
        <p style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;">
                        <b>Acceso al&nbsp;<u>AULA VIRTUAL</u>:</b>
                    </font>
                </font>
            </font>
        </p>
        <ul>
            <li>
                <p style="margin-bottom: 0cm; background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif"><font size="3" style="font-size: 12pt;">Usuario: {{$student['USUARIO AULA']}}</font></font>
                    </font>
                </p>
            </li>

            <li>
                <p style="background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif"><font size="3" style="font-size: 12pt;">Contraseña: {{$student['USUARIO AULA']}}</font></font>
                    </font>
                </p>
            </li>
        </ul>

        <div style="margin-top:50px;">
            <a target="_blank" style="background: #2c7379; padding: 10px 20px; text-decoration: none; color: white; font-weight: 700; border-radius: 10px;" href="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/Guia-para-uso-del-aula-virtual.pdf">
            Click aqui para descargar tu guía para uso del aula virtual
            </a>
         </div>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;"><font color="#000000">&nbsp;</font></p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;">
                        <b>Acceso al&nbsp;<u>SOFTWARE DE SAP</u>:</b>
                    </font>
                </font>
            </font>
        </p>
        <ul>
            <li>
                <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif"><font size="3" style="font-size: 12pt;">Usuario: {{$student['USUARIO SAP']}}</font></font>
                    </font>
                </p>
            </li>

            <li>
                <p align="justify" style="background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif">
                            <font size="3" style="font-size: 12pt;">Contraseña: CREADA POR TI, desde el inicio de tu capacitación. La misma te indicamos que debías conservar hasta el momento de tu retorno.</font>
                        </font>
                    </font>
                </p>
            </li>
        </ul>

        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;">
                        <i>
                            <b>
                                <span style="background: #feff66;">
                                    Desde el momento que es enviado este correo, ya tienes habilitados tus accesos y puedes ingresar con normalidad. De no ser así, por favor contáctame para validar el error. Quizás hayas olvidado tu clave.
                                </span>
                            </b>
                        </i>
                    </font>
                </font>
            </font>
        </p>
        <p align="justify" style="margin-bottom: 0cm; background: #ffffff;"><font color="#000000">&nbsp;</font></p>
        <p style="margin-bottom: 0cm; background: #ffffff;">
            <font color="#000000">
                <font face="Trebuchet MS, sans-serif">
                    <font size="3" style="font-size: 12pt;"><b>Tendrás habilitada nuevamente la licencia original de SAP y tu aula virtual:</b></font>
                </font>
            </font>
        </p>
        <ul>
            <li>
                <p style="margin-bottom: 0cm; background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif"><font size="3" style="font-size: 12pt;">Desde el día: {{$student['INICIO']}}</font></font>
                    </font>
                </p>
            </li>

            <li>
                <p style="line-height: 100%; background: #ffffff;">
                    <font color="#000000">
                        <font face="Trebuchet MS, sans-serif">
                            <font size="3" style="font-size: 12pt;">
                                Hasta el día:
                            </font>
                        </font>
                        <span style="display: inline-block; border-top: none; border-bottom: 1px dashed #555555; border-left: none; border-right: none; padding-top: 0cm; padding-bottom: 0.05cm; padding-left: 0cm; padding-right: 0cm;">
                            <font face="Trebuchet MS, sans-serif">
                                <font size="3" style="font-size: 12pt;">
                                    {{$student['FIN']}}
                                </font>
                            </font>
                        </span>
                    </font>
                </p>
            </li>
        </ul>

        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff">El tiempo de licencia no depende del cumplimiento del agendamiento por tu parte, ya que se agenda de acuerdo al tiempo y aceptación previa que nos brindes, los cambios posteriores por tu parte, son temas ajenos a la institución. Si tienes un ordenador empresarial, el cual tiene restricciones, no podrás recibir la instalación sin aprobación previa de tu empresa. Por lo que se recomienda tomar las previsiones antes de aceptar la instalación, por lo mencionado anteriormente.</span></span></span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">El acceso a nuestra conexión, es&nbsp;<b>imperativo</b>&nbsp;para el uso del curso, ya que, para realizar las prácticas y el examen de certificación, todo está configurado en nuestro servidor. Si en dado caso no logramos agendar tu instalación, pero recibiste este correo, desde este momento empieza a correr el tiempo de licencia y aula virtual.</span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">Debes culminar los cursos y dar tu examen de certificación en el tiempo brindado para cada uno, el cual te detallo líneas arriba. Si no lo pudieras terminar en el plazo establecidos, podrás pagar por una extensión de tiempo. El costo dependerá de la cantidad de meses adicionales que desee y será brindado por el área académica. Este pago es totalmente opcional, por lo que no estás obligado a pagarlo, si no deseas, pero no recibirás un certificado por participación de ningún curso.</span></font>
                </font>
            </font>
        </p>
        <p style="orphans: 2; widows: 2"><br /> <br /> </p>
        <p align="justify" style="orphans: 2; widows: 2"><br /> <br /> </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2"><a name="1698988202506110072_imgsrc_url_1"></a> <img src="https://globaltecnologiasacademy.com/wp-content/uploads/2023/08/unnamed.gif" name="Imagen2" align="left" width="320" height="276" border="0" />
            <font color="#ff0000">
                <font face="trebuchet ms, arial, helvetica, sans-serif, sans-serif">
                    <font size="5" style="font-size: 18pt"><span style="background: #ffffff">
                            <font face="Roboto"><u><b>AVISO IMPORTANTE:</b></u></font>&nbsp;
                        </span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">Cuando nuestro personal técnico se conecte e ingrese la clave generada por defecto, el sistema te pedirá que&nbsp;<b>CREES UNA NUEVA CONTRASEÑA</b>, para&nbsp;<b>mayor seguridad</b>. Esta contraseña la debes&nbsp;<u><b>anotar o guardar</b></u>, ya que si realizas&nbsp;<b>DOS O MÁS&nbsp;intentos</b>, el usuario se&nbsp;<u><b>BLOQUEARÁ</b></u>&nbsp;y el&nbsp;<b>reseteo del usuario y/o asignación de nuevas credenciales tiene un costo adicional, el cual tendrás que pagar</b>, tal como se te indicó en la ficha de alumno que firmaste al momento de matricularte; así que es importante que&nbsp;<b>no la olvides o que la guardes muy bien, para evitar estos inconvenientes; por temas de seguridad y ética de SAP Open Ecosystem no estamos autorizados a saber ni guardar las claves que crean los alumnos en nuestro servidor</b>. El desbloqueo o asignación de nuevas credenciales,&nbsp;<u>toma al menos 48horas desde el momento en el que hayas realizado el pago</u>&nbsp;correspondiente.</span></font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="center" style="font-variant: normal; letter-spacing: normal; font-style: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="4" style="font-size: 14pt"><u><b><span style="background: #ffffff">MODALIDAD DE ESTUDIO Y SESIONES EN VIVO:</span></b></u></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal">Recuerda que tus clases son pregrabadas y que puedes avanzar a tu propio ritmo. Tendrás al menos 30 horas de sesiones en vivo, distribuidas a lo largo de tu capacitación, pero son para aclarar dudas o consultas en tiempo real con los consultores, cuando algo no te haya quedado muy claro en los vídeos.&nbsp;</span></span></font>
                    </font>
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="font-style: normal"><u><span style="font-weight: normal">No son de asistencia obligatoria</span></u></span></font>
                    </font>
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="font-style: normal"><span style="font-weight: normal">&nbsp;</span></span></font>
                    </font>
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal">y tampoco tocan un tema en específico. Los alumnos se conectan de todos los niveles y hacen todas las consultas que tengan al momento.&nbsp;</span></span></font>
                    </font>
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="font-style: normal"><b>Tú puedes hacer lo mismo, no importa el nivel en el que te encuentres, ya que los consultores están en la disposición de responder todas las dudas.</b></span></font>
                    </font>
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="font-style: normal"><span style="font-weight: normal">&nbsp;</span></span></font>
                    </font>
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal">Estas sesiones se graban y se cuelgan en tu aula para que quienes no hayan podido asistir, las puedan ver luego y se retroalimenten de las preguntas de sus compañeros. Además, tendrás acceso a las sesiones que han sido realizadas durante el año 2021, hasta la actualidad. Te servirán como banco de consulta.&nbsp;</span></span></font>
                    </font>
                </span></font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><b>Tu aula está habilitada 24 horas al día los 7 días de la semana. Sigue las instrucciones detalladas en la guía adjunta al correo, si tienes algún inconveniente al momento de ingresar a tu aula virtual.</b></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">El curso brindado, está diseñado para ser llevado en una computadora, ya que es un curso totalmente práctico y el software sólo puede ser instalado en un ordenador. Si deseas ver clases en una Tablet o celular, podrás, pero no es responsabilidad de la institución cómo reproduzca los vídeos o que realices tus prácticas en el software.</font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">Las sesiones en vivo serán&nbsp;a través de CISCO WEBEX, puedes descargar el app en tu computadora o en tu celular y conectarte, o puedes realizarlo a través del navegador de tu computadora.&nbsp;<b>Estaré enviando el calendario de sesiones en vivo vía WhatsApp</b>&nbsp;para que puedas organizarte y programar tu asistencia vía correo aceptando la invitación, ya que, si no confirman al menos 6 alumnos, la sesión se reprograma.&nbsp;</span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                    <font size="2" style="font-size: 10pt"><span style="background: #ffffff">
                            <font face="Roboto">
                                <font size="3" style="font-size: 12pt"><b>Encontrarás en tu aula virtual los accesos directos para cada sesión en vivo</b></font>
                            </font>
                            <font face="Roboto">
                                <font size="3" style="font-size: 12pt">, siempre y cuando se hayan programado de acuerdo a la disponibilidad de los consultores. Adicionalmente, 15 minutos antes de la sesión en vivo, recibirás un recordatorio para que procedas a conectarte. Por otro lado encontrarás en tu aula virtual, un botón de descarga del calendario programado para el mes en curso y se actualizará cada mes.</font>
                            </font>​
                        </span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="center" style="font-variant: normal; letter-spacing: normal; font-style: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="4" style="font-size: 14pt"><u><b><span style="background: #ffffff">CERTIFICACIÓN Y CÓMO OBTENERLA:</span></b></u></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">El curso SAP cuenta con un examen de certificación, el cual es teórico práctico disponiendo de dos (02) horas para realizarlo; el mismo se lleva a cabo en una plataforma diferente a tu aula virtual, por lo que debes agendarlo con tiempo, y es imperativo que cuentes con nuestra conexión SAP habilitada (debido a que el ambiente del examen está creado en nuestro servidor) y tu usuario de SAP habilitado.</font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Los intentos de examen para certificarte, serán indicados en tu confirmación de compra, y estarán disponibles siempre y cuando completes todo el contenido del curso dentro del tiempo establecido indicado al inicio de este correo, de requerir un intento adicional, podrías optar por el mismo, pero con un costo adicional el cual variará si es presentado antes o después del tiempo de licencia y aula ofrecido, y será brindado por el área de asistencia académica de presentarse el caso.</font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Todos los intentos de examen gratuitos, deben realizarse dentro del tiempo de aula y licencia otorgado, de lo contrario los estarás perdiendo, no son acumulativos. En el caso de que estés optando por la certificación Master, para poder obtenerla tendrás que culminar y aprobar el examen de certificación de cada uno de los cursos especializados a los que accediste según la certificación Master correspondiente; teniendo en cuenta que primero debes culminar un curso, antes de iniciar el otro, no se pueden llevar en simultáneo.</font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Si no culminas el curso de SAP ofrecido dentro del tiempo brindado o no apruebas tu examen de certificación, por los cursos de obsequio ofrecidos y aprobados, sólo recibirás una certificación simple, sin aval internacional. Con respecto a los cursos de obsequio ofrecidos, dispondrás de un lapso de 6 MESES para escoger una fecha de inicio que será brindada por el área de asistencia académica, de no recibir respuesta del alumno lo estaría perdiendo. A su vez, si llegases a reprobar tu curso SAP y uno de los cursos obsequio, perderás el acceso al resto de los cursos que se hubiesen ofrecido inicialmente, aprovecho para comentarte que no brindamos certificados por participación. Al adquirir más de 1 curso SAP, dispones de quince (15) días para iniciar el siguiente, al término del primero. De lo contrario, pasarás a ser alumno en abandono y tendrás que matricularse nuevamente.</font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="center" style="font-variant: normal; letter-spacing: normal; font-style: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="4" style="font-size: 14pt"><u><b><span style="background: #ffffff">INSTALACIONES:</span></b></u></font>
                </font>
            </font>
        </p>
        <p align="center" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">Nuestro personal técnico realizará la instalación necesaria del software SAP GUI (Únicamente en sistema operativo Windows o MAC), como máximo en dos (02) ocasiones. Si requieres una instalación en una tercera ocasión, tendrá un costo adicional. Las instalaciones gratuitas no se renuevan cuando extiendes tu curso. La instalación se realiza a través del programa TeamViewer, bajo tu supervisión.</span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">La conexión la realizará únicamente nuestro personal técnico vía remota, no es posible enviar una guía o manual de conexión, ni tampoco realizar la instalación vía llamada telefónica.</span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="background: #ffffff">Tener en cuenta que, para los cursos de EXCEL EMPRESARIAL, POWER BI o MS PROJECT no brindamos licencias, ni realizamos instalaciones, sin embargo, al inicio del curso encontrarás un pequeño tutorial para obtener una versión del programa.</span></font>
                </font>
            </font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000"><span style="background: #ffffff">&nbsp;</span></font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><b>Si tienes más dudas o consultas, puedes contactarme por WhatsApp y con gusto podemos apoyarte.</b></font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p align="justify" style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Posteriormente, estarás recibiendo al menos una llamada/WhatsApp semanal de mi parte, para ver tu seguimiento académico y recordarte las sesiones en vivo mensualmente.</font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Nuevamente te doy la bienvenida a la familia&nbsp;<b>¡GLOBAL TECNOLOGÍAS ACADEMY!</b></font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Nos encantaría que pases por nuestras redes sociales:</font>
                </font>
            </font>
        </p>
        <p style="orphans: 2; widows: 2"> <span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000"> <span style="letter-spacing: normal"> <span style="background: #ffffff"> <span style="font-variant: normal"> ✅&nbsp; </span> </span> </span> </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"> <span style="letter-spacing: normal"> <span style="font-style: normal"> <span style="font-weight: normal"> <span style="background: #ffffff"> <span style="font-variant: normal">Sitio web:</span> </span> </span> </span> </span> </font>
                </font>
            </font> <span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"> <span style="letter-spacing: normal"> <span style="font-style: normal"> <span style="font-weight: normal"> <span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://globaltecnologiasacademy.com/" target="_blank">globaltecnologiasacademy.com</span></span></u></span></span></font>
                        </font>
                    </font>
                </span></a>
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">/</span></span></span></span></span></font>
                    </font>
                </font>
            </span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Instagram:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://instagram.com/globaltecnologiasacademy?utm_medium=copy_link" target="_blank">instagram.com/globaltecnologiasacademy?utm_medium=copy_link</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Youtube:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.youtube.com/@GlobalTecnologiasAcademy" target="_blank">www.youtube.com/@GlobalTecnologiasAcademy</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Fanpage de facebook:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.facebook.com/globaltecnologiasacademy" target="_blank">www.facebook.com/globaltecnologiasacademy</span></span></u></span></span></font>
                        </font>
                    </font>
                </span></a>
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">/</span></span></span></span></span></font>
                    </font>
                </font>
            </span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Fanpage de facebook Int:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.facebook.com/globaltecnologiasacademyint" target="_blank">www.facebook.com/globaltecnologiasacademyint</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Fanpage de facebook LATAM:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.facebook.com/globaltecnologiasacademylatam/reviews" target="_blank">www.facebook.com/globaltecnologiasacademylatam/reviews</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Fanpage de facebook SAP:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.facebook.com/globaltecnologiasacademysap/reviews" target="_blank">www.facebook.com/globaltecnologiasacademysap/reviews</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Perfil de CREDLY:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="https://www.credly.com/organizations/gacaam-global-tecnologias-academy-sac/badges" target="_blank">www.credly.com/organizations/gacaam-global-tecnologias-academy-s…</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> <br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm">
                <font color="#000000">
                    <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                        <font size="2" style="font-size: 10pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">✅&nbsp;</span></span></span></span></font>
                    </font>
                </font>
            </span>
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><span style="font-variant: normal">Perfil de LINKEDIN:</span></span></span></span></span></font>
                </font>
            </font></span><span style="font-variant: normal">
                <font color="#000000">
                    <font face="Roboto">
                        <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><span style="font-weight: normal"><span style="background: #ffffff"><br /> </span></span></span></span></font>
                    </font>
                </font>
            </span><span style="display: inline-block; border: none; padding: 0cm"><span style="font-variant: normal">
                    <font color="#000080">
                        <font face="Roboto">
                            <font size="3" style="font-size: 12pt"><span style="letter-spacing: normal"><span style="font-style: normal"><u><span style="font-weight: normal"><span style="background: #ffffff"><a href="http://www.linkedin.com/in/global-tecnolog%C3%ADas-academy/" target="_blank">www.linkedin.com/in/global-tecnolog%C3%ADas-academy/</span></span></span></u></span>
                </span></font>
                </font>
                </font></span></a>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">&nbsp;</font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal">Y nos regales un&nbsp;</span></span></font>
                </font>
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="font-style: normal"><b>ME GUSTA o COMENTARIO</b></span></font>
                </font>
                <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                    <font size="2" style="font-size: 10pt"><span style="font-style: normal"><span style="font-weight: normal">&nbsp;<img src="https://mail.zoho.com/zm/sap_html_ef0c339fc4de2428.png" name="Imagen12" align="bottom" width="32" height="32" border="0" /> &nbsp;</span></span></font>
                </font>
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt"><span style="font-style: normal"><span style="font-weight: normal">.</span></span></font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2">
            <font color="#000000">
                <font face="Roboto">
                    <font size="3" style="font-size: 12pt">Saludos cordiales,</font>
                </font>
            </font>
        </p>
        <p style="orphans: 2; widows: 2"><br /> <br /> </p>
        <p style="font-variant: normal; letter-spacing: normal; orphans: 2; widows: 2; margin-bottom: 0cm"><a name="1698988202506110072_imgsrc_url_2"></a>
            <font color="#000000"><img src="http://globaltecnologiasacademy.com/wp-content/uploads/2023/08/3.png" name="Imagen3" align="bottom" width="300" height="38" border="0" /> </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2; margin-bottom: 0cm">
            <font color="#000000">
                <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                    <font size="2" style="font-size: 10pt">Coordinación Académica</font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2; margin-bottom: 0cm">
            <font color="#000000">
                <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                    <font size="2" style="font-size: 10pt">Global Tecnologías Academy S.A.C.</font>
                </font>
            </font>
        </p>
        <p style="font-variant: normal; letter-spacing: normal; font-style: normal; font-weight: normal; orphans: 2; widows: 2; margin-bottom: 0cm">
            <font color="#000000">
                <font face="Lato 2, system-ui, apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif">
                    <font size="2" style="font-size: 10pt">WhatsApp: +51 935 355 105</font>
                </font>
            </font>
        </p>
        <p style="line-height: 100%; margin-bottom: 0cm"><br /> </p>



    </body>
</html>
