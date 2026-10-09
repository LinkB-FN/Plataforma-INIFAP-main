@extends('admin.dashboard')

@section('content_inner')
<style>
    .admin-card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .btn-primary { background: #2980b9; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;}
    .btn-primary:hover { background: #2471a3; }
    .btn-secondary { display: inline-block; background: #eee; color: #333; border: 1px solid #ccc; padding: 9px 15px; border-radius: 4px; text-decoration: none; margin-left: 8px; }
    .permissions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 10px; }
    .perm-box { border: 1px solid #eee; padding: 10px; border-radius: 4px; background: #fafafa; }
    .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;}
</style>

<div style="margin-top: 30px;">
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
        <h3>Editar Usuario</h3>
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}">
            @csrf
            @method('PUT')
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex:1;">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                </div>
            </div>
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex:2;">
                    <label for="password">Nueva Contraseña (dejar vacía para conservarla)</label>
                    <input type="password" id="password" name="password" minlength="6">
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="id_rol">Rol de Acceso</label>
                    <select id="id_rol" name="id_rol" required>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected(old('id_rol', $usuario->id_rol) == $rol->id)>{{ ucfirst($rol->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="activo">Estado</label>
                    <select id="activo" name="activo">
                        <option value="1" @selected(old('activo', $usuario->activo) == 1)>Activo</option>
                        <option value="0" @selected(old('activo', $usuario->activo) == 0)>Inactivo</option>
                    </select>
                </div>
            </div>

            <h4 style="margin-bottom: 5px;">Permisos por Módulo</h4>
            <p style="margin-top: 0; font-size: 13px; color: #666;">Los permisos marcados reemplazan a los actuales.</p>
            <div class="permissions-grid">
                @foreach($modulos as $modulo)
                    <div class="perm-box">
                        <strong>{{ $modulo->nombre }}</strong>
                        <div style="margin-top: 8px;">
                            @foreach(['ver' => 'Ver', 'crear' => 'Crear', 'editar' => 'Editar', 'borrar' => 'Borrar'] as $clave => $etiqueta)
                                <label style="font-weight:normal; display:block; margin-bottom: 3px;"><input type="checkbox" name="modulos[{{ $modulo->id }}][{{ $clave }}]" value="1" @checked(old("modulos.$modulo->id.$clave", ($permisos[$modulo->id]->{'puede_'.$clave} ?? false)))> Puede {{ $etiqueta }}</label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Guardar Cambios</button>
                <a href="{{ route('admin.usuarios') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
