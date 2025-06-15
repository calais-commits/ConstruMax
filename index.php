<?php
require_once 'config/database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica para el reseteo del contador de ventas
$current_date = date('Y-m-d');

if (!isset($_SESSION['sales_today_date']) || $_SESSION['sales_today_date'] !== $current_date) {

    $_SESSION['sales_today_count'] = 0;
    $_SESSION['sales_today_date'] = $current_date;
}

//Conexión a la base de datos para el dashboard
$database = new Database();
$db = $database->getConnection();

// Obtener datos para el dashboard
$stockCount = 0;

$lowStock = 0;

try {
    //Productos en stock
    $query = "SELECT COUNT(*) as total FROM productos";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stockCount = $stmt->fetch()['total'];

    //Ventas de hoy (monto total) - This still comes from the database
    $query = "SELECT SUM(total) as total_amount FROM ventas WHERE DATE(fecha) = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    $salesTodayAmount = $result['total_amount'] ?? 0;

    //Productos con bajo stock
    $query = "SELECT COUNT(*) as total FROM productos WHERE stock < 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $lowStock = $stmt->fetch()['total'];

} catch(PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    //Optionally: Set a user-facing error message here
}

//Get the real-time session counter
$displaySalesTodayCount = $_SESSION['sales_today_count'] ?? 0;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferretería ConstruMax - Sistema de Gestión</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Sistema Integrado de Gestión</p>
    </header>
    
    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Inventario</a>
        <a href="ventas.php">Ventas</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="reportes.php">Reportes</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="float:right">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        <?php endif; ?>
    </nav>
    <div class="main-wrapper">
        <div class="container" id="inicio">
            <h2>Bienvenido al Sistema de Gestión</h2>
            
            <?php if(isset($_SESSION['login_error'])): ?>
                <div class="alert error">
                    <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                    <?php unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                <p>Por favor inicie sesión para acceder al sistema</p>
            <?php else: ?>
                <p>Seleccione una opción del menú para comenzar</p>
                
                <div class="dashboard">
                    <div class="dashboard-row">
                        <div class="dashboard-panel panel-stock">
                            <h3>Productos en Stock</h3>
                            <p id="stock-count"><?php echo number_format($stockCount); ?></p>
                        </div>
                        <div class="dashboard-panel panel-sales">
                            <h3>Ventas de Hoy</h3>
                            <p id="sales-today">$ <?php echo number_format($salesTodayAmount, 2); ?> (<?php echo $displaySalesTodayCount; ?> Ventas)</p>
                        </div>
                        <div class="dashboard-panel panel-low">
                            <h3>Productos por Agotarse</h3>
                            <p id="low-stock"><?php echo $lowStock; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if(!isset($_SESSION['user_id'])): ?>
    <div class="login-form" id="loginForm">
        <h2>Iniciar Sesión</h2>
        <form id="loginFormElement" action="auth.php" method="POST">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
    <?php endif; ?>
    
    <footer>
        <p>Ferretería ConstruMax &copy; <?php echo date('Y'); ?> - Sistema de Gestión</p>
    </footer>
    
    <script src="assets/script.js"></script>
</body>
</html>