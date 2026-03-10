
function _formatDateToDDMMYYYY(input) {
    if (!input && input !== 0) return '';
    // Date object
    if (input instanceof Date) {
        const d = input;
        return `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
    }
    // numeric timestamp
    if (typeof input === 'number') {
        const d = new Date(input);
        if (!isNaN(d)) return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
        return String(input);
    }
    // string: already dd/mm/yyyy
    if (typeof input === 'string') {
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(input)) return input.replace(/\//g,'-');
        if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(input)) return input;
        // try ISO parse
        const d = new Date(input);
        if (!isNaN(d)) return `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
        return input;
    }
    return String(input);
}

function _formatTimeToHHMM(input) {
    if (!input && input !== 0) return '';
    if (input instanceof Date) {
        const d = input;
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }
    if (typeof input === 'number') {
        const d = new Date(input);
        if (!isNaN(d)) return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        return String(input);
    }
    if (typeof input === 'string') {
        if (/^\d{1,2}:\d{2}$/.test(input)) return input;
        const d = new Date(input);
        if (!isNaN(d)) return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
        return input;
    }
    return String(input);
}

function _convertIsoToDDMMYYYY(text) {
    if (!text || typeof text !== 'string') return text;
    // Replace ISO dates like 2025-10-16 -> 16-10-2025
    return text.replace(/\b(\d{4})-(\d{2})-(\d{2})\b/g, function(_, y, m, d) {
        return d + '-' + m + '-' + y;
    });
}

function _rewriteTextNodesInElement(root) {
    if (!root) return;
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(n => {
        const before = n.nodeValue;
        const after = _convertIsoToDDMMYYYY(before);
        if (after !== before) n.nodeValue = after;
    });

    // Also convert values/attributes inside form controls and other attributes
    const elements = root.querySelectorAll('input, textarea, select, [placeholder], [title], [alt]');
    elements.forEach(el => {
        try {
            if (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) {
                if (typeof el.value === 'string' && el.value) {
                    const v = _convertIsoToDDMMYYYY(el.value);
                    if (v !== el.value) el.value = v;
                }
                if (el.placeholder) {
                    const p = _convertIsoToDDMMYYYY(el.placeholder);
                    if (p !== el.placeholder) el.placeholder = p;
                }
            }
            if (el instanceof HTMLSelectElement) {
                Array.from(el.options).forEach(opt => {
                    if (opt.value) {
                        const vv = _convertIsoToDDMMYYYY(opt.value);
                        if (vv !== opt.value) opt.value = vv;
                    }
                    if (opt.text) {
                        const tt = _convertIsoToDDMMYYYY(opt.text);
                        if (tt !== opt.text) opt.text = tt;
                    }
                });
            }
            // generic attributes
            if (el.hasAttribute && el.hasAttribute('title')) {
                const t = _convertIsoToDDMMYYYY(el.getAttribute('title'));
                if (t !== el.getAttribute('title')) el.setAttribute('title', t);
            }
            if (el.hasAttribute && el.hasAttribute('alt')) {
                const a = _convertIsoToDDMMYYYY(el.getAttribute('alt'));
                if (a !== el.getAttribute('alt')) el.setAttribute('alt', a);
            }
            if (el.hasAttribute && el.hasAttribute('placeholder')) {
                const ph = _convertIsoToDDMMYYYY(el.getAttribute('placeholder'));
                if (ph !== el.getAttribute('placeholder')) el.setAttribute('placeholder', ph);
            }
        } catch (e) {
            // ignore conversion errors for unusual elements
        }
    });
}

function gerarpdf(nome='analitico', data=null, titulo=null, nomeEmpresa=null) {

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4"
    });

    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length === 0) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    let y = 15;

    const formattedDate = _formatDateToDDMMYYYY(data);

    const tipoSelec = document.querySelector('#filtro_operacional').value;
    let tipoFiltro = '';

    if (tipoSelec == 1) tipoFiltro = ' (Operacional)';
    else if (tipoSelec == 2) tipoFiltro = ' (Não Operacional)';

    // HEADER
    doc.setFontSize(14);
    doc.text(`${nomeEmpresa.substr(0,22)} ${tipoFiltro}`, 14, y);

    if(formattedDate){
        doc.setFontSize(10);
        doc.text(formattedDate, 14, y+5);
    }

    doc.setFontSize(16);
    doc.text('Relatório demonstrativo de resultado (DRE)', 105, y, {align:'center'});

    y += 10;

    accordionItems.forEach(item => {

        const header = item.querySelector('.accordion-header').innerText;

        if (nome === 'sintetico'){
            doc.setFontSize(11);
        } else {
            doc.setFontSize(13);
        }

        doc.text(header, 14, y);
        y += 4;

        const table = item.querySelector('table');

        if(!table) return;

        // HEADER DA TABELA
        const headers = [];
        table.querySelectorAll("thead th").forEach(th=>{
            headers.push(th.innerText.trim());
        });

        // LINHAS
        const rows = [];
        table.querySelectorAll("tbody tr").forEach(tr=>{
            const row = [];
            tr.querySelectorAll("td").forEach(td=>{
                row.push(td.innerText.trim());
            });
            rows.push(row);
        });
        const subtitleElement = item.querySelector('.accordion-body > *:not(table)');
let subtitle = '';

if(subtitleElement){
    subtitle = subtitleElement.innerText.trim();
}

if(subtitle){
    doc.setFontSize(11);
    doc.setFont(undefined, 'bold');
    doc.text(subtitle, 14, y);
    y += 5;
}

        doc.autoTable({
    head: [headers],
    body: rows,
    startY: y,

    theme: 'grid',

    headStyles:{
        fillColor: [220,220,220],   // preto
        textColor: [0,0,0], // branco
        fontStyle:'bold'
    },

    styles:{
        fontSize:8,
        cellPadding:2
    },

    alternateRowStyles:{
        fillColor:[242,242,242]
    }
});

        y = doc.lastAutoTable.finalY + 8;

    });

    // TOTAIS
    const totalReceitas = document.querySelector('#total-receitas');
    const totalDespesas = document.querySelector('#total-despesas');
    const totalDre = document.querySelector('#total-dre');

    y += 5;

    doc.setFontSize(11);

    let xLabel = 14;
let xValor = 60;

doc.setFontSize(11);

if(totalReceitas){
    doc.text("Total receitas:", xLabel, y);
    doc.text(totalReceitas.innerText.replace('Total receitas:', '').trim(), xValor, y);
    y += 6;
}

if(totalDespesas){
    doc.text("Total despesas:", xLabel, y);
    doc.text(totalDespesas.innerText.replace('Total despesas:', '').trim(), xValor, y);
    y += 6;
}

if(totalDre){
    doc.text("Saldo do DRE:", xLabel, y);
    doc.text(totalDre.innerText.replace('Saldo do DRE:', '').trim(), xValor, y);
}

    doc.save('dre-'+nome+'.pdf');
}

function gerarexcel(nome, data=null, hora=null, nomeEmpresa='') {
    if (nome == 'analitico') {
    // Check if XLSX library is loaded
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não carregada. Adicione <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script> ao seu HTML.');
        return;
    }

    // Select all accordion items
    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length === 0) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    let allData = [];

    // Adiciona header com nome, data e hora no topo do Excel (formatado)
    const headerTitle = nomeEmpresa + '  -  ' + 'Relatório demonstrativo de resultado - ' + String(nome).toUpperCase();
    const formattedDate = _formatDateToDDMMYYYY(data);
    const titulo = _formatTimeToHHMM(hora);
    const headerDateTime = (formattedDate ? 'Data: ' + formattedDate : '') + (titulo ? (formattedDate ? '<br>' : '') + 'Titulo: ' + titulo : '');
    allData.push([headerTitle]);
    if (headerDateTime.trim()) allData.push([headerDateTime]);
    allData.push([]); // linha em branco

    accordionItems.forEach((item) => {
        // Get title
        let title = '';
        const header = item.querySelector('.accordion-header .accordion-button span');
        if (header) title = header.textContent.trim();

        // Get all categories in this accordion-body
        const body = item.querySelector('.accordion-body');
        if (!body) return;

        // Find all category blocks (h5 + table + saldo do subtítulo)
        const categories = body.querySelectorAll('h5');
        categories.forEach((catElem) => {
            const category = catElem.textContent.trim();

            // Find the next table after this h5
            let table = catElem.nextElementSibling;
            while (table && table.tagName !== 'TABLE') {
                table = table.nextElementSibling;
            }
            if (!table) return;

            // Get table headers and rows (convert ISO dates to dd-mm-yyyy)
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => _convertIsoToDDMMYYYY(th.textContent.trim()));
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                Array.from(tr.querySelectorAll('td')).map(td => _convertIsoToDDMMYYYY(td.textContent.trim()))
            );

            // Add title and category
            if (title) allData.push([title]);
            if (category) allData.push([category]);
            if (headers.length) allData.push(headers);
            rows.forEach(row => allData.push(row));

            // 🔹 Procurar e adicionar o "Saldo do subtitulo" logo abaixo da tabela
            let saldoSubtitulo = '';
            let next = table.nextElementSibling;
            while (next) {
                if (next.tagName === 'DIV' && next.textContent.includes('Saldo do subtitulo')) {
                    saldoSubtitulo = next.textContent.trim();
                    break;
                }
                next = next.nextElementSibling;
            }
            if (saldoSubtitulo) allData.push([_convertIsoToDDMMYYYY(saldoSubtitulo)]);

            // Linha em branco entre categorias
            allData.push([]);
        });

        // Find the "Total Geral" div in this accordion-body
        let totalGeral = '';
        const totalGeralDiv = body.querySelector('div[id^="total-subtitulo-"]');
        if (totalGeralDiv) {
            totalGeral = _convertIsoToDDMMYYYY(totalGeralDiv.textContent.trim());
            if (totalGeral) allData.push([totalGeral]);
        }
        allData.push([]);
    });

    // Totais finais
    let totalReceitasDiv = document.querySelector('#total-receitas');
    if (totalReceitasDiv) {
        let totalReceitas = _convertIsoToDDMMYYYY(totalReceitasDiv.textContent.trim());
        if (totalReceitas) allData.push([totalReceitas]);
    }
    let totalDespesasDiv = document.querySelector('#total-despesas');
    if (totalDespesasDiv) {
        let totalDespesas = _convertIsoToDDMMYYYY(totalDespesasDiv.textContent.trim());
        if (totalDespesas) allData.push([totalDespesas]);
    }
    let totalDreDiv = document.querySelector('#total-dre');
    if (totalDreDiv) {
        let totalDre = _convertIsoToDDMMYYYY(totalDreDiv.textContent.trim());
        if (totalDre) allData.push([totalDre]);
    }

    // Create worksheet and workbook
    const ws = XLSX.utils.aoa_to_sheet(allData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Demonstrativo');

    // Export to Excel file
    XLSX.writeFile(wb, 'dre-' + nome + '.xlsx');
}
    
    
    
    
    
    else if(nome == 'sintetico') {
        console.log(nomeEmpresa)
        if (typeof XLSX === 'undefined') {
            alert('Biblioteca XLSX não carregada. Adicione <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script> ao seu HTML.');
            return;
        }

        // Seleciona todos os accordions do DRE Sintético
        const accordionItems = document.querySelectorAll('.accordion-item');
        if (accordionItems.length === 0) {
            alert('Nenhum conteúdo para exportar.');
            return;
        }

        let allData = [];

    // Header com nome, data e hora (formatado)
    const headerTitle = nomeEmpresa +'  -  ' + 'Relatório demonstrativo de resultado - ' + String(nome).toUpperCase();
    const formattedDate = _formatDateToDDMMYYYY(data);
    const titulo = _formatTimeToHHMM(hora);
    const headerDateTime = (formattedDate ? formattedDate : '') + (titulo ? (formattedDate ? ' — ' : '') + 'Titulo: ' + titulo : '');
        allData.push([headerTitle]);
        if (headerDateTime.trim()) allData.push([headerDateTime]);
        allData.push([]);

        accordionItems.forEach((item) => {
            // Título do accordion
            let title = '';
            const header = item.querySelector('.accordion-header .accordion-button span');
            if (header) title = header.textContent.trim();

            // Corpo do accordion
            const body = item.querySelector('.accordion-body');
            if (!body) return;

            // Tabela de subtítulos e receitas/despesas
            const table = body.querySelector('table');
            if (title) allData.push([title]);
            if (table) {
                // Cabeçalhos
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                if (headers.length) allData.push(headers);

                // Linhas
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                    Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim())
                );
                rows.forEach(row => allData.push(row));
            }

            // Total do título
            const totalTituloDiv = body.querySelector('div[id^="total-subtitulo-"]');
            if (totalTituloDiv) {
                let totalTitulo = totalTituloDiv.textContent.trim();
                if (totalTitulo) allData.push([totalTitulo]);
            }

            allData.push([]);
        });

        // Totais gerais (receitas, despesas, saldo DRE)
        let totalReceitasDiv = document.querySelector('#total-receitas');
            if (totalReceitasDiv) {
                let totalReceitas = totalReceitasDiv.textContent.trim();
                if (totalReceitas) allData.push([totalReceitas]);
            }
        let totalDespesasDiv = document.querySelector('#total-despesas');
            if (totalDespesasDiv) {
                let totalDespesas = totalDespesasDiv.textContent.trim();
                if (totalDespesas) allData.push([totalDespesas]);
            }

        let totalDreDiv = document.querySelector('#total-dre');
            if (totalDreDiv) {
                let totalDre = totalDreDiv.textContent.trim();
                if (totalDre) allData.push([totalDre]);
            }

        // Cria e exporta o arquivo Excel
        const ws = XLSX.utils.aoa_to_sheet(allData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'DRE Sintético');
        XLSX.writeFile(wb, 'dre-sintetico.xlsx');
    }
}