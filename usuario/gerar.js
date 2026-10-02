function formatarData(data){
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
}

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
       CABEÇALHO FIXO (PRIMEIRA PÁGINA)
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

    // Renderiza a tabela inteira e deixa o AutoTable gerenciar a paginação
    doc.autoTable({
        head: head,
        body: body,
        startY: y + 2,
        theme: 'striped',
        rowPageBreak: 'avoid',
        tableWidth: '100%',
        showHead: 'everyPage', // Repete o cabeçalho da tabela em todas as páginas automaticamente

        styles: {
            fontSize: modoReducao ? 12 : 8.5,
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
            top: 15,
            bottom: 15,
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

            // Garante o alinhamento à direita para a coluna de valor
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
                    data.cell.styles.fontSize = modoReducao ? 12 : 8.5; // Corrigido erro de maiúscula
                }

                if (data.row.index % 2 === 1) {
                    data.cell.styles.fillColor = [245, 245, 245];
                }
            }

        }
    });

    /* -------------------------
       PAGINAÇÃO (NUMERAÇÃO)
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

function gerarexcel(nome, nomeEmpresa = '') {
    try {
        const tabela = document.querySelector('#tabela-pdf');

        if (!tabela) {
            alert("Tabela não encontrada!");
            return;
        }

        /* -------------------------
           AUXILIARES
        ------------------------- */

        const limpar = (t) => String(t ?? '')
            .replace(/R\$\s?/g, '')
            .replace(/\s+/g, ' ')
            .trim();

        // yyyy-mm-dd -> dd/mm/yyyy (só quando o texto inteiro é uma data ISO)
        const fmtData = (t) => {
            const m = String(t).match(/^(\d{4})-(\d{2})-(\d{2})$/);
            return m ? `${m[3]}/${m[2]}/${m[1]}` : t;
        };

        // "1.234,56" -> 1234.56 (retorna null se não for valor monetário)
        const paraNumero = (t) => {
            const s = String(t).trim();
            if (!/^-?\d{1,3}(\.\d{3})*(,\d+)?$|^-?\d+(,\d+)?$/.test(s)) return null;
            const n = Number(s.replace(/\./g, '').replace(',', '.'));
            return isNaN(n) ? null : n;
        };

        // Lê as células da linha. O colspan só é respeitado quando pedido
        // (linha de totais); no corpo cada <td> vira exatamente uma coluna.
        const lerLinha = (tr, seletor, respeitarColspan = false) => {
            const out = [];
            tr.querySelectorAll(seletor).forEach((c) => {
                out.push({
                    texto: limpar(c.textContent),
                    acao: c.classList.contains('td-acoes')
                });
                if (respeitarColspan) {
                    for (let i = 1; i < (c.colSpan || 1); i++) {
                        out.push({ texto: '', acao: false });
                    }
                }
            });
            return out;
        };

        /* -------------------------
           EXTRAIR TABELA
        ------------------------- */

        const linhasHead = [...tabela.querySelectorAll('thead tr')]
            .map((tr) => lerLinha(tr, 'th'))
            .filter((r) => r.some((c) => c.texto !== ''));

        const cabecalho = (linhasHead[linhasHead.length - 1] || []).map((c) => c.texto);

        const linhasBody = [];
        const linhasTotal = [];

        tabela.querySelectorAll('tbody tr, tfoot tr').forEach((tr) => {
            const ehTotal = tr.id === 'tr-totais' || tr.parentElement.tagName.toLowerCase() === 'tfoot';
            const cells = lerLinha(tr, 'td', ehTotal);

            if (!cells.some((c) => c.texto !== '')) return;
            (ehTotal ? linhasTotal : linhasBody).push(cells);
        });

        /* -------------------------
           COLUNAS A MANTER
        ------------------------- */

        const totalCols = Math.max(
            cabecalho.length,
            ...linhasBody.map((r) => r.length),
            ...linhasTotal.map((r) => r.length),
            0
        );

        // Descarta a coluna de ações (botões editar/excluir etc.)
        const excluidas = new Set();
        for (let i = 0; i < totalCols; i++) {
            if (/^a[çc][õo]es?$/i.test(cabecalho[i] || '')) excluidas.add(i);
        }
        [...linhasBody, ...linhasTotal].forEach((r) =>
            r.forEach((c, i) => { if (c.acao) excluidas.add(i); })
        );

        const indices = [];
        for (let i = 0; i < totalCols; i++) {
            if (!excluidas.has(i)) indices.push(i);
        }

        // Colunas monetárias: qualquer cabeçalho com "valor" (VALOR, VALOR.PARC, VALOR.PAG...)
        const colunasValor = new Set(
            indices.filter((i) => /valor/i.test(cabecalho[i] || ''))
        );

        /* -------------------------
           MONTAR LINHAS
        ------------------------- */

        const converter = (texto, colunaOriginal) => {
            const t = fmtData(texto);
            if (colunasValor.has(colunaOriginal)) {
                const n = paraNumero(t);
                if (n !== null) return n;
            }
            return t;
        };

        const dados = [];

        dados.push(indices.map((i) => cabecalho[i] ?? ''));

        linhasBody.forEach((r) => {
            dados.push(indices.map((i) => converter(r[i]?.texto ?? '', i)));
        });

        if (linhasTotal.length) {
            dados.push([]);
            linhasTotal.forEach((r) => {
                dados.push(indices.map((i) => converter(r[i]?.texto ?? '', i)));
            });
        }

        /* -------------------------
           CABEÇALHO COM FILTROS
        ------------------------- */

        const headerFiltros = [];

        const titleEl = document.querySelector('.card .card-header h3');
        const titleText = titleEl ? titleEl.textContent.trim() : `Contas a ${nome}`;

        headerFiltros.push([`${nomeEmpresa} - ${titleText}`]);
        headerFiltros.push([]);

        const di = document.querySelector('#filtro_data_inicial')?.value;
        const df = document.querySelector('#filtro_data_final')?.value;
        const docNome = document.querySelector('#filtro_nome')?.value;

        if (di && df) {
            headerFiltros.push(['Período', `${fmtData(di)} até ${fmtData(df)}`]);
        } else if (di) {
            headerFiltros.push(['Data Inicial', fmtData(di)]);
        } else if (df) {
            headerFiltros.push(['Data Final', fmtData(df)]);
        }

        if (docNome) headerFiltros.push(['Documento', docNome]);

        headerFiltros.push([]);

        const aoaFinal = headerFiltros.concat(dados);

        /* -------------------------
           PLANILHA
        ------------------------- */

        const ws = XLSX.utils.aoa_to_sheet(aoaFinal);

        // Formato monetário nas colunas de valor (continuam somáveis no Excel)
        indices.forEach((colOriginal, c) => {
            if (!colunasValor.has(colOriginal)) return;

            for (let r = headerFiltros.length; r < aoaFinal.length; r++) {
                const addr = XLSX.utils.encode_cell({ r: r, c: c });
                if (ws[addr] && ws[addr].t === 'n') {
                    ws[addr].z = '#,##0.00';
                }
            }
        });

        // Largura das colunas conforme o conteúdo (ignora o título e os filtros)
        ws['!cols'] = indices.map((_, c) => {
            const maior = dados.reduce((max, row) => {
                const len = String(row[c] ?? '').length;
                return len > max ? len : max;
            }, 10);
            return { wch: Math.min(maior + 2, 50) };
        });

        const nomeAba = (titleText || 'Relatorio')
            .replace(/[\\\/\?\*\[\]:]/g, '')
            .slice(0, 31);

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, nomeAba || 'Relatorio');

        XLSX.writeFile(wb, 'relatorio.xlsx');

    } catch (error) {
        console.error('Erro ao gerar Excel:', error);
        alert('Erro ao gerar arquivo Excel. Verifique o console para mais detalhes.');
    }
}