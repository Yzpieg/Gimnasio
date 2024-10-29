function validarFormulario() {
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (nombre.trim() === "") {
        alert("Por favor, ingresa tu nombre.");
        return false;
    }

    if (email.trim() === "") {
        alert("Por favor, ingresa tu correo electrónico.");
        return false;
    }

    if (password.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }

    return true; // Permite el envío del formulario si todos los campos son válidos
}
