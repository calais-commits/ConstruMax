<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

// Mensajes de operaciones
$message = '';
$messageType = '';

// Procesar operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar_proveedor'])) {
        try {
            // Validar datos básicos
            if (empty($_POST['nombre'])) {
                throw new Exception("El nombre del proveedor es obligatorio");
            }

            // Preparar la consulta SQL
            $query = "INSERT INTO proveedores (nombre, contacto, telefono, email, direccion) 
                      VALUES (:nombre, :contacto, :telefono, :email, :direccion)";

            $stmt = $db->prepare($query);

            // Bind parameters
            $stmt->bindParam(':nombre', $_POST['nombre']);
            $stmt->bindParam(':contacto', $_POST['contacto']);
            $stmt->bindParam(':telefono', $_POST['telefono']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->bindParam(':direccion', $_POST['direccion']);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                $message = "Proveedor agregado correctamente";
                $messageType = "success";

                // Limpiar el formulario después de agregar exitosamente
                $_POST = array();
            } else {
                throw new Exception("Error al agregar el proveedor");
            }
        } catch (PDOException $e) {
            $message = "Error en la base de datos: " . $e->getMessage();
            $messageType = "error";
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
    } elseif (isset($_POST['editar_proveedor'])) {
        try {
            $query = "UPDATE proveedores SET 
                      nombre = :nombre, 
                      contacto = :contacto, 
                      telefono = :telefono, 
                      email = :email, 
                      direccion = :direccion
                      WHERE id = :id";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':nombre', $_POST['nombre']);
            $stmt->bindParam(':contacto', $_POST['contacto']);
            $stmt->bindParam(':telefono', $_POST['telefono']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->bindParam(':direccion', $_POST['direccion']);
            $stmt->bindParam(':id', $_POST['id']);

            if ($stmt->execute()) {
                $message = "Proveedor actualizado correctamente";
                $messageType = "success";
            }
        } catch (PDOException $e) {
            $message = "Error al actualizar proveedor: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Obtener lista de proveedores
$query = "SELECT * FROM proveedores ORDER BY nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener proveedor para edición (si se solicita)
$proveedorEditar = null;
if (isset($_GET['editar'])) {
    $query = "SELECT * FROM proveedores WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['editar']]);
    $proveedorEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Gestión de Proveedores</p>
    </header>

    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Inventario</a>
        <a href="ventas.php">Ventas</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="reportes.php">Reportes</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="float:right">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        <?php endif; ?>
    </nav>

    <div class="container">
        <h2>Gestión de Proveedores</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para agregar/editar proveedor -->
        <div class="form-container">
            <h3><?php echo $proveedorEditar ? 'Editar Proveedor' : 'Agregar Nuevo Proveedor'; ?></h3>
            <form method="POST">
                <?php if ($proveedorEditar): ?>
                    <input type="hidden" name="id" value="<?php echo $proveedorEditar['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required
                        value="<?php echo $proveedorEditar ? htmlspecialchars($proveedorEditar['nombre']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contacto">Persona de Contacto:</label>
                        <input type="text" id="contacto" name="contacto"
                            value="<?php echo $proveedorEditar ? htmlspecialchars($proveedorEditar['contacto']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono"
                            value="<?php echo $proveedorEditar ? htmlspecialchars($proveedorEditar['telefono']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email"
                        value="<?php echo $proveedorEditar ? htmlspecialchars($proveedorEditar['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <textarea id="direccion" name="direccion">
                        <?php echo $proveedorEditar ? htmlspecialchars($proveedorEditar['direccion']) : ''; ?>
                    </textarea>
                </div>

                <?php if ($proveedorEditar): ?>
                    <button type="submit" name="editar_proveedor" class="btn btn-edit">Guardar Cambios</button>
                    <a href="proveedores.php" style="color:white;text-decoration: none">
                        <button type="button" class="btn btn-delete" style="padding:10px 15px">Cancelar</button>
                    </a>
                <?php else: ?>
                    <button type="submit" name="agregar_proveedor" class="btn btn-complete">Agregar Proveedor</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de proveedores -->
        <div class="table-container">
            <h3>Listado de Proveedores</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['contacto']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($proveedor['email']); ?></td>
                            <td class="actions">
                                <a href="proveedores.php?editar=<?php echo $proveedor['id']; ?>" style="color:white;text-decoration: none;"><button class="btn btn-edit" style="padding:10px 15px; background-color:#3498db" type="submit">Editar</button></a>
                                <form action="eliminar_proveedor.php" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar este proveedor?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $proveedor['id']; ?>">
                                    <button type="submit" name="eliminar_proveedor" style="background-color:red">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer style="position:unset">
        <p>Ferretería ConstruMax &copy; <?php echo date('Y'); ?> - Sistema de Gestión</p>
    </footer>

    <script src="assets/script.js"></script>
</body>

</html>