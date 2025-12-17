function limparElementosInvisiveis(tabelaClone) {

    tabelaClone.querySelectorAll('a').forEach(a => {
        let span = document.createElement('span');
        span.textContent = a.textContent;
        a.replaceWith(span);
    });

    tabelaClone.querySelectorAll('.tooltip, .popover, .dropdown-menu, .fade').forEach(el => el.remove());

    tabelaClone.querySelectorAll('*').forEach(el => {
        el.removeAttribute('href');
        el.style.textDecoration = 'none';
        el.style.border = 'none';
        el.style.outline = 'none';
    });

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
    var tabelaOriginal = document.querySelector('#conteudo-pdf');
    if (!tabelaOriginal) return null;

    var tabelaClone = tabelaOriginal.cloneNode(true);
    tabelaClone.classList.add('avoid-page-break');

    tabelaClone.style.borderCollapse = 'collapse';
    tabelaClone.style.borderSpacing = '0';
    tabelaClone.style.width = '100%';
    tabelaClone.style.tableLayout = 'fixed';

    tabelaClone.querySelectorAll('*').forEach(function(el) {
        el.classList.remove(
            'parcela_cor_azul',
            'parcela_cor_amarela',
            'parcela_cor_vermelha',
            'parcela_cor_verde',
            'table-striped'
        );

        el.classList.add('avoid-page-break');
        el.style.removeProperty('background-color');
        el.style.boxShadow = 'none';
        el.style.filter = 'none';
        el.style.textDecoration = 'none';
        el.style.whiteSpace = 'normal';
        el.style.wordBreak = 'break-word';
    });

    limparElementosInvisiveis(tabelaClone);

    tabelaClone.querySelectorAll('td, th').forEach(function(el) {
        if (el.textContent.includes('R$')) {
            el.textContent = el.textContent.replace(/R\$\s?/g, '').trim();
        }
    });

    const ths = tabelaClone.querySelectorAll('table thead tr');
    ths.forEach(function(tr) {
        tr.querySelectorAll('th').forEach(function(th) {
            th.style.fontSize = '80%';
            th.style.backgroundColor = '#cececeff';
            th.style.whiteSpace = 'normal';
        });
        // remove as últimas 3 colunas
        for (let i = 0; i < 3; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }
    });

    const trs = tabelaClone.querySelectorAll('tbody tr');

    trs.forEach(function(tr, index) {
        tr.classList.add('avoid-page-break');

        for (let i = 0; i < 3; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }

        const cor = index % 2 === 0 ? '#ffffff' : '#f2f2f2';
        tr.querySelectorAll('td').forEach(function(td) {
            td.style.fontSize = '95%';
            td.style.backgroundColor = cor;
            td.style.textAlign = 'center';
            td.style.padding = '8px 4px';
            td.style.lineHeight = '1.2';
            td.style.border = 'none';
            td.style.outline = '1px solid #ccc';
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



// =====================================================
//  GERAR PDF — VERSÃO 100% EM TABELA ÚNICA
// =====================================================

function gerarpdf(nome, nomeEmpresa = '') {
    var tabela = getTabelaSemAcoes();
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    // Ajuste de margens e largura
    var marginMm = 8;
    var pxPerMm = 96 / 25.4;
    var usableWidthPx = Math.floor((210 - marginMm * 2) * pxPerMm);

    var opt = {
        margin: [marginMm, marginMm, marginMm, marginMm],
        filename: 'relatorio-bancario.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scrollY: 0,
            useCORS: true,
            scale: 1.2,
            width: usableWidthPx
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'l' },
        pagebreak: { mode: ['css', 'avoid-all'] }
    };

    const container = document.createElement('div');
    container.style.width = '100%';
    container.style.display = 'block';
    container.appendChild(tabela);

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