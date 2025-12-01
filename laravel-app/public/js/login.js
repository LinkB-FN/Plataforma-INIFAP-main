// Versión limpia de login.js para Laravel public
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    const email = document.getElementById('email') || document.getElementById('usuario');
    const password = document.getElementById('password') || document.getElementById('contrasena');
    if (!email || !password) return;

    if (!email.value.trim() || !password.value.trim()) {
      e.preventDefault();
      alert('Por favor completa usuario y contraseña.');
      return;
    }

    // Si el formulario apunta al backend de Laravel, dejamos que se envíe normalmente.
  });
});
