function obterDadosExportacaoMovimentacao() {
    const elemento = document.getElementById('dados-exportacao-movimentacao');
    if (!elemento) {
        alert('Não foi possível carregar os dados das movimentações.');
        return null;
    }

    return JSON.parse(elemento.textContent);
}

function obterContextoExportacaoMovimentacao() {
    const filtros = [];
    const campos = [
        ['data_inicial', 'Data inicial'],
        ['data_final', 'Data final'],
        ['tipo', 'Tipo'],
        ['filtroCadastro', 'Cliente/fornecedor'],
        ['filtroGrupo', 'Grupo'],
        ['filtroSubgrupo', 'Subgrupo'],
        ['filtroProduto', 'Produto']
    ];

    campos.forEach(([id, titulo]) => {
        const campo = document.getElementById(id);
        const valor = campo?.selectedOptions?.[0]?.textContent?.trim() || campo?.value || '';
        if (valor && !valor.startsWith('Todos')) {
            filtros.push(`${titulo}: ${valor}`);
        }
    });

    return filtros.length ? filtros.join(' | ') : 'Sem filtros';
}

function formatarValorExportacaoMovimentacao(valor) {
    return Number(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatarQuantidadeExportacaoMovimentacao(valor) {
    return Number(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    });
}

function criarLinhasExportacaoMovimentacao(dados, formatarValores = false) {
    const linhas = [];
    const indicesMovimentos = [];

    dados.forEach((movimento) => {
        indicesMovimentos.push(linhas.length);
        linhas.push([
            movimento.data,
            movimento.documento,
            movimento.tipo,
            movimento.cadastro,
            `Total do movimento (${movimento.quantidade_itens} itens)`,
            formatarValores
                ? formatarQuantidadeExportacaoMovimentacao(movimento.quantidade_total)
                : Number(movimento.quantidade_total),
            '',
            formatarValores
                ? `R$ ${formatarValorExportacaoMovimentacao(movimento.valor_custo_total)}`
                : Number(movimento.valor_custo_total),
            '',
            formatarValores
                ? `R$ ${formatarValorExportacaoMovimentacao(movimento.valor_venda_total)}`
                : Number(movimento.valor_venda_total)
        ]);

        movimento.itens.forEach((item) => {
            const quantidade = Number(item.quantidade);
            const valorCusto = quantidade * Number(item.preco_custo);
            const valorVenda = quantidade * Number(item.preco_venda);
            linhas.push([
                '',
                '',
                '',
                '',
                item.produto,
                formatarValores
                    ? formatarQuantidadeExportacaoMovimentacao(quantidade)
                    : quantidade,
                formatarValores
                    ? `R$ ${formatarValorExportacaoMovimentacao(item.preco_custo)}`
                    : Number(item.preco_custo),
                formatarValores
                    ? `R$ ${formatarValorExportacaoMovimentacao(valorCusto)}`
                    : valorCusto,
                formatarValores
                    ? `R$ ${formatarValorExportacaoMovimentacao(item.preco_venda)}`
                    : Number(item.preco_venda),
                formatarValores
                    ? `R$ ${formatarValorExportacaoMovimentacao(valorVenda)}`
                    : valorVenda
            ]);
        });
    });

    return { linhas, indicesMovimentos };
}

function gerarPdfMovimentacao() {
    if (typeof window.jspdf?.jsPDF === 'undefined') {
        alert('Biblioteca jsPDF não foi carregada.');
        return;
    }

    const dados = obterDadosExportacaoMovimentacao();
    if (!dados) return;

    const pdf = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    if (typeof pdf.autoTable !== 'function') {
        alert('Plugin AutoTable não foi carregado.');
        return;
    }

    const margem = 12;
    const filtros = obterContextoExportacaoMovimentacao();
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.text('Gestor Office', margem, 15);
    pdf.setFontSize(12);
    pdf.text('Movimentações de estoque', margem, 22);
    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(8);
    const linhasFiltros = pdf.splitTextToSize(
        `Filtros: ${filtros}`,
        pdf.internal.pageSize.getWidth() - margem * 2
    );
    pdf.text(linhasFiltros, margem, 28);

    const linhasExportacao = criarLinhasExportacaoMovimentacao(dados, true);
    pdf.autoTable({
        startY: 28 + linhasFiltros.length * 4 + 2,
        margin: { left: margem, right: margem, bottom: 14 },
        head: [[
            'Data', 'Documento', 'Tipo', 'Cliente/fornecedor', 'Produto',
            'Quantidade', 'Custo unitário', 'Valor de custo', 'Venda unitária', 'Valor de venda'
        ]],
        body: linhasExportacao.linhas,
        theme: 'grid',
        styles: { fontSize: 7, cellPadding: 1.5, overflow: 'linebreak' },
        headStyles: { fillColor: [13, 110, 253], textColor: [255, 255, 255], fontStyle: 'bold' },
        didParseCell(celula) {
            if (celula.section === 'body' && linhasExportacao.indicesMovimentos.includes(celula.row.index)) {
                celula.cell.styles.fillColor = [230, 235, 240];
                celula.cell.styles.fontStyle = 'bold';
            }
        }
    });

    const totalPaginas = pdf.internal.getNumberOfPages();
    for (let pagina = 1; pagina <= totalPaginas; pagina++) {
        pdf.setPage(pagina);
        const altura = pdf.internal.pageSize.getHeight();
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(100);
        pdf.text('Gestor Office - Movimentações de estoque', margem, altura - 7);
        pdf.text(
            `Página ${pagina} de ${totalPaginas}`,
            pdf.internal.pageSize.getWidth() - margem - 25,
            altura - 7
        );
    }

    pdf.save('movimentacoes_estoque.pdf');
}

function gerarExcelMovimentacao() {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não foi carregada.');
        return;
    }

    const dados = obterDadosExportacaoMovimentacao();
    if (!dados) return;

    const filtros = obterContextoExportacaoMovimentacao();
    const cabecalho = [
        'Data', 'Documento', 'Tipo', 'Cliente/fornecedor', 'Produto',
        'Quantidade', 'Custo unitário', 'Valor de custo', 'Venda unitária', 'Valor de venda'
    ];
    const linhasExportacao = criarLinhasExportacaoMovimentacao(dados);
    const dadosMovimentos = [
        ['Gestor Office'],
        ['Movimentações de estoque'],
        [`Filtros: ${filtros}`],
        [],
        cabecalho,
        ...linhasExportacao.linhas
    ];

    const workbook = XLSX.utils.book_new();
    const planilhaMovimentos = XLSX.utils.aoa_to_sheet(dadosMovimentos);
    planilhaMovimentos['!cols'] = [
        { wch: 18 }, { wch: 14 }, { wch: 12 }, { wch: 30 }, { wch: 36 },
        { wch: 16 }, { wch: 18 }, { wch: 18 }, { wch: 18 }, { wch: 18 }
    ];
    planilhaMovimentos['!autofilter'] = {
        ref: `A5:J${Math.max(5, dadosMovimentos.length)}`
    };
    const estiloResumoMovimento = {
        fill: { fgColor: { rgb: 'E6EBF0' } },
        font: { bold: true }
    };
    linhasExportacao.indicesMovimentos.forEach((indice) => {
        const linha = indice + 5;
        for (let coluna = 0; coluna < cabecalho.length; coluna++) {
            const endereco = XLSX.utils.encode_cell({ r: linha, c: coluna });
            if (planilhaMovimentos[endereco]) {
                planilhaMovimentos[endereco].s = estiloResumoMovimento;
            }
        }
    });
    XLSX.utils.book_append_sheet(workbook, planilhaMovimentos, 'Movimentações');
    XLSX.writeFile(workbook, 'movimentacoes_estoque.xlsx');
}

document.getElementById('botao-gerar-pdf')?.addEventListener('click', gerarPdfMovimentacao);
document.getElementById('botao-gerar-excel')?.addEventListener('click', gerarExcelMovimentacao);