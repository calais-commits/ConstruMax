<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$messageType = '';

//Procesar datos del formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_proveedor'])) {
    try {
        $query = "UPDATE proveedores SET 
                  nombre = :nombre,
                  contacto = :contacto,
                  telefono = :telefono,
                  email = :email,
                  direccion = :direccion
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':id', $_POST['id']);
        $stmt->bindParam(':nombre', $_POST['nombre']);
        $stmt->bindParam(':contacto', $_POST['contacto']);
        $stmt->bindParam(':telefono', $_POST['telefono']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':direccion', $_POST['direccion']);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Proveedor actualizado correctamente";
            $_SESSION['message_type'] = "success";
            header("Location: proveedores.php");
            exit();
        }
    } catch(PDOException $e) {
        $message = "Error al actualizar proveedor: " . $e->getMessage();
        $messageType = "error";
    }
}

//Obtener datos del proveedor a editar
if (isset($_GET['id'])) {
    $query = "SELECT * FROM proveedores WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proveedor) {
        $_SESSION['message'] = "Proveedor no encontrado";
        $_SESSION['message_type'] = "error";
        header("Location: proveedores.php");
        exit();
    }
} else {
    header("Location: proveedores.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Editar Proveedor</p>
    </header>
    
    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Inventario</a>
        <a href="#ventas">Ventas</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="#reportes">Reportes</a>
        <a href="logout.php" style="float:right">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
    </nav>
    
    <div class="container">
        <h2>Editar Proveedor: <?php echo htmlspecialchars($proveedor['nombre']); ?></h2>
        
        <?php if($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="editar_proveedor.php">
            <input type="hidden" name="id" value="<?php echo $proveedor['id']; ?>">
            
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($proveedor['nombre']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="contacto">Persona de Contacto:</label>
                    <input type="text" id="contacto" name="contacto" value="<?php echo htmlspecialchars($proveedor['contacto']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($proveedor['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($proveedor['direccion']); ?></textarea>
            </div>
            
            <button type="submit" name="editar_proveedor" class="btn-submit">Guardar Cambios</button>
            <a href="proveedores.php" class="btn-cancel">Cancelar</a>
        </form>
    </div>
    
    <footer>
        <p>Ferretería ConstruMax &copy; <?php echo date('Y'); ?> - Sistema de Gestión</p>
    </footer>
</body>
</html>