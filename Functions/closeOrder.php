<?php
include('connectionDB.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['table_session_id'], $_POST['cliente_nome'], $_POST['created_at'], $_POST['people_count'], $_POST['total_pedido'])) {
        
        $table_session_id = intval($_POST['table_session_id']);
        $cliente_nome = $_POST['cliente_nome'];
        $created_at = $_POST['created_at'];
        $people_count = intval($_POST['people_count']);
        $total_pedido = floatval($_POST['total_pedido']);

        try {
            // Iniciar transação
            $conn->begin_transaction();

            // 1. Buscar todos os itens do pedido antes de removê-los
            $sql_items = "SELECT oi.*, p.name as product_name, p.description 
                         FROM order_items oi 
                         JOIN products p ON oi.product_id = p.id 
                         WHERE oi.table_session_id = ? 
                         ORDER BY oi.created_at ASC";
            
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $table_session_id);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            
            $items_data = [];
            $items_count = 0;
            $calculated_total = 0;
            
            while ($item = $result_items->fetch_assoc()) {
                $items_data[] = $item;
                $items_count++;
                $calculated_total += ($item['price'] * $item['quantity']);
            }
            
            // 2. Gerar relatório CSV
            $report_filename = 'conta_mesa_' . $table_session_id . '_' . date('Y-m-d_H-i-s') . '.csv';
            $report_path = '../reports/' . $report_filename;
            
            // Criar diretório se não existir
            if (!is_dir('../reports/')) {
                mkdir('../reports/', 0755, true);
            }
            
            $csv_file = fopen($report_path, 'w');
            
            // Cabeçalho do CSV
            fputcsv($csv_file, [
                'Mesa', 'Produto', 'Quantidade', 'Preco_Unitario', 'Data_Adicao', 'Subtotal'
            ]);
            
            // Dados do CSV
            foreach ($items_data as $item) {
                fputcsv($csv_file, [
                    $table_session_id,
                    $item['product_name'],
                    $item['quantity'],
                    number_format($item['price'], 2, '.', ''),
                    $item['created_at'],
                    number_format($item['price'] * $item['quantity'], 2, '.', '')
                ]);
            }
            
            // Adicionar linha de total
            fputcsv($csv_file, []);
            fputcsv($csv_file, ['TOTAL', '', '', '', '', number_format($calculated_total, 2, '.', '')]);
            fputcsv($csv_file, ['CLIENTE', $cliente_nome, '', '', '', '']);
            fputcsv($csv_file, ['PESSOAS', $people_count, '', '', '', '']);
            fputcsv($csv_file, ['VALOR_POR_PESSOA', '', '', '', '', number_format($calculated_total / max(1, $people_count), 2, '.', '')]);
            
            fclose($csv_file);
            
            // 3. Inserir no histórico
            $sql_history = "INSERT INTO order_history 
                           (table_session_id, cliente_nome, people_count, total_amount, items_count, opened_at, closed_at, report_file) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt_history = $conn->prepare($sql_history);
            $stmt_history->bind_param("isidiss", 
                $table_session_id, 
                $cliente_nome, 
                $people_count, 
                $calculated_total, 
                $items_count, 
                $created_at, 
                $report_filename
            );
            
            if (!$stmt_history->execute()) {
                throw new Exception("Erro ao inserir no histórico: " . $stmt_history->error);
            }
            
            // 4. Remover itens do pedido
            $sql_delete_items = "DELETE FROM order_items WHERE table_session_id = ?";
            $stmt_delete = $conn->prepare($sql_delete_items);
            $stmt_delete->bind_param("i", $table_session_id);
            
            if (!$stmt_delete->execute()) {
                throw new Exception("Erro ao remover itens: " . $stmt_delete->error);
            }
            
            // 5. Atualizar sessão como fechada
            $sql_close_session = "UPDATE tables_sessions SET closed_at = NOW() WHERE id = ?";
            $stmt_close = $conn->prepare($sql_close_session);
            $stmt_close->bind_param("i", $table_session_id);
            
            if (!$stmt_close->execute()) {
                throw new Exception("Erro ao fechar sessão: " . $stmt_close->error);
            }
            
            // Confirmar transação
            $conn->commit();
            
            // Fechar statements
            $stmt_items->close();
            $stmt_history->close();
            $stmt_delete->close();
            $stmt_close->close();
            $conn->close();
            
            // Redirecionar com sucesso
            $redirect_url = "../views/viewMesa.php?" . http_build_query([
                'id' => $table_session_id,
                'cliente_nome' => urlencode($cliente_nome),
                'created_at' => urlencode($created_at),
                'count_people' => $people_count,
                'closed' => 1,
                'report' => $report_filename
            ]);
            
            header("Location: $redirect_url");
            exit;
            
        } catch (Exception $e) {
            // Reverter transação em caso de erro
            $conn->rollback();
            $conn->close();
            
            // Log do erro (opcional)
            error_log("Erro ao fechar conta: " . $e->getMessage());
            
            // Redirecionar com erro
            $redirect_url = "../views/viewMesa.php?" . http_build_query([
                'id' => $table_session_id,
                'cliente_nome' => urlencode($cliente_nome),
                'created_at' => urlencode($created_at),
                'count_people' => $people_count,
                'error' => urlencode($e->getMessage())
            ]);
            
            header("Location: $redirect_url");
            exit;
        }
        
    } else {
        echo "Erro: Dados obrigatórios não fornecidos.";
        
        $missing = [];
        if (!isset($_POST['table_session_id'])) $missing[] = 'table_session_id';
        if (!isset($_POST['cliente_nome'])) $missing[] = 'cliente_nome';
        if (!isset($_POST['created_at'])) $missing[] = 'created_at';
        if (!isset($_POST['people_count'])) $missing[] = 'people_count';
        if (!isset($_POST['total_pedido'])) $missing[] = 'total_pedido';
        
        echo "<br>Campos em falta: " . implode(', ', $missing);
        echo "<br>Dados recebidos: ";
        print_r($_POST);
    }
    
} else {
    echo "Método de requisição inválido.";
}
?>