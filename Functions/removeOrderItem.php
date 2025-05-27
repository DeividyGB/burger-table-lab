<?php
include('connectionDB.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['item_id'], $_POST['table_session_id'])) {
        
        $item_id = intval($_POST['item_id']);
        $table_session_id = intval($_POST['table_session_id']);
        
        $cliente_nome = isset($_POST['cliente_nome']) ? $_POST['cliente_nome'] : '';
        $created_at = isset($_POST['created_at']) ? $_POST['created_at'] : '';
        $count_people = isset($_POST['count_people']) ? intval($_POST['count_people']) : 0;
        
        if ($item_id <= 0 || $table_session_id <= 0) {
            echo "Erro: Valores inválidos fornecidos.";
            exit;
        }
        
        $stmt_check = $conn->prepare("SELECT id FROM order_items WHERE id = ? AND table_session_id = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("ii", $item_id, $table_session_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $stmt_delete = $conn->prepare("DELETE FROM order_items WHERE id = ? AND table_session_id = ?");
                
                if ($stmt_delete) {
                    $stmt_delete->bind_param("ii", $item_id, $table_session_id);
                    
                    if ($stmt_delete->execute()) {
                        $stmt_delete->close();
                        $stmt_check->close();
                        $conn->close();
                        
                        $redirect_url = "../views/viewMesa.php?id=$table_session_id";
                        if (!empty($cliente_nome)) {
                            $redirect_url .= "&cliente_nome=" . urlencode($cliente_nome);
                        }
                        if (!empty($created_at)) {
                            $redirect_url .= "&created_at=" . urlencode($created_at);
                        }
                        if ($count_people > 0) {
                            $redirect_url .= "&count_people=$count_people";
                        }
                        $redirect_url .= "&removed=1";
                        
                        header("Location: $redirect_url");
                        exit;
                    } else {
                        echo "Erro ao remover item: " . $stmt_delete->error;
                    }
                    
                    $stmt_delete->close();
                } else {
                    echo "Erro na preparação da query de remoção: " . $conn->error;
                }
            } else {
                echo "Erro: Item não encontrado ou não pertence a esta mesa.";
            }
            
            $stmt_check->close();
        } else {
            echo "Erro na preparação da query de verificação: " . $conn->error;
        }
        
    } else {
        echo "Erro: Dados obrigatórios não fornecidos.";
        
        $missing = [];
        if (!isset($_POST['item_id'])) $missing[] = 'item_id';
        if (!isset($_POST['table_session_id'])) $missing[] = 'table_session_id';
        
        echo "<br>Campos em falta: " . implode(', ', $missing);
        echo "<br>Dados recebidos: ";
        print_r($_POST);
    }
    
    $conn->close();
} else {
    echo "Método de requisição inválido.";
}
?>