function getTabelaSemAcoes() {
    var tabelaOriginal = document.querySelector('.table.table-striped');
    if (!tabelaOriginal) return null;

    var tabelaClone = tabelaOriginal.cloneNode(true);

    // Remove as últimas 3 colunas do cabeçalho
    var ths = tabelaClone.querySelectorAll('thead tr');
    ths.forEach(function(tr) {
        for (let i = 0; i < 3; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }
    });

    // Remove as últimas 3 colunas de cada linha do corpo
    var trs = tabelaClone.querySelectorAll('tbody tr');
    trs.forEach(function(tr) {
        for (let i = 0; i < 3; i++) {
            if (tr.children.length > 0) tr.children[tr.children.length - 1].remove();
        }
    });

    return tabelaClone;
}

// Build a simple header (title + visible filters) for export
function buildExportHeader(nome) {
    var header = document.createElement('div');
    header.style.marginBottom = '10px';
    header.style.fontFamily = 'Arial, Helvetica, sans-serif';

    // Title: try to find page card title, fallback to provided nome
    var titleEl = document.querySelector('.card .card-header h3') || document.querySelector('h3') || null;
    var titleText = titleEl ? titleEl.textContent.trim() : ('Contas a ' + nome);

    var h = document.createElement('h3');
    h.textContent = titleText;
    h.style.margin = '0 0 0 0';
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
    var opcao = getRadioLabel('opcao_filtro');
    var por = getRadioLabel('filtro_por');

    if (di && di.value) filters.push(['Data Inicial', di.value]);
    if (df && df.value) filters.push(['Data Final', df.value]);
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
    if (opcao) filters.push(['Opção', opcao]);
    if (por) filters.push(['Filtro por', por]);

    if (filters.length > 0) {
        var wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexWrap = 'wrap';
        wrap.style.gap = '8px 16px';

        filters.forEach(function(f) {
            var p = document.createElement('div');
            p.style.fontSize = '12px';
            p.innerHTML = '<strong>' + f[0] + ':</strong> ' + f[1];
            wrap.appendChild(p);
        });

        header.appendChild(wrap);
    }

    return header;
}

function gerarpdf(nome) {
    var tabela = getTabelaSemAcoes();
    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    // Criar container temporário para evitar corte
    var container = document.createElement('div');
    container.style.width = "100%";
    container.style.overflow = "visible";
    container.style.boxSizing = 'border-box';
    // prepend header with filters
    var headerEl = buildExportHeader(nome);
    container.appendChild(headerEl);
    container.appendChild(tabela);

    // Ajustar largura do container para corresponder à largura útil de A4 landscape
    // Conversão aproximada: 1mm = 96/25.4 px (para tela 96dpi)
    var pxPerMm = 96 / 40.4;
    var a4WidthMm = 297; // A4 landscape width in mm
    var marginMm = 8; // margem em mm (ajustável)
    var usableWidthMm = a4WidthMm - (marginMm * 2);
    var usableWidthPx = Math.floor(usableWidthMm * pxPerMm);

    // Aplicar largura calculada ao container (CSS pixels)
    container.style.width = usableWidthPx + 'px';
    // opcional: adicionar padding correspondente à margem para manter espaçamento
    container.style.padding = marginMm + 'mm';

    var scale = 2; // tela em alta resolução

    var opt = {
        margin: [marginMm, marginMm, marginMm, marginMm],
        filename: 'contas_a_'+nome+'.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scale: scale,
            scrollY: 0,
            useCORS: true,
            // define a largura de renderização para html2canvas (em CSS pixels * scale)
            width: usableWidthPx * scale
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'landscape'
        },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    // Gera e salva o PDF
    html2pdf().set(opt).from(container).save();
}





function gerarexcel(nome) {
    var tabela = getTabelaSemAcoes();
    if (!tabela) return;

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