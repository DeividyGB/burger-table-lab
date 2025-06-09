<?php
include('connectionDB.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_name = ($_POST['product_name'] ?? '');
    $product_price = $_POST['product_price'] ?? '';
    $product_description = trim($_POST['product_description'] ?? '');
    $category = $_POST['category'] ?? '';

    if (empty($product_name) || empty($product_price) || empty($product_description) || empty($category)) {
        die("Todos os campos são obrigatórios.");
    }

    $stmt = $conn->prepare("INSERT INTO products (name, price, description, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $product_name, $product_price, $product_description, $category);

    if ($stmt->execute()) {
        header("Location: /burger-table/views/products.php");
    } else {
        echo "Erro ao inserir o produto: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>
