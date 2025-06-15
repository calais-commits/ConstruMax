<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

//Variables para mensajes de operación
$message = '';
$messageType = '';

// ariables para el formulario de edición/adición
$productoEditar = null;
$formTitle = 'Agregar Nuevo Producto';
$submitButtonName = 'agregar_producto';
$submitButtonText = 'Agregar Producto';

//Gestión de Mensajes de Sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    // Limpiar mensajes de sesión después de mostrarlos
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

//Procesar Operaciones CRUD (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Lógica para AGREGAR o EDITAR producto
    if (isset($_POST['agregar_producto']) || isset($_POST['editar_producto'])) {
        try {
            //Validar campos obligatorios
            if (empty($_POST['nombre']) || empty($_POST['precio']) || empty($_POST['stock'])) {
                throw new Exception("El Nombre, Precio y Stock son obligatorios.");
            }

            $is_editing = isset($_POST['editar_producto']);
            $producto_id = $is_editing ? $_POST['id'] : null;

            if ($is_editing) {
                //Consulta SQL para ACTUALIZAR
                $query = "UPDATE productos SET 
                          nombre = :nombre,
                          descripcion = :descripcion,
                          precio = :precio,
                          stock = :stock,
                          proveedor_id = :proveedor_id
                          WHERE id = :id";
            } else {
                //Consulta SQL para INSERTAR
                $query = "INSERT INTO productos (nombre, descripcion, precio, stock, proveedor_id) 
                          VALUES (:nombre, :descripcion, :precio, :stock, :proveedor_id)";
            }

            $stmt = $db->prepare($query);

            $stmt->bindParam(':nombre', $_POST['nombre']);
            $stmt->bindParam(':descripcion', $_POST['descripcion']);
            $stmt->bindParam(':precio', $_POST['precio']);
            $stmt->bindParam(':stock', $_POST['stock'], PDO::PARAM_INT);

            //Manejar proveedor_id (puede ser nulo si no se selecciona)
            $proveedor_id = !empty($_POST['proveedor_id']) ? $_POST['proveedor_id'] : null;
            $stmt->bindParam(':proveedor_id', $proveedor_id, PDO::PARAM_INT);

            if ($is_editing) {
                $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
            }

            if ($stmt->execute()) {
                $message = $is_editing ? "Producto actualizado correctamente." : "Producto agregado correctamente.";
                $messageType = "success";
                //Redirigir para limpiar el formulario y evitar reenvío al refrescar
                header("Location: productos.php");
                exit();
            } else {
                throw new Exception($is_editing ? "Error al actualizar el producto." : "Error al agregar el producto.");
            }
        } catch (PDOException $e) {
            $message = "Error en la base de datos: " . $e->getMessage();
            $messageType = "error";
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = "error";
        }
    }
    //Lógica para ELIMINAR producto
    elseif (isset($_POST['eliminar_producto']) && isset($_POST['id'])) {
        try {
            $producto_id = $_POST['id'];

            //Eliminar el producto
            $stmt_delete = $db->prepare("DELETE FROM productos WHERE id = ?");
            $stmt_delete->execute([$producto_id]);

            $_SESSION['message'] = "Producto eliminado correctamente.";
            $_SESSION['message_type'] = "success";
            header("Location: productos.php"); //Redirigir para actualizar la lista
            exit();
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = "error";
            header("Location: productos.php"); //Redirigir incluso si hay error para mostrar el mensaje
            exit();
        }
    }
}

//Obtener datos del producto para edición (si se solicita vía GET)
if (isset($_GET['editar'])) {
    $query = "SELECT * FROM productos WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['editar'], PDO::PARAM_INT);
    $stmt->execute();
    $productoEditar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($productoEditar) {
        $formTitle = 'Editar Producto';
        $submitButtonName = 'editar_producto';
        $submitButtonText = 'Guardar Cambios';
    } else {
        //Si el producto no se encuentra, ignorar el parámetro de edición
        $_SESSION['message'] = "Producto no encontrado para editar.";
        $_SESSION['message_type'] = "error";
        header("Location: productos.php"); //Redirigir para limpiar URL
        exit();
    }
}

//Obtener la lista de todos los productos para mostrar en la tabla
$query_productos = "SELECT p.*, pr.nombre AS proveedor_nombre 
                    FROM productos p 
                    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
                    ORDER BY p.nombre";
$stmt_productos = $db->prepare($query_productos);
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

//Obtener la lista de proveedores para el select (tanto para agregar como editar) 
$query_proveedores_select = "SELECT id, nombre FROM proveedores ORDER BY nombre";
$stmt_proveedores_select = $db->prepare($query_proveedores_select);
$stmt_proveedores_select->execute();
$proveedores_select = $stmt_proveedores_select->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Gestión de Inventario</p>
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
        <h2>Gestión de Productos</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><?php echo htmlspecialchars($formTitle); ?></h3>
            <form method="POST" action="productos.php">
                <?php if ($productoEditar): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($productoEditar['id']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" required
                        value="<?php echo htmlspecialchars($productoEditar['nombre'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($productoEditar['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required
                            value="<?php echo htmlspecialchars($productoEditar['precio'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" id="stock" name="stock" min="0" required
                            value="<?php echo htmlspecialchars($productoEditar['stock'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="proveedor_id">Proveedor:</label>
                    <select id="proveedor_id" name="proveedor_id">
                        <option value="">-- Seleccione un proveedor --</option>
                        <?php foreach ($proveedores_select as $prov): ?>
                            <option value="<?php echo htmlspecialchars($prov['id']); ?>"
                                <?php echo ($productoEditar && $prov['id'] == $productoEditar['proveedor_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prov['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="<?php echo htmlspecialchars($submitButtonName); ?>" class="btn btn-submit">
                    <?php echo htmlspecialchars($submitButtonText); ?>
                </button>
                <?php if ($productoEditar): //Mostrar botón Cancelar solo en modo edición 
                ?>
                    <a href="productos.php" style="color:white;text-decoration: none">
                        <button type="button" class="btn btn-delete" style="padding:10px 15px">Cancelar</button>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <h3>Listado de Productos</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Proveedor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)); ?>...</td>
                                <td>$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                                <td><?php echo htmlspecialchars($producto['proveedor_nombre'] ?? 'N/A'); ?></td>
                                <td class="actions">
                                    <a href="productos.php?editar=<?php echo htmlspecialchars($producto['id']); ?>" style="color:white;text-decoration: none;"><button class="btn btn-edit" style="padding:10px 15px; background-color:#3498db" type="submit">Editar</button></a>
                                    <form action="productos.php" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar este producto?');" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['id']); ?>">
                                        <button type="submit" name="eliminar_producto" class="btn btn-delete" style="background-color: red">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No hay productos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer style="position:unset">
        <p>Ferretería ConstruMax &copy; <?php echo date('Y'); ?> - Sistema de Gestión</p>
    </footer>
</body>

</html>