function limparTextoEstoque(valor) {
    return (valor || '').replace(/\s+/g, ' ').trim();
}

function numeroEstoque(valor) {
    if (typeof valor === 'number') return valor;
    const texto = String(valor || '').replace(/[^\d,.-]/g, '');
    const normalizado = texto.includes(',')
        ? texto.replace(/\./g, '').replace(',', '.')
        : texto;
    const numero = Number(normalizado);
    return Number.isFinite(numero) ? numero : 0;
}

function formatarMoedaEstoque(valor) {
    return Number(valor || 0).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}

function elementoVisivelEstoque(elemento) {
    return !elemento.hidden && !elemento.closest('[hidden]');
}

function extrairComponentesEstoque(produto) {
    return [...produto.querySelectorAll('tbody tr')]
        .map((linha) => {
            const celulas = [...linha.querySelectorAll('td')];
            if (celulas.length < 4) return null;

            const nome = limparTextoEstoque(celulas[0].textContent);
            if (!nome) return null;

            const quantidade = numeroEstoque(
                linha.dataset.quantidade || celulas[1].textContent
            );
            const precoUnitario = numeroEstoque(
                linha.dataset.precoUnidade || celulas[2].textContent
            );

            return {
                nome,
                quantidade,
                precoUnitario,
                total: quantidade * precoUnitario
            };
        })
        .filter(Boolean);
}

function extrairEstoqueVisivel() {
    const grupos = [...document.querySelectorAll('.item-grupo-estoque')]
        .filter(elementoVisivelEstoque)
        .map((grupo) => {
            const subgrupos = [...grupo.querySelectorAll('.item-subgrupo-estoque')]
                .filter(elementoVisivelEstoque)
                .map((subgrupo) => {
                    const produtos = [...subgrupo.querySelectorAll('.item-produto-estoque')]
                        .filter(elementoVisivelEstoque)
                        .map((produto) => ({
                            nome: produto.dataset.produtoNome || '',
                            unidade: produto.dataset.unidade || '',
                            codigoBarras: produto.dataset.codigoBarras || '',
                            precoCusto: numeroEstoque(produto.dataset.precoCusto),
                            markup: numeroEstoque(produto.dataset.markup),
                            precoVenda: numeroEstoque(produto.dataset.precoVenda),
                            estoque: numeroEstoque(produto.dataset.estoque),
                            componentes: extrairComponentesEstoque(produto)
                        }));

                    return {
                        nome: limparTextoEstoque(subgrupo.querySelector('.accordion-button')?.textContent),
                        produtos
                    };
                });

            return {
                nome: limparTextoEstoque(grupo.querySelector('.accordion-button')?.textContent),
                subgrupos
            };
        });

    return grupos;
}

function obterContextoRelatorioEstoque() {
    const textoOpcaoSelecionada = (select) =>
        select?.selectedOptions?.[0]?.textContent?.trim() || '';
    const filtros = [
        ['Grupo', textoOpcaoSelecionada(document.getElementById('filtroGrupo'))],
        ['Subgrupo', textoOpcaoSelecionada(document.getElementById('filtroSubgrupo'))],
        ['Produto', textoOpcaoSelecionada(document.getElementById('filtroProduto'))]
    ]
        .filter(([, valor]) => valor && !valor.startsWith('Todos '))
        .map(([nome, valor]) => `${nome}: ${valor}`);

    return {
        empresa: document.body.dataset.nomeEmpresa || 'Gestor Office',
        titulo: 'Cadastro de Estoque',
        subtitulo: filtros.join(' | ') || 'Todos os grupos, subgrupos e produtos'
    };
}

function prepararGeracaoEstoque(tipo) {
    const contexto = obterContextoRelatorioEstoque();
    const estrutura = extrairEstoqueVisivel();

    if (tipo === 'pdf') {
        gerarPdfEstoque(contexto, estrutura);
        return;
    }

    gerarExcelEstoque(contexto, estrutura);
}

function gerarPdfEstoque(contexto, estrutura) {
    if (typeof window.jspdf?.jsPDF === 'undefined') {
        alert('Biblioteca jsPDF não foi carregada.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const autoTable = pdf.autoTable || window.autoTable;
    if (typeof autoTable !== 'function') {
        alert('Erro: Plugin AutoTable não encontrado.');
        return;
    }

    const margem = 12;
    const largura = pdf.internal.pageSize.getWidth() - margem * 2;
    let y = 16;

    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(16);
    pdf.text(contexto.empresa, margem, y);
    y += 7;
    pdf.setFontSize(12);
    pdf.text(contexto.titulo, margem, y);
    y += 6;
    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(9);
    pdf.text(contexto.subtitulo, margem, y);
    y += 8;

    const iniciarNovaPaginaSeNecessario = (altura = 14) => {
        if (y + altura > pdf.internal.pageSize.getHeight() - 16) {
            pdf.addPage();
            y = 15;
        }
    };

    if (!estrutura.length) {
        pdf.setFontSize(10);
        pdf.text('Nenhum dado encontrado para os filtros selecionados.', margem, y);
    } else {
        estrutura.forEach((grupo) => {
            iniciarNovaPaginaSeNecessario(10);
            pdf.setFillColor(40, 50, 60);
            pdf.rect(margem, y, largura, 8, 'F');
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(10);
            pdf.setTextColor(255, 255, 255);
            pdf.text(grupo.nome.toUpperCase(), margem + 3, y + 5.5);
            pdf.setTextColor(0, 0, 0);
            y += 11;

            grupo.subgrupos.forEach((subgrupo) => {
                iniciarNovaPaginaSeNecessario(8);
                pdf.setFillColor(230, 235, 240);
                pdf.rect(margem, y, largura, 7, 'F');
                pdf.setFontSize(9);
                pdf.text(subgrupo.nome, margem + 3, y + 5);
                y += 9;

                if (!subgrupo.produtos.length) {
                    pdf.setFont('helvetica', 'italic');
                    pdf.setFontSize(8);
                    pdf.text('Nenhum produto cadastrado.', margem + 3, y + 4);
                    pdf.setFont('helvetica', 'normal');
                    y += 7;
                    return;
                }

                subgrupo.produtos.forEach((produto) => {
                    iniciarNovaPaginaSeNecessario(12);
                    autoTable.call(pdf, {
                        startY: y,
                        margin: { left: margem, right: margem, bottom: 16 },
                        head: [['Produto', 'Unidade', 'Código de barras', 'Custo', 'Markup', 'Venda', 'Estoque']],
                        body: [[
                            produto.nome,
                            produto.unidade,
                            produto.codigoBarras,
                            formatarMoedaEstoque(produto.precoCusto),
                            `${produto.markup.toLocaleString('pt-BR')}%`,
                            formatarMoedaEstoque(produto.precoVenda),
                            produto.estoque.toLocaleString('pt-BR')
                        ]],
                        theme: 'grid',
                        styles: { fontSize: 8, cellPadding: 1.5 },
                        headStyles: { fillColor: [13, 110, 253], textColor: [255, 255, 255], fontStyle: 'bold' }
                    });
                    y = pdf.lastAutoTable.finalY + 2;

                    if (produto.componentes.length) {
                        iniciarNovaPaginaSeNecessario(12);
                        autoTable.call(pdf, {
                            startY: y,
                            margin: { left: margem + 5, right: margem + 5, bottom: 16 },
                            head: [[`Componentes de ${produto.nome}`, 'Quantidade', 'Preço unitário', 'Total']],
                            body: produto.componentes.map((componente) => [
                                componente.nome,
                                componente.quantidade.toLocaleString('pt-BR'),
                                formatarMoedaEstoque(componente.precoUnitario),
                                formatarMoedaEstoque(componente.total)
                            ]),
                            theme: 'grid',
                            styles: { fontSize: 8, cellPadding: 1.5 },
                            headStyles: { fillColor: [108, 117, 125], textColor: [255, 255, 255], fontStyle: 'bold' }
                        });
                        y = pdf.lastAutoTable.finalY + 4;
                    } else {
                        y += 3;
                    }
                });
            });
        });
    }

    const totalPaginas = pdf.internal.getNumberOfPages();
    for (let pagina = 1; pagina <= totalPaginas; pagina++) {
        pdf.setPage(pagina);
        const altura = pdf.internal.pageSize.getHeight();
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(100);
        pdf.text('Gestor Office - Cadastro de Estoque', margem, altura - 8);
        pdf.text(`Página ${pagina} de ${totalPaginas}`, pdf.internal.pageSize.getWidth() - margem - 24, altura - 8);
    }

    pdf.save('cadastro_estoque.pdf');
}

function gerarExcelEstoque(contexto, estrutura) {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX (xlsx-js-style) não foi carregada.');
        return;
    }

    const estiloEmpresa = { font: { bold: true, sz: 14, name: 'Arial' } };
    const estiloTitulo = { font: { bold: true, sz: 12, name: 'Arial' } };
    const estiloSubtitulo = { font: { sz: 10, color: { rgb: '555555' }, name: 'Arial' } };
    const estiloGrupo = {
        fill: { fgColor: { rgb: '28323C' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 11, name: 'Arial' },
        alignment: { vertical: 'center' }
    };
    const estiloSubgrupo = {
        fill: { fgColor: { rgb: 'E6EBF0' } },
        font: { bold: true, sz: 10, name: 'Arial' },
        alignment: { vertical: 'center' }
    };
    const estiloCabecalho = {
        fill: { fgColor: { rgb: '0D6EFD' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };
    const estiloComponente = {
        fill: { fgColor: { rgb: '6C757D' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };
    const estiloDado = { font: { sz: 9, name: 'Arial' } };
    const dados = [
        [{ v: contexto.empresa, s: estiloEmpresa }],
        [{ v: contexto.titulo, s: estiloTitulo }],
        [{ v: contexto.subtitulo, s: estiloSubtitulo }],
        []
    ];

    if (!estrutura.length) {
        dados.push([{ v: 'Nenhum dado encontrado para os filtros selecionados.', s: estiloDado }]);
    } else {
        estrutura.forEach((grupo) => {
            dados.push([{ v: grupo.nome, s: estiloGrupo }]);

            if (!grupo.subgrupos.length) {
                dados.push([{ v: 'Nenhum subgrupo cadastrado.', s: estiloDado }]);
            }

            grupo.subgrupos.forEach((subgrupo) => {
                dados.push([{ v: subgrupo.nome, s: estiloSubgrupo }]);

                if (!subgrupo.produtos.length) {
                    dados.push([{ v: 'Nenhum produto cadastrado.', s: estiloDado }]);
                    return;
                }

                dados.push([
                    'Produto', 'Unidade', 'Código de barras', 'Custo', 'Markup (%)', 'Venda', 'Estoque',

                ].map((valor) => ({ v: valor, s: estiloCabecalho })));

                subgrupo.produtos.forEach((produto) => {
                    dados.push([
                        produto.nome,
                        produto.unidade,
                        produto.codigoBarras,
                        { v: produto.precoCusto, t: 'n', z: '"R$" #,##0.00' },
                        { v: produto.markup, t: 'n', z: '0.00"%"' },
                        { v: produto.precoVenda, t: 'n', z: '"R$" #,##0.00' },
                        { v: produto.estoque, t: 'n', z: '#,##0.000' },
                        '',
                        '',
                        '',
                        ''
                    ].map((valor) => typeof valor === 'object' ? { ...valor, s: estiloDado } : { v: valor, s: estiloDado }));

                    if (produto.componentes.length) {
                        dados.push([
                            { v: 'Componentes', s: estiloComponente },
                            { v: 'Quantidade', s: estiloComponente },
                            { v: 'Preço unitário', s: estiloComponente },
                            { v: 'Total', s: estiloComponente }
                        ]);
                        produto.componentes.forEach((componente) => {
                            dados.push([
                                { v: componente.nome, s: estiloDado },
                                { v: componente.quantidade, t: 'n', z: '#,##0.000', s: estiloDado },
                                { v: componente.precoUnitario, t: 'n', z: '"R$" #,##0.00', s: estiloDado },
                                { v: componente.total, t: 'n', z: '"R$" #,##0.00', s: estiloDado }
                            ]);
                        });
                    }
                });
            });
            dados.push([]);
        });
    }

    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(dados);
    worksheet['!cols'] = [
        { wch: 30 }, { wch: 14 }, { wch: 22 }, { wch: 16 }, { wch: 14 }, { wch: 16 },
        { wch: 14 }, { wch: 28 }, { wch: 14 }, { wch: 18 }, { wch: 20 }
    ];
    worksheet['!rows'] = dados.map(() => ({ hpt: 20 }));
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Cadastro de Estoque');
    XLSX.writeFile(workbook, 'cadastro_estoque.xlsx');
}