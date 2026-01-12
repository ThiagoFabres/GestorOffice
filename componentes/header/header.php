<?php
require_once __DIR__ . '/../../db/entities/empresas.php';
$nomeEmpresa = Empresa::read($_SESSION['usuario']->id_empresa)[0]->nom_fant;
?>
<link rel="stylesheet" href="/componentes/header/header.css"> 

<div id="header">
       <div id="titulo-header">
            <button class="btn mb-0" onclick="encolher()">
                <span class="btn bi bi-list mb-0"></span>
            </button>
            <a href="/usuario/index.php">Dashboard</a>
        </div>

        <div id="nome-empresa">
            <h1><?=$nomeEmpresa?></h1>
        </div>
        <!-- <div id="conta-header">
            <button id="userBtn" type="button">
                <span style="color:#181f2b;"><?= htmlspecialchars($_SESSION['usuario']->nome, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </button> -->
            <div id="conta-header">
                <a href="/" class="dropdown-item">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </a>
            </div>
        <!-- </div> -->
    </div>