# Plataforma INIFAP: guía de instalación y operación

Esta guía explica cómo instalar la Plataforma INIFAP (biblioteca digital de publicaciones técnicas y científicas) en un servidor Debian propio, y cómo mantenerla: respaldos, restauración, actualizaciones y solución de problemas.

Está pensada para la persona que administra el servidor. No hace falta saber Laravel ni PHP: basta con poder entrar al servidor por SSH y ejecutar comandos con `sudo`.

## 1. Qué incluye el paquete

El paquete `plataforma-inifap-<versión>.tar.gz` trae todo lo necesario:

| Contenido | Para qué sirve |
|---|---|
| `laravel-app/` | La aplicación, el esquema de la base de datos y la interfaz ya compilada |
| `deploy/provision-debian.sh` | Instala o actualiza todo el sistema |
| `deploy/backup.sh` | Hace respaldos (manuales o diarios) |
| `deploy/restore.sh` | Restaura un respaldo |
| `deploy/status.sh` | Revisa que todo funcione |
| `deploy/admin-password.sh` | Restablece la contraseña de un usuario |
| `LEEME.txt` | Resumen de los comandos |

Junto al paquete va un archivo `.sha256` para comprobar que se descargó completo.

## 2. Requisitos del servidor

| | Mínimo | Recomendado |
|---|---|---|
| Sistema | Debian 12 o 13, 64 bits, instalación limpia | Debian 13 |
| Procesador | 2 núcleos | 4 núcleos |
| Memoria | 2 GB | 4 GB |
| Disco | 20 GB | Ver el cálculo de abajo |

Además se necesita:

- Acceso por SSH con un usuario que pueda usar `sudo`.
- Conexión a internet durante la instalación, para descargar los paquetes de Debian.
- El puerto 80 (HTTP) abierto hacia los usuarios que consultarán la biblioteca.

**Cómo calcular el disco.** El sistema ocupa unos 3 GB. A eso súmale el tamaño de los archivos que piensan publicar. Si los respaldos se guardan en el mismo disco, cada respaldo copia todos los archivos y se conservan 7; en ese caso reserva unas 8 veces el tamaño de los archivos. Lo ideal es guardar los respaldos en otro disco (ver la sección 7).

La instalación se probó en Debian 12.15 y Debian 13.7.

## 3. Instalación

### 3.1 Copiar el paquete al servidor

Desde la computadora donde descargaste el paquete:

```
scp plataforma-inifap-*.tar.gz plataforma-inifap-*.tar.gz.sha256 usuario@IP-DEL-SERVIDOR:
```

### 3.2 Comprobar y descomprimir

Ya en el servidor:

```
sha256sum -c plataforma-inifap-*.tar.gz.sha256
tar -xzf plataforma-inifap-*.tar.gz
cd plataforma-inifap-*/
```

El primer comando debe responder `OK`. Si no, el archivo llegó dañado: vuelve a copiarlo.

### 3.3 Ejecutar el instalador

```
sudo bash deploy/provision-debian.sh
```

Tarda entre 5 y 15 minutos, según la conexión a internet. El instalador:

1. Instala Nginx (servidor web), PHP, PostgreSQL (base de datos), ffmpeg y Ghostscript (para comprimir videos y PDF).
2. Crea la base de datos `inifap_dp`, con una contraseña aleatoria.
3. Copia la aplicación a `/var/www/inifap` y carga el esquema y el catálogo de publicaciones del sitio anterior.
4. Crea la cuenta de administrador `admin@inifap.gob.mx` con una contraseña aleatoria.
5. Activa el servicio `inifap-worker`, que comprime en segundo plano los archivos que se suben.
6. Configura el sitio web en el puerto 80.

Al terminar verás algo así:

```
==> Done: http://192.168.1.50/
Queue worker: running
Admin account (also saved in /root/inifap-admin.txt, readable only by root):
    URL: http://192.168.1.50
    User: admin@inifap.gob.mx
    Password: 3f9c0a5e7b1d4c2a9e8f6b7a1c2d3e4f
```

**Guarda esa contraseña.** También queda en `/root/inifap-admin.txt`, que solo puede leer el usuario root (`sudo cat /root/inifap-admin.txt`).

### 3.4 Programar los respaldos

```
sudo bash deploy/backup.sh --daily
```

Hace un respaldo en ese momento y programa uno diario a las 2:30 a. m. Más detalles en la sección 7.

### 3.5 Dónde dejar el paquete

No borres la carpeta `plataforma-inifap-<versión>`: los scripts de `deploy/` se ejecutan desde ahí. El respaldo diario no depende de ella.

## 4. Primer acceso

1. Abre en el navegador la dirección que mostró el instalador, por ejemplo `http://192.168.1.50`. Verás el catálogo público.
2. Para administrar, entra a `http://IP-DEL-SERVIDOR/biblioteca/administrador/login` con `admin@inifap.gob.mx` y la contraseña del instalador.
3. **Cambia la contraseña:** en el menú del panel, entra a **Gestión de Usuarios**, pulsa **Editar** en tu cuenta, escribe una contraseña nueva y guarda.

### Crear más usuarios

En **Gestión de Usuarios** puedes crear cuentas y darles permisos por módulo (ver, crear, editar, borrar):

| Módulo | Qué controla |
|---|---|
| Publicaciones técnicas | Publicaciones de tipo técnico |
| Publicaciones científicas | Publicaciones de tipo científico |
| Gestión de Archivos | Ilustraciones y otros archivos |
| Usuarios | Administrar cuentas |

El rol **superadmin** tiene todos los permisos. El sistema no deja desactivar, quitar el rol ni borrar al último superadmin activo, para que nunca se pierda el acceso.

## 5. Subir publicaciones

En el menú del panel, **Subir Documento**. Cada publicación lleva un tipo, título, año, el contenido (un archivo o un enlace, por ejemplo de YouTube) y, si se quiere, una portada.

### Formatos y límites

| Contenido | Formatos | Tamaño máximo |
|---|---|---|
| Documento | PDF | 20 MB |
| Audio | MP3 | 20 MB |
| Video | MP4, MOV, WebM | 200 MB |
| Imagen | JPG, PNG, WebP | 5 MB |
| Portada | JPG, PNG, WebP | 5 MB |

### Compresión automática

Para que la biblioteca cargue rápido y ocupe menos disco, la plataforma convierte cada archivo a un formato más ligero:

| Se sube | Se guarda como | Cuándo |
|---|---|---|
| Imagen o portada | WebP, máximo 2560 px de lado (portadas: 1200 px), con la orientación corregida | Al momento |
| PDF | PDF optimizado para pantalla (150 dpi); solo si queda al menos 10 % más ligero | En segundo plano |
| Video | MP4 H.264 en 720p, compatible con todos los navegadores | En segundo plano |
| MP3 | MP3 a 128 kbps; solo si queda al menos 10 % más ligero | En segundo plano |

Ejemplos reales de las pruebas: una foto de 3 MB quedó en 530 KB; un PDF con fotografías, de 20.8 MB a 456 KB; un video de 33 MB, en 3.2 MB.

Mientras un PDF o video se comprime, la publicación ya está visible y muestra el archivo original; al terminar, se reemplaza solo. Un PDF tarda segundos; un video, más o menos lo que dura (con 4 núcleos, suele ser menos). Los PDF de puro texto casi no se reducen, así que se conservan tal cual.

## 6. Comprobar que todo funciona

```
sudo bash deploy/status.sh
```

Muestra:

- El estado de los servicios.
- Si el sitio responde.
- Cuántas publicaciones y usuarios hay.
- Cuántos archivos esperan compresión y cuántas compresiones fallaron.
- El espacio en disco.
- El último respaldo.
- Las versiones instaladas.
- Los errores recientes.

Todo en orden se ve así:

```
Services
  nginx                        active
  php8.4-fpm                   active
  postgresql                   active
  inifap-worker                active
Website
  http://127.0.0.1/            HTTP 200
```

## 7. Respaldos

### Qué incluye un respaldo

Un respaldo es un solo archivo `.tar` con:

- La base de datos completa: publicaciones, usuarios, permisos y bitácora.
- Todos los archivos subidos: documentos, imágenes, videos y portadas.
- La configuración (`.env`).

### Comandos

| Tarea | Comando |
|---|---|
| Respaldo ahora | `sudo bash deploy/backup.sh` |
| Respaldo diario a las 2:30 a. m. | `sudo bash deploy/backup.sh --daily` |
| Ver los respaldos | `sudo ls -lh /var/backups/inifap/` |

Se guardan en `/var/backups/inifap/` y se conservan los 7 más recientes; los anteriores se borran solos.

Para cambiar la carpeta o cuántos se conservan, indícalo al programarlos. Por ejemplo, en un segundo disco montado en `/mnt/respaldos` y conservando 14:

```
sudo BACKUP_DIR=/mnt/respaldos KEEP=14 bash deploy/backup.sh --daily
```

### Copiar los respaldos fuera del servidor

Un respaldo en el mismo disco no sirve si ese disco falla. Copia los respaldos con regularidad a otra máquina. Por ejemplo, desde otra computadora:

```
scp usuario@IP-DEL-SERVIDOR:/var/backups/inifap/inifap-backup-*.tar .
```

(El usuario necesita permiso de lectura; si no lo tiene, cópialos primero con `sudo` a una carpeta suya.)

El respaldo contiene la contraseña de la base de datos y la clave de la aplicación: guárdalo en un lugar protegido.

## 8. Restaurar un respaldo

```
sudo bash deploy/restore.sh /var/backups/inifap/inifap-backup-20261009-023000.tar
```

Pide escribir `YES` para confirmar. Luego:

1. Detiene el sitio unos segundos.
2. Reemplaza la base de datos con la del respaldo.
3. Reemplaza los archivos subidos. Los que había se mueven a `/var/www/inifap/storage/uploads-before-restore-<fecha>`; bórralos cuando compruebes que todo está bien.
4. Vuelve a encender el sitio.

La configuración del servidor (`.env`) no se toca, porque guarda la contraseña de la base de datos y la dirección de este servidor.

### Pasar la plataforma a un servidor nuevo

1. En el servidor nuevo, instala el paquete (sección 3).
2. Copia el respaldo más reciente del servidor anterior al nuevo.
3. Restáuralo con `restore.sh`.

Los usuarios y sus contraseñas vienen en el respaldo. Las sesiones abiertas se cierran, así que hay que volver a iniciar sesión.

## 9. Actualizar a una versión nueva

1. Haz un respaldo: `sudo bash deploy/backup.sh`.
2. Copia el paquete nuevo al servidor, compruébalo y descomprímelo (secciones 3.1 y 3.2).
3. Desde la carpeta del paquete nuevo, ejecuta `sudo bash deploy/provision-debian.sh`.

El instalador detecta que la plataforma ya existe y la actualiza. Conserva la base de datos, los archivos subidos, la configuración y las contraseñas. Cuando compruebes que todo funciona, puedes borrar la carpeta del paquete anterior.

## 10. Ajustes opcionales

La configuración está en `/var/www/inifap/.env`. Estas variables controlan la compresión; si no aparecen en el archivo, se usan los valores por defecto:

| Variable | Por defecto | Qué hace |
|---|---|---|
| `MEDIA_IMAGE_MAX_SIDE` | 2560 | Lado mayor de las imágenes, en píxeles |
| `MEDIA_COVER_MAX_SIDE` | 1200 | Lado mayor de las portadas, en píxeles |
| `MEDIA_WEBP_QUALITY` | 80 | Calidad de las imágenes WebP (0 a 100) |
| `MEDIA_PDF_PRESET` | ebook | Calidad de los PDF: `screen` (72 dpi), `ebook` (150 dpi) o `printer` (300 dpi) |
| `MEDIA_VIDEO_SHORT_SIDE` | 720 | Resolución de los videos (720 = 720p, 1080 = 1080p) |
| `MEDIA_VIDEO_CRF` | 27 | Compresión del video: más alto, más ligero y menor calidad (23 a 30 es razonable) |
| `MEDIA_AUDIO_KBPS` | 128 | Calidad del audio MP3 |

Para aplicar un cambio:

1. Edita el archivo: `sudo nano /var/www/inifap/.env`.
2. Vuelve a ejecutar el instalador desde la carpeta del paquete: `sudo bash deploy/provision-debian.sh`.

Los cambios solo afectan a los archivos que se suban después.

**Si cambia la IP del servidor:** el sitio sigue funcionando con la IP nueva. Para que los enlaces que genera la plataforma (por ejemplo, en los correos) la usen, cambia `APP_URL` en el mismo archivo y vuelve a ejecutar el instalador.

## 11. Solución de problemas

Empieza siempre por `sudo bash deploy/status.sh`.

| Problema | Qué hacer |
|---|---|
| El sitio no abre | Revisa que `nginx` y `php…-fpm` estén `active`. Reinícialos con `sudo systemctl restart nginx php8.4-fpm` (en Debian 12, `php8.2-fpm`). |
| Los archivos no se comprimen | Revisa que `inifap-worker` esté `active` y mira su registro: `sudo journalctl -u inifap-worker -n 50`. Reinícialo con `sudo systemctl restart inifap-worker`. |
| Hay compresiones fallidas | El archivo original sigue publicado y funciona. Para reintentar: `cd /var/www/inifap && sudo -u www-data php artisan queue:retry all`. |
| "413 Request Entity Too Large" al subir | El archivo supera 200 MB. Comprímelo antes de subirlo. |
| Olvidé la contraseña del administrador | `sudo bash deploy/admin-password.sh` genera una nueva y la muestra. Para otro usuario: `sudo bash deploy/admin-password.sh correo@ejemplo.com`. |
| Me falta espacio en disco | Revisa el tamaño de los archivos y respaldos en `status.sh`. Mueve los respaldos a otro disco o reduce cuántos se conservan (sección 7). |

Registros útiles:

- Aplicación: `/var/www/inifap/storage/logs/laravel.log`
- Servidor web: `/var/log/nginx/error.log`
- Compresión: `sudo journalctl -u inifap-worker`

## 12. Seguridad recomendada

- **Cambia la contraseña del administrador** en cuanto entres por primera vez.
- **Activa un firewall** que deje pasar solo SSH y HTTP:
  ```
  sudo apt install ufw
  sudo ufw allow OpenSSH
  sudo ufw allow 80/tcp
  sudo ufw enable
  ```
- **Activa las actualizaciones de seguridad automáticas** de Debian:
  ```
  sudo apt install unattended-upgrades
  ```
- La base de datos solo acepta conexiones desde el mismo servidor; no hace falta abrir su puerto (5432).
- Los archivos subidos nunca se ejecutan como programas, aunque alguien suba uno malicioso.
- Si en el futuro la plataforma tiene un dominio (por ejemplo `biblioteca.inifap.gob.mx`), conviene activar HTTPS. Pídelo al equipo de desarrollo.

## 13. Referencia

| Elemento | Ubicación |
|---|---|
| Aplicación | `/var/www/inifap` |
| Configuración | `/var/www/inifap/.env` |
| Archivos subidos | `/var/www/inifap/storage/app/public/uploads` |
| Respaldos | `/var/backups/inifap` |
| Credenciales iniciales | `/root/inifap-admin.txt` |
| Base de datos | PostgreSQL, base `inifap_dp`, usuario `inifap_app` |

| Servicio | Función |
|---|---|
| `nginx` | Servidor web |
| `php8.4-fpm` (Debian 13) o `php8.2-fpm` (Debian 12) | Ejecuta la aplicación |
| `postgresql` | Base de datos |
| `inifap-worker` | Comprime los archivos subidos |
| `inifap-backup.timer` | Respaldo diario (si se programó) |
