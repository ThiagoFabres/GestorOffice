function limparElementosInvisiveis(tabelaClone) {

    // Remove todos os link <a>
    tabelaClone.querySelectorAll('a').forEach(a => {
        let span = document.createElement('span');
        span.textContent = a.textContent;
        a.replaceWith(span);
    });

    // Remove tooltip, popover, dropdown invisíveis
    tabelaClone.querySelectorAll('.tooltip, .popover, .dropdown-menu, .fade').forEach(el => el.remove());

    // Remove atributos que podem gerar sublinhado invisível
    tabelaClone.querySelectorAll('*').forEach(el => {
        el.removeAttribute('href');
        el.style.textDecoration = 'none';
        el.style.border = 'none';
        el.style.outline = 'none';
        // remover possíveis imagens/fundos e alturas que geram caixas visuais grandes
        try {
            el.style.backgroundImage = 'none';
            el.style.background = 'none';
            el.style.backgroundColor = 'transparent';
            el.style.boxShadow = 'none';
            el.style.backgroundRepeat = 'no-repeat';
            el.style.backgroundSize = 'auto';
        } catch (e) {}
    });

    // Remove pseudo-elementos
    const estiloPseudo = document.createElement('style');
    estiloPseudo.innerHTML = `
        *::after, *::before {
            content: none !important;
            display: none !important;
            background-image: none !important;
            background: none !important;
            box-shadow: none !important;
        }
    `;
    tabelaClone.appendChild(estiloPseudo);
}

function getTabelaSemAcoes() {
    var tabelaOriginal = document.querySelector('#tabela-pdf');
    if (!tabelaOriginal) return null;

    var tabelaClone = tabelaOriginal.cloneNode(true);
    tabelaClone.classList.remove('table-striped');




    // remove classes e estilos herdados
    tabelaClone.querySelectorAll('*').forEach(function(el) {
        el.classList.remove(
            'parcela_cor_azul',
            'parcela_cor_amarela',
            'parcela_cor_vermelha',
            'parcela_cor_verde',
            'table-striped'
        );

        el.style.removeProperty('background-color');
        el.style.boxShadow = 'none';
        el.style.filter = 'none';
        el.style.textDecoration = 'none';
    });


    limparElementosInvisiveis(tabelaClone);


    // Remove 'R$' de todos os elementos da tabela
    tabelaClone.querySelectorAll('td, th').forEach(function(el) {
        if (el.textContent.includes('R$')) {
            el.textContent = el.textContent.replace(/R\$\s?/g, '').trim();
        }
    });


    // Remove as últimas 4 colunas do cabeçalho
    var ths = tabelaClone.querySelectorAll('thead tr');
    ths.forEach(function(tr) {
        tr.querySelectorAll('th').forEach(function(th) {
            th.style.backgroundColor = '#cececeff'
        })
    });

    // ensure colgroup + fixed layout so th/td widths stay aligned
    var firstHeaderRow = tabelaClone.querySelector('thead tr');
    var finalColCount = firstHeaderRow ? firstHeaderRow.children.length : 0;
    if (finalColCount > 0) {
        // remove any existing colgroup to avoid duplicates
        var existingColgroup = tabelaClone.querySelector('colgroup');
        if (existingColgroup) existingColgroup.remove();

        var colgroup = document.createElement('colgroup');
        for (let i = 0; i < finalColCount; i++) {
            var col = document.createElement('col');
            col.style.width = (100 / finalColCount) + '%';
            colgroup.appendChild(col);
        }
        tabelaClone.insertBefore(colgroup, tabelaClone.firstChild);
        tabelaClone.style.tableLayout = 'fixed';
    }

    // Remove as últimas 4 colunas de cada linha do corpo
    var trs = tabelaClone.querySelectorAll('tbody tr');

    trs.forEach(function(tr, index) {
        // Aplicar mesma cor a 2 linhas seguidas, depois alternar para as próximas 2
        var groupIndex = Math.floor(index / 2); // 0,0,1,1,2,2...
        var cor = (groupIndex % 2 === 0) ? '#ffffff' : '#f2f2f2';
            tr.style.backgroundColor = cor;
        // evita quebra de página dentro da linha
        try { tr.style.pageBreakInside = 'avoid'; tr.style.breakInside = 'avoid'; } catch(e){}
        // remove as mesmas 4 colunas pelo índice final, garantindo consistência com o thead
        for (let i = 0; i < 4; i++) {
            // remove a última célula apenas se o número de células for maior que finalColCount
            if (tr.children.length > finalColCount) {
                tr.removeChild(tr.children[tr.children.length - 1]);
            } else if (tr.children.length > 0 && i === 0 && finalColCount === 0) {
                // fallback - evita deixar linhas com número variável de colunas
                tr.removeChild(tr.children[tr.children.length - 1]);
            }
        }

        
        // aplicar estilo a todas as td (não somente '.tr-clientes td')
        tr.querySelectorAll('td').forEach(function(td) {
            
            // permitir quebra interna para não forçar overflow que desalinha colunas
            td.style.whiteSpace = 'normal';
            
            td.style.overflow = 'hidden';
            td.style.textOverflow = 'ellipsis';

            td.style.fontSize = '95%';
            td.style.backgroundColor = cor;

            td.style.textAlign = 'center';
            td.style.alignItems = 'center';

            td.style.border = 'none';
            td.style.outline = '1px solid #ccc';

            td.style.padding = "6px 4px";
            try { td.style.pageBreakInside = 'avoid'; td.style.breakInside = 'avoid'; } catch(e){}
        });




    });

var trTotais = tabelaClone.querySelector('#tr-totais');
if (trTotais) {

    trTotais.style.backgroundColor = '#cececeff';
    trTotais.querySelectorAll('td').forEach(function(td) {
        td.style.backgroundColor = '#cececeff';
    });        
}
    return tabelaClone;
}

// Build a simple header (title + visible filters) for export
function buildExportHeader(nome, nomeEmpresa) {
    var header = document.createElement('div');
    header.style.fontFamily = 'Arial, Helvetica, sans-serif';

    // Title: try to find page card title, fallback to provided nome
    var titleEl = document.querySelector('.card .card-header h3') || document.querySelector('h3') || null;
    var titleText = titleEl ? titleEl.textContent.trim() : ("Relatorio de Movimentação Bancária");
    
    var h = document.createElement('p');
    const s = document.createElement('div')
    h.textContent = nomeEmpresa + ' - ' + titleText;
    h.style.margin = '0 0 0 0';
    h.style.fontSize = '24px';
    cabecalho = document.createElement('div')
    cabecalho.style.display = 'flex'
    cabecalho.style.justifyContent = 'space-between'
    cabecalho.appendChild(h)
    header.appendChild(cabecalho);

    // Collect filter values from known inputs/selects
    function getSelectText(sel) {
        if (!sel) return '';
        try {
            return (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].text) ? sel.options[sel.selectedIndex].text.trim() : '';
        } catch (e) { return ''; }
    }

    function getRadioLabel(name) {
        var checked = document.querySelector('input[name="' + name + '"]:checked');
        if (!checked) return '';
        var parent = checked.parentElement;
        var lbl = parent ? parent.querySelector('label') : null;
        if (lbl && lbl.textContent.trim() !== '') return lbl.textContent.trim();
        // fallback to value
        return checked.value || '';
    }

    var filters = [];
    var di = document.querySelector('#filtro_data_inicial');
    var df = document.querySelector('#filtro_data_final');
    var doc = document.querySelector('#filtro_nome');
    var pagamento = document.querySelector('select[name="forma_pagamento"]') || document.querySelector('select[name="forma_pagamento"]');
    var cadastro = document.querySelector('select[name="filtro_cadastro"]');
    var titulo = document.querySelector('select[name="filtro_titulo"]') || document.querySelector('#titulo-filtro');
    var subtitulo = document.querySelector('select[name="filtro_subtitulo"]') || document.querySelector('#subtitulo-filtro');
    var custo = document.querySelector('select[name="filtro_custo"]') || document.querySelector('#custo-filtro');
    var opcao = getRadioLabel('opcao_filtro');
    var por = getRadioLabel('filtro_por');
    var valor_i = document.getElementById('saldo-inicial-pdf').innerHTML
    


    // Função para formatar datas yyyy-mm-dd para dd-mm-yyyy
    function formatarDataBR(data) {
    if (!data) return '';

    const [year, month, day] = data.split('-');

    return `${day}/${month}/${year}`;
}

    filters.push(['Saldo Inicial', 'R$ ' + valor_i])
    if((di && di.value) && (df && df.value)) {
        filters.push(['Período', formatarDataBR(di.value) + ' até ' + formatarDataBR(df.value)]);
    } else {
        if (di && di.value) filters.push(['Data Inicial', formatarDataBR(di.value)]);
    if (df && df.value) filters.push(['Data Final', formatarDataBR(df.value)]);
    }

    
    if (doc && doc.value) filters.push(['Documento', doc.value]);
    if (pagamento) {
        var ptxt = getSelectText(pagamento);
        if (ptxt && ptxt !== 'Selecione') filters.push(['Pagamento', ptxt]);
    }
    if (cadastro) {
        var ctxt = getSelectText(cadastro);
        if (ctxt && ctxt !== 'Selecione') filters.push(['Cadastro', ctxt]);
    }
    if (titulo) {
        var ttxt = getSelectText(titulo);
        if (ttxt && ttxt !== 'Selecione') filters.push(['Titulo', ttxt]);
    }
    if (subtitulo) {
        var stxt = getSelectText(subtitulo);
        if (stxt && stxt !== 'Selecione') filters.push(['Subtitulo', stxt]);
    }
    if(custo) {
        var custotxt = getSelectText(custo);
        if (custotxt && custotxt !== 'Selecione') filters.push(['Centro de Custos', custotxt]);
    }
    if (opcao) filters.push(['Opção', opcao, '']);
    if (por) filters.push(['Filtro por', por, '']);

    // container que sempre será retornado (evita retornar undefined)
    var headerGeral = document.createElement('div');
    headerGeral.style.width = '100%';
    headerGeral.style.marginBottom = '0';

    if (filters.length > 0) {
        var wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexWrap = 'wrap';
        wrap.style.gap = '8px 16px';
        wrap.style.width = '100%'
        wrap.style.alignItems = 'center'
        wrap.style.textAlign = 'center'

        filters.forEach(function(f) {
            var p = document.createElement('p');
            p.style.fontSize = '16px';
            p.innerHTML = '<strong>' + f[0] + ':</strong> ' + f[1];
            wrap.appendChild(p);
        });

        header.appendChild(wrap);
    }

    headerGeral.appendChild(header);
    return headerGeral;
}

function ajustarUltimoBloco(blocos) {
    if (blocos.length === 0) return;

    const ultimo = blocos[blocos.length - 1];

    if (ultimo.qtdLinhas === 1) {
        const thead = ultimo.tabela.querySelector('thead');
        if (thead) thead.remove();
    }
}


function dividirTabelaEmBlocos(tabela, linhasPorBloco = 18) {
    const blocos = [];

    const thead = tabela.querySelector('thead');
    const linhas = Array.from(tabela.querySelectorAll('tbody tr'));

    for (let i = 0; i < linhas.length; i += linhasPorBloco) {
        const blocoTabela = tabela.cloneNode(false);

        // copia colgroup se existir
        const colgroup = tabela.querySelector('colgroup');
        if (colgroup) blocoTabela.appendChild(colgroup.cloneNode(true));

        // copia thead
        if (thead) blocoTabela.appendChild(thead.cloneNode(true));

        const tbody = document.createElement('tbody');
        linhas.slice(i, i + linhasPorBloco).forEach(tr => {
            tbody.appendChild(tr.cloneNode(true));
        });

        blocoTabela.appendChild(tbody);
        blocoTabela.classList.add('export-table');

        blocos.push({
            tabela: blocoTabela,
            qtdLinhas: tbody.children.length
        });
    }

    return blocos;
}


function gerarpdf(nome, nomeEmpresa = '') {
    var tabela = getTabelaSemAcoes();
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }
    tabela.style.width = '100%';


    


    var marginMm = 8;
    var pxPerMm = 96 / 25.4;
    var a4WidthMm = 297; // paisagem
    var usableWidthMm = a4WidthMm - (marginMm * 2);
    var usableWidthPx = Math.floor(usableWidthMm * pxPerMm);

    var opt = {
        margin: [marginMm, marginMm, marginMm, marginMm],
        filename: "relatorio_" + nome + '.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scrollY: 0,
            useCORS: true,
            scale: 1.1,
            width: usableWidthPx
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak: { mode: ['css', 'avoid-all'] }
    };

    // Não dividimos em blocos — exportamos a tabela inteira e evitamos quebras dentro das linhas
    const tabelaParaExport = tabela.cloneNode(true);

    // marca a tabela exportada com uma classe para aplicar CSS local sem depender de ID
    tabelaParaExport.classList.add('export-table');
    // aplica estilos que evitam quebra dentro das linhas/células
    tabelaParaExport.querySelectorAll('tbody tr').forEach(function(r) {
        try { r.style.pageBreakInside = 'avoid'; r.style.breakInside = 'avoid'; } catch(e){}
    });
    tabelaParaExport.querySelectorAll('td, th').forEach(function(c) {
        try { c.style.pageBreakInside = 'avoid'; c.style.breakInside = 'avoid'; } catch(e){}
    });

    const container = document.createElement('div');
    container.style.width = '100%';
    container.style.maxWidth = 'none';
    container.style.display = 'block';
    container.style.margin = '0';

    // aplicar ajustes de célula ao clone que vamos exportar
    tabelaParaExport.querySelectorAll('th, td').forEach(function(cell) {
        cell.style.fontSize = '10px';
        cell.style.padding = '2px 2px';
        cell.style.wordBreak = 'break-all';
        cell.style.whiteSpace = 'normal';
        cell.style.maxWidth = '100%';
    });

    const saldo_f = document.querySelector('#saldo-final-pdf strong').cloneNode(true);
    saldo_f.style.textAlign = 'center'
    const footer = document.createElement('div')
    footer.style.width = '100%';
    footer.style.display = 'flex';
    footer.style.justifyContent = 'center'
    footer.style.alignItems = 'center'
    footer.style.paddingTop = '20px'
    footer.style.borderTop = '1px solid #ccc'
    footer.appendChild(saldo_f)

    const style = document.createElement('style');
    style.innerHTML = `
        .export-table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }
        /* remover alturas fixas para evitar cálculos diferentes entre head/body */
        .export-table tbody tr, .export-table thead tr{
            padding:0;

        }
        .export-table, .export-table tr, .export-table td, .export-table th, .export-table thead, .export-table tbody, .avoid-page-break {
            page-break-inside: avoid !important;
            box-sizing: border-box !important;
            background-image: none !important;
            box-shadow: none !important;
        }
        .export-table thead tr th  {
            font-size: 75% !important;
            padding: 4px !important;
            text-align:center;
            word-break: normal !important;
            

        }
        .export-table tbody tr td {
            font-size: 75% !important;
            padding: 6px !important;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:hidden;

            font-weight: bold !important;
        }
            #td-descricao {
            text-align: start !important;
            }
    `;
    container.appendChild(style);
    const blocos = dividirTabelaEmBlocos(tabelaParaExport, 18);
ajustarUltimoBloco(blocos);

blocos.forEach((bloco, index) => {

    const wrapper = document.createElement('div');
    wrapper.classList.add('avoid-page-break');
    wrapper.style.marginBottom = '16px';

    // header apenas no início de cada página/bloco
    const headerClone = buildExportHeader(nome, nomeEmpresa);
    wrapper.appendChild(headerClone);

    wrapper.appendChild(bloco.tabela);

    // footer só no último bloco
    if (index === blocos.length - 1) {
        wrapper.appendChild(footer);
    }

    container.appendChild(wrapper);
});



    html2pdf()
    .set(opt)
    .from(container)
    .toPdf()
    .get('pdf')
    .then(function (pdf) {

        const totalPages = pdf.internal.getNumberOfPages();
        const pageWidth = pdf.internal.pageSize.getWidth();

        pdf.setFontSize(9);

        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.text(
                `Página ${i} de ${totalPages}`,
                pageWidth - 10,
                10,
                { align: 'right' }
            );
        }
    })
    .save();

}


















function gerarexcel(nome, nomeEmpresa = '') {
    try {
        var tabela = getTabelaSemAcoes();
        if (!tabela) {
            alert("Tabela não encontrada!");
            return;
        }

        // Função auxiliar para formatar datas de yyyy-mm-dd para dd/mm/yyyy
        function formatarData(dataStr) {
            if (!dataStr || dataStr.trim() === '') return '';
            
            const regex = /(\d{4})-(\d{2})-(\d{2})/;
            const match = dataStr.match(regex);
            
            if (match) {
                return match[3] + '/' + match[2] + '/' + match[1];
            }
            return dataStr;
        }

        // Função para extrair valor do select
        function getSelectValue(selector) {
            var el = document.querySelector(selector);
            if (!el) return '';
            return (el.options && el.options[el.selectedIndex]) ? 
                   el.options[el.selectedIndex].text.trim() : '';
        }

        // Função para extrair valor do radio button
        function getRadioValue(name) {
            var checked = document.querySelector('input[name="' + name + '"]:checked');
            if (!checked) return '';
            var parent = checked.parentElement;
            var lbl = parent ? parent.querySelector('label') : null;
            return lbl ? lbl.textContent.trim() : checked.value;
        }

        // Clona a tabela para não alterar o DOM original
        var tabelaClone = tabela.cloneNode(true);

        // Remove símbolos de moeda
        tabelaClone.querySelectorAll('td, th').forEach(function(el) {
            if (el.textContent.includes('R$')) {
                el.textContent = el.textContent.replace(/R\$\s?/g, '').trim();
            }
        });

        // Converte tabela HTML para array de arrays
        var dados = [];
        
        // Adiciona cabeçalho
        var thElements = tabelaClone.querySelectorAll('thead tr:last-child th');
        if (thElements.length > 0) {
            var headerRow = [];
            thElements.forEach(function(th) {
                headerRow.push(th.textContent.trim());
            });
            dados.push(headerRow);
        }

        // Adiciona linhas do corpo
        var rows = tabelaClone.querySelectorAll('tbody tr');
        rows.forEach(function(tr) {
            // Ignora a linha de totais
            if (tr.id === 'tr-totais') return;
            
            var row = [];
            var tds = tr.querySelectorAll('td');
            
            tds.forEach(function(td, idx) {
                var valor = td.textContent.trim();
                
                // Formata datas
                valor = formatarData(valor);
                
                row.push(valor);
            });
            
            if (row.length > 0) {
                dados.push(row);
            }
        });

        // Adiciona linha de totais se existir
        var trTotais = tabelaClone.querySelector('#tr-totais');
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

        // Constrói o header com filtros
        var headerFiltros = [];
        
        // Título
        var titleEl = document.querySelector('.card .card-header h3') || document.querySelector('h3');
        var titleText = titleEl ? titleEl.textContent.trim() : ('Relatório de Movimentação Bancária');
        
        headerFiltros.push([nomeEmpresa + ' - ' + titleText]);
        headerFiltros.push([]); // linha vazia

        // Coleta os filtros aplicados
        var di = document.querySelector('#filtro_data_inicial');
        var df = document.querySelector('#filtro_data_final');
        var titulo = getSelectValue('select[name="filtro_titulo"]') || getSelectValue('#titulo-filtro');
        var subtitulo = getSelectValue('select[name="filtro_subtitulo"]') || getSelectValue('#subtitulo-filtro');
        var tipo = getSelectValue('select[name="filtro_tipo"]');
        var conta = getSelectValue('select[name="filtro_conta"]');
        var conciliado = document.querySelector('input[name="filtro_conciliado"]');
        var opcao = getRadioValue('opcao_filtro');
        var por = getRadioValue('filtro_por');

        // Adiciona saldo inicial se disponível
        var saldoInicialEl = document.querySelector('#saldo-inicial-pdf');
        if (saldoInicialEl) {
            var saldoInicial = saldoInicialEl.textContent.replace(/Saldo Inicial: R\$\s?/g, '').trim();
            if (saldoInicial) {
                headerFiltros.push(['Saldo Inicial', saldoInicial]);
            }
        }

        // Adiciona filtros não vazios
        if ((di && di.value) || (df && df.value)) {
            var dataInicialFmt = di && di.value ? formatarData(di.value) : '';
            var dataFinalFmt = df && df.value ? formatarData(df.value) : '';
            
            if (dataInicialFmt && dataFinalFmt) {
                headerFiltros.push(['Período', dataInicialFmt + ' até ' + dataFinalFmt]);
            } else if (dataInicialFmt) {
                headerFiltros.push(['Data Inicial', dataInicialFmt]);
            } else if (dataFinalFmt) {
                headerFiltros.push(['Data Final', dataFinalFmt]);
            }
        }
        
        if (tipo && tipo !== 'Selecione') headerFiltros.push(['Tipo', tipo]);
        if (conta && conta !== 'Selecione') headerFiltros.push(['Conta', conta]);
        if (titulo && titulo !== 'Selecione') headerFiltros.push(['Título', titulo]);
        if (subtitulo && subtitulo !== 'Selecione') headerFiltros.push(['Subtítulo', subtitulo]);
        if (conciliado && conciliado.checked) headerFiltros.push(['Conciliado', 'Sim']);
        if (opcao) headerFiltros.push(['Opção', opcao]);
        if (por) headerFiltros.push(['Filtro por', por]);

        // Adiciona saldo final se disponível
        var saldoFinalEl = document.querySelector('#saldo-final-pdf strong');
        if (saldoFinalEl) {
            headerFiltros.push([]);
            headerFiltros.push([saldoFinalEl.textContent.trim()]);
        }

        headerFiltros.push([]); // linha vazia
        headerFiltros.push([]); // linha vazia

        // Combina header com dados
        var aoaFinal = headerFiltros.concat(dados);

        // Cria workbook
        var ws = XLSX.utils.aoa_to_sheet(aoaFinal);
        
        // Define largura das colunas
        var colWidths = [];
        for (var i = 0; i < (dados[0] ? dados[0].length : 10); i++) {
            colWidths.push({ wch: 18 });
        }
        ws['!cols'] = colWidths;

        // Cria workbook e adiciona worksheet
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Relatório de " + nome);

        // Gera arquivo
        var filename = "relatorio_" + nome + ".xlsx";
        XLSX.writeFile(wb, filename);

    } catch (error) {
        console.error('Erro ao gerar Excel:', error);
        alert('Erro ao gerar arquivo Excel. Verifique o console para mais detalhes.');
    }
}