<?php
if (
    isset($_POST['table_id']) && !empty($_POST['table_id']) &&
    isset($_POST['name_client']) && !empty($_POST['name_client'])
) {
    include('connectionDB.php');

    $table_id = intval($_POST['table_id']);
    $nome_cliente = $_POST['name_client'];
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;

    // 1. Inserir nova sessão da mesa
    $sql_session = "INSERT INTO tables_sessions (sess_table_num, opened_at) VALUES (?, NOW())";
    $stmt_session = $conn->prepare($sql_session);

    if ($stmt_session) {
        $stmt_session->bind_param("i", $table_id);

        if ($stmt_session->execute()) {
            $session_id = $conn->insert_id; // precisa trocar de ID da sessão para numero da mesa? ou adicionar outra variável para identificar mesa?
            $created_at = date('Y-m-d H:i:s');

            // 2. Inserir pedido vinculado à sessão
            $sql_order = "INSERT INTO orders (session_id, client_name, people_count) VALUES (?, ?, ?)";
            $stmt_order = $conn->prepare($sql_order);

            if ($stmt_order) {
                $stmt_order->bind_param("isi", $session_id, $nome_cliente, $people_count);

                if ($stmt_order->execute()) {
                    $order_id = $stmt_order->insert_id;

                    header("Location: /burger-table-lab/views/viewMesa.php?session_id=$session_id&count_people=$people_count&created_at=" . urlencode($created_at) . "&cliente_nome=" . urlencode($nome_cliente));
                    exit;
                } else {
                    echo "Erro ao inserir pedido: " . $stmt_order->error;
                }

                $stmt_order->close();
            } else {
                echo "Erro na preparação do pedido: " . $conn->error;
            }
        } else {
            echo "Erro ao criar sessão da mesa: " . $stmt_session->error;
        }

        $stmt_session->close();
    } else {
        echo "Erro na preparação da sessão da mesa: " . $conn->error;
    }

} else {
    echo "Por favor, selecione um número de mesa e insira o nome do cliente.";
}
?>
