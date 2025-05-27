<?php
if (
    isset($_POST['table_number']) && !empty($_POST['table_number']) &&
    isset($_POST['name_client']) && !empty($_POST['name_client'])
) {
    include('connectionDB.php');

    $table_number = intval($_POST['table_number']);
    $nome_cliente = $_POST['name_client'];
    $people_count = isset($_POST['people_count']) ? intval($_POST['people_count']) : 0;

    $sql_check_table = "SELECT id FROM tables WHERE table_number = ?";
    $stmt_check_table = $conn->prepare($sql_check_table);
    if (!$stmt_check_table) {
        die("Erro na preparação da verificação da mesa: " . $conn->error);
    }
    $stmt_check_table->bind_param("i", $table_number);
    $stmt_check_table->execute();
    $result = $stmt_check_table->get_result();

    if ($result->num_rows === 0) {
        $sql_insert_table = "INSERT INTO tables (table_number) VALUES (?)";
        $stmt_insert_table = $conn->prepare($sql_insert_table);
        if (!$stmt_insert_table) {
            die("Erro na preparação da inserção da mesa: " . $conn->error);
        }
        $stmt_insert_table->bind_param("i", $table_number);
        if (!$stmt_insert_table->execute()) {
            die("Erro ao inserir nova mesa: " . $stmt_insert_table->error);
        }
        $table_id = $stmt_insert_table->insert_id;
        $stmt_insert_table->close();
    } else {
        $row = $result->fetch_assoc();
        $table_id = $row['id'];
    }
    $stmt_check_table->close();

    echo "ID da Mesa: " . $table_id;

    $sql_session = "INSERT INTO tables_sessions (table_id, client_name, people_count, opened_at) VALUES (?, ?, ?, NOW())";
    $stmt_session = $conn->prepare($sql_session);

    if ($stmt_session) {
        $stmt_session->bind_param("isi", $table_id, $nome_cliente, $people_count);

        if ($stmt_session->execute()) {
            $mesa_session_id = $conn->insert_id;
            $created_at = date('Y-m-d H:i:s');

            header("Location: /burger-table/views/viewMesa.php?id=$mesa_session_id&table_id=$table_id&count_people=$people_count&created_at=" . urlencode($created_at) . "&cliente_nome=" . urlencode($nome_cliente));
            exit;
        } else {
            echo "Erro ao criar sessão da mesa: " . $stmt_session->error;
        }

        $stmt_session->close();
    } else {
        echo "Erro na preparação da sessão da mesa: " . $conn->error;
    }
    
    $conn->close();
} else {
    echo "Por favor, selecione um número de mesa e insira o nome do cliente.";
}
?>