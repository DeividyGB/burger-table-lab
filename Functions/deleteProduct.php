<?php
    include('connectionDB.php');

    $id = $_POST['id'];

    $sql = "DELETE FROM products WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: /burger-table/views/products.php");
    } else {
        echo "Erro ao deletar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
?>
