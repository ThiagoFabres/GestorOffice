// Utilities
function _formatDateToDDMMYYYY(input) {
    if (!input && input !== 0) return '';
    if (input instanceof Date) {
        const d = input;
        return `${String(d.getDate()).padStart(2, '0')}-${String(d.getMonth() + 1).padStart(2, '0')}-${d.getFullYear()}`;
    }
    if (typeof input === 'number') {
        const d = new Date(input);
        if (!isNaN(d)) return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
        return String(input);
    }
    if (typeof input === 'string') {
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(input)) return input.replace(/\//g,'-');
        if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(input)) return input;
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
    return text.replace(/\b(\d{4})-(\d{2})-(\d{2})\b/g, function(_, y, m, d) {
        return d + '/' + m + '/' + y;
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
            // ignore conversion errors
        }
    });
}

async function gerarpdf(nome='analitico', data=null, titulo=null, nomeEmpresa=null) {
    console.log('Rendering')
    if (typeof jsPDF === 'undefined' && !(window.jspdf && window.jspdf.jsPDF)) {
        alert('Biblioteca jsPDF não encontrada. Adicione jsPDF e jspdf-autotable ao seu HTML.');
        return;
    }

    const PDFClass = (typeof jsPDF !== 'undefined') ? jsPDF : window.jspdf.jsPDF;
    const pdf = new PDFClass({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    const margin = 12;
    const pageWidth = pdf.internal.pageSize.getWidth();
    const usableWidth = pageWidth - margin * 2;

    if (typeof pdf.autoTable !== 'function') {
        alert('jspdf-autotable não detectado. Adicione o plugin jspdf-autotable ao seu HTML.');
        return;
    }

    const accordionItems = Array.from(document.querySelectorAll('.accordion-item'));
    if (accordionItems.length === 0) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    // Header
    pdf.setFontSize(14);
    const formattedDate = _convertIsoToDDMMYYYY(data);
    console.log(data + ' - ' +formattedDate);
    const headerTitle = nomeEmpresa ? nomeEmpresa.substr(0, 60) : '';
    const headerLines = pdf.splitTextToSize(headerTitle + (formattedDate ? ' — ' + formattedDate : ''), usableWidth);
    let cursorY = margin;
    pdf.text(headerLines, margin, cursorY);
    cursorY += headerLines.length * 7;

    if (titulo) {
        pdf.setFontSize(11);
        const titleLines = pdf.splitTextToSize(String(titulo), usableWidth);
        pdf.text(titleLines, margin, cursorY);
        cursorY += titleLines.length * 6;
    }

    pdf.setFontSize(12);
    pdf.text('Relatório demonstrativo de resultado (DRE)', margin, cursorY);
    cursorY += 8;

    // Iterate accordions and convert tables using autoTable
    for (let i = 0; i < accordionItems.length; i++) {
        const item = accordionItems[i];
        const headerSpan = item.querySelector('.accordion-header .accordion-button span');
        const title = headerSpan ? headerSpan.textContent.trim() : (item.querySelector('.accordion-header') ? item.querySelector('.accordion-header').textContent.trim() : '');

        pdf.setFontSize(11);
        const titleLines = pdf.splitTextToSize(title, usableWidth);
        if (cursorY + titleLines.length * 6 > pdf.internal.pageSize.getHeight() - margin) pdf.addPage(), cursorY = margin;
       pdf.setFont(undefined, 'bold');
        pdf.setFontSize(18)
        pdf.text(titleLines, margin, cursorY);
        pdf.setFont(undefined, 'normal');
        pdf.setFontSize(11)
        cursorY += titleLines.length * 6 + 4;

        const body = item.querySelector('.accordion-body');
        if (!body) continue;

        const tables = Array.from(body.querySelectorAll('table'));
        for (let t = 0; t < tables.length; t++) {
            const table = tables[t];

        // procurar subtitulo (h5) anterior à tabela
        let subtitle = '';
        let prev = table.previousElementSibling;

        while (prev) {
            if (prev.tagName === 'H5') {
                subtitle = prev.textContent.trim();
                break;
            }
            prev = prev.previousElementSibling;
        }

        // escrever subtitulo no PDF
        if (subtitle) {
            pdf.setFontSize(10);
            const subtitleLines = pdf.splitTextToSize(subtitle, usableWidth);

            if (cursorY + subtitleLines.length * 6 > pdf.internal.pageSize.getHeight() - margin) {
                pdf.addPage();
                cursorY = margin;
            }
            pdf.setFontSize(15)
            pdf.text(subtitleLines, margin, cursorY);
            pdf.setFontSize(10)
            cursorY += subtitleLines.length * 6 + 2;
        }
            let headers = Array.from(table.querySelectorAll('thead th')).map(th => _convertIsoToDDMMYYYY(th.textContent.trim()));
            if (!headers || headers.length === 0) {
                const firstRow = table.querySelector('tbody tr');
                if (firstRow) headers = Array.from(firstRow.querySelectorAll('td')).map((_, idx) => 'Col ' + (idx + 1));
            }

            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                Array.from(tr.querySelectorAll('td')).map(td => _convertIsoToDDMMYYYY(td.textContent.trim()))
            );

            if (!rows || rows.length === 0) continue;
            if(nome == 'sintetico'){
            pdf.autoTable({
        startY: cursorY,
        head: headers.length ? [headers] : [],
        body: rows,
        margin: { left: margin, right: margin },

        styles: { fontSize: 9, cellPadding: 3 },

        headStyles: { 
            fillColor: [230, 230, 230], 
            textColor: 20,
            halign: 'left'
        },
        

        columnStyles: {
            0: { cellWidth: usableWidth * 0.75 },
            1: { cellWidth: usableWidth * 0.25, halign: 'right' }
        },


        didParseCell: function (data) {
            const totalLinhas = data.table.body.length;

            if (data.row.index === totalLinhas - 1) {
        data.cell.styles.fontStyle = 'bold';
        data.cell.styles.fillColor = [220, 220, 220]; 
        data.cell.styles.textColor = [30, 30, 30]
        data.cell.styles.halign = 'right';
    }
        },

        theme: 'striped'
    });
} else {
   pdf.autoTable({
        startY: cursorY,
        head: headers.length ? [headers] : [],
        body: rows,
        margin: { left: margin, right: margin },

        styles: { fontSize: 9, cellPadding: 3 },

        headStyles: { 
            fillColor: [230, 230, 230], 
            textColor: 20,
            halign: 'left'
        },
        

        columnStyles: {
            0: { cellWidth: usableWidth * 0.15 },
            1: { cellWidth: usableWidth * 0.60 },
            2:{ cellWidth: usableWidth * 0.25, halign: 'right' }
        },


        didParseCell: function (data) {
            const totalLinhas = data.table.body.length;

            if (data.row.index === totalLinhas - 1) {
        data.cell.styles.fontStyle = 'bold';
        data.cell.styles.fillColor = [220, 220, 220]; 
        data.cell.styles.textColor = [30, 30, 30]
        data.cell.styles.halign = 'right';
    }
        },

        theme: 'striped'
    });
}
            cursorY = (pdf.lastAutoTable && pdf.lastAutoTable.finalY) ? pdf.lastAutoTable.finalY + 6 : pdf.internal.pageSize.getHeight() - margin;
            if (cursorY > pdf.internal.pageSize.getHeight() - margin) {
                pdf.addPage();
                cursorY = margin;
            }
        }

        cursorY += 4;
        if (cursorY > pdf.internal.pageSize.getHeight() - margin) {
            pdf.addPage();
            cursorY = margin;
        }
    }

    function escreverLinhaTotal(label, valor) {
    const pageWidth = pdf.internal.pageSize.getWidth();
    const rightMargin = 130;

    pdf.text(label, margin, cursorY);

    pdf.text(valor, pageWidth - rightMargin, cursorY, {
        align: 'right'
    });

    cursorY += 6;
}

    // Totals
    const totalReceitasDiv = document.querySelector('#total-receitas');
    
    const totalDespesasDiv = document.querySelector('#total-despesas');
    const totalDreDiv = document.querySelector('#total-dre');
    totalReceitasDiv.style.whiteSpace = 'nowrap';
    totalDespesasDiv.style.whiteSpace = 'nowrap';
    totalDreDiv.style.whiteSpace = 'nowrap';
    pdf.setFontSize(11);
    if (totalReceitasDiv) {
    const texto = totalReceitasDiv.textContent.trim();
    const partes = texto.split('R$');
    escreverLinhaTotal(partes[0] + 'R$', partes[1]?.trim() || '');
}

if (totalDespesasDiv) {
    const texto = totalDespesasDiv.textContent.trim();
    const partes = texto.split('R$');
    escreverLinhaTotal(partes[0] + 'R$', partes[1]?.trim() || '');
}

if (totalDreDiv) {
    const texto = totalDreDiv.textContent.trim();
    const partes = texto.split('R$');
    escreverLinhaTotal(partes[0] + 'R$', partes[1]?.trim() || '');
}

    try {
        const pageCount = pdf.getNumberOfPages();

for (let i = 1; i <= pageCount; i++) {
    pdf.setPage(i);

    pdf.setFontSize(9);

    pdf.text(
        `Página ${i} de ${pageCount}`,
        pdf.internal.pageSize.getWidth() / 1.1 ,
        10,
        { align: 'center' }
    );
}

        pdf.save('dre-' + nome + '.pdf');
    } catch (e) {
        console.error('Erro ao salvar PDF gerado por jsPDF:', e);
        alert('Erro ao salvar PDF. Veja o console para detalhes.');
    }
}

// gerarexcel: export tables to Excel
function gerarexcel(nome, data=null, hora=null, nomeEmpresa='') {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não carregada. Adicione <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script> ao seu HTML.');
        return;
    }

    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length === 0) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    let allData = [];
    const headerTitle = nomeEmpresa + '  -  ' + 'Relatório demonstrativo de resultado - ' + String(nome).toUpperCase();
    const formattedDate = _formatDateToDDMMYYYY(data);
    const titulo = _formatTimeToHHMM(hora);
    
    const headerDateTime = (formattedDate ? 'Data: ' + formattedDate : '') + (titulo ? (formattedDate ? '<br>' : '') + 'Titulo: ' + titulo : '');
    allData.push([headerTitle]);
    if (headerDateTime.trim()) allData.push([headerDateTime]);
    allData.push([]);

    accordionItems.forEach((item) => {
        let title = '';
        const subtitulo = ''
        const header = item.querySelector('.accordion-header .accordion-button span');
        if (header) title = header.textContent.trim();
        const body = item.querySelector('.accordion-body');
        if (!body) return;

        const categories = body.querySelectorAll('h5');
        categories.forEach((catElem) => {
            const category = catElem.textContent.trim();

            let table = catElem.nextElementSibling;
            while (table && table.tagName !== 'TABLE') table = table.nextElementSibling;
            if (!table) return;

            const headers = Array.from(table.querySelectorAll('thead th')).map(th => _convertIsoToDDMMYYYY(th.textContent.trim()));
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                Array.from(tr.querySelectorAll('td')).map(td => _convertIsoToDDMMYYYY(td.textContent.trim()))
            );

            if (title) allData.push([title]);
            if (category) allData.push([category]);
            if (headers.length) allData.push(headers);
            rows.forEach(row => allData.push(row));

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
            allData.push([]);
        });

        const totalGeralDiv = body.querySelector('div[id^="total-subtitulo-"]');
        if (totalGeralDiv) {
            const totalGeral = _convertIsoToDDMMYYYY(totalGeralDiv.textContent.trim());
            if (totalGeral) allData.push([totalGeral]);
        }
        allData.push([]);
    });

    const totalReceitasDiv = document.querySelector('#total-receitas');
    if (totalReceitasDiv) {
        const totalReceitas = _convertIsoToDDMMYYYY(totalReceitasDiv.textContent.trim());
        if (totalReceitas) allData.push([totalReceitas]);
    }
    const totalDespesasDiv = document.querySelector('#total-despesas');
    if (totalDespesasDiv) {
        const totalDespesas = _convertIsoToDDMMYYYY(totalDespesasDiv.textContent.trim());
        if (totalDespesas) allData.push([totalDespesas]);
    }
    const totalDreDiv = document.querySelector('#total-dre');
    if (totalDreDiv) {
        const totalDre = _convertIsoToDDMMYYYY(totalDreDiv.textContent.trim());
        if (totalDre) allData.push([totalDre]);
    }

    const ws = XLSX.utils.aoa_to_sheet(allData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Demonstrativo');
    XLSX.writeFile(wb, 'dre-' + nome + '.xlsx');
}
