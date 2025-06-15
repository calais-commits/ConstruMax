<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

//Obtener productos disponibles
$productos = $db->query("SELECT id, codigo, nombre, precio, stock FROM productos")->fetchAll();

//Procesar la compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_compra'])) {
    // Guardar los datos de la compra en sesión
    $_SESSION['compra'] = [
        'productos' => $_POST['productos'],
        'fecha' => date('d/m/Y H:i:s'),
        'total' => array_reduce($_POST['productos'], function ($carry, $item) {
            return $carry + ($item['precio'] * $item['cantidad']);
        }, 0)
    ];

    header('Location: compra.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_venta'])) {
    $total_venta = $_POST['total_venta']; 
    $fecha_venta = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? null;

    try {
        $db = (new Database())->getConnection();

        // Insert into ventas table
        $stmt_venta = $db->prepare("INSERT INTO ventas (fecha, total, user_id) VALUES (?, ?, ?)");
        $stmt_venta->execute([$fecha_venta, $total_venta, $user_id]);
        $last_venta_id = $db->lastInsertId();

        //Lógica para iterar en el carrito de ventar 
        //Lógica para actualizar el stock de productos

        $current_date = date('Y-m-d');
        if (!isset($_SESSION['sales_today_date']) || $_SESSION['sales_today_date'] !== $current_date) {
            $_SESSION['sales_today_count'] = 0; // Reset if new day
            $_SESSION['sales_today_date'] = $current_date;
        }
        $_SESSION['sales_today_count']++; // Incrementar contador

        $_SESSION['message'] = "Venta #{$last_venta_id} realizada con éxito.";
        $_SESSION['message_type'] = "success";

        header("Location: index.php"); 
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error al procesar la venta: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        error_log("Error en ventas.php: " . $e->getMessage());
        header("Location: ventas.php"); 
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: ventas.php"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/styles.css">

</head>

<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Módulo de Ventas</p>
    </header>

    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Inventario</a>
        <a href="ventas.php">Ventas</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="proveedores.php">Reportes</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="float:right">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        <?php endif; ?>
    </nav>

    <div class="container">
        <h2>Realizar Venta</h2>

        <form method="POST">
            <div class="product-list">
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card">
                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                        <p>Código: <?= htmlspecialchars($producto['codigo']) ?></p>
                        <p>Precio: $ <?= number_format($producto['precio'], 2) ?></p>
                        <p>Disponibles: <?= $producto['stock'] ?></p>

                        <input type="hidden" name="productos[<?= $producto['id'] ?>][id]" value="<?= $producto['id'] ?>">
                        <input type="hidden" name="productos[<?= $producto['id'] ?>][nombre]" value="<?= $producto['nombre'] ?>">
                        <input type="hidden" name="productos[<?= $producto['id'] ?>][precio]" value="<?= $producto['precio'] ?>">

                        <label>Cantidad:
                            <select name="productos[<?= $producto['id'] ?>][cantidad]">
                                <?php for ($i = 1; $i <= $producto['stock']; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($productos) > 0): ?>
                <div class="form-group" style="text-align: center; margin-top: 20px;">
                    <button type="submit" name="confirmar_compra" class="btn btn-complete">Confirmar Compra</button>
                </div>
            <?php else: ?>
                <div class="alert">
                    No hay productos disponibles para la venta.
                </div>
            <?php endif; ?>
        </form>
    </div>

    <footer>
        <p>Ferretería ConstruMax &copy; <?= date('Y') ?> - Sistema de Gestión</p>
    </footer>
</body>

</html>