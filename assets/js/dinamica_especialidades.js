/**
 * Configura la actualización dinámica de monitores al seleccionar una especialidad.
 * @param {string} especialidadSelectId - ID del selector de especialidades.
 * @param {string} monitorSelectId - ID del selector de monitores.
 */
function configurarMonitoresPorEspecialidad(especialidadSelectId, monitorSelectId) {
    const especialidadSelect = document.getElementById(especialidadSelectId);
    const monitorSelect = document.getElementById(monitorSelectId);

    especialidadSelect.addEventListener('change', function () {
        const especialidadOption = this.options[this.selectedIndex];
        const monitoresData = especialidadOption.getAttribute('data-monitores');

        // Limpiar y deshabilitar el desplegable mientras se actualiza
        monitorSelect.innerHTML = '<option value="" disabled selected>Cargando monitores...</option>';
        monitorSelect.disabled = true;

        if (monitoresData) {
            const monitores = monitoresData.split(',');
            monitorSelect.innerHTML = '<option value="" disabled selected>Seleccionar monitor</option>';
            monitores.forEach(monitor => {
                const [id, nombre, disponibilidad] = monitor.split(':');
                if (disponibilidad === 'disponible') { // Solo añadir monitores disponibles
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = nombre;
                    monitorSelect.appendChild(option);
                }
            });
            monitorSelect.disabled = false;

            // Si no hay monitores disponibles
            if (monitorSelect.options.length === 1) {
                monitorSelect.innerHTML = '<option value="" disabled selected>No hay monitores disponibles</option>';
            }
        } else {
            monitorSelect.innerHTML = '<option value="" disabled selected>No hay monitores disponibles</option>';
        }
    });
}
