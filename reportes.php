<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

// Obtener productos con stock actual
$query = "SELECT p.*, prov.nombre as proveedor 
          FROM productos p
          LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
          WHERE p.stock > 0
          ORDER BY p.nombre";

$stmt = $db->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Inventario - Ferretería ConstruMax</title>
    <link rel="stylesheet" href="assets/styles.css">
    <!-- Coloco aquí los estilos porque otros están tomando prioridad sobre estos -->
    <style>
        /* Estilos específicos para reportes */
        .report-container {
            margin-top: 30px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .report-title {
            color: #2c3e50;
            margin: 0;
        }

        .print-btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .print-btn:hover {
            background-color: #2980b9;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .report-table th {
            background-color: #34495e;
            color: white;
            padding: 12px 15px;
            text-align: left;
        }

        .report-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .report-table tr:hover {
            background-color: #f5f5f5;
        }

        .stock-low {
            color: #e74c3c;
            font-weight: bold;
        }

        .stock-ok {
            color: #27ae60;
        }

        @media print {

            nav,
            .print-btn {
                display: none;
            }

            .report-table {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Ferretería ConstruMax</h1>
        <p>Reportes de Inventario</p>
    </header>

    <nav>
        <a href="index.php">Inicio</a>
        <a href="productos.php">Productos</a>
        <a href="proveedores.php">Proveedores</a>
        <a href="reportes.php" class="active">Reportes</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" style="float:right">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        <?php endif; ?>

    </nav>

    <div class="container">
        <div class="report-header">
            <h2 class="report-title">Inventario Actual</h2>
            <button onclick="window.print()" class="print-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
                    <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                </svg>
                Imprimir
            </button>
        </div>

        <div class="report-container">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Proveedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                <td class="<?php echo $producto['stock'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                    <?php echo $producto['stock']; ?>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($producto['proveedor'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No hay productos registrados con stock</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>Ferretería ConstruMax &copy; <?php echo date('Y'); ?> - Sistema de Gestión</p>
    </footer>

    <script>
        // Resaltar filas con bajo stock
        document.addEventListener('DOMContentLoaded', function() {
            const lowStockRows = document.querySelectorAll('.stock-low');
            lowStockRows.forEach(row => {
                row.closest('tr').style.backgroundColor = '#ffebee';
            });
        });
    </script>
</body>

</html>