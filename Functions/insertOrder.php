<?php
if (isset($_POST['table_session_id']) && !empty($_POST['table_session_id']) && 
    isset($_POST['name_client']) && !empty($_POST['name_client'])) {

    include('connectionDB.php');

    $mesa = intval($_POST['table_session_id']);
    $nome_cliente = $_POST['name_client'];
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;
    $sql = "INSERT INTO orders (table_session_id, client_name, people_count) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("isi", $mesa, $nome_cliente, $people_count);

        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;

            header("Location: /burger-table/views/viewMesa.php?id=$mesa&count_people=$people_count&created_at=" . urlencode($created_at) . "&cliente_nome=" . urlencode($nome_cliente));
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
