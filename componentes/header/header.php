<?php
require_once __DIR__ . '/../../db/entities/empresas.php';
$nomeEmpresa = Empresa::read($_SESSION['usuario']->id_empresa)[0]->nom_fant;
?>
<link rel="stylesheet" href="/componentes/header/header.css"> 
<link rel="stylesheet" href="/usuario/style/responsivo.css"> 

<div id="header">
       <div id="titulo-header">
            <button class="btn mb-0" onclick="encolher()">
                <span class="btn bi bi-list mb-0"></span>
            </button>
            <a>Dashboard</a>
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

    <script>



        
        
        function encolher(acao) {
        const barra = document.getElementById('barra-lateral');
        const container = document.getElementById('container');
        const superior = document.getElementById('header');

        



        if ( barra.style.animationName != 'encolher-lateral'){
            

            if(!document.querySelector('body').clientWidth <= 800) {                
            localStorage.setItem('tela', 'cheia')
            superior.style.animationName = 'encolher-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'forwards';

            container.style.animationName = 'encolher-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'forwards';
            }
            barra.style.animationName = 'encolher-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'forwards';
        } else if( barra.style.animationName == 'encolher-lateral') {
            if(!document.querySelector('body').clientWidth <= 800) {
                localStorage.setItem('tela', 'normal')
            superior.style.animationName = 'expandir-header'
            superior.style.animationDuration = '0.5s';
            superior.style.animationFillMode = 'backwards';
            container.style.animationName = 'expandir-container'
            container.style.animationDuration = '0.5s';
            container.style.animationFillMode = 'backwards';
            }
            barra.style.animationName = 'expandir-lateral';
            barra.style.animationDuration = '0.5s';
            barra.style.animationFillMode = 'backwards';

            

            return;
        } 
    }
    if(!document.querySelector('body').clientWidth <= 800) {
        if(localStorage.getItem('tela') == 'cheia') {
        setTimeout(
            () => {
                encolher('encolher')
            }, 100
        )
        
    }
    }
     
    
    </script>