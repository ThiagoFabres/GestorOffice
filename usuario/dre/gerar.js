function gerarpdf(nome) {
	// Select all accordion items (accordion-item class)
	const accordionItems = document.querySelectorAll('.accordion-item');
	if (accordionItems.length === 0) {
		alert('Nenhum conteúdo para exportar.');
		return;
	}

	// Create a container to clone all accordion contents
	const pdfContainer = document.createElement('div');
	pdfContainer.style.padding = '20px';

	accordionItems.forEach(item => {
		// Clone only the visible content of each accordion
		const header = item.querySelector('.accordion-header');
		const body = item.querySelector('.accordion-body');
		if (header) pdfContainer.appendChild(header.cloneNode(true));
		if (body) pdfContainer.appendChild(body.cloneNode(true));
		// Add a separator between accordions
		pdfContainer.appendChild(document.createElement('hr'));
	});

    let totalReceitasDiv = document.querySelector('#total-receitas');
        if (totalReceitasDiv) pdfContainer.appendChild(totalReceitasDiv.cloneNode(true));
    let totalDespesasDiv = document.querySelector('#total-despesas');
        if (totalDespesasDiv) pdfContainer.appendChild(totalDespesasDiv.cloneNode(true));
    let totalDreDiv = document.querySelector('#total-dre');
        if (totalDreDiv) pdfContainer.appendChild(totalDreDiv.cloneNode(true));

	// Use html2pdf to generate PDF
	html2pdf()
		.set({
			margin: 10,
			filename: 'dre-'+nome+'.pdf',
			image: { type: 'jpeg', quality: 0.98 },
			html2canvas: { scale: 10 },
			jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
		})
		.from(pdfContainer)
		.save();
}

function gerarexcel(nome) {
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

            // Get table headers and rows
            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr =>
                Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim())
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
            if (saldoSubtitulo) allData.push([saldoSubtitulo]);

            // Linha em branco entre categorias
            allData.push([]);
        });

        // Find the "Total Geral" div in this accordion-body
        let totalGeral = '';
        const totalGeralDiv = body.querySelector('div[id^="total-subtitulo-"]');
        if (totalGeralDiv) {
            totalGeral = totalGeralDiv.textContent.trim();
            if (totalGeral) allData.push([totalGeral]);
        }
        allData.push([]);
    });

    // Totais finais
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

    // Create worksheet and workbook
    const ws = XLSX.utils.aoa_to_sheet(allData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Demonstrativo');

    // Export to Excel file
    XLSX.writeFile(wb, 'dre-' + nome + '.xlsx');
}
    
    
    
    
    
    
    
    else if(nome == 'sintetico') {
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
        let totalReceitasDiv = body.querySelector('#total-receitas');
            if (totalReceitasDiv) {
                let totalReceitas = totalReceitasDiv.textContent.trim();
                if (totalReceitas) allData.push([totalReceitas]);
            }
        let totalDespesasDiv = body.querySelector('#total-despesas');
            if (totalDespesasDiv) {
                let totalDespesas = totalDespesasDiv.textContent.trim();
                if (totalDespesas) allData.push([totalDespesas]);
            }

        let totalDreDiv = body.querySelector('#total-dre');
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
    
