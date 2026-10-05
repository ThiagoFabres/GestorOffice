function formatarData(dataStr) {
            if (!dataStr || dataStr.trim() === '') return '';
            const regex = /(\d{4})-(\d{2})-(\d{2})/;
            const match = dataStr.match(regex);
            if (match) {
                return match[3] + '/' + match[2] + '/' + match[1];
            }
            return dataStr;
        }
async function gerarpdf(nome, nomeEmpresa = '', estilo = 'completo') {
    console.log('Rendering');
    const tabela = document.querySelector('#tabela-pdf');
    const modoReducao = estilo === 'reduzido';

    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    const { jsPDF } = window.jspdf;

    const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4"
    });

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    /* -------------------------
       1. CABEÇALHO (Nome da Empresa e Título)
    ------------------------- */
    const titulo =
        document.querySelector('.card .card-header h3')?.textContent ||
        `Relatório de Movimentação Bancária`;

    doc.setFontSize(14);
    doc.setFont(undefined, "bold");
    
    let y = 12;
    if (nomeEmpresa) {
        doc.text(nomeEmpresa, 10, y);
        y += 6;
    }
    doc.setFontSize(12);
    doc.text(titulo, 10, y);
    y += 8;

    /* -------------------------
       2. PERÍODO / DATA INICIAL / DATA FINAL (Entre Cabeçalho e Tabela)
    ------------------------- */
    doc.setFontSize(10);
    doc.setFont(undefined, "normal");

    const di = document.querySelector('#filtro_data_inicial')?.value;
    const df = document.querySelector('#filtro_data_final')?.value;

    if (di && df) {
        doc.text(`Período: ${formatarData(di)} até ${formatarData(df)}`, 10, y);
        y += 6;
    } else if (di) {
        doc.text(`Data Inicial: ${formatarData(di)}`, 10, y);
        y += 6;
    } else if (df) {
        doc.text(`Data Final: ${formatarData(df)}`, 10, y);
        y += 6;
    }

    // Demais filtros (ex.: Documento/Nome)
    const valDoc = document.querySelector('#filtro_nome')?.value;
    if (valDoc) {
        doc.text(`Documento: ${valDoc}`, 10, y);
        y += 6;
    }

    /* -------------------------
       3. EXTRAIR TABELA (tbody + tfoot)
    ------------------------- */
    let head = [];
    let body = [];

    tabela.querySelectorAll("thead tr").forEach(tr => {
        const row = [];
        tr.querySelectorAll("th").forEach(th => row.push(th.innerText.trim()));
        head.push(row);
    });

    tabela.querySelectorAll("tbody tr, tfoot tr").forEach(tr => {
        const row = [];
        tr.querySelectorAll("td").forEach(td => {
            row.push(td.textContent.replace('R$', '').replace(/\s+/g, ' ').trim());
        });

        if (tr.parentElement.tagName.toLowerCase() === 'tfoot' || tr.id === 'tr-totais') {
            row.isTotalRow = true;
        }

        body.push(row);
    });

    /* -------------------------
       MODO REDUZIDO: Data, Cliente/Fornecedor e Valor
    ------------------------- */
    if (modoReducao) {
        const cabecalho = head[head.length - 1] || [];
        const achar = (regex) => cabecalho.findIndex(t => regex.test(String(t || '')));

        const idxData  = achar(/data/i);
        const idxTipo  = achar(/tipo/i);
        const idxNome  = achar(/nome|cliente|fornecedor|favorecido/i);
        let   idxValor = achar(/valor/i);

        if (idxValor === -1 && cabecalho.length) idxValor = cabecalho.length - 1;

        const indices = [idxData, idxTipo, idxNome, idxValor].filter(i => i !== -1);

        head = [indices.map(i => cabecalho[i])];

        body = body.map(row => {
            const nova = indices.map(i => row[i] ?? '');
            if (row.isTotalRow) nova.isTotalRow = true;
            return nova;
        });
    }

    /* -------------------------
       ESTILOS DE COLUNA
    ------------------------- */
    const colunas = head[head.length - 1] || [];
    const columnStylesConfig = {};

    colunas.forEach((tituloCol, i) => {
        const t = String(tituloCol || '');

        if (/valor/i.test(t)) {
            columnStylesConfig[i] = { halign: 'right'};
        } else if (/data/i.test(t)) {
            columnStylesConfig[i] = { halign: 'left', cellWidth: 20};
        } else if (/descri/i.test(t) || /cliente|fornecedor|favorecido/i.test(t)) {
            columnStylesConfig[i] = { halign: 'left' };
        } else if (/tipo/i.test(t)) {
            columnStylesConfig[i] = { halign: 'center', cellWidth: 16};
        }
    });

    /* -------------------------
       4. DESENHAR TABELA
    ------------------------- */
    doc.autoTable({
    head: head,
    body: body,
    startY: y + 2,
    theme: 'striped',
    showHead: 'everyPage',
    rowPageBreak: 'avoid',

    styles: {
        fontSize: 9,
        cellPadding: 2,
        halign: 'left',
        valign: 'middle',
        overflow: 'linebreak'
    },

    columnStyles: columnStylesConfig,

    /* ---------------------------------------------------------
       1. AUMENTAR TAMANHO DO TH (Aumentado fontSize para 10 ou 11)
    --------------------------------------------------------- */
    headStyles: {
        fillColor: [206, 206, 206],
        textColor: 0,
        fontStyle: "bold",
        fontSize: 13,
        cellPadding: 3
    },

    alternateRowStyles: {
        fillColor: [255, 255, 255]
    },

    margin: {
        top: 15,
        left: 8,
        right: 8
    },

    didParseCell: function (data) {
        /* ---------------------------------------------------------
           2. ALINHAR O CABEÇALHO "VALOR" À DIREITA
        --------------------------------------------------------- */
        if (data.section === 'head') {
            const textoCabecalho = String(data.cell.raw || '');
            if (/valor/i.test(textoCabecalho)) {
                data.cell.styles.halign = 'right';
            }
        }

        /* ---------------------------------------------------------
           3. ESTILOS DAS LINHAS DE TOTAL E EFEITO ZEBRA
        --------------------------------------------------------- */
        if (data.row.raw?.isTotalRow) {
            data.cell.styles.fontStyle = 'bold';
            data.cell.styles.fillColor = [220, 220, 220];
            data.cell.styles.textColor = [0, 0, 0];
            data.cell.styles.halign = 'right';
        } else if (data.section === 'body') {
            const rowData = data.row.raw || [];
            const ehInicioDeRegistro = rowData[0] || rowData[1] || rowData[2];

            if (ehInicioDeRegistro && data.column.index === 0) {
                if (data.row.index === 0) {
                    data.table._registroIndex = 0;
                } else {
                    data.table._registroIndex = (data.table._registroIndex || 0) + 1;
                }
            }

            const registroAtual = data.table._registroIndex || 0;

            if (registroAtual % 2 === 1) {
                data.cell.styles.fillColor = [245, 245, 245];
            } else {
                data.cell.styles.fillColor = [255, 255, 255];
            }
        }
    }
});

    /* -------------------------
       5. RESUMO DE SALDOS (Apenas no modo completo)
    ------------------------- */
    if (!modoReducao) {
        const saldos = obterSaldos();
        let resumoY = doc.lastAutoTable?.finalY ? doc.lastAutoTable.finalY + 10 : y + 10;

        if (resumoY > pageHeight - 35) {
            doc.addPage();
            resumoY = 15;
        }

        doc.setFontSize(10);
        doc.setFont(undefined, "bold");
        doc.text('Resumo de saldos', 10, resumoY);
        doc.setFont(undefined, "normal");
        
        doc.text(`Saldo inicial da conta: R$ ${saldos.inicial}`, 10, resumoY + 6);
        doc.text(`Saldo do periodo: R$ ${saldos.filtro}`, 10, resumoY + 12);
        doc.text(`Saldo total: R$ ${saldos.total}`, 10, resumoY + 18);
    }

    /* -------------------------
       PAGINAÇÃO E SALVAMENTO
    ------------------------- */
    const totalPages = doc.internal.getNumberOfPages();
    doc.setFontSize(9);

    for (let i = 1; i <= totalPages; i++) {
        doc.setPage(i);
        doc.text(`Página ${i} de ${totalPages}`, pageWidth - 10, 10, { align: 'right' });
    }

    doc.save(modoReducao ? `relatorio_movimentacao_vinculados.pdf` : `relatorio_movimentacao.pdf`);
}















function gerarexcel(nome, nomeEmpresa = '') {
    try {
        var tabela = document.querySelector('#tabela-pdf');
        if (!tabela) {
            alert("Tabela não encontrada!");
            return;
        }

        

        function getSelectValue(selector) {
            var el = document.querySelector(selector);
            if (!el) return '';
            return (el.options && el.options[el.selectedIndex]) ? 
                   el.options[el.selectedIndex].text.trim() : '';
        }

        function getRadioValue(name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            if (!checked) return '';
            var parent = checked.parentElement;
            var lbl = parent ? parent.querySelector('label') : null;
            return lbl ? lbl.textContent.trim() : checked.value;
        }

        var headerFiltros = [];

        /* 1. Nome da Empresa e Título no topo */
        if (nomeEmpresa) {
            headerFiltros.push([nomeEmpresa]);
        }

        var titleEl = document.querySelector('.card .card-header h3') || document.querySelector('h3');
        var titleText = titleEl ? titleEl.textContent.trim() : 'Relatório de Movimentação Bancária';
        headerFiltros.push([titleText]);
        headerFiltros.push([]); // Linha em branco

        /* 2. Período / Data Inicial / Data Final */
        var di = document.querySelector('#filtro_data_inicial');
        var df = document.querySelector('#filtro_data_final');

        if ((di && di.value) || (df && df.value)) {
            var dataInicialFmt = di && di.value ? formatarData(di.value) : '';
            var dataFinalFmt = df && df.value ? formatarData(df.value) : '';
            
            if (dataInicialFmt && dataFinalFmt) {
                headerFiltros.push(['Período:', dataInicialFmt + ' até ' + dataFinalFmt]);
            } else if (dataInicialFmt) {
                headerFiltros.push(['Data Inicial:', dataInicialFmt]);
            } else if (dataFinalFmt) {
                headerFiltros.push(['Data Final:', dataFinalFmt]);
            }
        }

        /* Demais filtros */
        var tipo = getSelectValue('select[name="filtro_tipo"]');
        var conta = getSelectValue('select[name="filtro_conta"]');
        var titulo = getSelectValue('select[name="filtro_titulo"]') || getSelectValue('#titulo-filtro');
        var subtitulo = getSelectValue('select[name="filtro_subtitulo"]') || getSelectValue('#subtitulo-filtro');
        var conciliado = document.querySelector('input[name="filtro_conciliado"]');
        var opcao = getRadioValue('opcao_filtro');
        var por = getRadioValue('filtro_por');

        if (tipo && tipo !== 'Selecione') headerFiltros.push(['Tipo:', tipo]);
        if (conta && conta !== 'Selecione') headerFiltros.push(['Conta:', conta]);
        if (titulo && titulo !== 'Selecione') headerFiltros.push(['Título:', titulo]);
        if (subtitulo && subtitulo !== 'Selecione') headerFiltros.push(['Subtítulo:', subtitulo]);
        if (conciliado && conciliado.checked) headerFiltros.push(['Conciliado:', 'Sim']);
        if (opcao) headerFiltros.push(['Opção:', opcao]);
        if (por) headerFiltros.push(['Filtro por:', por]);

        headerFiltros.push([]); // Linha em branco antes da tabela

        /* 3. Extrair dados da Tabela */
        var tabelaClone = tabela.cloneNode(true);

        tabelaClone.querySelectorAll('td, th').forEach(function(el) {
            if (el.textContent.includes('R$')) {
                el.textContent = el.textContent.replace(/R\$\s?/g, '').trim();
            }
        });

        var dados = [];
        
        // Cabeçalho da Tabela
        var thElements = tabelaClone.querySelectorAll('thead tr:last-child th');
        if (thElements.length > 0) {
            var headerRow = [];
            thElements.forEach(function(th) {
                headerRow.push(th.textContent.trim());
            });
            dados.push(headerRow);
        }

        // Linhas da Tabela
        var rows = tabelaClone.querySelectorAll('tbody tr');
        rows.forEach(function(tr) {
            if (tr.id === 'tr-totais') return;
            
            var row = [];
            var tds = tr.querySelectorAll('td');
            
            tds.forEach(function(td) {
                var valor = td.textContent.trim();
                valor = formatarData(valor);
                row.push(valor);
            });
            
            if (row.length > 0) {
                dados.push(row);
            }
        });

        // Linha de Totais
        var trTotais = tabelaClone.querySelector('#tr-totais') || tabelaClone.querySelector('tfoot tr');
        if (trTotais) {
            var totalRow = [];
            var totalTds = trTotais.querySelectorAll('td');
            totalTds.forEach(function(td) {
                var valor = td.textContent.trim();
                valor = formatarData(valor);
                totalRow.push(valor);
            });
            if (totalRow.length > 0) {
                dados.push([]); 
                dados.push(totalRow);
            }
        }

        // Resumo de Saldos no fim da tabela
        var saldos = obterSaldos();
        dados.push([]);
        dados.push(['Resumo de saldos']);
        dados.push(['Saldo inicial da conta', saldos.inicial]);
        dados.push(['Saldo do periodo', saldos.filtro]);
        dados.push(['Saldo total', saldos.total]);

        /* 4. Combinar Cabeçalho/Filtros com Dados da Tabela */
        var aoaFinal = headerFiltros.concat(dados);

        // Criar Planilha
        var ws = XLSX.utils.aoa_to_sheet(aoaFinal);
        
        var colWidths = [];
        for (var i = 0; i < (dados[0] ? dados[0].length : 10); i++) {
            colWidths.push({ wch: 18 });
        }
        ws['!cols'] = colWidths;

        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Relatório de Movimentação");

        XLSX.writeFile(wb, "relatorio_movimentacao.xlsx");

    } catch (error) {
        console.error('Erro ao gerar Excel:', error);
        alert('Erro ao gerar arquivo Excel. Verifique o console para mais detalhes.');
    }
}