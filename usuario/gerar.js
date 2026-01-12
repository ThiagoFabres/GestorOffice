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
            td.style.lineHeight = "1.2";
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
        const data_formatada = new Date(Date(data))
        console.log(data_formatada)
        return data_formatada.toLocaleDateString('pt-BR');
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

// bloco logic removed — we will export the whole table and use CSS to avoid page breaks inside rows

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
            height: auto !important;;
            min-height: 0 !important;
        }
        .export-table tbody tr td {
            font-size: 75% !important;
            padding: 6px !important;

            height: auto !important;
            font-weight: bold !important;
        }
            #td-descricao {
            text-align: start !important;
            }
    `;
    container.appendChild(style);


    const headerEl = buildExportHeader(nome, nomeEmpresa);
    container.appendChild(headerEl);
    container.appendChild(tabelaParaExport);


    html2pdf().set(opt).from(container).save();
}


















function gerarexcel(nome) {
    var tabela = getTabelaSemAcoes();
    if (!tabela) return;



    // Remove 'R$' de todos os elementos da tabela
    tabela.querySelectorAll('td, th').forEach(function(el) {
        if (el.textContent.includes('R$')) {
            el.textContent = el.textContent.replace(/R\$\s?/g, '').trim();
        }
    });

    // Formata datas para dd-mm-YYYY em todos os elementos
    const dataRegex = /(\d{4})-(\d{2})-(\d{2})/g;
    tabela.querySelectorAll('td, th, div, span, h3, h4, h5, h6, h1, h2').forEach(function(el) {
        el.childNodes.forEach(function(node) {
            if (node.nodeType === 3) { // text node
                node.nodeValue = node.nodeValue.replace(dataRegex, function(_, y, m, d) {
                    return d + '-' + m + '-' + y;
                });
            }
        });
    });

    // Converte datas para texto antes de exportar
    var trs = tabela.querySelectorAll('tbody tr');
    trs.forEach(function(tr) {
        [1, 8, 9].forEach(function(idx) {
            var td = tr.children[idx];
            if (td) {
                td.textContent = "'" + td.textContent + "'";
            }
        });
    });

    // Build header AOA (array of arrays) and prepend to sheet data
    function buildHeaderAoA() {
        var aoa = [];
        var titleEl = document.querySelector('.card .card-header h3') || document.querySelector('h3');
        var titleText = titleEl ? titleEl.textContent.trim() : ('Contas a ' + nome);
        aoa.push([titleText]);
        aoa.push([]);

        var di = document.querySelector('#filtro_data_inicial');
        var df = document.querySelector('#filtro_data_final');
        var doc = document.querySelector('#filtro_nome');
        var pagamento = document.querySelector('select[name="forma_pagamento"]');
        var cadastro = document.querySelector('select[name="filtro_cadastro"]');
        var titulo = document.querySelector('select[name="filtro_titulo"]') || document.querySelector('#titulo-filtro');
        var subtitulo = document.querySelector('select[name="filtro_subtitulo"]') || document.querySelector('#subtitulo-filtro');

        function addIf(label, value) { if (value && value !== '' && value !== 'Selecione') aoa.push([label, value]); }

        addIf('Data Inicial', di && di.value);
        addIf('Data Final', df && df.value);
        addIf('Documento', doc && doc.value);
        addIf('Pagamento', pagamento && (pagamento.options[pagamento.selectedIndex] && pagamento.options[pagamento.selectedIndex].text));
        addIf('Cadastro', cadastro && (cadastro.options[cadastro.selectedIndex] && cadastro.options[cadastro.selectedIndex].text));
        addIf('Titulo', titulo && (titulo.options[titulo.selectedIndex] && titulo.options[titulo.selectedIndex].text));
        addIf('Subtitulo', subtitulo && (subtitulo.options[subtitulo.selectedIndex] && subtitulo.options[subtitulo.selectedIndex].text));

        // radios
        var opc = document.querySelector('input[name="opcao_filtro"]:checked');
        if (opc) {
            var p = opc.parentElement ? opc.parentElement.querySelector('label') : null;
            addIf('Opção', p ? p.textContent.trim() : opc.value);
        }
        var por = document.querySelector('input[name="filtro_por"]:checked');
        if (por) {
            var p2 = por.parentElement ? por.parentElement.querySelector('label') : null;
            addIf('Filtro por', p2 ? p2.textContent.trim() : por.value);
        }

        aoa.push([]);
        return aoa;
    }

    var wb = XLSX.utils.table_to_book(tabela, {sheet: "Contas a"+nome});
    var sheetName = wb.SheetNames[0];
    var ws = wb.Sheets[sheetName];
    var dataAoA = XLSX.utils.sheet_to_json(ws, {header:1});
    var headerAoA = buildHeaderAoA();
    var newAoA = headerAoA.concat(dataAoA);
    var newWs = XLSX.utils.aoa_to_sheet(newAoA);
    wb.Sheets[sheetName] = newWs;
    XLSX.writeFile(wb, "contas_a_"+nome+".xlsx");
}