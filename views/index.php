<?php
session_start();
include('../Functions/connectionDB.php');

$filtro_mesa = isset($_GET['mesa']) ? $_GET['mesa'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_data = isset($_GET['data']) ? $_GET['data'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions_active = [];
$where_conditions_history = [];
$params_active = [];
$params_history = [];
$types_active = '';
$types_history = '';

if (!empty($filtro_mesa)) {
    $where_conditions_active[] = "ts.table_id = ?";
    $params_active[] = $filtro_mesa;
    $types_active .= 'i';
    
    $where_conditions_history[] = "oh.table_session_id IN (SELECT id FROM tables_sessions WHERE table_id = ?)";
    $params_history[] = $filtro_mesa;
    $types_history .= 'i';
}

if (!empty($filtro_cliente)) {
    $where_conditions_active[] = "ts.client_name LIKE ?";
    $params_active[] = '%' . $filtro_cliente . '%';
    $types_active .= 's';
    
    $where_conditions_history[] = "oh.cliente_nome LIKE ?";
    $params_history[] = '%' . $filtro_cliente . '%';
    $types_history .= 's';
}

if (!empty($filtro_data)) {
    $where_conditions_active[] = "DATE(oi.created_at) = ?";
    $params_active[] = $filtro_data;
    $types_active .= 's';
    
    $where_conditions_history[] = "DATE(oh.closed_at) = ?";
    $params_history[] = $filtro_data;
    $types_history .= 's';
}

$where_clause_active = '';
if (!empty($where_conditions_active)) {
    $where_clause_active = 'WHERE ' . implode(' AND ', $where_conditions_active);
}

$where_clause_history = '';
if (!empty($where_conditions_history)) {
    $where_clause_history = 'WHERE ' . implode(' AND ', $where_conditions_history);
}

$orders = [];

if ($filtro_status !== 'fechado') {
    $sql_active = "SELECT oi.id, oi.quantity, oi.price, oi.created_at,
                   p.name as product_name, 
                   p.type as product_type, 
                   p.description as product_description,
                   ts.table_id,
                   ts.client_name,
                   ts.people_count,
                   ts.opened_at,
                   ts.closed_at,
                   'ativo' as status_pedido,
                   ts.id as session_id
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id
            $where_clause_active
            AND ts.closed_at IS NULL
            ORDER BY oi.created_at DESC";

    $stmt_active = $conn->prepare($sql_active);
    if (!empty($params_active)) {
        $stmt_active->bind_param($types_active, ...$params_active);
    }
    $stmt_active->execute();
    $result_active = $stmt_active->get_result();

    while ($row = $result_active->fetch_assoc()) {
        $orders[] = $row;
    }
}

if ($filtro_status !== 'ativo') {
    $sql_history = "SELECT 
                    oh.id,
                    1 as quantity,
                    oh.total_amount as price,
                    oh.closed_at as created_at,
                    CONCAT('Conta Fechada - ', oh.items_count, ' itens') as product_name,
                    'fechado' as product_type,
                    CONCAT('Total: R$ ', FORMAT(oh.total_amount, 2, 'pt_BR')) as product_description,
                    ts.table_id,
                    oh.cliente_nome as client_name,
                    oh.people_count,
                    oh.opened_at,
                    oh.closed_at,
                    'fechado' as status_pedido,
                    oh.table_session_id as session_id,
                    oh.report_file
            FROM order_history oh
            LEFT JOIN tables_sessions ts ON oh.table_session_id = ts.id
            $where_clause_history
            ORDER BY oh.closed_at DESC";

    $stmt_history = $conn->prepare($sql_history);
    if (!empty($params_history)) {
        $stmt_history->bind_param($types_history, ...$params_history);
    }
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();

    while ($row = $result_history->fetch_assoc()) {
        $orders[] = $row;
    }
}

usort($orders, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM order_items oi LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id WHERE ts.closed_at IS NULL) as pedidos_ativos,
    (SELECT COUNT(*) FROM order_history) as pedidos_fechados,
    (SELECT COUNT(DISTINCT table_session_id) FROM order_items oi LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id WHERE ts.closed_at IS NULL) as mesas_ativas,
    (SELECT COUNT(*) FROM order_history) as mesas_fechadas,
    (SELECT SUM(oi.price * oi.quantity) FROM order_items oi LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id WHERE ts.closed_at IS NULL) as receita_ativa,
    (SELECT SUM(total_amount) FROM order_history) as receita_fechada,
    (SELECT COUNT(DISTINCT client_name) FROM tables_sessions WHERE closed_at IS NULL) as clientes_ativos,
    (SELECT COUNT(DISTINCT cliente_nome) FROM order_history) as clientes_fechados";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

$total_pedidos = ($stats['pedidos_ativos'] ?? 0) + ($stats['pedidos_fechados'] ?? 0);
$total_mesas = ($stats['mesas_ativas'] ?? 0) + ($stats['mesas_fechadas'] ?? 0);
$receita_total = ($stats['receita_ativa'] ?? 0) + ($stats['receita_fechada'] ?? 0);
$total_clientes = ($stats['clientes_ativos'] ?? 0) + ($stats['clientes_fechados'] ?? 0);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Burger Table</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="meuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="meuModalLabel"><i class="ph ph-note" style="margin-right: 5px;"></i>Inserir Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <hr class="hr">
                <form action="/burger-table/Functions/insertOrder.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Número da mesa.</label>
                                <select name="table_number" required>
                                    <option value="">Selecione...</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantidade de pessoas.</label>
                                <input type="number" name="people_count" placeholder="Nº" min="1" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="name_client">Nome do cliente.</label>
                            <input type="text" id="name_client" name="name_client" placeholder="Digite o nome do cliente." required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="button-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="ph ph-fork-knife"></i>
            </div>
            <div class="app-name">BurgerTable</div>
        </div>
        <div class="menu">
            <a href="index.php" class="menu-item active">
                <i class="ph ph-house-line"></i>
                Pedidos
            </a>
            <a href="products.php" class="menu-item">
                <i class="ph ph-cube"></i>
                Produtos
            </a>
        </div>
        <a class="logout">Sair</a>
    </div>

    <div class="section">
        <div class="header">
            <div class="header-left">
                <div class="d-flex align-items-center">
                    <button id="toggleSidebar" class="btn btn-light me-3">
                        <i class="ph ph-list"></i>
                    </button>
                    <div>
                        <h1 class="page-title">
                            <i class="ph ph-receipt"></i> Gerenciamento de Pedidos
                        </h1>
                    </div>
                </div>
            </div>
            <div class="user-info">
                <div class="user-name">
                    <h4>Diogo da Silva</h4>
                    <p>Garçom</p>
                </div>
                <div class="avatar cs">DPM</div>
            </div>
        </div>

        <div class="main-content">
            <div class="stats-card">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-3"><i class="ph ph-chart-bar"></i> Estatísticas dos Pedidos</h3>
                    <a href="/burger-table/Functions/gerar_relatorio.php" class="mb-3" target="_blank">
                        <button class="btn-generatePDF"><i class="ph ph-file-pdf"></i> Gerar Relatório PDF</button>
                    </a>

                </div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= $total_pedidos ?></span>
                        <span class="stat-label">Total de Pedidos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $total_mesas ?></span>
                        <span class="stat-label">Mesas Atendidas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $total_clientes ?></span>
                        <span class="stat-label">Total de clientes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">R$ <?= number_format($receita_total, 2, ',', '.') ?></span>
                        <span class="stat-label">Receita Total</span>
                    </div>
                </div>
            </div>

            <button type="button" class="button-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                <i class="ph ph-plus"></i>
                Novo pedido.
            </button>

            <div class="filters-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4><i class="ph ph-funnel"></i> Filtros</h4>
                </div>

                <form method="GET" class="filter-form">
                    <div>
                        <label class="form-label">Mesa</label>
                        <input type="number" name="mesa" class="form-control"
                               value="<?= htmlspecialchars($filtro_mesa) ?>"
                               placeholder="Número da mesa">
                    </div>

                    <div>
                        <label class="form-label">Cliente</label>
                        <input type="text" name="cliente" class="form-control"
                               value="<?= htmlspecialchars($filtro_cliente) ?>"
                               placeholder="Nome do cliente">
                    </div>
                    
                    <div>
                        <label class="form-label">Data</label>
                        <input type="date" name="data" class="form-control"
                               value="<?= htmlspecialchars($filtro_data) ?>">
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="ativo" <?= $filtro_status === 'ativo' ? 'selected' : '' ?>>Pedidos Ativos</option>
                            <option value="fechado" <?= $filtro_status === 'fechado' ? 'selected' : '' ?>>Contas Fechadas</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="button-primary w-100">
                            <i class="ph ph-magnifying-glass"></i> Filtrar
                        </button>
                    </div>

                    <div>
                        <a href="?" class="btn btn-outline-secondary w-100">
                            <i class="ph ph-x"></i> Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>

            <div class="orders-container">
                <?php if (count($orders) > 0): ?>
                    <h4 class="mb-3">
                        <i class="ph ph-list-checks"></i>
                        Pedidos Encontrados (<?= count($orders) ?>)
                    </h4>

                    <?php foreach ($orders as $order): ?>
                        <div class="order-card <?= $order['status_pedido'] ?>" 
                             onclick="window.location.href='viewMesa.php?id=<?= $order['session_id'] ?>&cliente_nome=<?= urlencode($order['client_name']) ?>&created_at=<?= urlencode($order['opened_at']) ?>&count_people=<?= $order['people_count'] ?>'">
                            <div class="order-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="mesa-badge">Mesa <?= $order['table_id'] ?></span>
                                    <h5 class="mb-0"><?= htmlspecialchars($order['client_name']) ?></h5>
                                    <?php if ($order['product_type']): ?>
                                        <span class="product-type-badge type-<?= strtolower($order['product_type']) ?>">
                                            <?= $order['status_pedido'] === 'fechado' ? 'Conta Fechada' : ucfirst($order['product_type']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="session-status status-<?= $order['status_pedido'] ?>">
                                        <?= $order['status_pedido'] === 'ativo' ? 'Sessão Aberta' : 'Sessão Fechada' ?>
                                    </span>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <div class="price-highlight <?= $order['status_pedido'] ?>">
                                        R$ <?= number_format($order['price'] * $order['quantity'], 2, ',', '.') ?>
                                    </div>
                                    <?php if ($order['status_pedido'] === 'fechado' && isset($order['report_file'])): ?>
                                        <a href="/burger-table/Functions/downloadReport.php?file=<?= urlencode($order['report_file']) ?>" 
                                           class="btn-download" onclick="event.stopPropagation();">
                                            <i class="ph ph-download"></i> Relatório
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="product-info">
                                <h6 class="mb-1">
                                    <i class="ph ph-package"></i>
                                    <?= htmlspecialchars($order['product_name'] ?: 'Produto não encontrado') ?>
                                </h6>
                                <?php if ($order['product_description']): ?>
                                    <p class="text-muted mb-2" style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($order['product_description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="order-details">
                                <?php if ($order['status_pedido'] === 'ativo'): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Quantidade</span>
                                        <span class="detail-value">
                                            <i class="ph ph-hash"></i> <?= $order['quantity'] ?>
                                        </span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Preço Unitário</span>
                                        <span class="detail-value">
                                            <i class="ph ph-currency-dollar"></i>
                                            R$ <?= number_format($order['price'], 2, ',', '.') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div class="detail-item">
                                    <span class="detail-label">Pessoas na Mesa</span>
                                    <span class="detail-value">
                                        <i class="ph ph-users"></i> <?= $order['people_count'] ?>
                                    </span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Sessão Iniciada</span>
                                    <span class="detail-value">
                                        <i class="ph ph-calendar"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['opened_at'])) ?>
                                    </span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label"><?= $order['status_pedido'] === 'ativo' ? 'Pedido Feito' : 'Conta Fechada' ?></span>
                                    <span class="detail-value">
                                        <i class="ph ph-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </span>
                                </div>

                                <?php if ($order['status_pedido'] === 'ativo' && !is_null($order['closed_at'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Sessão Fechada</span>
                                    <span class="detail-value">
                                        <i class="ph ph-x-circle"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['closed_at'])) ?>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <div class="detail-item">
                                    <span class="detail-label">ID do <?= $order['status_pedido'] === 'ativo' ? 'Item' : 'Histórico' ?></span>
                                    <span class="detail-value">
                                        <i class="ph ph-identifier"></i> #<?= $order['id'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="ph ph-receipt"></i>
                        <h4>Nenhum pedido encontrado</h4>
                        <p>Não há pedidos que correspondam aos filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
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

        $('.filter-form input[type="date"]').on('change', function() {
            if (confirm('Aplicar filtro automaticamente?')) {
                $(this).closest('form').submit();
            }
        });

        $(document).ready(function() {
            $('.order-card').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
                $(this).addClass('animate__animated animate__fadeInUp');
            });
        });
    </script>
</body>
</html>