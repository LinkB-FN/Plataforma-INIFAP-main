Setup rápido para desarrolladores

1) Copiar `.env.example` a `.env` y editar los valores de base de datos.

2) Instalar dependencias:

```powershell
cd laravel-app
composer install
```

3) Generar key:

```powershell
php artisan key:generate
```

4) Migraciones (si las vas a usar):

```powershell
php artisan migrate
```

5) Crear usuario de prueba (si se incluyó el seeder):

```powershell
php artisan db:seed --class=TestUserSeeder
```

6) Levantar servidor:

```powershell
php artisan serve
```

Notas:
- No subas `.env` al repositorio.
- El volcado de la base de datos con publicaciones se entregará por separado.
