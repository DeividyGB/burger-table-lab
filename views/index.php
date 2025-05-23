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
            <a href="index.php"  class="menu-item active">
                <i class="ph ph-house-line"></i>
                Pedidos
            </a>
            <a href="#" class="menu-item">
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
            <button type="button" class="button-primary w-25" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                <i class="ph ph-plus"></i>
                Novo pedido.
            </button>
              
            <div class="modal fade" id="newOrderModal" tabindex="-1" aria-labelledby="meuModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content custom-modal">
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center" id="meuModalLabel"><i class="ph ph-note" style="margin-right: 5px;"></i>Inserir Pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <hr class="hr">
                    <form action="/burger-table-lab/Functions/insertOrder.php" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Número da mesa.</label>
                                    <select name="table_id">
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
                                    <input type="number" name="people_count" placeholder="Nº">
                                </div>
                            </div>
                    
                            <div class="mt-3">
                                <label class="form-label" for="name_client">Nome do cliente.</label>
                                <input type="text" id="name_client" name="name_client" placeholder="Digite o nome do cliente.">
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