{{--
    Correo de verificacion de cuenta.

    Todo el CSS va EN LINEA y la maqueta esta hecha con <table>: los clientes de
    correo (sobre todo Outlook, que renderiza con el motor de Word) ignoran las
    hojas de estilo, flexbox y grid. Lo que aqui parece anticuado es justo lo
    que hace que el correo se vea igual en Gmail, Outlook y Apple Mail.

    El logo se incrusta con $message->embed() en vez de enlazarlo con una URL:
    casi todos los clientes bloquean las imagenes remotas por defecto, asi que
    un <img src="https://..."> aparece roto hasta que el usuario pulsa "mostrar
    imagenes". Incrustado viaja dentro del propio correo y se ve siempre.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Confirma tu correo - {{ $appName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; -webkit-font-smoothing:antialiased;">

    {{-- Preheader: el texto de vista previa que Gmail muestra junto al asunto.
         Oculto en el cuerpo del correo, pero visible en la bandeja de entrada. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; height:0; width:0;">
        Confirma tu correo para activar tu cuenta de {{ $appName }}. El enlace caduca en {{ $expiraMinutos }} minutos.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 14px rgba(0,0,0,0.08);">

                    {{-- Banda de color de la marca --}}
                    <tr>
                        <td style="height:6px; background-color:#a10013; line-height:6px; font-size:0;">&nbsp;</td>
                    </tr>

                    {{-- Cabecera con el logo.

                         $message es la instancia del correo que Laravel inyecta
                         en las vistas de mail; embed() adjunta el fichero y
                         devuelve el "cid:" al que apunta el <img>. Si el fichero
                         no estuviera (por ejemplo, un despliegue sin public/),
                         se cae al nombre en texto en vez de dejar una imagen
                         rota. --}}
                    @php($logo = ($logoPath && is_file($logoPath)) ? $message->embed($logoPath) : null)
                    <tr>
                        <td align="center" style="padding:32px 32px 8px 32px;">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="{{ $appName }}" width="220" style="display:block; width:220px; max-width:100%; height:auto; border:0;">
                            @else
                                <span style="font-size:22px; font-weight:700; color:#1a202c; letter-spacing:-0.5px;">{{ $appName }}</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Titulo --}}
                    <tr>
                        <td align="center" style="padding:16px 32px 0 32px;">
                            <h1 style="margin:0; font-size:24px; line-height:32px; color:#1a202c; font-weight:700;">
                                Confirma tu correo
                            </h1>
                        </td>
                    </tr>

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding:16px 32px 0 32px;">
                            <p style="margin:0 0 16px 0; font-size:16px; line-height:26px; color:#4a5568;">
                                Hola <strong style="color:#1a202c;">{{ $nombre }}</strong>, gracias por unirte a {{ $appName }}.
                            </p>
                            <p style="margin:0 0 8px 0; font-size:16px; line-height:26px; color:#4a5568;">
                                Solo queda un paso: pulsa el botón para confirmar que este correo es tuyo y activar tu cuenta.
                            </p>
                        </td>
                    </tr>

                    {{-- Boton. El <table> con bgcolor es lo que hace que Outlook
                         pinte el fondo del boton; un <a> con background-color a
                         secas se le queda en blanco. --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 8px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="#a10013" style="border-radius:999px;">
                                        <a href="{{ $url }}" target="_blank" style="display:inline-block; padding:14px 36px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:999px; background-color:#a10013;">
                                            Verificar mi cuenta
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Caducidad --}}
                    <tr>
                        <td align="center" style="padding:8px 32px 0 32px;">
                            <p style="margin:0; font-size:14px; line-height:22px; color:#718096;">
                                El enlace caduca en {{ $expiraMinutos }} minutos.
                            </p>
                        </td>
                    </tr>

                    {{-- Enlace alternativo: hay clientes que desactivan los
                         botones, y algunos usuarios abren el correo en un
                         dispositivo y quieren pegarlo en otro. --}}
                    <tr>
                        <td style="padding:24px 32px 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0 0 6px 0; font-size:13px; line-height:20px; color:#718096;">
                                            Si el botón no funciona, copia y pega esta dirección en tu navegador:
                                        </p>
                                        <p style="margin:0; font-size:13px; line-height:20px; word-break:break-all;">
                                            <a href="{{ $url }}" target="_blank" style="color:#a10013; text-decoration:underline;">{{ $url }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Separador --}}
                    <tr>
                        <td style="padding:28px 32px 0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr><td style="height:1px; background-color:#e2e8f0; line-height:1px; font-size:0;">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td style="padding:20px 32px 32px 32px;">
                            <p style="margin:0 0 12px 0; font-size:13px; line-height:20px; color:#718096;">
                                Si no creaste una cuenta en {{ $appName }}, puedes ignorar este mensaje: sin confirmar el enlace no se activa nada.
                            </p>
                            <p style="margin:0; font-size:13px; line-height:20px; color:#a0aec0;">
                                &copy; {{ date('Y') }} {{ $appName }} &middot; República Dominicana
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
