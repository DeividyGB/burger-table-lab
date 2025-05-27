<?php
session_start();
include('Functions/connectionDB.php');

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtros
$filter_date_start = $_GET['date_start'] ?? '';
$filter_date_end = $_GET['date_end'] ?? '';
$filter_cliente = $_GET['cliente'] ?? '';

// Query base
$where_conditions = [];
$params = [];
$param_types = '';

if ($filter_date_start) {
    $where_conditions[] = "DATE(closed_at) >= ?";
    $params[] = $filter_date_start;
    $param_types .= 's';
}

if ($filter_date_end) {
    $where_conditions[] = "DATE(closed_at) <= ?";
    $params[] = $filter_date_end;
    $param_types .= 's';
}

if ($filter_cliente) {
    $where_conditions[] = "cliente_nome LIKE ?";
    $params[] = '%' . $filter_cliente . '%';
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Contar total de registros
$count_sql = "SELECT COUNT(*) as total FROM order_history $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Buscar registros
$sql = "SELECT * FROM order_history $where_clause ORDER BY closed_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Estatísticas gerais
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(items_count) as total_items
    FROM order_history $where_clause";
$stats_stmt = $conn->prepare($stats_sql);
if (!empty($where_conditions)) {
    $stats_param_types = substr($param_types, 0, -2); // Remove os últimos 'ii' (limit e offset)
    $stats_params = array_slice($params, 0, -2);
    if (!empty($stats_params)) {
        $stats_stmt->bind_param($stats_param_types, ...$stats_params);
    }
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Pedidos - Burger Table</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stats-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.9;
        }
        
        .filters-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .history-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        
        .table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #eee;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #28a745;
            color: white;
        }
        
        .download-btn {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background: #138496;
            color: white;
            transform: translateY(-1px);
        }
        
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="d-flex"><i class="ph ph-fork-knife"></i></i>
            </div>
            <div class="app-name">BurgerTable</div>
        </div>

        <div class="menu">
            <a href="index.php" class="menu-item">
                <i class="ph ph-house-line"></i>
                Pedidos
            </a>
            <!-- <a href="#" class="menu-item">
                <i class="ph ph-fork-knife"></i>
                Mesas
            </a> -->
            <a href="products.php" class="menu-item">
                <i class="ph ph-cube"></i>
                Produtos
            </a>
            <a href="orderHistory.php" class="menu-item active">
                <i class="ph ph-clock-clockwise"></i>
                Histórico
            </a>
        </div>

        <a class="logout">
            Sair
        </a>
    </div>

    <div class="section">
        <div class="header">
            <div class="header-left">
                <div class="d-flex align-items-center">
                    <button id="toggleSidebar" class="btn btn-light me-3"><i class="ph ph-list"></i></button>
                    <div>
                        <h1 class="page-title"><i class="ph ph-clock-clockwise"></i> Histórico de Pedidos</h1>
                    </div>
                </div>
            </div>

            <div class="user-info">
                <div class="user-name">
                    <h4>Diogo da Silva</h4>
                    <p>Gerente do pal mole</p>
                </div>
                <div class="avatar cs">DPM</div>
            </div>
        </div>
        
        <div class="main-content">
            <!-- Estatísticas -->
            <div class="stats-cards">
                <div class="stats-card">
                    <h3><?= number_format($stats['total_orders'] ?? 0) ?></h3>
                    <p>Total de Pedidos</p>
                </div>
                <div class="stats-card">
                    <h3>R$ <?= number_format($stats['total_revenue'] ?? 0, 2, ',', '.') ?></h3>
                    <p>Receita Total</p>
                </div>
                <div class="stats-card">
                    <h3>R$ <?= number_format($stats['avg_order_value'] ?? 0, 2, ',', '.') ?></h3>
                    <p>Ticket Médio</p>
                </div>
                <div class="stats-card">
                    <h3><?= number_format($stats['total_items'] ?? 0) ?></h3>
                    <p>Itens Vendidos</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-card">
                <h5 class="mb-3"><i class="ph ph-funnel"></i> Filtros</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Data Inicial</label>
                        <input type="date" class="form-control" name="date_start" value="<?= htmlspecialchars($filter_date_start) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Final</label>
                        <input type="date" class="form-control" name="date_end" value="<?= htmlspecialchars($filter_date_end) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cliente</label>
                        <input type="text" class="form-control" name="cliente" value="<?= htmlspecialchars($filter_cliente) ?>" placeholder="Nome do cliente">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="ph ph-magnifying-glass"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabela de Histórico -->
            <div class="history-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Mesa</th>
                            <th>Cliente</th>
                            <th>Pessoas</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Aberto em</th>
                            <th>Fechado em</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $row['table_session_id'] ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($row['cliente_nome']) ?></td>
                                    <td>
                                        <i class="ph ph-users"></i> <?= $row['people_count'] ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $row['items_count'] ?> itens</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">R$ <?= number_format($row['total_amount'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($row['opened_at'])) ?></small>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($row['closed_at'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge">Fechado</span>
                                    </td>
                                    <td>
                                        <a href="Functions/downloadReport.php?file=<?= urlencode($row['report_file']) ?>" 
                                           class="download-btn" title="Baixar Relatório">
                                            <i class="ph ph-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="ph ph-empty" style="font-size: 2rem; color: #ccc;"></i>
                                    <p class="mt-2 text-muted">Nenhum pedido encontrado</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>">
                                    <i class="ph ph-caret-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>">
                                    <i class="ph ph-caret-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            <div class="d-flex flex-column align-items-center">
                <span>🍔 Sistema de Pedidos - Burger Table © <?php echo date('Y'); ?>. Todos os direitos reservados.</span>
                <span style="font-size: 0.8rem; color: #777;">Desenvolvido com ❤ Pela equipe Rock Wins 💻</span>
            </div>
        </footer>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $('#toggleSidebar').on('click', function () {
            $('.sidebar').toggleClass('hidden');
        });

        // Auto-submit form quando as datas mudarem
        $('input[name="date_start"], input[name="date_end"]').on('change', function() {
            if ($(this).val()) {
                $(this).closest('form').submit();
            }
        });

        // Tooltip para botões de download
        $('[title]').tooltip();
    </script>
</body>
</html>