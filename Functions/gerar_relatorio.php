<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include('connectionDB.php');

$sql = "SELECT * FROM order_history";
$result = $conn->query($sql);

$html = '
    <h1 style="text-align:center;">Relatório de Pedidos Fechados</h1>
    <table border="1" width="100%" cellspacing="0" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Qtd Pessoas</th>
            <th>Itens</th>
            <th>Total</th>
            <th>Abertura</th>
            <th>Fechamento</th>
        </tr>
';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '
            <tr>
                <td>' . $row['id'] . '</td>
                <td>' . htmlspecialchars($row['cliente_nome']) . '</td>
                <td>' . $row['people_count'] . '</td>
                <td>' . $row['items_count'] . '</td>
                <td>R$ ' . number_format($row['total_amount'], 2, ',', '.') . '</td>
                <td>' . $row['opened_at'] . '</td>
                <td>' . $row['closed_at'] . '</td>
            </tr>
        ';
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">Nenhum registro encontrado</td></tr>';
}

$html .= '</table>';

// Configurações do DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

// Tamanho e orientação do papel
$dompdf->setPaper('A4', 'landscape');

// Renderiza o HTML como PDF
$dompdf->render();

// Envia o PDF para o navegador
$dompdf->stream("relatorio_pedidos.pdf", array("Attachment" => false));
exit;
?>
