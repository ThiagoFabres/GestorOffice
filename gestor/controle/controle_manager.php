<?php

require_once __DIR__ . '/../../db/base.php';
require_once __DIR__ . '/../../db/entities/usuarios.php';
require_once __DIR__ . '/../../db/entities/empresas.php';
require_once __DIR__ . '/../../db/entities/controle.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->cargo != 2) {
	header('Location: /');
	exit;
}

$empresaId = $_SESSION['usuario']->id_empresa;
$empresa = Empresa::read($empresaId)[0] ?? null;

if ($empresa === null || $empresa->permissao_seguranca != 1) {
	header('Location: /');
	exit;
}

$acao = filter_input(INPUT_POST, 'acao') ?? filter_input(INPUT_GET, 'acao');
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($acao === 'deletar') {
	$controle = null;
	foreach (Controle::read($empresaId) as $item) {
		if ((int) $item->id === (int) $id) {
			$controle = $item;
			break;
		}
	}

	if ($controle !== null) {
		Controle::delete($controle->id);
	}

	header('Location: controle.php');
	exit;
}

$hora = trim((string) filter_input(INPUT_POST, 'hora'));
$tolerancia = filter_input(INPUT_POST, 'tolerancia', FILTER_VALIDATE_INT);

if ($acao === 'adicionar' || $acao === 'editar') {
	if ($hora === '' || $tolerancia === false || $tolerancia === null || $tolerancia < 0) {
		header('Location: controle.php?erro=campos_vazios');
		exit;
	}

	if ($acao === 'editar') {
		$controle = null;
		foreach (Controle::read($empresaId) as $item) {
			if ((int) $item->id === (int) $id) {
				$controle = $item;
				break;
			}
		}

		if ($controle === null) {
			header('Location: controle.php?erro=nao_encontrado');
			exit;
		}

		$controle->hora = $hora;
		$controle->tolerancia = $tolerancia;
		Controle::update($controle);
	} else {
		Controle::create(new Controle(null, $empresaId, $hora, $tolerancia));
	}
}

header('Location: controle.php');
exit;
