<?php
include('connectionDB.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['table_session_id'], $_POST['product_id'], $_POST['quantity'], $_POST['price'])) {
        
        $table_session_id = intval($_POST['table_session_id']);
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        // Validações básicas
        if ($table_session_id <= 0 || $product_id <= 0 || $quantity <= 0 || $price <= 0) {
            echo "Erro: Valores inválidos fornecidos.";
            echo "<br>table_session_id: $table_session_id";
            echo "<br>product_id: $product_id";
            echo "<br>quantity: $quantity";
            echo "<br>price: $price";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO order_items (table_session_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("iiid", $table_session_id, $product_id, $quantity, $price);
            
            if ($stmt->execute()) {
                $stmt->close();
                
                // Buscar os dados da sessão para fazer o redirect correto
                $sql_session = "SELECT ts.id, ts.client_name, ts.opened_at, ts.people_count 
                               FROM tables_sessions ts 
                               WHERE ts.id = ?";
                $stmt_session = $conn->prepare($sql_session);
                
                if ($stmt_session) {
                    $stmt_session->bind_param("i", $table_session_id);
                    $stmt_session->execute();
                    $result_session = $stmt_session->get_result();
                    
                    if ($result_session->num_rows > 0) {
                        $session_data = $result_session->fetch_assoc();
                        $cliente_nome = urlencode($session_data['client_name']);
                        $created_at = urlencode($session_data['opened_at']);
                        $people_count = $session_data['people_count'];
                        
                        $stmt_session->close();
                        $conn->close();
                        
                        header("Location: ../views/viewMesa.php?id=$table_session_id&cliente_nome=$cliente_nome&created_at=$created_at&count_people=$people_count&success=1");
                        exit;
                    } else {
                        $stmt_session->close();
                        $conn->close();
                        
                        header("Location: ../views/viewMesa.php?id=$table_session_id&success=1");
                        exit;
                    }
                } else {
                    $conn->close();
                    echo "Erro na preparação da query de sessão: " . $conn->error;
                }
            } else {
                echo "Erro ao adicionar item: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            echo "Erro na preparação da query: " . $conn->error;
        }
        
    } else {
        echo "Erro: Dados obrigatórios não fornecidos.";

        $missing = [];
        if (!isset($_POST['table_session_id'])) $missing[] = 'table_session_id';
        if (!isset($_POST['product_id'])) $missing[] = 'product_id';
        if (!isset($_POST['quantity'])) $missing[] = 'quantity';
        if (!isset($_POST['price'])) $missing[] = 'price';
        
        echo "<br>Campos em falta: " . implode(', ', $missing);
        echo "<br>Dados recebidos: ";
        print_r($_POST);
    }
    
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>