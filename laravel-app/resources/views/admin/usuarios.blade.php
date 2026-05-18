@extends('admin.dashboard')

@section('content')
<style>
    .admin-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .btn-primary { background: #2980b9; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;}
    .btn-primary:hover { background: #2471a3; }
    .permissions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 10px; }
    .perm-box { border: 1px solid #eee; padding: 10px; border-radius: 4px; background: #fafafa; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background-color: #f2f2f2; }
    .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;}
    .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;}
</style>

<div style="margin-top: 30px;">
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-danger">
            <ul style="margin:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-card">
        <h3>Crear Nuevo Usuario</h3>
        <form method="POST" action="{{ route('admin.usuarios.post') }}">
            @csrf
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex:1;">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex:1;">
                    <label for="password">Contraseña (Tú la defines para compartirla)</label>
                    <input type="password" id="password" name="password" minlength="6" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="id_rol">Rol de Acceso</label>
                    <select id="id_rol" name="id_rol" required>
                        <option value="">Selecciona un rol...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 style="margin-bottom: 5px;">Permisos por Módulo</h4>
            <p style="margin-top: 0; font-size: 13px; color: #666;">Define exactamente qué puede hacer este usuario en cada sección.</p>
            <div class="permissions-grid">
                @foreach($modulos as $modulo)
                    <div class="perm-box">
                        <strong>{{ $modulo->nombre }}</strong>
                        <div style="margin-top: 8px;">
                            <label style="font-weight:normal; display:block; margin-bottom: 3px;"><input type="checkbox" name="modulos[{{ $modulo->id }}][ver]" value="1"> Puede Ver</label>
                            <label style="font-weight:normal; display:block; margin-bottom: 3px;"><input type="checkbox" name="modulos[{{ $modulo->id }}][crear]" value="1"> Puede Crear</label>
                            <label style="font-weight:normal; display:block; margin-bottom: 3px;"><input type="checkbox" name="modulos[{{ $modulo->id }}][editar]" value="1"> Puede Editar</label>
                            <label style="font-weight:normal; display:block; margin-bottom: 3px;"><input type="checkbox" name="modulos[{{ $modulo->id }}][borrar]" value="1"> Puede Borrar</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Registrar Usuario de forma Segura</button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h3>Usuarios Registrados en el Sistema</h3>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo Electrónico</th>
                    <th>Rol Asignado</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $user)
                    <tr>
                        <td>{{ $user->nombre }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->rol_nombre) }}</td>
                        <td>
                            @if($user->activo)
                                <span style="color: green; font-weight: bold;">Activo</span>
                            @else
                                <span style="color: red; font-weight: bold;">Inactivo</span>
                            @endif
                        </td>
                        <td>{{ $user->ultimo_acceso ? date('d/m/Y H:i', strtotime($user->ultimo_acceso)) : 'Nunca' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
