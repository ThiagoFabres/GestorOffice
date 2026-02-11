

<?php 
if($acao == 'conciliar') {
  $id = filter_input(INPUT_GET, 'id');
  $ban02 = Ban02::read($id)[0];
  $ban02_tipo = $ban02->valor < 0 ? 'D' : 'C';
}
?>
<div class="modal fade" id="modal_conciliar" tabindex="-1" role="dialog" aria-labelledby="modalCadastroCidadeLabel">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                
                <!-- Cabeçalho -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastroCidadeLabel">Conciliar Lançamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <!-- Corpo -->
                <div class="modal-body">
                    <e id="mensagem-erro"></e>
                    <form method="post" action="movimentacao_manager.php" id="form-conciliar">
                    <input type="hidden" name="caminho" value="<?=$caminho?>">
                    <input id="conciliar-id" type="hidden" name="id" value="<?=$id?>">

                    <div class="d-flex fd-row gap-3" >
                    <div class="modal-input-group w-50">
                        <label for="titulo">Titulo</label>
                        <div class="titulo-group">
                            <div class="input-titulo" style="width:75%;">
                                    <!--Nome: -->
                                    
                                <select name="titulo" class="form-control form-select-titulo" id="titulo"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0; ">
                                    <option value="">Selecione</option>

                                    <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($titulos as $titulo) { ?>
                                        <option value="<?= $titulo->id ?>" data-tipo="<?=$titulo->tipo?>">
                                            <?= htmlspecialchars($titulo->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                    </select>
                                    
                            </div>
                            <div class="input-documento-generator" style="width:25%">
                                    <button data-bs-toggle="modal" data-bs-target="#modal_titulo" type="button"
                                        class="form-control" id="btnModalCadastro"><i
                                            class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>                   
                    </div>

                    <div class="modal-input-group w-50 mb-3">
                        <label for="subtitulo">Sub-Titulo</label>
                        <div class="subtitulo-group">
                            <div class="input-subtitulo-div" style="width:75%;">

                                <select id="subtitulo" name="subtitulo" class="form-control form-select-titulo">
                                    <option value="">Selecione</option>
                                    <?php
                                    // Buscar todos os subtítulos da empresa
                                    $todosSubtitulos = Con02::read(null, $_SESSION['usuario']->id_empresa);
                                    foreach ($todosSubtitulos as $sub) { ?>
                                        <option value="<?= $sub->id ?>"

                                            data-titulo-id="<?= $sub->id_con01 ?>">
                                            <?= htmlspecialchars($sub->nome, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="input-documento-generator" style="width:25%">
                            <button data-bs-toggle="modal" data-bs-target="#modal_subtitulo" type="button"
                                class="form-control" id="btnModalCadastro"><i
                                    class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                    </div>   
                </div>
                <div class="d-flex flex-row justify-content-between">
      
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2 w-100">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="conciliar" class="btn btn-primary" id="conciliar-btn">Conciliar</button>
                        <button type="submit" name="acao" value="conciliar_marcados" id="conciliar-marcados-btn" class="btn btn-primary">Conciliar Marcados</button>
                        
                    </div>
                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    

    const modalConciliar = document.getElementById('modal_conciliar');
    modalConciliar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        document.getElementById('conciliar-id').value = id;

    });

});

  document.addEventListener('show.bs.modal', function() {
  const tabelaMov = document.getElementById('tabela-bancario')
  formConciliar = document.getElementById('form-conciliar')
  const button = event.relatedTarget;
  const idsListaConciliar = []
  const tipoLista = []
  const id = button.getAttribute('data-id');
  tipoLista.push(button.getAttribute('data-tipo'))
  if(id) {
        idsListaConciliar.push("id_check[" + id + "]")
  }
  tabelaMov.querySelectorAll('tbody tr td input[type="checkbox"]:checked').forEach(function(el) {
    if(el.value = 'on') {
        var inputEl = document.createElement('input')
        inputEl.setAttribute('name', el.getAttribute('name'))
        inputEl.setAttribute('type', 'hidden')
        inputEl.value = el.getAttribute('data-id')
        formConciliar.appendChild(inputEl)
        
        if(id) {
            if(!idsListaConciliar.includes(el.getAttribute('name'))) {
                idsListaConciliar.push(el.getAttribute('name'))
            }
            if(!tipoLista.includes(el.getAttribute('data-tipo'))) {
                tipoLista.push(el.getAttribute('data-tipo'))
            }
        }
  }})
  console.log(tipoLista)
    if(tipoLista.includes('C') && tipoLista.includes('D') ) {
        document.getElementById('mensagem-erro').innerHTML = `<div class="w-100 d-flex justify-content-center"><p  style="color: red">Mais de um tipo de lançamento marcado</p> </div>` 
        document.getElementById('conciliar-marcados-btn').style.display = 'none'
        document.getElementById('conciliar-btn').style.display = 'none'
        
    } 
    console.log(idsListaConciliar)
    if(idsListaConciliar.length <= 1) {
    document.getElementById('conciliar-marcados-btn').style.display = 'none'
    document.getElementById('conciliar-btn').style.display = 'block'
  } else if(idsListaConciliar.length > 1){
    document.getElementById('conciliar-marcados-btn').style.display = 'block'
    document.getElementById('conciliar-btn').style.display = 'none'
  }
  if(tipoLista.includes('C') && tipoLista.includes('D') ) {
        document.getElementById('mensagem-erro').innerHTML = `<div class="w-100 d-flex justify-content-center"><p  style="color: red">Mais de um tipo de lançamento marcado</p> </div>` 
        document.getElementById('conciliar-marcados-btn').style.display = 'none'
        document.getElementById('conciliar-btn').style.display = 'none'
        document.getElementById('form-conciliar').style.display = 'none'

        
    } else if(tipoLista.includes('C') && !tipoLista.includes('D')) {
        document.getElementById('mensagem-erro').innerHTML = ''
        if(idsListaConciliar.length <= 1) {
            document.getElementById('conciliar-btn').style.display = 'block'
        } else if(idsListaConciliar.length > 1) {
            document.getElementById('conciliar-marcados-btn').style.display = 'block'
        }
        
        
        document.getElementById('form-conciliar').style.display = 'block'
  }
    
  })
  
var tituloModalElement = document.querySelector('#titulo');

const todosTitulosModal = tituloModalElement
    ? Array.from(tituloModalElement.querySelectorAll('option')).map(opt => ({
        value: opt.value,
        label: opt.textContent.trim(),
        tipo: opt.getAttribute('data-tipo')
    }))
    : [];




    </script>


