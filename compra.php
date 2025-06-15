<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

//Obtener datos de la compra
if(!isset($_SESSION['compra'])) {
    header('Location: ventas.php');
    exit;
}

$compra = $_SESSION['compra'];
$total = $compra['total'];
$impuesto = $total * 0.13;
$subtotal = $total - $impuesto;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Facturación Electrónica</p>
    </header>
    
    <nav>
        <a href="index.php">Inicio</a>
        <a href="ventas.php">Nueva Venta</a>
        <a href="logout.php" style="float:right">Cerrar Sesión</a>
    </nav>
    
    <div class="container">
        <div class="invoice">
            <div class="invoice-header">
                <h2>FACTURA ELECTRÓNICA</h2>
                <!-- Sintáxis de esta línea de código para la factura encontrada en una fórmula en internet -->
                <p>No. FAC-<?= date('Ymd') ?>-<?= str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT) ?></p>
            </div>
            
            <div class="invoice-details">
                <p><strong>Fecha:</strong> <?= $compra['fecha'] ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($_SESSION['nombre']) ?> (Consumidor Final)</p>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unit.</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($compra['productos'] as $producto): ?>
                    <?php if($producto['cantidad'] > 0): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>% <?= number_format($producto['precio'], 2) ?></td>
                        <td><?= $producto['cantidad'] ?></td>
                        <td>$ <?= number_format($producto['precio'] * $producto['cantidad'], 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="invoice-totals">
                <p>Subtotal: $ <?= number_format($subtotal, 2) ?></p>
                <p>Impuesto (13%): $ <?= number_format($impuesto, 2) ?></p>
                <p><strong>Total: $ <?= number_format($total, 2) ?></strong></p>
            </div>
            
            <div class="invoice-footer">
                <p>¡Gracias por su compra!</p>
                <p>Esta es una factura de prueba para fines educativos</p>
            </div>
            
            <div class="invoice-actions">
                <button onclick="window.print()" class="btn">Imprimir Factura</button>
                <a href="ventas.php" style="text-decoration: none;"><button type="submit" class="btn">Nueva Venta</button></a>
            </div>
        </div>
    </div>
    
    <footer style="position:unset">
        <p>Ferretería ConstruMax &copy; <?= date('Y') ?> - Sistema de Gestión</p>
    </footer>
</body>
</html>