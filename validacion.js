function validarFormulario() {
    const password = document.getElementById('password').value;

    if (password.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres.');
        return false;
    }

    return true;
}
