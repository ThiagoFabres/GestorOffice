async function gerarpdf(nome, nomeEmpresa = '', estilo = 'completo') {

    const tabela = document.querySelector('#tabela-pdf');
    const modoReducao = estilo === 'reduzido';

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
        `Contas a ${nome}`;

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

    function limitarDescricao(texto, maximo = 80) {
        if (texto === null || texto === undefined) return '';

        const valor = String(texto)
            .replace(/\s+/g, ' ')
            .trim();

        if (!valor) return '';
        if (valor.length <= maximo) return valor;

        return `${valor.slice(0, maximo).trimEnd()}…`;
    }

    tabela.querySelectorAll("thead tr").forEach(tr => {
        const row = [];

        tr.querySelectorAll("th").forEach((th, index, arr) => {
            if (index < arr.length) {
                row.push(th.innerText.trim());
            }
        });

        if (row.some(valor => String(valor).trim() !== '')) {
            head.push(row);
        }
    });

    // Encontra o índice das colunas 'Descrição' e 'Valor'
    const descricaoIndex = head[0]?.findIndex((titulo) => /descri[çc]ao/i.test(String(titulo || ''))) ?? -1;
    let valorIndex = head[0]?.findIndex((titulo) => /valor/i.test(String(titulo || ''))) ?? -1;

    // Se não encontrar pelo nome 'VALOR', assume que é a última coluna
    if (valorIndex === -1 && head[0]?.length) {
        valorIndex = head[0].length - 1;
    }

    tabela.querySelectorAll("tbody tr").forEach(tr => {
        const row = [];

        tr.querySelectorAll("td").forEach((td, index, arr) => {
            if (index < arr.length) {
                let valor = td.textContent.replace('R$', '').replace(/\s+/g, ' ').trim();

                if (descricaoIndex !== -1 && index === descricaoIndex) {
                    valor = limitarDescricao(valor, 34);
                }

                row.push(valor);
            }
        });

        if (row.some(valor => String(valor).trim() !== '')) {
            body.push(row);
        }
    });

    /* -------------------------
       TABELA & ESTILOS DE COLUNA
    ------------------------- */

    const columnStylesConfig = {};

    if (descricaoIndex !== -1) {
        columnStylesConfig[descricaoIndex] = {
            overflow: 'linebreak',
            cellWidth: 'auto',
            halign: 'left',
            minCellHeight: 5,
            noWrap: true,
            fontSize: 8.5
        };
    }

    if (valorIndex !== -1) {
        columnStylesConfig[valorIndex] = {
            halign: 'right'
        };
    }

    const alturaDisponivel = doc.internal.pageSize.getHeight() - y - 18;
    const alturaLinha = 6.2;
    const linhasPorPagina = Math.max(10, Math.floor(alturaDisponivel / alturaLinha));

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
            rowPageBreak: 'avoid',
            tableWidth: '100%',

            styles: {
                fontSize: modoReducao ? 10 : 8.5,
                cellPadding: 1,
                overflow: 'linebreak',
                halign: modoReducao ? 'left' : 'center',
                valign: 'middle',
                lineWidth: 0.1,
                lineColor: [230, 230, 230],
                cellHeight: 5
            },

            headStyles: {
                fillColor: [206, 206, 206],
                textColor: 0,
                fontStyle: 'bold',
                cellPadding: 1
            },

            alternateRowStyles: {
                fillColor: [255, 255, 255]
            },

            columnStyles: columnStylesConfig,

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
                    data.cell.styles.fillColor = [220, 220, 220];
                }

                // Garante o alinhamento à direita para a coluna de valor (cabeçalho e corpo)
                if (valorIndex !== -1 && data.column.index === valorIndex) {
                    data.cell.styles.halign = 'right';
                }

                if (data.section === 'body') {
                    if (data.column.index === descricaoIndex) {
                        const limite = modoReducao ? 100 : 80;
                        data.cell.text = limitarDescricao(data.cell.text, limite);
                        data.cell.styles.overflow = 'linebreak';
                        data.cell.styles.halign = 'left';
                        data.cell.styles.noWrap = true;
                        data.cell.styles.fontSize = 8.5;
                    }

                    if (data.row.index % 2 === 1) {
                        data.cell.styles.fillColor = [245, 245, 245];
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

    doc.save(`relatorio.pdf`);
}