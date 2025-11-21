<?php
session_start();
if (isset($_SESSION['usuario'])) {
    session_destroy();
}


$sucesso = (filter_input(INPUT_GET, 'sucesso') == 1) ? true : false;
$erro = (!$sucesso) ? filter_input(INPUT_GET, 'erro') : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x" -icon>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Gestor Office</title>
</head>

<body style="background-color: #f3f4f7;">
    <!-- Login -->
<?php if(!isset($_GET['acao'])) { ?>
    <div class="card login-card">
        <div class="card-body">
            <div id="card-titulo">
                <h1>Login</h1>
            </div>

            <div id="card-sub">Entre com a sua conta.</div>
            <?php if($sucesso) { ?><label style="width:100%; text-align: center; color: #009e08ff;"> Alteração de senha bem-sucedida. </label><?php } ?>
            <?php if(isset($erro) && $erro == 'credenciais') { ?><label style="width:100%; text-align: center; color: #ff3030ff;"> Email ou senha inválidos. </label><?php } ?>
            <?php if(isset($erro) && $erro == 'dados') { ?><label style="width:100%; text-align: center; color: #ff3030ff;"> Dados inválidos. </label><?php } ?>
            <?php if(isset($erro) && $erro == 'campos') { ?><label style="width:100%; text-align: center; color: #ff3030ff;"> Por favor, preencha todos os campos. </label><?php } ?>
            <form action="login.php" method="post">
                <div id="input-login-card" style="<?php if(isset($erro) && $erro != null) { ?>border: 1px solid #ff30309d;<?php }  else if($sucesso) {?>border: 1px solid #30ff3aff;<?php } ?> ">

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <input type="text" class="form-control" id="username" placeholder="Email" name="email" required>
                </div>

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="senha" required>
                </div>

                </div>

                <div class="footer">
                    <button type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Login</button>
                    <a href="index.php?acao=editar" style="text-decoration:underline; color:#5856d6; float:right;">Redefinir a senha</a>
                </div>
            </form>
        </div>
    </div>

    
<?php } else if(isset($_GET['acao']) && $_GET['acao'] == 'editar') { ?>
    <div class="card login-card">
        <div class="card-body">
            <div id="card-titulo">
                <h1>Redefinição de senha</h1>
            </div>

            <div id="card-sub">Basta preencher os campos para redefinir a sua senha.</div>

            <form action="login.php" method="post">
                <div id="input-login-card" style="<?php if(isset($erro) && $erro != null) { ?>border: 1px solid #ff30309d; <?php } ?> ">
                <input type="hidden" name="acao" value="editar">

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <input type="text" class="form-control" id="username" placeholder="Email" name="email" required>
                </div>

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="senha" required>
                </div>

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <input type="password" class="form-control" id="nova_senha" placeholder="Nova senha" name="nova_senha" required>
                </div>

                <div style="display:flex; flex-direction:row;" class="mb-3">
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                    </div>
                    <input type="password" class="form-control" id="nova_senha_confirmar" placeholder="Repetir a nova senha" name="senha_confirmar" required>
                </div>

                </div>

                <div class="footer">
                    <a href="index.php" style=" padding-inline:1.5em; border: 0;" class="btn btn-secondary">Voltar</a>
                    <button type="submit" style=" float: right;background-color:#5856d6; padding-inline:1.5em; border:0;" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
<?php } ?>


</body>

</html>