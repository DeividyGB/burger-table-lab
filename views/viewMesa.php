<?php
    session_start();

    if (isset($_GET['id'], $_GET['cliente_nome'], $_GET['created_at'])) {
        $mesa_id = $_GET['id'];
        $cliente_nome = $_GET['cliente_nome'];
        $created_at = $_GET['created_at'];
        $people_count = $_GET['count_people'];

    } else {
        echo "Dados do pedido não encontrados.";
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burger Table</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <style>
        
    .pedido-item {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .pedido-item:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .pedido-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .produto-nome {
        font-weight: 600;
        font-size: 1.1rem;
        color: #333;
    }
    .produto-quantidade {
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 15px;
        font-size: 0.9rem;
        color: #666;
    }
    .produto-preco {
        font-weight: 700;
        color: #28a745;
        font-size: 1.1rem;
    }
    .btn-remover {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .btn-remover:hover {
        background: #c82333;
    }
    .total-pedido {
        background: linear-gradient(322deg, #ff4f4f, var(--primary-light));
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        text-align: center;
    }
    .total-valor {
        font-size: 1.5rem;
        font-weight: 700;
    }
    .empty-pedido {
        text-align: center;
        padding: 2rem;
        color: #666;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .empty-pedido i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .alert {
    margin-bottom: 1rem;
}

.alert .ph {
    margin-right: 0.5rem;
}

.resumo-fechamento {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.resumo-fechamento .d-flex {
    margin-bottom: 0.5rem;
}

.conta-detalhes .info-item {
    margin-bottom: 1rem;
}

.conta-detalhes .info-item small {
    display: block;
    font-size: 0.8rem;
}

.conta-item {
    border-bottom: 1px solid #eee;
}

.conta-item:last-child {
    border-bottom: none;
}

.conta-total {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
    transform: translateY(-1px);
}

.custom-modal .modal-body .alert {
    margin-bottom: 1rem;
}

.empty-pedido i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 1rem;
}

    </style>
</head>
<body>
    <!-- Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Adicionar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <hr class="hr">
                <form action="/burger-table/Functions/insertOrderItem.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="table_session_id" id="table_session_id" value="<?= htmlspecialchars($mesa_id) ?>">
                        <input type="hidden" name="product_id" id="product_id" value="">
                        <input type="hidden" name="price" id="price" value="">

                        <div class="mb-3">
                            <label class="form-label">Produto</label>
                            <input type="text" id="product_name" name="product_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="button-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    

    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="d-flex"><i class="ph ph-fork-knife"></i></i>
            </div>

            <div class="app-name">BurgerTable</div>
        </div>

        <div class="menu">
            <a href="index.php"  class="menu-item active">
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
                        <h1 class="page-title"><i class="ph ph-hamburger"></i> BurgerTable</h1>
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
            <div class="info-mesa-card">
                <div class="info-header">
                    <h2>Mesa IDº <?= htmlspecialchars($mesa_id) ?></h2>
                    <span class="data-criacao"><i>Criado às: </i> <?= date('d/m/Y H:i', strtotime($created_at)) ?></span>
                </div>
                <div class="info-body">
                    <div class="info-item">
                        <strong><i class="ph ph-user"></i> Cliente:</strong> <?= htmlspecialchars($cliente_nome) ?>
                    </div>
                    <div class="info-item">
                        <strong><i class="ph ph-users"></i> Pessoas:</strong> <?= (int)$people_count ?>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['closed']) && $_GET['closed'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ph ph-check-circle"></i>
                    <strong>Conta fechada com sucesso!</strong> 
                    <?php if (isset($_GET['report'])): ?>
                        <a href="/burger-table/Functions/downloadReport.php?file=<?= urlencode($_GET['report']) ?>" class="alert-link">
                            <i class="ph ph-download"></i> Baixar Relatório CSV
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ph ph-x-circle"></i>
                    <strong>Erro ao fechar conta:</strong> <?= htmlspecialchars($_GET['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
                
            <div class="d-flex gap-3 flex-column h-100">
                <div class="tabs-container p-2">
                    <div class="tab-button active" data-tab="pedido">Pedido</div>
                    <div class="tab-button" data-tab="cardapio">Cardápio</div>
                    <div class="tab-button" data-tab="conta">Conta</div>
                </div>

                <div class="tab-content" id="pedido">
                    <?php
                    // Buscar itens do pedido
                    include('../Functions/connectionDB.php');
                    
                    $sql_pedido = "SELECT oi.*, p.name as product_name, p.description 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.table_session_id = ? 
                                  ORDER BY oi.created_at DESC";
                    
                    $stmt_pedido = $conn->prepare($sql_pedido);
                    $stmt_pedido->bind_param("i", $mesa_id);
                    $stmt_pedido->execute();
                    $result_pedido = $stmt_pedido->get_result();
                    
                    $total_pedido = 0;
                    $itens_pedido = [];
                    
                    while ($item = $result_pedido->fetch_assoc()) {
                        $itens_pedido[] = $item;
                        $total_pedido += ($item['price'] * $item['quantity']);
                    }
                    
                    if (count($itens_pedido) > 0): ?>
                        <div class="pedidos-lista">
                            <?php foreach ($itens_pedido as $item): ?>
                                <div class="pedido-item">
                                    <div class="pedido-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="produto-nome"><?= htmlspecialchars($item['product_name']) ?></span>
                                            <span class="produto-quantidade">x<?= $item['quantity'] ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="produto-preco">R$ <?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></span>
                                            <form method="POST" action="/burger-table/Functions/removeOrderItem.php" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                <input type="hidden" name="table_session_id" value="<?= $mesa_id ?>">
                                                <input type="hidden" name="cliente_nome" value="<?= urlencode($cliente_nome) ?>">
                                                <input type="hidden" name="created_at" value="<?= urlencode($created_at) ?>">
                                                <input type="hidden" name="count_people" value="<?= $people_count ?>">
                                                <button type="submit" class="btn-remover" onclick="return confirm('Tem certeza que deseja remover este item?')">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php if (!empty($item['description'])): ?>
                                        <p class="text-muted mb-0" style="font-size: 0.9rem;"><?= htmlspecialchars($item['description']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">Adicionado em: <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="total-pedido">
                                <div>Total do Pedido</div>
                                <div class="total-valor">R$ <?= number_format($total_pedido, 2, ',', '.') ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-pedido">
                            <i class="ph ph-shopping-cart"></i>
                            <h4>Nenhum item no pedido</h4>
                            <p>Adicione itens do cardápio para começar o pedido.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-content" id="cardapio" style="display: none;">
                    <?php
                    $sql = "SELECT * FROM products";
                    $result = $conn->query($sql);

                    $produtos = [];
                    $categorias = [
                        'hamburguer' => [],
                        'acompanhamentos' => [],
                        'bebidas' => [],
                        'doces' => []
                    ];

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $produtos[] = $row;

                            $tipo = strtolower($row['type']);
                            if (isset($categorias[$tipo])) {
                                $categorias[$tipo][] = $row;
                            }
                        }
                    }
                        function gerarCard($produto) {
                        $nome = htmlspecialchars($produto['name']);
                        $descricao = htmlspecialchars($produto['description']);
                        $preco = number_format($produto['price'], 2, ',', '.');
                        $tipo = ucfirst($produto['type']);
                        $precoRaw = $produto['price'];

                        return <<<HTML
                        <div class="card-produto">
                            <div class="tag-tipo">{$tipo}</div>
                            <div class="card-conteudo">
                                <h3 class="card-titulo">{$nome}</h3>
                                <p class="card-descricao">{$descricao}</p>
                                <div class="card-footer">
                                    <span class="preco">R$ {$preco}</span>
                                    <button 
                                        class="btn-adicionar" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addProductModal" 
                                        data-id="{$produto['id']}" 
                                        data-name="{$nome}" 
                                        data-price="{$precoRaw}"
                                    >+ Adicionar</button>
                                </div>
                            </div>
                        </div>
                    HTML;
                    }

                    ?>
                    
                    <div class="tabs-buttons mb-2">
                        <button class="active-tab" data-tab="tab-todos"><i class="ph ph-bowl-food"></i> Todos</button>
                        <button data-tab="tab-hamburgueres"><i class="ph ph-hamburger"></i> Hamburgueres</button>
                        <button data-tab="tab-acompanhamentos"><i class="ph ph-bread"></i> Acompanhamentos</button>
                        <button data-tab="tab-bebidas"><i class="ph ph-martini"></i> Bebidas</button>
                        <button data-tab="tab-sobremesas"><i class="ph ph-ice-cream"></i> Sobremesas</button>
                    </div>

                    <section class="card-section">
                        <section id="tab-todos" class="tab-section active-tab-section">
                            <?php foreach ($produtos as $produto): ?>
                                <?= gerarCard($produto) ?>
                            <?php endforeach; ?>
                        </section>

                        <section id="tab-hamburgueres" class="tab-section">
                            <?php foreach ($categorias['hamburguer'] as $produto): ?>
                                <?= gerarCard($produto) ?>
                            <?php endforeach; ?>
                        </section>

                        <section id="tab-acompanhamentos" class="tab-section">
                            <?php foreach ($categorias['acompanhamentos'] as $produto): ?>
                                <?= gerarCard($produto) ?>
                            <?php endforeach; ?>
                        </section>

                        <section id="tab-bebidas" class="tab-section">
                            <?php foreach ($categorias['bebidas'] as $produto): ?>
                                <?= gerarCard($produto) ?>
                            <?php endforeach; ?>
                        </section>

                        <section id="tab-sobremesas" class="tab-section">
                            <?php foreach ($categorias['doces'] as $produto): ?>
                                <?= gerarCard($produto) ?>
                            <?php endforeach; ?>
                        </section>
                    </section>
                </div>

                <!-- Aba Conta -->
                <div class="tab-content" id="conta" style="display: none;">
                    <div class="conta-resumo">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Resumo da Conta</h3>
                            <?php if (count($itens_pedido) > 0): ?>
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeOrderModal">
                                    <i class="ph ph-receipt"></i> Fechar Conta
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($itens_pedido) > 0): ?>
                            <div class="conta-detalhes">
                                <div class="info-conta mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <small class="text-muted">Mesa</small>
                                                <div class="fw-bold">#<?= htmlspecialchars($mesa_id) ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <small class="text-muted">Cliente</small>
                                                <div class="fw-bold"><?= htmlspecialchars($cliente_nome) ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <small class="text-muted">Pessoas</small>
                                                <div class="fw-bold"><?= (int)$people_count ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <small class="text-muted">Aberta em</small>
                                                <div class="fw-bold"><?= date('d/m/Y H:i', strtotime($created_at)) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="conta-itens">
                                    <h5>Itens do Pedido</h5>
                                    <?php 
                                    $stats_categoria = [];
                                    foreach ($itens_pedido as $item): 
                                        $categoria = ucfirst($item['product_name'][0] ?? 'Outros');
                                        if (!isset($stats_categoria[$categoria])) {
                                            $stats_categoria[$categoria] = 0;
                                        }
                                        $stats_categoria[$categoria] += ($item['price'] * $item['quantity']);
                                    ?>
                                        <div class="conta-item d-flex justify-content-between align-items-center py-2">
                                            <div>
                                                <span class="fw-medium"><?= htmlspecialchars($item['product_name']) ?></span>
                                                <small class="text-muted d-block">Qtd: <?= $item['quantity'] ?> × R$ <?= number_format($item['price'], 2, ',', '.') ?></small>
                                            </div>
                                            <span class="fw-bold">R$ <?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <hr>
                                    
                                    <div class="conta-total">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h6">Subtotal:</span>
                                            <span class="h6">R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="h5">Total:</span>
                                            <span class="h4 text-success fw-bold">R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                                        </div>
                                        <div class="text-center">
                                            <small class="text-muted">
                                                Valor por pessoa: R$ <?= number_format($total_pedido / max(1, $people_count), 2, ',', '.') ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="empty-pedido">
                                <i class="ph ph-receipt"></i>
                                <h4>Nenhum item no pedido</h4>
                                <p>Adicione itens do cardápio para gerar uma conta.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="closeOrderModal" tabindex="-1" aria-labelledby="closeOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content custom-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="closeOrderModalLabel">Fechar Conta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="ph ph-warning-circle"></i>
                            <strong>Atenção!</strong> Ao fechar a conta, será gerado um relatório em CSV e todos os itens do pedido serão removidos.
                        </div>
                        
                        <div class="resumo-fechamento">
                            <h6>Resumo do Pedido:</h6>
                            <div class="d-flex justify-content-between">
                                <span>Total de Itens:</span>
                                <span id="modal-total-itens"><?= count($itens_pedido) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Valor Total:</span>
                                <span id="modal-valor-total" class="fw-bold text-success">R$ <?= number_format($total_pedido, 2, ',', '.') ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Valor por Pessoa:</span>
                                <span id="modal-valor-pessoa">R$ <?= number_format($total_pedido / max(1, $people_count), 2, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <p class="mt-3">Deseja realmente fechar esta conta?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form method="POST" action="/burger-table/Functions/closeOrder.php" style="display: inline;">
                            <input type="hidden" name="table_session_id" value="<?= htmlspecialchars($mesa_id) ?>">
                            <input type="hidden" name="cliente_nome" value="<?= htmlspecialchars($cliente_nome) ?>">
                            <input type="hidden" name="created_at" value="<?= htmlspecialchars($created_at) ?>">
                            <input type="hidden" name="people_count" value="<?= $people_count ?>">
                            <input type="hidden" name="total_pedido" value="<?= $total_pedido ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="ph ph-receipt"></i> Fechar Conta
                            </button>
                        </form>
                    </div>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script>
        $('#toggleSidebar').on('click', function () {
            $('.sidebar').toggleClass('hidden');
        });

        $(document).ready(function () {
            $('.tabs-buttons button').on('click', function () {
                var tabId = $(this).data('tab');

                $('.tabs-buttons button').removeClass('active-tab');
                $(this).addClass('active-tab');

                $('.tab-section').removeClass('active-tab-section');
                $('#' + tabId).addClass('active-tab-section');
            });
        });

        $(document).ready(function () {
            $('.tab-button').on('click', function () {
                const tab = $(this).data('tab');

                $('.tab-content').hide();

                $('.tab-button').removeClass('active');

                $('#' + tab).show();

                $(this).addClass('active');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const botoesAdicionar = document.querySelectorAll('.btn-adicionar');

            botoesAdicionar.forEach(botao => {
                botao.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = this.getAttribute('data-price');

                document.getElementById('product_id').value = id;
                document.getElementById('product_name').value = name;
                document.getElementById('price').value = price;
                });
            });
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                console.log('Item adicionado com sucesso!');
            <?php endif; ?>
        });
    </script>
</body>
</html>