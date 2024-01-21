<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correo de Términos y Condiciones</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f3f3; color: #333; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="background-color: #0b6e51; color: white; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">Términos y Condiciones</h1>
        </div>

        <div style="padding: 20px;">
            <p>Estimado usuario,</p>
            <p>Gracias por completar con su confirmacion de compra. A continuacion de adjuntamos un link donde puede descargar su confirmacion en PDF. Estaremos encantados de poder aclarar cualquier duda que tenga, un saludo.</p>
            @php
                // Get app url from env
                $app_url = env('APP_URL').'/descargar-orden-pdf/'.$order->key;
            @endphp
            <a href="{{$app_url}}" download style="display: inline-block; background-color: #0b6e51; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-bottom: 15px;">
                Descargar Términos y Condiciones
            </a>
            {{-- <img src="url-de-tu-imagen.jpg" alt="Imagen Relacionada" style="width: 100%; height: auto; margin-bottom: 15px;"> --}}
        </div>

        <div style="background-color: #f8f8f8; padding: 20px; text-align: center; font-size: 14px;">
            <p>Si tiene alguna duda, no dude en contactarnos.</p>
            <p>Contacto: coordinacionacademica@globaltecnologiasacademy.com</p>
        </div>
    </div>
</body>
</html>
