<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include('connectionDB.php');

$sql = "SELECT * FROM order_history ORDER BY closed_at DESC";
$result = $conn->query($sql);

// Calcular estatísticas
$total_pedidos = 0;
$total_vendas = 0;
$total_pessoas = 0;

if ($result->num_rows > 0) {
    $result->data_seek(0); // Reset pointer
    while ($row = $result->fetch_assoc()) {
        $total_pedidos++;
        $total_vendas += $row['total_amount'];
        $total_pessoas += $row['people_count'];
    }
    $result->data_seek(0); // Reset pointer again
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --primary: #c11e1e;
            --primary-light: #d54d4d;
            --light-gray: #f5f5f5;
            --dark-text: #2a2522;
            --medium-text: #666;
            --light-text: #888;
            --green: #2ecc71;
            --background-color: #fcf5e9;
        }
        
        @page {
            margin: 20mm;
            size: A4 landscape;
        }
        
        body {
            font-family: Poppins, Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark-text);
            line-height: 1.4;
            font-size: 12px;
            background-color: var(--background-color);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(193, 30, 30, 0.2);
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--medium-text);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: var(--medium-text);
            opacity: 0.9;
        }
        
        .stats-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            background: white;
            border: 1px solid var(--light-gray);
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin: 0 5px;
        }
        
        .stat-box:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }
        
        .stat-box:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: none;
        }
        
        .stat-box:not(:first-child):not(:last-child) {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--medium-text);
            margin: 5px 0 0 0;
            font-weight: 600;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: 1px solid var(--light-gray);
        }
        
        .table-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 15px 20px;
            text-align: center;
            margin-bottom: 0;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .table-header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            position: relative;
            font-size: 11px;
        }
        
        thead {
            background: var(--primary);
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 10px;
        }
        
        tbody tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        tbody tr:hover {
            background-color: #ede7dc;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            background: var(--green);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        
        .badge-items {
            background: var(--primary);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        
        .price {
            font-weight: 600;
            color: var(--primary);
        }
        
        .date {
            font-size: 10px;
            color: var(--medium-text);
        }
        
        .footer {
            text-align: center;
            position: relative;
            padding: 15px;
            background: white;
            border-radius: 6px;
            font-size: 10px;
            color: var(--medium-text);
            border: 1px solid var(--light-gray);
        }
        
        .logo {
            display: inline-block;
            margin-right: 10px;
            font-size: 24px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--medium-text);
            background: white;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .highlight {
            background: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light), var(--primary));
            margin: 20px 0;
            border-radius: 1px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BurgerTable - Relatório de Pedidos</h1>
        <div class="subtitle">Relatório gerado em ' . date('d/m/Y H:i:s') . '</div>
    </div>
    
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">' . $total_pedidos . '</div>
            <div class="stat-label">Total de Pedidos</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">' . $total_pessoas . '</div>
            <div class="stat-label">Mesas Atendidas</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">' . $total_pessoas . '</div>
            <div class="stat-label">Total de Clientes</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">R$ ' . number_format($total_vendas, 2, ',', '.') . '</div>
            <div class="stat-label">Receita Total</div>
        </div>
    </div>
    
    <div class="section-divider"></div>';

if ($result->num_rows > 0) {
    $html .= '
    <div class="table-container">
        <div class="table-header">
            <h2>Detalhamento dos Pedidos</h2>
            <p>Histórico completo de todos os pedidos fechados - Ordenados por data de fechamento</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th width="8%" class="text-center">ID do Pedido</th>
                    <th width="25%"> Nome do Cliente</th>
                    <th width="10%" class="text-center">Pessoas</th>
                    <th width="10%" class="text-center">Itens</th>
                    <th width="12%" class="text-right">Valor Total</th>
                    <th width="17%">Data/Hora Abertura</th>
                    <th width="18%">Data/Hora Fechamento</th>
                </tr>
            </thead>
            <tbody>';

            while ($row = $result->fetch_assoc()) {
                $abertura = date('d/m/Y H:i', strtotime($row['opened_at']));
                $fechamento = date('d/m/Y H:i', strtotime($row['closed_at']));
                
                $html .= '
                        <tr>
                            <td class="text-center"><span class="highlight">#' . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . '</span></td>
                            <td><strong>' . htmlspecialchars($row['cliente_nome']) . '</strong></td>
                            <td class="text-center"><span class="badge">' . $row['people_count'] . '</span></td>
                            <td class="text-center"><span class="badge-items">' . $row['items_count'] . '</span></td>
                            <td class="text-right price">R$ ' . number_format($row['total_amount'], 2, ',', '.') . '</td>
                            <td class="date">' . $abertura . '</td>
                            <td class="date">' . $fechamento . '</td>
                        </tr>';
            }

    $html .= '
            </tbody>
        </table>
    </div>';
} else {
    $html .= '
    <div class="empty-state">
        <h3>Nenhum registro encontrado</h3>
        <p>Não há pedidos fechados para exibir no relatório.</p>
    </div>';
}

$html .= '
    <div class="footer">
        <div>Sistema de Pedidos - Burger Table © ' . date('Y') . '. Todos os direitos reservados.</div>
        <div style="margin-top: 5px;">Desenvolvido com Pela equipe Rock Wins</div>
    </div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "relatorio_pedidos_" . date('Y-m-d_H-i-s') . ".pdf";
$dompdf->stream($filename, array("Attachment" => false));
exit;
?>