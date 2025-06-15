<?php
require_once 'config/database.php';
require_once 'includes/auth_check.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
  $database = new Database();
  $db = $database->getConnection();

  $proveedor_id = $_POST['id'];

  try {
    // Preparar consulta
    $query = "DELETE FROM proveedores WHERE id = :id";
    $stmt = $db->prepare($query);

    // Bindear datos
    $stmt->bindParam(':id', $proveedor_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
      $_SESSION['message'] = "Proveedor eliminado correctamente.";
      $_SESSION['message_type'] = "success";
    } else {
      $_SESSION['message'] = "Error al eliminar el proveedor.";
      $_SESSION['message_type'] = "error";
    }
  } catch (PDOException $e) {
    $_SESSION['message'] = "Error de base de datos: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
  }
} else {
  $_SESSION['message'] = "Solicitud inválida para eliminar proveedor.";
  $_SESSION['message_type'] = "error";
}

header("Location: proveedores.php");
exit();
