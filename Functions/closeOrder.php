<?php
session_start();
require '../vendor/autoload.php';
include('connectionDB.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_session_id = $_POST['table_session_id'];
    $cliente_nome = $_POST['cliente_nome'];
    $created_at = $_POST['created_at'];
    $people_count = $_POST['people_count'];

    try {
        $sql_itens = "SELECT oi.*, p.name as product_name, p.description, p.type 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.table_session_id = ? 
                      ORDER BY oi.created_at ASC";

        $stmt_itens = $conn->prepare($sql_itens);
        $stmt_itens->bind_param("i", $table_session_id);
        $stmt_itens->execute();
        $result_itens = $stmt_itens->get_result();

        $itens = [];
        while ($item = $result_itens->fetch_assoc()) {
            $itens[] = $item;
        }

        if (empty($itens)) {
            throw new Exception("Nenhum item encontrado no pedido.");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório Mesa');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $tableHeaderStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFCCCCCC'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'RELATÓRIO DE FECHAMENTO DE MESA');
        $sheet->getStyle('A1')->applyFromArray($headerStyle);

        $sheet->setCellValue('A3', 'Mesa:');
        $sheet->setCellValue('B3', $table_session_id);
        $sheet->setCellValue('A4', 'Cliente:');
        $sheet->setCellValue('B4', $cliente_nome);
        $sheet->setCellValue('A5', 'Pessoas:');
        $sheet->setCellValue('B5', $people_count);
        $sheet->setCellValue('A6', 'Abertura:');
        $sheet->setCellValue('B6', date('d/m/Y H:i:s', strtotime($created_at)));
        $sheet->setCellValue('A7', 'Fechamento:');
        $sheet->setCellValue('B7', date('d/m/Y H:i:s'));

        $sheet->fromArray(
            ['Produto', 'Categoria', 'Quantidade', 'Preço Unitário', 'Subtotal', 'Data/Hora'],
            null,
            'A9'
        );

        $sheet->getStyle('A9:F9')->applyFromArray($tableHeaderStyle);

        $linha = 10;
        $total_geral = 0;
        foreach ($itens as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $total_geral += $subtotal;

            $sheet->fromArray([
                $item['product_name'],
                ucfirst($item['type']),
                $item['quantity'],
                'R$ ' . number_format($item['price'], 2, ',', '.'),
                'R$ ' . number_format($subtotal, 2, ',', '.'),
                date('d/m/Y H:i:s', strtotime($item['created_at']))
            ], null, 'A' . $linha);

            $sheet->getStyle("A$linha:F$linha")->applyFromArray($borderStyle);
            $linha++;
        }

        $linha += 1;
        $sheet->setCellValue('A' . $linha, '=== RESUMO FINANCEIRO ===');
        $sheet->mergeCells("A$linha:F$linha");
        $sheet->getStyle("A$linha")->applyFromArray($headerStyle);

        $linha++;
        $sheet->setCellValue('A' . $linha, 'Total de Itens:');
        $sheet->setCellValue('B' . $linha, count($itens));

        $linha++;
        $sheet->setCellValue('A' . $linha, 'Valor Total:');
        $sheet->setCellValue('B' . $linha, 'R$ ' . number_format($total_geral, 2, ',', '.'));

        $linha++;
        $sheet->setCellValue('A' . $linha, 'Valor por Pessoa:');
        $sheet->setCellValue('B' . $linha, 'R$ ' . number_format($total_geral / max(1, $people_count), 2, ',', '.'));

        $linha += 2;
        $sheet->setCellValue('A' . $linha, '=== ESTATÍSTICAS POR CATEGORIA ===');
        $sheet->mergeCells("A$linha:F$linha");
        $sheet->getStyle("A$linha")->applyFromArray($headerStyle);

        $linha++;
        $sheet->fromArray(['Categoria', 'Quantidade', 'Valor Total'], null, 'A' . $linha);
        $sheet->getStyle("A$linha:C$linha")->applyFromArray($tableHeaderStyle);

        $stats_categoria = [];
        foreach ($itens as $item) {
            $categoria = ucfirst($item['type']);
            if (!isset($stats_categoria[$categoria])) {
                $stats_categoria[$categoria] = ['quantidade' => 0, 'valor' => 0];
            }
            $stats_categoria[$categoria]['quantidade'] += $item['quantity'];
            $stats_categoria[$categoria]['valor'] += ($item['price'] * $item['quantity']);
        }

        foreach ($stats_categoria as $categoria => $stats) {
            $linha++;
            $sheet->fromArray([
                $categoria,
                $stats['quantidade'],
                'R$ ' . number_format($stats['valor'], 2, ',', '.')
            ], null, 'A' . $linha);
            $sheet->getStyle("A$linha:C$linha")->applyFromArray($borderStyle);
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'relatorio_mesa_' . $table_session_id . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = '../reports/' . $filename;

        if (!file_exists('../reports/')) {
            mkdir('../reports/', 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        $sql_historico = "INSERT INTO order_history (table_session_id, cliente_nome, people_count, total_amount, items_count, opened_at, closed_at, report_file) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

        $stmt_historico = $conn->prepare($sql_historico);
        $items_count = count($itens);
        $stmt_historico->bind_param("isidiss",
            $table_session_id,
            $cliente_nome,
            $people_count,
            $total_geral,
            $items_count,
            $created_at,
            $filename
        );
        $stmt_historico->execute();

        $sql_delete = "DELETE FROM order_items WHERE table_session_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $table_session_id);
        $stmt_delete->execute();

        header("Location: /burger-table/views/viewMesa.php?id=$table_session_id&cliente_nome=" . urlencode($cliente_nome) . "&created_at=" . urlencode($created_at) . "&count_people=$people_count&closed=1&report=" . urlencode($filename));
        exit();

    } catch (Exception $e) {
        header("Location: /burger-table/views/viewMesa.php?id=$table_session_id&cliente_nome=" . urlencode($cliente_nome) . "&created_at=" . urlencode($created_at) . "&count_people=$people_count&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
