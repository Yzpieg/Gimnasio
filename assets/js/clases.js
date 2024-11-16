function ordenarTabla(columna) {
    const tabla = document.getElementById('tabla-clases');
    const filas = Array.from(tabla.tBodies[0].rows);
    const th = tabla.tHead.rows[0].cells[columna];
    const tipoOrden = th.classList.contains('sorted-asc') ? 'desc' : 'asc';
    const esNumerico = columna >= 5; // Duración y Capacidad son numéricos.

    // Limpiar clases de orden en todas las columnas
    Array.from(tabla.tHead.rows[0].cells).forEach(cell => {
        cell.classList.remove('sorted-asc', 'sorted-desc');
    });

    filas.sort((a, b) => {
        const celdaA = a.cells[columna].innerText.trim();
        const celdaB = b.cells[columna].innerText.trim();

        if (esNumerico) {
            return tipoOrden === 'asc'
                ? parseFloat(celdaA) - parseFloat(celdaB)
                : parseFloat(celdaB) - parseFloat(celdaA);
        } else {
            return tipoOrden === 'asc'
                ? celdaA.localeCompare(celdaB)
                : celdaB.localeCompare(celdaA);
        }
    });

    // Aplicar la nueva clase de orden
    th.classList.add(tipoOrden === 'asc' ? 'sorted-asc' : 'sorted-desc');

    // Reinsertar las filas ordenadas en la tabla
    filas.forEach(fila => tabla.tBodies[0].appendChild(fila));
}

function confirmarEliminacion() {
    return confirm("¿Estás seguro de que deseas eliminar esta clase? Esta acción no se puede deshacer.");
}
function limpiarFormulario() {
    const form = document.querySelector('.search-form'); // Selecciona el formulario
    form.reset(); // Limpia los valores de los campos
    window.location.href = 'clases.php'; // Opcional: Redirige a la página sin parámetros
}
