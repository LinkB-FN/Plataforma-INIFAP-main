// Login ligero: valida que los campos no estén vacíos y simula inicio de sesión
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('loginForm');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const usuario = document.getElementById('usuario').value.trim();
    const contrasena = document.getElementById('contrasena').value.trim();

    if (!usuario || !contrasena) {
      alert('Por favor completa usuario y contraseña.');
      return;
    }

    // Simulación: redirigir a la página principal.
    // Aquí se integraría la llamada al servidor (fetch / formulario POST).
    window.location.href = 'index.html';
  });
});
