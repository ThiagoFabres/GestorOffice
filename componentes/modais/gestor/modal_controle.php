<?php
// Identifica se o objeto em edição é um Turno02 (atividade) ou Controle
$isAtividade = false;

if ($controleEdicao !== null) {
    // Se possuir a propriedade hora_inicio ou hora_final preenchida, é uma Atividade/Turno
    $isAtividade = isset($controleEdicao->hora_inicio) || isset($controleEdicao->hora_final);
}
?>

<link rel="stylesheet" href="/modal_controle.css"></link>

<div class="modal fade" id="modal_controle" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?= $controleEdicao !== null ? 'Editar Registro' : 'Novo Registro' ?></h5>
            </div>
            <div class="modal-body">
                <form method="post" id="content" action="controle_manager.php">
                    <input type="hidden" name="id" value="<?= $controleEdicao !== null ? (int) $controleEdicao->id : '' ?>">
                    
                    <!-- CAMPOS DO INÍCIO (Atende Controle e Turno) -->
                    <div class="d-flex flex-row w-100 gap-2 mb-3">
                        <div class="d-flex flex-column w-50">
                            <label class="form-label" id="label_hora">Hora</label>
                            <div class="input-nome input-form-adm">
                                <input type="time" name="hora" class="form-control" value="<?= $controleEdicao !== null ? htmlspecialchars($controleEdicao->hora ?? $controleEdicao->hora_inicio ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" required>
                            </div>
                        </div>
                        <div class="d-flex flex-column w-50">
                            <label class="form-label" id="label_tolerancia">Tolerância (Minutos)</label>
                            <div class="input-nome input-form-adm">
                                <input type="number" name="tolerancia" class="form-control" min="0" step="1" value="<?= $controleEdicao !== null ? (int) ($controleEdicao->tolerancia ?? $controleEdicao->tolerancia_inicio ?? 0) : '' ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- SELECTOR DE TIPO (RADIO GROUP) -->
                    <div class="custom-radio-group my-3">
                        <div class="custom-radio-item">
                            <label for="opcao_controle">Controle</label>
                            <input type="radio" id="opcao_controle" name="opcao_filtro" value="controle" <?= !$isAtividade ? 'checked' : '' ?>>
                        </div>
                        <div class="custom-radio-item">
                            <label for="opcao_atividade">Início</label>
                            <input type="radio" id="opcao_atividade" name="opcao_filtro" value="inicio" <?= $isAtividade ? 'checked' : '' ?>>
                        </div>
                        <div class="custom-radio-item">
                            <label for="opcao_atividade">Término</label>
                            <input type="radio" id="opcao_atividade" name="opcao_filtro" value="termino" <?= $isAtividade ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" name="acao" value="<?= $controleEdicao !== null ? 'editar' : 'adicionar' ?>" class="btn btn-success" style="background-color: #5856d6; border-color: #5856d6;">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>