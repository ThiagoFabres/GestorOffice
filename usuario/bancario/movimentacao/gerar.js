async function gerarpdf(nome, nomeEmpresa = '') {
    console.log('Rendering');
    const tabela = document.querySelector('#tabela-pdf');

    if (!tabela) {
        alert("Tabela não encontrada!");
        return;
    }

    const { jsPDF } = window.jspdf;

    const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a4"
    });

    const pageWidth = doc.internal.pageSize.getWidth();

    /* -------------------------
       CABEÇALHO
    ------------------------- */

    const titulo =
        document.querySelector('.card .card-header h3')?.textContent ||
        `Relatório de Movimentação Bancária`;

    doc.setFontSize(16);
    doc.setFont(undefined, "bold");
    doc.text(`${nomeEmpresa} - ${titulo}`, 10, 10);

    doc.setFontSize(10);
    doc.setFont(undefined, "normal");

    let y = 16;

    const filtros = [
        ['Período', '#filtro_data_inicial', '#filtro_data_final'],
        ['Documento', '#filtro_nome']
    ];

    filtros.forEach(f => {

        if (f.length === 3) {

            const di = document.querySelector(f[1])?.value;
            const df = document.querySelector(f[2])?.value;

            if (di && df) {
                doc.text(`${f[0]}: ${formatarData(di)} até ${formatarData(df)}`, 10, y);
                y += 5;
            }

        } else {

            const val = document.querySelector(f[1])?.value;

            if (val) {
                doc.text(`${f[0]}: ${val}`, 10, y);
                y += 5;
            }

        }

    });

    /* -------------------------
       EXTRAIR TABELA
    ------------------------- */

    const head = [];
    const body = [];

    tabela.querySelectorAll("thead tr").forEach(tr => {

        const row = [];

        tr.querySelectorAll("th").forEach((th, index, arr) => {

            if (index < arr.length) { // remove últimas 4 colunas
                row.push(th.innerText.trim());
            }

        });

        head.push(row);

    });

    tabela.querySelectorAll("tbody tr").forEach(tr => {

        const row = [];

        tr.querySelectorAll("td").forEach((td, index, arr) => {

            if (index < arr.length) {
                row.push(td.textContent.replace('R$', '').trim());
            }


        });

        body.push(row);

    });

    /* -------------------------
       TABELA
    ------------------------- */

    const linhasPorPagina = 22;

for (let i = 0; i < body.length; i += linhasPorPagina) {

    const chunk = body.slice(i, i + linhasPorPagina);

    if (i !== 0) {
        doc.addPage();
    }

    doc.autoTable({
        head: head,
        body: chunk,
        startY: y + 2,
        theme: 'striped',

        styles: {
            fontSize: 8,
            cellPadding: 2,
            halign: "center",
            valign: "middle"
        },

        headStyles: {
            fillColor: [206,206,206],
            textColor: 0,
            fontStyle: "bold"
        },

        alternateRowStyles: {
            fillColor: [255,255,255]
        },

        margin: {
            left: 8,
            right: 8
        },

        didParseCell: function (data) {

            if (data.cell.raw?.classList?.contains('td-acoes')) {
                data.cell.text = '';
            }

            if (data.row.raw?.id === 'tr-totais') {
                data.cell.styles.fontStyle = 'bold';
                data.cell.styles.fillColor = [220,220,220];
            }

            if (data.section === 'body') {

                const grupo = Math.floor(data.row.index / 2);

                if (grupo % 2 === 1) {
                    data.cell.styles.fillColor = [245,245,245];
                }

            }

        }

    });

}
    /* -------------------------
       PAGINAÇÃO
    ------------------------- */

    const totalPages = doc.internal.getNumberOfPages();

    doc.setFontSize(9);

    for (let i = 1; i <= totalPages; i++) {

        doc.setPage(i);

        doc.text(
            `Página ${i} de ${totalPages}`,
            pageWidth - 10,
            10,
            { align: 'right' }
        );

    }

    /* -------------------------
       SALVAR
    ------------------------- */

    doc.save(`contas_a_${nome}.pdf`);

}

/* -------------------------
   UTIL
------------------------- */

function formatarData(data){

    const [ano, mes, dia] = data.split('-');

    return `${dia}/${mes}/${ano}`;

}















function gerarexcel(nome, nomeEmpresa = '') {
    try {
        var tabela = document.querySelector('#tabela-pdf')
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
        var titleText = titleEl ? titleEl.textContent.trim() : ('Relatório de Movimentação Bancária');
        
        headerFiltros.push([nomeEmpresa + ' - ' + titleText]);
        headerFiltros.push([]); // linha vazia

        // Coleta os filtros aplicados
        var di = document.querySelector('#filtro_data_inicial');
        var df = document.querySelector('#filtro_data_final');
        var titulo = getSelectValue('select[name="filtro_titulo"]') || getSelectValue('#titulo-filtro');
        var subtitulo = getSelectValue('select[name="filtro_subtitulo"]') || getSelectValue('#subtitulo-filtro');
        var tipo = getSelectValue('select[name="filtro_tipo"]');
        var conta = getSelectValue('select[name="filtro_conta"]');
        var conciliado = document.querySelector('input[name="filtro_conciliado"]');
        var opcao = getRadioValue('opcao_filtro');
        var por = getRadioValue('filtro_por');

        // Adiciona saldo inicial se disponível
        var saldoInicialEl = document.querySelector('#saldo-inicial-pdf');
        if (saldoInicialEl) {
            var saldoInicial = saldoInicialEl.textContent.replace(/Saldo Inicial: R\$\s?/g, '').trim();
            if (saldoInicial) {
                headerFiltros.push(['Saldo Inicial', saldoInicial]);
            }
        }

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
        
        if (tipo && tipo !== 'Selecione') headerFiltros.push(['Tipo', tipo]);
        if (conta && conta !== 'Selecione') headerFiltros.push(['Conta', conta]);
        if (titulo && titulo !== 'Selecione') headerFiltros.push(['Título', titulo]);
        if (subtitulo && subtitulo !== 'Selecione') headerFiltros.push(['Subtítulo', subtitulo]);
        if (conciliado && conciliado.checked) headerFiltros.push(['Conciliado', 'Sim']);
        if (opcao) headerFiltros.push(['Opção', opcao]);
        if (por) headerFiltros.push(['Filtro por', por]);

        // Adiciona saldo final se disponível
        var saldoFinalEl = document.querySelector('#saldo-final-pdf strong');
        if (saldoFinalEl) {
            headerFiltros.push([]);
            headerFiltros.push([saldoFinalEl.textContent.trim()]);
        }

        headerFiltros.push([]); // linha vazia
        headerFiltros.push([]); // linha vazia

        // Combina header com dados
        var aoaFinal = headerFiltros.concat(dados);

        // Cria workbook
        var ws = XLSX.utils.aoa_to_sheet(aoaFinal);
        
        // Define largura das colunas
        var colWidths = [];
        for (var i = 0; i < (dados[0] ? dados[0].length : 10); i++) {
            colWidths.push({ wch: 18 });
        }
        ws['!cols'] = colWidths;

        // Cria workbook e adiciona worksheet
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Relatório de " + nome);

        // Gera arquivo
        var filename = "relatorio_" + nome + ".xlsx";
        XLSX.writeFile(wb, filename);

    } catch (error) {
        console.error('Erro ao gerar Excel:', error);
        alert('Erro ao gerar arquivo Excel. Verifique o console para mais detalhes.');
    }
}