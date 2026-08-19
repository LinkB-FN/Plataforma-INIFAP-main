<!DOCTYPE html>
<html lang="es">
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    <h1 style="color: #611232; font-size: 24px;">Nuevo contenido de tu interés</h1>
    <p>Se publicó contenido nuevo relacionado con <strong>{{ implode(', ', $palabrasCoincidentes) }}</strong>:</p>
    <h2 style="font-size: 20px;">{{ $publicacion->titulo }}</h2>
    @if($publicacion->descripcion)
        <p>{{ $publicacion->descripcion }}</p>
    @endif
    <p><a href="{{ route('publicaciones.index', ['buscar' => $publicacion->titulo]) }}" style="color: #611232;">Ver publicación en la biblioteca</a></p>
    <p style="font-size: 13px; color: #666;">Recibes este correo porque te suscribiste a novedades de la Biblioteca Digital INIFAP.</p>
</body>
</html>
