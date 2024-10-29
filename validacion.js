function validarFormulario() {
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Validación del nombre
    if (nombre.trim() === "") {
        alert("Por favor, ingresa tu nombre.");
        return false;
    }

    // Validación del correo electrónico
    if (email.trim() === "") {
        alert("Por favor, ingresa tu correo electrónico.");
        return false;
    }

    // Validación de la contraseña (mínimo 6 caracteres)
    if (password.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }

    return true; // Permite el envío del formulario si todos los campos son válidos
}

function valFormMiembro() {
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const telefono = document.getElementById('telefono').value;

    // Validación del nombre
    if (nombre.trim() === "") {
        alert("Por favor, ingresa tu nombre.");
        return false;
    }

    // Validación del correo electrónico
    if (email.trim() === "") {
        alert("Por favor, ingresa tu correo electrónico.");
        return false;
    }

    // Validación de la contraseña (mínimo 6 caracteres, solo si se proporciona)
    if (password.length > 0 && password.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres si se proporciona.");
        return false;
    }

    // Validación del teléfono (solo si se proporciona y tiene exactamente 9 dígitos)
    if (telefono.trim().length > 0) { // Verifica solo si el campo no está vacío
        const telefonoRegex = /^\d{9}$/;
        if (!telefonoRegex.test(telefono)) {
            alert("Por favor, ingresa un número de teléfono válido de exactamente 9 dígitos.");
            return false;
        }
    }

    return true; // Permite el envío del formulario si todos los campos son válidos
}



