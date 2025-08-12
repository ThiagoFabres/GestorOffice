<?php
session_start();
if (isset($_SESSION['usuario'])) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="gestor-office.png" type="image/x"-icon>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Gestor Office</title>
</head>
<body style="background-color: #f3f4f7;">
<!-- Login -->

<div class="card login-card">
    <div class="card-body">
<div id="card-titulo"> <h1>Login</h1></div>
<div id="card-sub">Entre com a sua conta.</div>
<form action="login.php" method="post">
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
    <div class="footer">
    <button type="submit" style="background-color:#5856d6; padding-inline:1.5em; " class="btn btn-primary">Login</button>
    <a style="text-decoration:underline; color:#5856d6; float:right;">Esqueceu a senha?</a>
    </div>
    </div>
</div>
</form>



</body>
</html>