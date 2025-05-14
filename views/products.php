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
                Pedidos
            </a>
            <a href="#" class="menu-item">
                <i class="ph ph-fork-knife"></i>
                Mesas
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

    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ph ph-pencil-simple"></i> Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <hr class="hr">
                <form action="/burger-table/Functions/updateProduct.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="editProductId">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Nome do produto.</label>
                                <input type="text" name="product_name" id="editProductName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preço.</label>
                                <input type="number" step="0.01" name="product_price" id="editProductPrice" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">Descrição.</label>
                                <input type="text" name="product_description" id="editProductDescription" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">Categoria.</label>
                                <select name="category" id="editProductCategory" required>
                                    <option value="hamburger">Hambúrger</option>
                                    <option value="bebidas">Bebidas</option>
                                    <option value="acompanhamentos">Acompanhamentos</option>
                                    <option value="doces">Doces</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="button-primary w-25">Salvar Alterações</button>
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

            <div class="d-flex justify-content-between">
                <button class="button-primary w-25 mb-3" data-bs-toggle="modal" data-bs-target="#newProductModal">
                    <i class="ph ph-plus"></i>
                    Novo produto.
                </button>

                <div class="d-flex align-items-center mb-3 w-50" style="gap: 5px">
                    <label for="Pesquisar Produto">Pesquisar</label>
                    <input type="text" id="searchInput" placeholder="Pesquisar produto por nome... ">
                </div>
            </div>

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

                    // Abas
                    echo '<ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist" style="gap: 5px">';
                    $i = 0;
                    foreach ($categorias as $categoria => $produtos) {
                        $activeClass = $i === 0 ? 'active' : '';
                        echo '
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ' . $activeClass . '" id="tab-' . $i . '" data-bs-toggle="tab" data-bs-target="#content-' . $i . '" type="button" role="tab">
                                    ' . htmlspecialchars($categoria) . '
                                </button>
                            </li>
                        ';
                        $i++;
                    }
                    echo '</ul>';

                    echo '<div class="tab-content" id="categoryTabsContent">';
                    $i = 0;
                    foreach ($categorias as $categoria => $produtos) {
                        $showActive = $i === 0 ? 'show active' : '';
                        echo '<div class="tab-pane fade ' . $showActive . '" id="content-' . $i . '" role="tabpanel">';
                        echo '<div class="d-flex flex-wrap gap-3">';

                        foreach ($produtos as $produto) {
                            echo '<div class="product-card border rounded p-3 shadow-sm flex-grow-1" style="width: 250px; background: #fff;" data-name="' . strtolower(htmlspecialchars($produto['name'])) . '">';

                            echo '<h5 style="gap: 5px"><span class="fw-bold">Produto:</span> <span class="text-primary-light">' . htmlspecialchars($produto['name']) . '.</span></h5>';
                            echo '<hr>';
                            echo '<p class="description"><span class="fw-bold">Descrição:</span> ' . htmlspecialchars($produto['description']) . '</p>';
                            echo '<p class="price text-success fw-bold align-self-end">R$ ' . number_format($produto['price'], 2, ',', '.') . '</p>';

                            // Botões
                            echo '<div class="d-flex justify-content-between mt-3">';

                            echo '<button class="button-secondary" data-bs-toggle="modal" data-bs-target="#editProductModal" 
                                    data-id="' . $produto['id'] . '" 
                                    data-name="' . htmlspecialchars($produto['name']) . '" 
                                    data-description="' . htmlspecialchars($produto['description']) . '" 
                                    data-price="' . $produto['price'] . '" 
                                    data-type="' . $produto['type'] . '">
                                    <i class="ph ph-pencil-simple"></i> Editar
                                </button>';

                                        echo '<form method="POST" action="../Functions/deleteProduct.php" onsubmit="return confirm(\'Tem certeza que deseja excluir este produto?\')" class="m-0 p-0">
                                    <input type="hidden" name="id" value="' . $produto['id'] . '">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="ph ph-trash"></i> Excluir
                                    </button>
                                </form>';

                            echo '</div>';
                            echo '</div>';
                        }


                        echo '</div>';
                        echo '</div>';
                        $i++;
                    }
                    echo '</div>';
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

    <script>
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.tab-pane.active .product-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('#categoryTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', () => {
                document.getElementById('searchInput').value = '';
                const allCards = document.querySelectorAll('.product-card');
                allCards.forEach(card => card.style.display = '');
            });
        });

        const editModal = document.getElementById('editProductModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

        document.getElementById('editProductId').value = button.getAttribute('data-id');
        document.getElementById('editProductName').value = button.getAttribute('data-name');
        document.getElementById('editProductDescription').value = button.getAttribute('data-description');
        document.getElementById('editProductPrice').value = button.getAttribute('data-price');
        document.getElementById('editProductCategory').value = button.getAttribute('data-type');
        });
    </script>

</body>
</html>