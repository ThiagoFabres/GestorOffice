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
            el.style.minHeight = '0';
            el.style.height = 'auto';
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
            min-height: 0 !important;
            height: auto !important;
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

            td.style.backgroundColor = cor;

            td.style.textAlign = 'center';
            td.style.alignItems = 'center';

            td.style.border = 'none';
            td.style.outline = '1px solid #ccc';

            td.style.padding = "6px 4px";
            // td.style.lineHeight = "1.2";
        });




    });

var trTotais = tabelaClone.querySelector('#tr-totais');
if (trTotais) {

    trTotais.style.backgroundColor = '#cececeff';
    trTotais.querySelectorAll('td').forEach(function(td) {
        td.style.fontWeight = 'bold';
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
    var titleText = titleEl ? titleEl.textContent.trim() : ('Contas a ' + nome);
    
    var h = document.createElement('p');
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

    // Função para formatar datas yyyy-mm-dd para dd-mm-yyyy
    function formatarDataBR(data) {
        if (!data) return '';
        console.log(data)
        const [ano, mes, dia] = data.split('-');
        const data_formatada = `${dia}/${mes}/${ano}`;
        return data_formatada;
    }

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

function dividirTabelaEmBlocos(tabela, tamanhoBloco = 12) {
    const thead = tabela.querySelector('thead')?.cloneNode(true);
    const tfoot = tabela.querySelector('tfoot')?.cloneNode(true);
    const rows = Array.from(tabela.querySelectorAll('tbody tr'));

    const blocos = [];

    for (let i = 0; i < rows.length; i += tamanhoBloco) {
        const novaTabela = document.createElement('table');
        novaTabela.className = tabela.className;
        novaTabela.style.width = '100%';
        novaTabela.style.tableLayout = 'fixed';
        novaTabela.style.borderCollapse = 'collapse';

        if (tabela.querySelector('colgroup')) {
            novaTabela.appendChild(tabela.querySelector('colgroup').cloneNode(true));
        }

        if (thead) novaTabela.appendChild(thead.cloneNode(true));

        const novoTbody = document.createElement('tbody');
        rows.slice(i, i + tamanhoBloco).forEach(tr => {
            novoTbody.appendChild(tr.cloneNode(true));
        });

        novaTabela.appendChild(novoTbody);

        if (tfoot) novaTabela.appendChild(tfoot.cloneNode(true));

        blocos.push(novaTabela);
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
        filename: 'contas_a_' + nome + '.pdf',
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
    console.log(tabelaParaExport.innerHTML)
    // aplica estilos que evitam quebra dentro das linhas/células


    const container = document.createElement('div');
    container.style.width = '100%';
    container.style.maxWidth = 'none';
    container.style.display = 'block';
    container.style.margin = '0';

    // aplicar ajustes de célula ao clone que vamos exportar
    tabelaParaExport.querySelectorAll('th, td').forEach(function(cell) {
        // cell.style.fontSize = '20px';
        cell.style.padding = '2px 2px';
        cell.style.wordBreak = 'break-all';
        cell.style.whiteSpace = 'normal';
        cell.style.maxWidth = '100%';
    });

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
            height: auto !important;
            min-height: 0 !important;
        }
        .export-table, .export-table tr, .export-table td, .export-table th, .export-table thead, .export-table tbody, .avoid-page-break {
            box-sizing: border-box !important;
            background-image: none !important;
            box-shadow: none !important;
        }
        .export-table thead tr th  {
            font-size: 75% !important;
            padding: 4px !important;
            text-align:center;
            word-break: normal !important;
            height: auto !important;;
            min-height: 0 !important;
        }
        .export-table tbody tr td {
            font-size: 75% !important;
            padding: 6px !important;
            white-space:nowrap !important;
            height: auto !important;
            font-weight: bold !important;
        }
            #td-descricao {
            text-align: start !important;
            }
        .export-table tbody #tr-totais td {
            background-color: #cececeff !important;
            font-weight: bold !important;
            overflow: visible !important;
            text-overflow: unset !important;
            white-space: nowrap !important;
            word-break: nowrap !important;
            font-size: 17px !important;
            width: auto !important;
            padding: 0.8em !important;

        }
        
    `;
    container.appendChild(style);


const tabelasPaginadas = dividirTabelaEmBlocos(tabelaParaExport, 20);
const ultimoIndex = tabelasPaginadas.length - 1;

tabelasPaginadas.forEach((tbl, index) => {

    const headerClone = buildExportHeader(nome, nomeEmpresa);


    const qtdLinhas = tbl.querySelectorAll('tbody tr').length;

    if (index === ultimoIndex && qtdLinhas === 1) {
        const thead = tbl.querySelector('thead');
        if (thead) thead.remove();
    }

    const bodytbl = document.createElement('div');
    bodytbl.appendChild(headerClone);
    bodytbl.appendChild(tbl);

    bodytbl.classList.add('avoid-page-break');

    container.appendChild(bodytbl);
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
                pageWidth - 10, // canto direito
                10,             // topo
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
        var titleText = titleEl ? titleEl.textContent.trim() : ('Contas a ' + nome);
        
        headerFiltros.push([nomeEmpresa + ' - ' + titleText]);
        headerFiltros.push([]); // linha vazia

        // Coleta os filtros aplicados
        var di = document.querySelector('#filtro_data_inicial');
        var df = document.querySelector('#filtro_data_final');
        var doc = document.querySelector('#filtro_nome');
        var pagamento = getSelectValue('select[name="forma_pagamento"]');
        var cadastro = getSelectValue('select[name="filtro_cadastro"]');
        var titulo = getSelectValue('select[name="filtro_titulo"]') || getSelectValue('#titulo-filtro');
        var subtitulo = getSelectValue('select[name="filtro_subtitulo"]') || getSelectValue('#subtitulo-filtro');
        var custo = getSelectValue('select[name="filtro_custo"]') || getSelectValue('#custo-filtro');
        var opcao = getRadioValue('opcao_filtro');
        var por = getRadioValue('filtro_por');

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
        
        if (doc && doc.value) headerFiltros.push(['Documento', doc.value]);
        if (pagamento && pagamento !== 'Selecione') headerFiltros.push(['Pagamento', pagamento]);
        if (cadastro && cadastro !== 'Selecione') headerFiltros.push(['Cadastro', cadastro]);
        if (titulo && titulo !== 'Selecione') headerFiltros.push(['Título', titulo]);
        if (subtitulo && subtitulo !== 'Selecione') headerFiltros.push(['Subtítulo', subtitulo]);
        if (custo && custo !== 'Selecione') headerFiltros.push(['Centro de Custos', custo]);
        if (opcao) headerFiltros.push(['Opção', opcao]);
        if (por) headerFiltros.push(['Filtro por', por]);

        headerFiltros.push([]); // linha vazia
        headerFiltros.push([]); // linha vazia

        // Combina header com dados
        var aoaFinal = headerFiltros.concat(dados);

        // Cria workbook
        var ws = XLSX.utils.aoa_to_sheet(aoaFinal);
        
        // Define largura das colunas
        var colWidths = [];
        for (var i = 0; i < (dados[0] ? dados[0].length : 13); i++) {
            colWidths.push({ wch: 18 });
        }
        ws['!cols'] = colWidths;

        // Cria workbook e adiciona worksheet
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Contas a " + nome);

        // Gera arquivo
        var filename = "contas_a_" + nome + ".xlsx";
        XLSX.writeFile(wb, filename);

    } catch (error) {
        console.error('Erro ao gerar Excel:', error);
        alert('Erro ao gerar arquivo Excel. Verifique o console para mais detalhes.');
    }
}