<?php
session_start();

if (isset($_POST['table_session_id']) && !empty($_POST['table_session_id']) && isset($_POST['name_client']) && !empty($_POST['name_client'])) {
    include('connectionDB.php');

    $mesa = intval($_POST['table_session_id']);
    $nome_cliente = $_POST['name_client'];
    $nome_cliente = $_POST['count_people'];

    $sql = "INSERT INTO orders (table_session_id, client_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("is", $mesa, $nome_cliente);

        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;

            $_SESSION['cliente_nome'] = $nome_cliente;
            $_SESSION['created_at'] = date('Y-m-d H:i:s');

            header("Location: /burger-table/views/viewMesa.php?id=$mesa&created_at=" . urlencode($_SESSION['created_at']) . "&cliente_nome=" . urlencode($nome_cliente));;
            exit;
        } else {
            echo "Erro ao inserir pedido: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conn->error;
    }
} else {
    echo "Por favor, selecione um número de mesa e insira o nome do cliente.";
}
?>
