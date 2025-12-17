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
    });

    // Remove pseudo-elementos
    const estiloPseudo = document.createElement('style');
    estiloPseudo.innerHTML = `
        *::after, *::before {
            content: none !important;
            display: none !important;
        }
    `;
    tabelaClone.appendChild(estiloPseudo);
}

function getTabelaSemAcoes() {
    var tabelaOriginal = document.querySelector('#tabela-pdf');
    if (!tabelaOriginal) return null;

    var tabelaClone = tabelaOriginal.cloneNode(true);
    tabelaClone.classList.remove('table-striped');
    tabelaClone.classList.add('avoid-page-break')

    tabelaClone.style.borderCollapse = 'collapse';
    tabelaClone.style.borderSpacing = '0';
    tabelaClone.style.width = '100%';
    tabelaClone.style.tableLayout = 'fixed';
    tabelaClone.style.display = 'block';


    // remove classes e estilos herdados
    tabelaClone.querySelectorAll('*').forEach(function(el) {
        el.classList.remove(
            'parcela_cor_azul',
            'parcela_cor_amarela',
            'parcela_cor_vermelha',
            'parcela_cor_verde',
            'table-striped'
        );

        el.classList.add('avoid-page-break')
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
            th.style.fontSize = '80%'
            th.style.backgroundColor = '#cececeff'
        })
        for (let i = 0; i < 4; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }
    });

    // Remove as últimas 4 colunas de cada linha do corpo
    var trs = tabelaClone.querySelectorAll('tbody tr');

    trs.forEach(function(tr, index) {
            tr.classList.add('avoid-page-break')
        for (let i = 0; i < 4; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }
        const cor = (index % 2 === 0) ? '#ffffff' :  '#f2f2f2';
tr.querySelectorAll('.tr-clientes td').forEach(function(td) {
    td.style.whiteSpace = 'nowrap';
    td.style.overflow = 'hidden';
    td.style.textOverflow = 'ellipsis';

    td.style.fontSize = '95%';
    td.style.backgroundColor = cor;

    td.style.textAlign = 'center';
    td.style.alignItems = 'center';

    td.style.border = 'none';              // remove borda duplicada
    td.style.outline = '1px solid #ccc';   // borda segura no pdf

    td.style.padding = "20px 4px";
    td.style.lineHeight = "1.2";     // mantém texto estável

            // padding mínimo
});




    });

var trTotais = tabelaClone.querySelector('#tr-totais');
if (trTotais) {
    trTotais.classList.add('avoid-page-break')
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
    
    var h = document.createElement('h3');
    h.textContent = titleText;
    h.style.margin = '0 0 0 0';
    var n = document.createElement('h1')
    n.textContent = nomeEmpresa
    n.style.margin = '0 0 0 0'
    header.appendChild(n)
    header.appendChild(h);

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
        const match = data.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (match) {
            return `${match[3]}-${match[2]}-${match[1]}`;
        }
        return data;
    }

    if (di && di.value) filters.push(['<h3>Data Inicial', formatarDataBR(di.value), '</h3>']);
    if (df && df.value) filters.push(['<h3>Data Final', formatarDataBR(df.value), '</h3>']);
    if (doc && doc.value) filters.push(['<h3>Documento', doc.value, '</h3>']);
    if (pagamento) {
        var ptxt = getSelectText(pagamento);
        if (ptxt && ptxt !== 'Selecione') filters.push(['<h3>Pagamento', ptxt, '</h3>']);
    }
    if (cadastro) {
        var ctxt = getSelectText(cadastro);
        if (ctxt && ctxt !== 'Selecione') filters.push(['<h3>Cadastro', ctxt, '</h3>']);
    }
    if (titulo) {
        var ttxt = getSelectText(titulo);
        if (ttxt && ttxt !== 'Selecione') filters.push(['<h3>Titulo', ttxt, '</h3>']);
    }
    if (subtitulo) {
        var stxt = getSelectText(subtitulo);
        if (stxt && stxt !== 'Selecione') filters.push(['<h3>Subtitulo', stxt, '</h3>']);
    }
    if(custo) {
        var custotxt = getSelectText(subtitulo);
        if (custotxt && custotxt !== 'Selecione') filters.push(['<h3>Centro de Custos', custotxt, '</h3>']);
    }
    if (opcao) filters.push(['<h3>Opção', opcao, '</h3>']);
    if (por) filters.push(['<h3>Filtro por', por, '</h3>']);

    if (filters.length > 0) {
        var wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexWrap = 'wrap';
        wrap.style.gap = '8px 16px';
        wrap.style.width = '100%'
        wrap.style.alignItems = 'center'
        wrap.style.textAlign = 'center'

        filters.forEach(function(f) {
            var p = document.createElement('div');
            p.style.fontSize = '10px';
            p.innerHTML = '<strong>' + f[0] + ':</strong> ' + f[1] + f[2];
            wrap.appendChild(p);
        });

        header.appendChild(wrap);
        var headerGeral = document.createElement('div')
        header.style.marginBottom = '0';
        headerGeral.style.width = '100%'
        headerGeral.appendChild(header)
    } else {
        header.style.marginBottom = '0';
    }

    return headerGeral;
}

function dividirTabelaEmBlocos(tabela, linhasPorBloco = 22) {
    const tbody = tabela.querySelector('tbody');
    const cabecalho = tabela.querySelector('thead');
    if (!tbody) return [];

    const linhas = Array.from(tbody.querySelectorAll('tr'));
    const blocos = [];
    var primeira = true

    for (let i = 0; i < linhas.length; i += linhasPorBloco) {
        var linhasAntes = linhasPorBloco
        const bloco = document.createElement('div');
        bloco.classList.add('tabela-bloco', 'avoid-page-break');
        if(primeira) {
            linhasPorBloco = linhasPorBloco -2
        }
            

        // cria nova tabela para o bloco
        const novaTabela = tabela.cloneNode(false);
        novaTabela.style.width = '100%';
        novaTabela.style.tableLayout = 'fixed';


        novaTabela.classList.add('avoid-page-break');

        // adiciona o cabeçalho
        cabecalhoClone = cabecalho.cloneNode(true)
        novaTabela.appendChild(cabecalhoClone);

        // adiciona o corpo com subset de linhas
        const novoTbody = document.createElement('tbody');
        novoTbody.style.whiteSpace = 'nowrap'
        linhas.slice(i, i + linhasPorBloco).forEach(function(tr) {
            tr.classList.add('avoid-page-break')  
            novoTbody.appendChild(tr.cloneNode(true))
    });
        novaTabela.appendChild(novoTbody);

        bloco.appendChild(novaTabela);
        
        blocos.push(bloco);
        if(primeira) {
            linhasPorBloco = linhasAntes
        }
        primeira = false
    }

    return blocos;
}

function gerarpdf(nome, nomeEmpresa = '') {
    var tabela = getTabelaSemAcoes();
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }
    tabela.style.width = '297px';


    


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

    // Divide a tabela em blocos
    const blocos = dividirTabelaEmBlocos(tabela, 10);

    const container = document.createElement('div');
    container.style.width = '100%';
    container.style.maxWidth = 'none';
    container.style.display = 'block';
    container.style.margin = '0';

    tabela.style.width = '100%';
    tabela.style.maxWidth = 'none';
    tabela.style.margin = '0';
    tabela.style.fontSize = '10px';

    tabela.querySelectorAll('th, td').forEach(function(cell) {
        cell.style.fontSize = '10px';
        cell.style.padding = '2px 2px';
        cell.style.wordBreak = 'break-all';
        cell.style.whiteSpace = 'normal';
        cell.style.maxWidth = '120px';
    });

    const style = document.createElement('style');
    style.innerHTML = `
        #tabela-pdf {
        width:297px;
        }
        #tabela-pdf tbody tr, #tabela-pdf thead tr{
            width: 1060px;
            padding:0;
            height: 50px;
            }
        #tabela-pdf table, tr, td, th, thead, tbody, .avoid-page-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        #tabela-pdf thead tr th  {
            font-size: 80% !important;
            padding: 0;
            text-align:center;
            word-break: normal !important;
            width: calc(100% / 14) !important;
            height:20px;
        }
        #tabela-pdf tbody tr td {
            font-size: 70% !important;
            padding: 6px !important;
            word-break: break-all !important;
            white-space: wrap !important;
            width: calc(100% / 14) !important;
            height: 50px;
            }
        

    `;
    container.appendChild(style);


    const headerEl = buildExportHeader(nome, nomeEmpresa);
    container.appendChild(headerEl);

    blocos.forEach((b, idx) => {
        container.appendChild(b);
        if (idx < blocos.length - 1) {
            const pageBreak = document.createElement('div');
            pageBreak.classList.add('avoid-page-break')
            container.appendChild(pageBreak);
        }
    });


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