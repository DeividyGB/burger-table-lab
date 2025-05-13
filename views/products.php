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
                Dashboard
            </a>
            <a href="#" class="menu-item">
                <i class="ph ph-fork-knife"></i>
                Mesas
            </a>
            <a href="#" class="menu-item">
                <i class="ph ph-note"></i>
                Pedidos
            </a>
            <a href="products.php" class="menu-item active">
                <i class="ph ph-cube"></i>
                Produtos
            </a>            
        </div>
        
        <a class="logout">
            Sair
        </a>
    </div>

    <!-- Modais -->
    <div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center"><i class="ph ph-plus"></i> Novo Produto.</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <hr class="hr">
            <form action="/burger-table/Functions/insertProduct.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label" for="Nome do produto">Nome do produto.</label>
                            <input type="text" name="product_name" placeholder="Digite o nome do produto">
                        </div>
            
                        <div class="col-md-6">
                            <label class="form-label" for="Preço do produto">Preço.</label>
                            <input type="number" name="product_price" placeholder="Digite o valor do produto">
                        </div>
                    </div>
            
                    <div class="row">
                        <div class="mt-3 col-md-12">
                            <label class="form-label" for="Descrição do produto">Descrição.</label>
                            <input type="text" name="product_description" placeholder="Digite a descrição do produto.">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mt-3 col-md-12">
                            <label class="form-label" for="Descrição do produto">Categoria.</label>
                            <select name="category" required>
                                <option value="">Selecione a categoria...</option>
                                <option value="hamburger">Hambúrger</option>
                                <option value="bebidas">Bebidas</option>  
                                <option value="acompanhamentos">Acompanhamentos</option>
                                <option value="doces">Doces</option>
                            </select>
                        </div>
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
            <h3><i class="ph ph-hamburger"></i> Meus produtos</h3>
            <hr>

            <button class="button-primary w-25" data-bs-toggle="modal" data-bs-target="#newProductModal">
                    <i class="ph ph-plus"></i>
                    Novo produto.
            </button>

            <section class="card-section">
            <?php
                include('../Functions/connectionDB.php');

                $sql = "SELECT * FROM products ORDER BY type, name";
                $result = $conn->query($sql);
                $categorias = [];

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $categoria = ucfirst($row['type']);
                        $categorias[$categoria][] = $row;
                    }

                    foreach ($categorias as $categoria => $produtos) {
                        echo '<div class="category-card">';
                        echo '<h4 class="category-title">' . htmlspecialchars($categoria) . '</h4>';
                        echo '<div class="product-list">';

                        foreach ($produtos as $produto) {
                            echo '<div class="product-card flex-grow-1">';
                            echo '<h5>' . htmlspecialchars($produto['name']) . '</h5>';
                            echo '<p class="price">R$ ' . number_format($produto['price'], 2, ',', '.') . '</p>';
                            echo '<p class="description">' . htmlspecialchars($produto['description']) . '</p>';
                            echo '</div>';
                        }

                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="info-card">';
                    echo '<span class="align-self-center">Nenhum produto cadastrado.</span>';
                    echo '</div>';
                }
            ?>
            </section>
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
        console.log("ai ai ui ui");
        $('.sidebar').toggleClass('hidden');
    });
    </script>
</body>
</html>