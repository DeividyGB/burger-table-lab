<?php
    session_start();

    if (isset($_GET['session_id'], $_GET['cliente_nome'], $_GET['created_at'])) {
        $sessao_id = $_GET['session_id'];
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
</head>
<body>
    <!-- Modal -->

    <!-- <button type="button" class="button-primary w-25" data-bs-toggle="modal" data-bs-target="#newOrderModal">
        <i class="ph ph-plus"></i>
        Novo pedido.
    </button>

    <div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="meuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="meuModalLabel">Inserir Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <hr class="hr">
            <form action="/burger-table/Functions/insertOrder.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numero da mesa</label>
                        <select name="session_id">
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

                </div>
                <div class="modal-footer">
                    <button type="button" class="button-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="button-primary">Salvar</button>
                </div>
            </form>
        </div>
        </div>
    </div> -->

    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <i class="d-flex"><i class="ph ph-fork-knife"></i></i>
            </div>

            <div class="app-name">BurgerTable</div>
        </div>

        <div class="menu">
            <a href="index.php"  class="menu-item">
                <i class="ph ph-house-line"></i>
                Pedidos
            </a>
            <a href="viewMesa.php?session_id=1&count_people=0&created_at=2000-01-01+00%3A46%3A46&cliente_nome=none" class="menu-item active">
                <i class="ph ph-fork-knife"></i>
                Mesas
            </a>
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
                        <h2>Sessão IDº <?= htmlspecialchars($sessao_id) ?></h2>
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
                    
                <div class="d-flex gap-3 flex-column h-100">
                    <div class="tabs-container p-2">
                        <div class="tab-button active" data-tab="pedido">Pedido</div>
                        <div class="tab-button" data-tab="cardapio">Cardápio</div>
                        <div class="tab-button" data-tab="conta">Conta</div>
                    </div>

                    <div class="tab-content" id="pedido">
                        Conteúdo da aba Pedido
                    </div>

                    <div class="tab-content" id="cardapio" style="display: none;">
                        <?php
                        include('../Functions/connectionDB.php');

                        $sql = "SELECT * FROM products";
                        $result = $conn->query($sql);

                        $produtos = [];
                        $categorias = [
                            'hamburger' => [],
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

                            return <<<HTML
                            <div class="card-produto">
                                <div class="tag-tipo">{$tipo}</div>
                                <div class="card-conteudo">
                                    <h3 class="card-titulo">{$nome}</h3>
                                    <p class="card-descricao">{$descricao}</p>
                                    <div class="card-footer">
                                        <span class="preco">R$ {$preco}</span>
                                        <button class="btn-adicionar">+ Adicionar</button>
                                    </div>
                                </div>
                            </div>
                            HTML;
                        }
                        ?>

                        <!-- Abas internas de categorias -->
                        <div class="tabs-buttons mb-2">
                            <button class="active-tab" data-tab="tab-todos"><i class="ph ph-bowl-food"></i> Todos</button>
                            <button data-tab="tab-hamburgueres"><i class="ph ph-hamburger"></i> Hamburgueres</button>
                            <button data-tab="tab-acompanhamentos"><i class="ph ph-bread"></i> Acompanhamentos</button>
                            <button data-tab="tab-bebidas"><i class="ph ph-martini"></i> Bebidas</button>
                            <button data-tab="tab-sobremesas"><i class="ph ph-ice-cream"></i> Sobremesas</button>
                        </div>

                        <!-- Conteúdo das categorias -->
                        <section class="card-section">
                            <section id="tab-todos" class="tab-section active-tab-section">
                                <?php foreach ($produtos as $produto): ?>
                                    <?= gerarCard($produto) ?>
                                <?php endforeach; ?>
                            </section>

                            <section id="tab-hamburgueres" class="tab-section">
                                <?php foreach ($categorias['hamburger'] as $produto): ?>
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
                        Conteúdo da aba Conta
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

    </script>

    
</body>
</html>
