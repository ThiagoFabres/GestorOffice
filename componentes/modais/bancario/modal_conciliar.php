

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
                    <form method="post" action="movimentacao_manager.php">

                    <input type="hidden" name="id" value="<?=$id?>">

                    <div class="d-flex fd-row gap-3" >
                    <div class="modal-input-group w-50">
                        <label for="titulo">Titulo</label>
                            <div class="titulo-group">
                                <div class="input-titulo" style="width:75%;">
                                    <!--Nome: -->
                                    
                                    <select name="titulo" class="form-control form-select-titulo" id="titulo"
                                        style="border-top-right-radius: 0; border-bottom-right-radius: 0; ">
                                        <option value="">Selecione</option>

                                        <?php $titulos = Con01::read(null, $_SESSION['usuario']->id_empresa, $ban02_tipo);
                                        foreach ($titulos as $titulo) { ?>
                                            <option value="<?= $titulo->id ?>">
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
                    
                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="conciliar" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                        
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>

// --- Função genérica para inicializar título e subtítulo (com Choices e filtro) ---
function initTituloSubtitulo(tituloId, subtituloId, subtituloSelecionado) {
  const tituloSelect = document.getElementById(tituloId);
  const subtituloSelect = document.getElementById(subtituloId);
  if (!tituloSelect || !subtituloSelect) return;

  // 🔹 Inicia Choices no título
  if (!tituloSelect._choices) {
    try {
      const tituloChoices = new Choices(tituloSelect, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: '',
        removeItemButton: false,
        searchPlaceholderValue: 'Digite para buscar...',
        noResultsText: 'Nenhum resultado encontrado'
      });
      tituloSelect._choices = tituloChoices;
    } catch (e) {
      console.warn('⚠️ Erro iniciando Choices em', tituloId, e);
    }
  }

  // 🔹 Guarda opções originais do subtítulo
  const origOptions = Array.from(subtituloSelect.querySelectorAll('option')).map(o => ({
    value: String(o.value),
    label: o.textContent.trim(),
    tituloId: (o.getAttribute('data-titulo-id') || '').toString()
  })).filter(o => o.value && o.value.toLowerCase() !== 'selecione');

  // 🔹 Destroi instância anterior se houver
  try {
    if (subtituloSelect._choices && typeof subtituloSelect._choices.destroy === 'function') {
      subtituloSelect._choices.destroy();
    }
  } catch {}

  // 🔹 Inicializa Choices no subtítulo
  let subtituloChoice = null;
  
  try {
    subtituloChoice = new Choices(subtituloSelect, {
      searchEnabled: true,
      shouldSort: false,
      itemSelectText: '',
      removeItemButton: false,
      searchPlaceholderValue: 'Digite para buscar...',
      noResultsText: 'Nenhum resultado encontrado'
    });
    subtituloSelect._choices = subtituloChoice;

    // 🔸 Começa VAZIO (apenas “Selecione”)
    subtituloChoice.clearChoices()
    subtituloChoice.clearStore()
    subtituloChoice.setChoices([{ value: '', label: 'Selecione', disabled: true }], 'value', 'label', true);
    // Se quiser pré-selecionar, use subtituloSelecionado
    if (subtituloSelecionado) {
      subtituloChoice.setChoiceByValue(subtituloSelecionado);
    }
  } catch (e) {
    console.error('Erro ao inicializar Choices para', subtituloId, e);
  }

  // 🔹 Filtra subtítulos pelo título
  function filtrarSubtitulosPorTitulo(tituloValue, manterSelecao = false) {

    if (!subtituloChoice) return;
    subtituloChoice.clearChoices()
    subtituloChoice.clearStore()
    try {
      const filtrados = origOptions.filter(o => o.tituloId === String(tituloValue));
      subtituloChoice.clearChoices();
      subtituloChoice.setChoices([{ value: '', label: 'Selecione', disabled: true }], 'value', 'label', true);
      if (filtrados.length) subtituloChoice.setChoices(filtrados, 'value', 'label', false);

      // limpa seleção, se necessário
      if (!manterSelecao) {
        subtituloChoice.removeActiveItems();
        subtituloSelect.value = '';
      } else {
        const atual = subtituloSelect.value;
        if (atual && filtrados.some(f => f.value === atual)) {
          subtituloChoice.setChoiceByValue(atual);
        } else {
          subtituloChoice.removeActiveItems();
          subtituloSelect.value = '';
        }
      }
    } catch (e) {
      console.error('Erro filtrando subtítulos:', e);
    }
  }

  // 🔹 Listener para mudança no título
  tituloSelect.addEventListener('change', function () {
    const tituloId = this.value;
    filtrarSubtitulosPorTitulo(tituloId, false);
  });

  // 🔹 Pré-seleciona subtítulo do PHP
  function aplicarPreSelecao() {
    if (!subtituloSelecionado) return;
    const found = origOptions.find(o => o.value === String(subtituloSelecionado));
    if (!found) return;

    const tituloId = found.tituloId;
    if (tituloSelect && tituloId && tituloSelect.value !== tituloId) {
      tituloSelect.value = tituloId;
      if (tituloSelect._choices) tituloSelect._choices.setChoiceByValue(tituloId);
      filtrarSubtitulosPorTitulo(tituloId, true);
    }

    setTimeout(() => {
      try {
        subtituloChoice.setChoiceByValue(String(found.value));
      } catch {
        const container = subtituloSelect.closest('.choices');
        const item = container?.querySelector(`.choices__item[data-value="${found.value}"]`);
        if (item) item.click();
      }
      subtituloSelect.value = found.value;
      subtituloSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }, 200);
  }

  // 🔹 Inicialização
  setTimeout(() => {
    if (tituloSelect.value) {
      filtrarSubtitulosPorTitulo(tituloSelect.value, true);
    }
    aplicarPreSelecao();
  }, 300);
}

// --- Inicialização automática apenas dos filtros (não do modal) ---
document.addEventListener('DOMContentLoaded', () => {
  // --- Valores vindos do PHP ---
  const tituloSelecionado = <?= json_encode($post_titulo ?? '') ?>;
  const subtituloSelecionado = <?= json_encode($post_subtitulo ?? '') ?>;
  const filtroTituloSelecionado = '';
  const filtroSubtituloSelecionado = '';

  // Inicializa os filtros (fora do modal)
  initTituloSubtitulo('titulo-filtro', 'subtitulo-filtro', filtroSubtituloSelecionado);

  // Restaura o título do filtro (PHP)
  setTimeout(() => {
    if (filtroTituloSelecionado) {
      const select = document.getElementById('titulo-filtro');
      if (select._choices) select._choices.setChoiceByValue(filtroTituloSelecionado.toString());
      else select.value = filtroTituloSelecionado;
      console.log('✅ Filtro de título restaurado:', filtroTituloSelecionado);
    }
  }, 400);
});


</script>