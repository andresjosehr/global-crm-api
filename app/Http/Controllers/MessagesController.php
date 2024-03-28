<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ApiResponseController::response('Consulta Exitosa', 200, Message::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$message = Message::find($id)) {
            return ApiResponseController::response('No se encontró el mensaje', 404);
        }

        return ApiResponseController::response('Consulta Exitosa', 200, $message);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }





    static public function getMessagesEstudiantesRetrasados($studentName)
    {
        $text = 'Hola, ' . $studentName . '

        Espero te encuentres bien. Lamento que todas las opciones que te he brindado, para evitar que pierdas tu inversión, no sean factibles para ti.

        Por lo tanto, tu caso estará pasando al área de finanzas. Con esto queda formalmente cerrado tu proceso con Global Tech Academy.

        Saludos cordiales.
        ';
        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }

    static public function estudiantesConPocoRetraso($studentName, $due, $order)
    {

        $currency = Currency::where('id', $due->currency_id)->orWhere('id', $order->currency_id)->first();
        $text = '¡Hola! ' . $studentName . '

        Te saludamos de parte de *Global Tech Academy*, para recordarte que tienes un *RETRASO* en tu cuota de pago desde el ' . Carbon::parse($due->date)->format('d/m/Y') . ' por el monto de:
        ' . $due->amount . ' ' . $currency->iso_code . '

        Quería saber si haz tenido algún problema o si puedo ayudarte en algo, para que realices tu pago y evites cualquier cobro moratorio:
        Recuerda que te matriculaste con un precio *PROMOCIONAL*, el cual está sujeto a tu pago en las fechas acordadas.

        Si hay algo en lo que te pueda apoyar con respecto a tu retraso, avísame por favor.';

        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }

    static public function estudiantesPagoHoyIniciaManana($studentName, $due)
    {
        $currency = Currency::find($due->currency_id);
        $text = '¡Hola! ' . $studentName . '

        Te saludamos de parte de *Global Tech Academy*, para recordarte que hoy vence tu cuota de pago:
        ' . $due->amount . ' ' . $currency->iso_code . ' por vencer el ' . Carbon::parse($due->date)->format('d/m/Y') . '

        *Para poder agendar tu fecha de instalación, esta cuota debe estar al día.*
        Luego te estaríamos brindando tus accesos tanto al aula virtual, como al servidor de SAP.

        *Coméntame a qué hora estarías realizando tu pago para poder ir coordinando la agenda de instalaciones de mañana en una hora que se acomode a tu disponibilidad*

        Recuerda que te matriculaste con un precio *PROMOCIONAL*, el cual está sujeto a tu pago en las fechas acordadas.

        *Si tienes dudas de cómo realizar el pago, escríbeme para apoyarte.*
        ';

        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }

    static public function estudiantesPagoHoyIniciaFufuro($studentName, $due)
    {
        $currency = Currency::find($due->currency_id);
        $text = '¡Hola!
        ' . $studentName . '

        Te saludamos de parte de *Global Tech Academy*, para recordarte que hoy ' . Carbon::parse($due->date)->format('d/m/Y') . ' vence tu cuota de pago por el monto de: ' . $due->amount . ' ' . $currency->iso_code . '.

        Recuerda que te matriculaste con un precio *PROMOCIONAL*, el cual está sujeto a tu pago en las fechas acordadas.

        Por favor me indicas *a qué hora estaría contando con tu pago*

        *Si tienes dudas de cómo realizar el pago, escríbeme para apoyarte.*';

        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }

    static public function estudiantesInicianTresDias($startDate)
    {

        $text = 'Hola, te escribía para comentarte que el día
        ' . $startDate . '

        Es tu fecha de inicio, y muchos de tus compañeros están realizando su pago hoy, para poder recibir la instalación en el horario de su preferencia.
        Conforme los alumnos van pagando, la agenda se va ocupando y luego sólo quedarán algunos horarios disponibles.

        Por eso aproveché para consultarte, si es que puedes ir adelantando tu pago para hoy, y así agendar de una vez tu instalación para el día de tu inicio.
        De esta manera escoges un horario que se acomode a ti, y no sólo los que queden libres.';

        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }

    static public function estudiantesPagaHoyIniciaLunes($studentName, $due)
    {

        $currency = Currency::find($due->currency_id);
        $text = '¡Hola!
        ' . $studentName . '

        Te saludamos de parte de *Global Tech Academy*, para recordarte que hoy ' . Carbon::parse($due->date)->format('d/m/Y') . ' vence tu cuota de pago por el monto de: ' . $due->amount . ' ' . $currency->iso_code . '.

        *Para poder agendar tu fecha de instalación, esta cuota debe estar al día.*
        Luego te estaríamos brindando tus accesos tanto al aula virtual, como al servidor de SAP.

        *Coméntame a qué hora estarías realizando tu pago para poder ir coordinando la agenda de instalaciones del dia lunes *INICIO* en una hora que se acomode a tu disponibilidad*

        Recuerda que te matriculaste con un precio *PROMOCIONAL*, el cual está sujeto a tu pago en las fechas acordadas.

        *Si tienes dudas de cómo realizar el pago, escríbeme para apoyarte.*';

        // remove space from the beginning of each line
        $text = preg_replace('/^\s+/m', '', $text);

        return $text;
    }
}
