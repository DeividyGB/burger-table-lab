<?php
    include('connectionDB.php');

    $id = $_POST['product_id'];
    $name = $_POST['product_name'];
    $price = $_POST['product_price'];
    $description = $_POST['product_description'];
    $type = $_POST['category'];

    $sql = "UPDATE products SET name=?, price=?, description=?, type=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $name, $price, $description, $type, $id);

    if ($stmt->execute()) {
        header("Location: /burger-table-lab/views/products.php");
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
?>
