// Función para validar el formulario de registro de usuario
function validarFormulario() {
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const contrasenya = document.getElementById('contrasenya').value;
    const confirmarContrasenya = document.getElementById('confirmar_contrasenya').value;

    // Validación del campo de nombre: se asegura de que contenga al menos una letra
    const nombreRegex = /[a-zA-Z]/;
    if (!nombreRegex.test(nombre)) {
        alert("Por favor, ingresa un nombre válido con al menos una letra.");
        return false;
    }

    // Validación del campo de email: verifica que no esté vacío
    if (email.trim() === "") {
        alert("Por favor, ingresa tu correo electrónico.");
        return false;
    }

    // Validación del formato del email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Por favor, ingresa un correo electrónico válido.");
        return false;
    }

    // Validación de la longitud de la contraseña: al menos 6 caracteres si no está vacía
    if (contrasenya && contrasenya.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }

    // Validación de coincidencia de contraseñas: confirma que ambas contraseñas son iguales
    if (contrasenya !== confirmarContrasenya) {
        alert("Las contraseñas no coinciden. Por favor, verifica.");
        return false;
    }

    const mensajeConfirmacion = "Estás a punto de actualizar los datos del usuario.\n\n" +
        "Esta acción no se puede deshacer. Asegúrate de que toda la información sea correcta " +
        "antes de continuar.\n\n" +
        "¿Deseas continuar con la actualización de los datos?";
    const confirmar = confirm(mensajeConfirmacion);
    return confirmar;
}



// Función para validar el formulario de actualización de usuario en la página de perfil
function valFormUsuario() {
    const nombre = document.getElementById('nombre').value;
    const telefono = document.getElementById('telefono').value;
    const password = document.getElementById('contrasenya').value;
    const confirmarPassword = document.getElementById('confirmar_contrasenya').value;

    // Validación del nombre: debe contener al menos una letra
    const nombreRegex = /[a-zA-Z]/;
    if (!nombreRegex.test(nombre)) {
        alert("Por favor, ingresa un nombre válido con al menos una letra.");
        return false;
    }

    // Validación del teléfono: solo se verifica si tiene valor y consta de exactamente 9 dígitos
    const telefonoRegex = /^\d{9}$/;
    if (telefono && !telefonoRegex.test(telefono)) {
        alert("El teléfono debe tener exactamente 9 dígitos.");
        return false;
    }

    // Validación de la contraseña: solo se verifica si tiene al menos 6 caracteres si no está vacía
    if (password && password.length < 6) {
        alert("La contraseña debe tener al menos 6 caracteres.");
        return false;
    }

    // Validación de coincidencia de contraseñas: confirma que ambas contraseñas son iguales si se ingresó una nueva contraseña
    if (password && password !== confirmarPassword) {
        alert("Las contraseñas no coinciden. Por favor, verifica.");
        return false;
    }

    // Si todas las validaciones pasan, se permite el envío del formulario
    return true;
}


function validarFormularioEdicion(tipoFormulario) {
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const experiencia = document.getElementById('experiencia') ? document.getElementById('experiencia').value : null;
    const disponibilidad = document.getElementById('disponibilidad') ? document.getElementById('disponibilidad').value : null;

    // Validación del nombre: debe contener al menos una letra
    const nombreRegex = /[a-zA-Z]/;
    if (!nombreRegex.test(nombre)) {
        alert("Por favor, ingresa un nombre válido con al menos una letra.");
        return false;
    }

    // Validación del campo de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert("Por favor, ingresa un correo electrónico válido.");
        return false;
    }

    // Validación específica para 'monitor' - experiencia y disponibilidad
    if (tipoFormulario === 'monitor') {
        if (experiencia === null || isNaN(experiencia) || experiencia < 0) {
            alert("Por favor, ingresa un número válido para la experiencia (años). Puede ser cero o mayor.");
            return false;
        }
        if (disponibilidad === null || disponibilidad.trim() === "") {
            alert("Por favor, selecciona la disponibilidad.");
            return false;
        }
    }

    // Validación específica para 'miembro' - otros requisitos específicos podrían añadirse aquí

    // Confirmación final para asegurarse
    const mensajeConfirmacion = "Estás a punto de actualizar los datos del " + tipoFormulario + ".\n\n" +
        "Esta acción no se puede deshacer. ¿Deseas continuar con la actualización de los datos?";
    return confirm(mensajeConfirmacion);
}








