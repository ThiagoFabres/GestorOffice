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
    let accordionItems = [];
    if(nome !== 'pagamento') {
        accordionItems = Array.from(document.querySelectorAll('.accordion-item'));
        if (accordionItems.length === 0) {
            alert('Nenhum conteúdo para exportar.');
            return;
        }
    }

    // Header
    pdf.setFontSize(14);
    const formattedDate = _convertIsoToDDMMYYYY(data);
    console.log(data + ' - ' +formattedDate);
    const headerTitle = nomeEmpresa ? nomeEmpresa.substr(0, 60) : '';
    const dateLabel = formattedDate ? formattedDate.replace(/Data Inicial:/i, 'Data:').replace(/Data Final:/i, 'Data:') : '';
    const headerLines = pdf.splitTextToSize(headerTitle + (dateLabel ? ' — ' + dateLabel : ''), usableWidth);
    let cursorY = margin;
    pdf.text(headerLines, margin, cursorY);
    cursorY += headerLines.length * 7;

    pdf.setFontSize(12);
    if(nome !== 'pagamento') {
        pdf.text('Relatório demonstrativo de resultado (DRE)', margin, cursorY);
        cursorY += 8;
        pdf.setLineWidth(0.2);
        pdf.line(margin, cursorY, pageWidth - margin, cursorY);
        cursorY += 8;
    } else {
        pdf.text('Fechamento de Caixa', margin, cursorY);
        cursorY += 8;
        pdf.setLineWidth(0.2);
        pdf.line(margin, cursorY, pageWidth - margin, cursorY);
        cursorY += 8;
    }

    if (nome === 'pagamento') {
    const table = document.querySelector('#table-pagamento-pdf');
        console.log(table)
    if (!table) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    let headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => _convertIsoToDDMMYYYY(th.textContent.trim()));

    if (!headers.length) {
        const firstRow = table.querySelector('tbody tr');
        if (firstRow) {
            headers = Array.from(firstRow.querySelectorAll('td'))
                .map((_, i) => 'Col ' + (i + 1));
        }
    }

    const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
    Array.from(tr.querySelectorAll('td')).map(td => {
        let text = td.textContent.trim();

        // 🔥 junta "R$" com valor
        text = text.replace(/\s*\n\s*/g, ' '); // remove quebra
        text = text.replace(/R\$\s+/g, 'R$ ');

        return _convertIsoToDDMMYYYY(text);
    })
);

    const numCols = headers.length;

    let colWidths = {};
    if (numCols === 2) {
        colWidths = {
            0: { cellWidth: usableWidth * 0.6 },
            1: { cellWidth: usableWidth * 0.4, halign: 'right' }
        };
    }

    pdf.autoTable({
        startY: cursorY,
        head: headers.length ? [headers] : [],
        body: rows,
        margin: { left: margin, right: margin },

        styles: { fontSize: 9, cellPadding: 3 },

        headStyles: {
            fillColor: [220, 220, 220],
            textColor: 0
        },

        columnStyles: colWidths,

        didParseCell: function (data) {
            const totalLinhas = data.table.body.length;

            if (data.column.index === numCols - 1) {
                data.cell.styles.halign = 'right';
            }

            if (data.row.index === totalLinhas - 1) {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [220, 220, 220];
            }

            if (data.section === 'body' && data.row.index % 2 === 0) {
                data.cell.styles.fillColor = [245, 245, 245];
            }
        },

        theme: 'grid'
    });

    cursorY = pdf.lastAutoTable.finalY + 6;

    // TOTAL FINAL
const totalDiv = document.querySelector('#total-dre');
if (totalDiv) {
    let texto = totalDiv.textContent.trim();
    texto = texto.replace(/\s*\n\s*/g, ' ').replace(/R\$\s+/g, 'R$ ');
    
    const match = texto.match(/(Total Recebido:)\s*R\$\s*([\d.,]+)/);
    if (match) {
        const label = match[1];               // "Total Recebido:"
        const valor = 'R$ ' + match[2].trim(); // "R$ 203,00"
        pdf.setFontSize(11);
        pdf.text(label, margin, cursorY);
        pdf.text(valor, pageWidth - margin, cursorY, { align: 'right' });
    } else {
        pdf.text(texto, margin, cursorY);
    }
}

    // paginação
    const pageCount = pdf.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        pdf.setPage(i);
        pdf.setFontSize(9);
        pdf.text(
            `Página ${i} de ${pageCount}`,
            pageWidth / 1.1,
            10,
            { align: 'center' }
        );
    }

    pdf.save('dre-pagamento.pdf');
    return; // 🚨 IMPORTANTE: para não cair no código dos accordions

}

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
if(nome == 'sintetico') {
    console.log('sintetico')
    pdf.autoTable({
        startY: cursorY,
        head: headers.length ? [headers] : [],
        body: rows,
        margin: { left: margin, right: margin },

        styles: { fontSize: 9, cellPadding: 3 },

        headStyles: { 
            fillColor: [190, 190, 190], 
            textColor: 20,
            halign: 'left'
        },
        

        columnStyles: {
            0: { cellWidth: usableWidth * 0.75 },
            1: { cellWidth: usableWidth * 0.25, halign: 'right' }
        },


        didParseCell: function (data) {
            if (data.column.index === 1) {
                data.cell.styles.halign = 'right';
            }
            const totalLinhas = data.table.body.length;

            if (data.row.index === totalLinhas - 1) {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [220, 220, 220]; 
                data.cell.styles.textColor = [30, 30, 30]
                data.cell.styles.halign = 'right';
            }

            if (data.section === 'body') {
                if (data.row.index % 2 === 0) {
                    data.cell.styles.fillColor = [245, 245, 245]; 
                }
            }
        },

        theme: 'grid'
    });
} else {
    console.log('analitico')
    pdf.autoTable({
        startY: cursorY,
        head: headers.length ? [headers] : [],
        body: rows,
        margin: { left: margin, right: margin },

        styles: { fontSize: 9, cellPadding: 3 },

        headStyles: { 
            fillColor: [190, 190, 190], 
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
            if (data.column.index === 2) {
                data.cell.styles.halign = 'right';
            }

            if (data.row.index === totalLinhas - 1) {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [220, 220, 220]; 
                data.cell.styles.textColor = [30, 30, 30]
                data.cell.styles.halign = 'right';
            }
            if (data.section === 'body') {
                if (data.row.index % 2 === 0) {
                    data.cell.styles.fillColor = [245, 245, 245]; 
                }
            }
        },

        theme: 'grid'
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
function gerarexcel(nome, data=null, hora=null, nomeEmpresa='') {
    if (nome === 'pagamento') {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não carregada.');
        return;
    }

    const table = document.querySelector('#table-pagamento-pdf');

    if (!table) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    let allData = [];

    // Header
    const headerTitle = nomeEmpresa + ' Relatório de tipo de pagamento';
    const formattedDate = _formatDateToDDMMYYYY(data);
    const formattedHora = _formatTimeToHHMM(hora);

    allData.push([headerTitle]);
    if (formattedDate || formattedHora) {
        allData.push([`${formattedDate || ''}${formattedHora ? ' — ' + formattedHora : ''}`]);
    }
    allData.push([]);

    // Cabeçalhos
    let headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());

    if (!headers.length) {
        const firstRow = table.querySelector('tbody tr');
        if (firstRow) {
            headers = Array.from(firstRow.querySelectorAll('td'))
                .map((_, i) => 'Col ' + (i + 1));
        }
    }

    if (headers.length) allData.push(headers);

    const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
        Array.from(tr.querySelectorAll('td')).map(td => {
            let text = td.textContent.trim();

            text = text.replace(/\s*\n\s*/g, ' ');
            text = text.replace(/R\$\s+/g, 'R$ ');

            return text;
        })
    );

    rows.forEach(row => allData.push(row));

    // Total final
    const totalDiv = document.querySelector('#total-dre');
    if (totalDiv) {
        let texto = totalDiv.textContent.trim();

        texto = texto.replace(/\s*\n\s*/g, ' ');
        texto = texto.replace(/R\$\s+/g, 'R$ ');

        allData.push([]);
        allData.push([texto]);
    }

    // Criar Excel
    const ws = XLSX.utils.aoa_to_sheet(allData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Pagamento');

    XLSX.writeFile(wb, 'relatorio_pagamento.xlsx');
    return;
}
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
    const headerTitle = nomeEmpresa + '  -  ' + 'Relatório de tipos de pagamento';
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
            pdf.setFontSize(20);
            if (title) allData.push([title]);
            pdf.setFontSize(12)
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
            pdf.setFontSize(20)
            if (title) allData.push([title]);
            pdf.setFontSize(12)
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
/**
 * Exportação PDF — Relatório Anual
 *
 * Lê #tabela-anual e gera um PDF A4 paisagem com:
 *  - Cabeçalho (empresa + ano)
 *  - Linha "Título" em negrito / fundo roxo claro
 *  - Linhas "Subtítulo" recuadas
 *  - Linha "Total Geral" em negrito / fundo escuro
 */
function gerarpdf_anual(ano, nomeEmpresa) {
    if (typeof jsPDF === 'undefined' && !(window.jspdf && window.jspdf.jsPDF)) {
        alert('Biblioteca jsPDF não encontrada.');
        return;
    }

    const PDFClass = (typeof jsPDF !== 'undefined') ? jsPDF : window.jspdf.jsPDF;
    const pdf = new PDFClass({ unit: 'mm', format: 'a4', orientation: 'landscape' });

    if (typeof pdf.autoTable !== 'function') {
        alert('jspdf-autotable não detectado.');
        return;
    }

    const margin     = 10;
    const pageWidth  = pdf.internal.pageSize.getWidth();
    const usableW    = pageWidth - margin * 2;
    let cursorY      = margin;

    /* ── Cabeçalho ──────────────────────────────────────────────── */
    pdf.setFontSize(13);
    const headerLines = pdf.splitTextToSize(
        (nomeEmpresa || '') + (ano ? '  —  ' + ano : ''),
        usableW
    );
    pdf.text(headerLines, margin, cursorY);
    cursorY += headerLines.length * 7;

    pdf.setFontSize(11);
    pdf.text('Relatório Anual de Recebimentos', margin, cursorY);
    cursorY += 6;
    pdf.setLineWidth(0.2);
    pdf.line(margin, cursorY, pageWidth - margin, cursorY);
    cursorY += 6;

    /* ── Ler tabela ─────────────────────────────────────────────── */
    const table = document.querySelector('#tabela-anual');
    if (!table) { alert('Tabela anual não encontrada.'); return; }

    /* Cabeçalhos */
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());

    /* Linhas com metadados de estilo */
    const bodyRows = [];
    table.querySelectorAll('tbody tr').forEach(tr => {
        const cells = Array.from(tr.querySelectorAll('td'))
            .map((td, idx) => {
                if (idx === 0) {
                    /* Remove o ícone toggle (span.toggle-icon) antes de ler o texto,
                       para não capturar "▼" nem "▶" na coluna descritiva */
                    const clone = td.cloneNode(true);
                    const icon = clone.querySelector('.toggle-icon');
                    if (icon) icon.remove();
                    /* Colapsa múltiplas quebras/espaços em um único espaço */
                    return clone.textContent.replace(/\s+/g, ' ').trim();
                }
                return td.textContent.trim();
            });
        if (!cells.length) return;

        let tipo = 'subtitulo';
        if (tr.classList.contains('row-titulo'))      tipo = 'titulo';
        if (tr.classList.contains('row-total-geral')) tipo = 'total';

        bodyRows.push({ cells, tipo });
    });

    /* ── autoTable ──────────────────────────────────────────────── */
    // A4 paisagem = 297mm utilizável (~277mm com margens de 10mm cada lado).
    // 13 colunas numéricas (Jan…Dez + Total) + 1 descritiva.
    // Cada coluna numérica precisa de ~14mm para "1.234.567,89" em fonte 6.5.
    // Coluna descritiva ocupa o restante.
    const numColCount = headers.length - 1;
    const numW        = 17;
    const descW       = usableW - numW * numColCount; // espaço real da coluna descritiva

    // Fonte responsiva baseada na largura disponível
    // Referência: descW ~56mm → fontSize 6.5 | descW ~40mm → fontSize 5.5
    const minFont  = 5;
    const maxFont  = 7;
    const fontSize = Math.min(maxFont, Math.max(minFont, 5 + (descW / 56) * 1.5));

    const colStyles = { 0: { cellWidth: 'auto', halign: 'left' } };
    for (let i = 1; i < headers.length; i++) {
        colStyles[i] = { cellWidth: numW, halign: 'right' };
    }

    pdf.autoTable({
        startY:  cursorY,
        head:    [headers],
        body:    bodyRows.map(r => r.cells),
        tableWidth:  'auto',
        margin:  { left: margin, right: margin },
        styles:  { fontSize: fontSize, cellPadding: { top: 2, bottom: 2, left: 2, right: 2 }, overflow: 'visible' },

        headStyles: {
            fillColor:  [52, 52, 52],
            textColor:  255,
            halign:     'center',
            fontStyle:  'bold',
            fontSize:   fontSize + 0.5,
        },

        columnStyles: colStyles,

        didParseCell: function (data) {
            if (data.section !== 'body') return;

            const meta = bodyRows[data.row.index];
            if (!meta) return;

            /* Coluna descritiva: alinha à esquerda */
            if (data.column.index === 0) {
                data.cell.styles.halign = 'left';
            }

            if (meta.tipo === 'titulo') {
                data.cell.styles.fillColor  = [232, 231, 251]; /* roxo bem claro */
                data.cell.styles.fontStyle  = 'bold';
                data.cell.styles.textColor  = [40, 40, 90];
                if (data.column.index === 0) {
                    data.cell.styles.fillColor = [232, 231, 251];
                }
            } else if (meta.tipo === 'subtitulo') {
                data.cell.styles.fillColor  = [248, 248, 255];
                data.cell.styles.textColor  = [68, 68, 68];
                if (data.column.index === 0) {
                    data.cell.styles.cellPadding = { left: 6, top: 2.5, right: 2.5, bottom: 2.5 };
                    data.cell.styles.fillColor   = [248, 248, 255];
                }
            } else if (meta.tipo === 'total') {
                data.cell.styles.fillColor  = [52, 52, 52];
                data.cell.styles.textColor  = [255, 255, 255];
                data.cell.styles.fontStyle  = 'bold';
            }

            /* Zeroes em cinza (exceto linha de total) */
            if (meta.tipo !== 'total' && data.column.index > 0) {
                const raw = (data.cell.text || []).join('').replace('.', '').replace(',', '.');
                if (parseFloat(raw) === 0) {
                    data.cell.styles.textColor = [187, 187, 187];
                }
            }
        },

        theme: 'grid',
    });

    /* ── Paginação ──────────────────────────────────────────────── */
    const pageCount = pdf.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        pdf.setPage(i);
        pdf.setFontSize(8);
        pdf.text(
            'Página ' + i + ' de ' + pageCount,
            pageWidth - margin,
            8,
            { align: 'right' }
        );
    }

    pdf.save('relatorio_anual_' + (ano || 'exportado') + '.pdf');
}


/**
 * Exportação Excel — Relatório Anual
 *
 * Lê #tabela-anual e gera um .xlsx com:
 *  - Cabeçalho (empresa + ano)
 *  - Linha de cabeçalho dos meses
 *  - Dados (título, subtítulos, total geral)
 *  - Valores numéricos como número (não string) para permitir fórmulas no Excel
 */
function gerarexcel_anual(ano, nomeEmpresa) {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não carregada.');
        return;
    }

    const table = document.querySelector('#tabela-anual');
    if (!table) { alert('Tabela anual não encontrada.'); return; }

    const allData = [];

    /* ── Cabeçalho ──────────────────────────────────────────────── */
    allData.push([(nomeEmpresa || '') + (ano ? '  —  ' + ano : '')]);
    allData.push(['Relatório Anual de Recebimentos']);
    allData.push([]);

    /* ── Cabeçalhos de coluna ───────────────────────────────────── */
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());
    allData.push(headers);

    /* ── Linhas de dados ────────────────────────────────────────── */
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach((td, idx) => {
            if (idx === 0) {
                /* Remove ícone toggle e normaliza espaços */
                const clone = td.cloneNode(true);
                const icon = clone.querySelector('.toggle-icon');
                if (icon) icon.remove();
                row.push(clone.textContent.replace(/\s+/g, ' ').trim());
            } else {
                const raw = td.textContent.trim();
                /* Colunas numéricas: converte para número */
                const num = parseFloat(raw.replace(/\./g, '').replace(',', '.'));
                row.push(isNaN(num) ? raw : num);
            }
        });
        if (row.length) allData.push(row);
    });

    /* ── Criar planilha ─────────────────────────────────────────── */
    const ws = XLSX.utils.aoa_to_sheet(allData);

    /* Largura das colunas: primeira larga, demais iguais */
    const colCount = headers.length;
    ws['!cols'] = [{ wch: 32 }];
    for (let i = 1; i < colCount; i++) ws['!cols'].push({ wch: 12 });

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Anual');

    XLSX.writeFile(wb, 'relatorio_anual_' + (ano || 'exportado') + '.xlsx');
}

function gerarpdf_curvaabc(dataTexto = '', nomeEmpresa = '') {
    const PDFClass = (typeof jsPDF !== 'undefined')
        ? jsPDF
        : window.jspdf.jsPDF;

    const pdf = new PDFClass({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
    });

    const margin = 10;

    // Cabeçalho
    pdf.setFontSize(14);
    pdf.text(nomeEmpresa || 'Curva ABC', margin, 15);

    pdf.setFontSize(10);
    if (dataTexto) {
        pdf.text(dataTexto, margin, 22);
    }

    pdf.setFontSize(12);
    pdf.text('Relatório Curva ABC', margin, 30);

    // Tabela
    const tabela = document.querySelector('#table-curva-abc');

    const headers = [];
    tabela.querySelectorAll('thead th').forEach(th => {
        headers.push(th.innerText.trim());
    });

    const rows = [];

tabela.querySelectorAll('tbody tr').forEach(tr => {
    const cols = [];

    tr.querySelectorAll('td').forEach((td, index) => {

        if (index === 1) {
            const texto = td.innerText
                .replace(/\n/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            cols.push(texto.replace(/R\$\s*/g, '').trim());
        } else {
            cols.push(
                td.innerText
                    .replace(/\n/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim()
            );
        }
    });

    rows.push(cols);
});

    pdf.autoTable({
    startY: 38,
    head: [headers],
    body: rows,

    theme: 'grid',

    styles: {
        fontSize: 9,
        cellPadding: 3,
        valign: 'middle'
    },

    headStyles: {
        fillColor: [235,235,235],
        textColor: [0,0,0],
        fontStyle: 'bold'
    },

    columnStyles: {
        0: {
            cellWidth: 110
        },
        1: {
            cellWidth: 45,
            halign: 'right'
        },
        2: {
            cellWidth: 30,
            halign: 'right'
        }
    },

    margin: {
        left: 15,
        right: 15
    },

    tableWidth: 'auto',

    didParseCell: function(data) {

        if (data.column.index === 1 && data.section === 'body') {
            data.cell.text = [
                'R$ ' + data.cell.text[0]
            ];
        }

        if (data.row.index % 2 === 0 && data.section === 'body') {
            data.cell.styles.fillColor = [248,248,248];
        }
    }
});
    // Rodapé com numeração
    const paginas = pdf.getNumberOfPages();

    for (let i = 1; i <= paginas; i++) {
        pdf.setPage(i);

        pdf.setFontSize(8);

        pdf.text(
            `Página ${i} de ${paginas}`,
            pdf.internal.pageSize.getWidth() - 15,
            pdf.internal.pageSize.getHeight() - 5,
            { align: 'right' }
        );
    }

    pdf.save('curva-abc.pdf');
}

function gerarexcel_curvaabc(dataTexto = '', nomeEmpresa = '') {

    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX não carregada.');
        return;
    }

    const tabela = document.querySelector('#table-curva-abc');

    const dados = [];

    dados.push([nomeEmpresa]);
    dados.push(['Relatório Curva ABC']);

    if (dataTexto) {
        dados.push([dataTexto]);
    }

    dados.push([]);

    const headers = [];

    tabela.querySelectorAll('thead th').forEach(th => {
        headers.push(th.innerText.trim());
    });

    dados.push(headers);

    tabela.querySelectorAll('tbody tr').forEach(tr => {

        const linha = [];

        tr.querySelectorAll('td').forEach((td, index) => {

            let texto = td.innerText
                .replace(/\n/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            if (index === 1) {
                texto = texto
                    .replace(/R\$/g, '')
                    .trim();

                const numero = parseFloat(
                    texto.replace(/\./g, '').replace(',', '.')
                );

                linha.push(isNaN(numero) ? texto : numero);

            } else {
                linha.push(texto);
            }
        });

        if (linha.length) {
            dados.push(linha);
        }
    });

    const ws = XLSX.utils.aoa_to_sheet(dados);

    ws['!cols'] = [
        { wch: 40 },
        { wch: 20 },
        { wch: 15 }
    ];

    const wb = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(
        wb,
        ws,
        'Curva ABC'
    );

    XLSX.writeFile(
        wb,
        'curva_abc.xlsx'
    );
}