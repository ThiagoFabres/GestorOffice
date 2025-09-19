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
    container.appendChild(tabela);

    var opt = {
        margin: [5, 5, 5, 5],
        filename: 'contas_a_'+nome+'.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scale: 2,
            scrollY: 0,
            useCORS: true
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'landscape'
        },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

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

    var wb = XLSX.utils.table_to_book(tabela, {sheet: "Contas a" + nome});
    XLSX.writeFile(wb, "contas_a_"+nome+".xlsx");
}