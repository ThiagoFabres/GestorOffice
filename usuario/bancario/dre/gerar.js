
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

        }
    });
}

function gerarpdf(nome='analitico', data=null, titulo=null, nomeEmpresa=null) {
    console.log(titulo);
    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length === 0) {
        alert('Nenhum conteúdo para exportar.');
        return;
    }

    const pdfContainer = document.createElement('div');
    pdfContainer.style.padding = '20px';

    const formattedDate = _formatDateToDDMMYYYY(data);
    const headerDiv = document.createElement('div');
    headerDiv.style.marginBottom = '1px';
    headerDiv.innerHTML = `
    <div style=" display:flex; flex-direction:row; justify-content:start  align-itens:start; height:100%; ;">
        <div style="display:flex; justify-content:start; align-itens:start; height:100%; width:100%;" >
            <div style="display:flex; justify-content:start; align-itens:start; height:100%; width:100%;">
                <p style="font-size:25px; text-align:start; margin:0;  white-space: nowrap; overflow:hidden; text-overflow:hidden;  padding-right:9px;">
                    ${nomeEmpresa.substr(0, 22)}  - 
                </p>

            <p style="  font-size:20px; padding-top:5px; text-align:start; margin:0;  white-space: nowrap; overflow:hidden; text-overflow:hidden;">` 
                +
                
                    ((formattedDate || titulo) ?  `${ formattedDate ? formattedDate + '</p>' : '</p>'}` : '</div>') + '</div>  </div> </p>' 
                +
            `</div>
        </div> 
        <div style="display:flex; flex-direction:column;"> 
       `     
    +
    `<p style="text-align:center; font-size:1.5em; margin:0; padding:0;">Relatório demonstrativo de resultado (DRE)</p>` 
    +
    '</div></div><hr>'

    pdfContainer.appendChild(headerDiv);

    accordionItems.forEach(item => {
        const mainContainer = document.createElement("div")
        const headerOriginal = item.querySelector('.accordion-header');
        const header = headerOriginal.cloneNode(true);

        if(nome == 'sintetico'){
        header.style.fontSize = '95%'
        }   else {
        header.style.fontSize = '120%'
        header.style.fontWeight = 'bold'
        }
        const bodyOriginal = item.querySelector('.accordion-body');
        const body = bodyOriginal.cloneNode(true);

        body.style.background = 'white';
        body.style.backgroundColor = 'white';
        body.style.boxShadow = 'none';
        body.style.filter = 'none';

        body.querySelectorAll('*').forEach(function(el) {
            el.style.background = 'transparent';
            el.style.backgroundColor = 'transparent';
            el.style.boxShadow = 'none';
            el.style.filter = 'none';
        });

        header.style.backgroundColor = 'white';

        body.querySelectorAll('table').forEach(function(el) {
            el.classList.remove('table-striped');
        });
        body.querySelectorAll('table tr').forEach(function(tr, index) {
            const cor = (index % 2 === 0) ? '#f2f2f2'  : '#ffffff';
            tr.querySelectorAll('td').forEach(td => td.style.backgroundColor = cor);
        });

        body.querySelectorAll('.tr-dre-total').forEach(function(tr) {
            tr.style.backgroundColor = '#e1e1e2';
            tr.querySelectorAll('td').forEach(td => td.style.backgroundColor = '#e1e1e2');
        });

        mainContainer.appendChild(header);
        mainContainer.appendChild(body);

        if (mainContainer) pdfContainer.appendChild(mainContainer.cloneNode(true));
        pdfContainer.appendChild(document.createElement('hr'));
    });
const totaisContainer = document.createElement("div");
totaisContainer.classList.add('avoid-page-break');
totaisContainer.style.display = "flex";
totaisContainer.style.justifyContent = "space-between";  
totaisContainer.style.gap = "10px"; 
totaisContainer.style.marginTop = "20px"; 


const totalReceitasDiv = document.querySelector('#total-receitas');
if (totalReceitasDiv) {
    totaisContainer.appendChild(totalReceitasDiv.cloneNode(true));
    totaisContainer.style.fontSize = '75%'
}

const totalDespesasDiv = document.querySelector('#total-despesas');
if (totalDespesasDiv) totaisContainer.appendChild(totalDespesasDiv.cloneNode(true));

const totalDreDiv = document.querySelector('#total-dre');
if (totalDreDiv) totaisContainer.appendChild(totalDreDiv.cloneNode(true));


pdfContainer.appendChild(totaisContainer);

    _rewriteTextNodesInElement(pdfContainer);

    

    html2pdf()
        .set({
            margin: 0,
            filename: 'dre-'+nome+'.pdf',
            image: { type: 'jpeg', quality: 0.8 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            width: 1366
        })
        .from(pdfContainer)
        .save();

        body.querySelectorAll('table tr').forEach(function(tr) {
            tr.querySelectorAll('td').forEach(td => td.style.backgroundColor = 'white')
        })

        const body = item.querySelector('.accordion-body');
        body.querySelectorAll('table').forEach(function(el) {
            el.classList.add (
                'table-striped'
            )
        })
        
}

function gerarexcel(nome, data=null, hora=null, nomeEmpresa='') {
    if (nome == 'analitico') {
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
        const header = item.querySelector('.accordion-header .accordion-button span');
        if (header) title = header.textContent.trim();

        const body = item.querySelector('.accordion-body');
        if (!body) return;

        const categories = body.querySelectorAll('h5');
        categories.forEach((catElem) => {
            const category = catElem.textContent.trim();

            let table = catElem.nextElementSibling;
            while (table && table.tagName !== 'TABLE') {
                table = table.nextElementSibling;
            }
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

        let totalGeral = '';
        const totalGeralDiv = body.querySelector('div[id^="total-subtitulo-"]');
        if (totalGeralDiv) {
            totalGeral = _convertIsoToDDMMYYYY(totalGeralDiv.textContent.trim());
            if (totalGeral) allData.push([totalGeral]);
        }
        allData.push([]);
    });

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

    const ws = XLSX.utils.aoa_to_sheet(allData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Demonstrativo');

    XLSX.writeFile(wb, 'dre-' + nome + '.xlsx');
}
    
    
    
    
    
    else if(nome == 'sintetico') {
        console.log(nomeEmpresa)
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

    const headerTitle = nomeEmpresa +'  -  ' + 'Relatório demonstrativo de resultado - ' + String(nome).toUpperCase();
    const formattedDate = _formatDateToDDMMYYYY(data);
    const titulo = _formatTimeToHHMM(hora);
    const headerDateTime = (formattedDate ? formattedDate : '') + (titulo ? (formattedDate ? ' — ' : '') + 'Titulo: ' + titulo : '');
        allData.push([headerTitle]);
        if (headerDateTime.trim()) allData.push([headerDateTime]);
        allData.push([]);

        accordionItems.forEach((item) => {
            let title = '';
            const header = item.querySelector('.accordion-header .accordion-button span');
            if (header) title = header.textContent.trim();

            const body = item.querySelector('.accordion-body');
            if (!body) return;

            const table = body.querySelector('table');
            if (title) allData.push([title]);
            if (table) {
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                if (headers.length) allData.push(headers);

                const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                    Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim())
                );
                rows.forEach(row => allData.push(row));
            }

            const totalTituloDiv = body.querySelector('div[id^="total-subtitulo-"]');
            if (totalTituloDiv) {
                let totalTitulo = totalTituloDiv.textContent.trim();
                if (totalTitulo) allData.push([totalTitulo]);
            }

            allData.push([]);
        });

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

        const ws = XLSX.utils.aoa_to_sheet(allData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'DRE Sintético');
        XLSX.writeFile(wb, 'dre-sintetico.xlsx');
    }
}