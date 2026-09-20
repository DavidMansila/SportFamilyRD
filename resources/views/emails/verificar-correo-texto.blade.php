{{--
    Version en texto plano del correo de verificacion.

    Va como parte alternativa del mismo mensaje (multipart/alternative). No es
    decorativa: un correo que solo lleva HTML puntua peor en los filtros de
    spam, y es lo que ven los clientes configurados para no mostrar HTML.
--}}
Hola {{ $nombre }}, gracias por unirte a {{ $appName }}.

Solo queda un paso: abre esta dirección para confirmar que este correo es tuyo y activar tu cuenta.

{{ $url }}

El enlace caduca en {{ $expiraMinutos }} minutos.

Si no creaste una cuenta en {{ $appName }}, puedes ignorar este mensaje: sin confirmar el enlace no se activa nada.

-- {{ $appName }}
