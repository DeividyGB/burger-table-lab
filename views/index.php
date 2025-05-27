<?php
session_start();
include('../Functions/connectionDB.php');

// Filtros
$filtro_mesa = isset($_GET['mesa']) ? $_GET['mesa'] : '';
$filtro_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtro_data = isset($_GET['data']) ? $_GET['data'] : '';

// Construir query com filtros
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filtro_mesa)) {
    $where_conditions[] = "ts.table_id = ?";
    $params[] = $filtro_mesa;
    $types .= 'i';
}

if (!empty($filtro_cliente)) {
    $where_conditions[] = "ts.client_name LIKE ?";
    $params[] = '%' . $filtro_cliente . '%';
    $types .= 's';
}

if (!empty($filtro_data)) {
    $where_conditions[] = "DATE(oi.created_at) = ?";
    $params[] = $filtro_data;
    $types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Query principal para buscar pedidos
$sql = "SELECT oi.id, oi.quantity, oi.price, oi.created_at,
               p.name as product_name, 
               p.type as product_type, 
               p.description as product_description,
               ts.table_id,
               ts.client_name,
               ts.people_count,
               ts.opened_at,
               ts.closed_at
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id
        $where_clause
        ORDER BY oi.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Estatísticas gerais
$sql_stats = "SELECT 
    COUNT(DISTINCT oi.table_session_id) as total_mesas,
    COUNT(*) as total_pedidos,
    SUM(oi.price * oi.quantity) as receita_total,
    COUNT(DISTINCT ts.client_name) as total_clientes
    FROM order_items oi 
    LEFT JOIN tables_sessions ts ON oi.table_session_id = ts.id
    $where_clause";

$stmt_stats = $conn->prepare($sql_stats);
if (!empty($params)) {
    $stmt_stats->bind_param($types, ...$params);
}
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
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
    <style>
        .stats-card {
            background: var(--primary-light);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .filters-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .mesa-badge {
            background: #007bff;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .session-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-open { background: #d4edda; color: #155724; }
        .status-closed { background: #f8d7da; color: #721c24; }

        .product-type-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .type-hamburguer { background: #ffe6e6; color: #cc0000; }
        .type-acompanhamentos { background: #fff3cd; color: #856404; }
        .type-bebidas { background: #d4edda; color: #155724; }
        .type-doces { background: #f8d7da; color: #721c24; }

        .price-highlight {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
            display: flex;
            flex-direction: column;
            align-items: center;
            border: solid 2px #cbcbcb;
            border-radius: 5px;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 600;
            color: #212529;
        }

        .btn-export {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 1rem;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
            <!-- <a href="#" class="menu-item">
            <a href="viewMesa.php?session_id=1&count_people=0&created_at=2000-01-01+00%3A46%3A46&cliente_nome=none" class="menu-item">
                <i class="ph ph-fork-knife"></i>
                Mesas
            </a> -->
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
                    <p>Gerente do pal mole</p>
                </div>
                <div class="avatar cs">DPM</div>
            </div>
        </div>

        <div class="main-content">
            <div class="stats-card">
                <h3 class="mb-3"><i class="ph ph-chart-bar"></i> Estatísticas dos Pedidos</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['total_pedidos'] ?: 0 ?></span>
                        <span class="stat-label">Total de Pedidos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['total_mesas'] ?: 0 ?></span>
                        <span class="stat-label">Mesas Atendidas</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $stats['total_clientes'] ?: 0 ?></span>
                        <span class="stat-label">Clientes Únicos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">R$ <?= number_format($stats['receita_total'] ?: 0, 2, ',', '.') ?></span>
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

            <!-- Lista de Pedidos -->
            <div class="orders-container">
                <?php if (count($orders) > 0): ?>
                    <h4 class="mb-3">
                        <i class="ph ph-list-checks"></i>
                        Pedidos Encontrados (<?= count($orders) ?>)
                    </h4>

                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="mesa-badge">Mesa <?= $order['table_id'] ?></span>
                                    <h5 class="mb-0"><?= htmlspecialchars($order['client_name']) ?></h5>
                                    <?php if ($order['product_type']): ?>
                                        <span class="product-type-badge type-<?= strtolower($order['product_type']) ?>">
                                            <?= ucfirst($order['product_type']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="session-status <?= is_null($order['closed_at']) ? 'status-open' : 'status-closed' ?>">
                                        <?= is_null($order['closed_at']) ? 'Sessão Aberta' : 'Sessão Fechada' ?>
                                    </span>
                                </div>
                                <div class="price-highlight">
                                    R$ <?= number_format($order['price'] * $order['quantity'], 2, ',', '.') ?>
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
                                    <span class="detail-label">Pedido Feito</span>
                                    <span class="detail-value">
                                        <i class="ph ph-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    </span>
                                </div>

                                <?php if (!is_null($order['closed_at'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Sessão Fechada</span>
                                    <span class="detail-value">
                                        <i class="ph ph-x-circle"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['closed_at'])) ?>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <div class="detail-item">
                                    <span class="detail-label">ID do Item</span>
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
                        <p>Não há pedidos que correspondam aos  filtros aplicados.</p>
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

        // Auto-submit do formulário quando mudança nos filtros (opcional)
        $('.filter-form input[type="date"]').on('change', function() {
            if (confirm('Aplicar filtro automaticamente?')) {
                $(this).closest('form').submit();
            }
        });

        // Animação suave para os cards
        $(document).ready(function() {
            $('.order-card').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
                $(this).addClass('animate__animated animate__fadeInUp');
            });
        });
    </script>
</body>
</html>