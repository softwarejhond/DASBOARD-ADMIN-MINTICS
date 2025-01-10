<?php
// Actualizar el medio de contacto si se envió el formulario correspondiente
if (isset($_POST['btnActualizarMedioContacto'])) {
    $codigo = $_POST['codigo']; // Obtener el código del usuario desde el formulario
    $nuevoMedioContacto = $_POST['nuevoMedioContacto']; // Obtener el nuevo medio de contacto

    // Consulta SQL para actualizar el campo contactMedium
    $updateSql = "UPDATE user_register SET contactMedium = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    // Usar bind_param correctamente con tipos: 's' para string y 'i' para entero
    $stmt->bind_param('si', $nuevoMedioContacto, $codigo); // Preparar la consulta para ejecutar

    if ($stmt->execute()) {
        $mensajeToast = 'Medio de contacto actualizado correctamente.';
    } else {
        $mensajeToast = 'Error al actualizar el medio de contacto.';
    }
}

?>
<td>
    <form method="POST" class="d-inline" onsubmit="return confirmarActualizacion();">
        <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($row['number_id']); ?>">
        <div class="input-group">
            <select class="form-control" name="nuevoMedioContacto" required>
                <option value="Teléfono" <?php echo ($row['contactMedium'] == 'Teléfono' ? 'selected' : ''); ?>>Teléfono</option>
                <option value="Email" <?php echo ($row['contactMedium'] == 'Email' ? 'selected' : ''); ?>>Email</option>
                <option value="WhatsApp" <?php echo ($row['contactMedium'] == 'WhatsApp' ? 'selected' : ''); ?>>WhatsApp</option>
                <!-- Agrega más opciones si es necesario -->
            </select>
            <div class="input-group-append">
                <button type="submit" name="btnActualizarMedioContacto" class="btn bg-indigo-dark text-white">
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </div>
        </div>
    </form>
</td>