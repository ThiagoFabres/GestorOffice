function formatarDataParaTexto(data) {
    if (!data) return '';
    const d = new Date(data);
    if (Number.isNaN(d.getTime())) return data;
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
}

function limparTexto(valor) {
    return (valor || '').replace(/\s+/g, ' ').trim();
}

function extrairPontos(turnoEl) {
    const linhas = [];
    const secaoPontos = [...turnoEl.querySelectorAll('.accordion-item, .card')].find(item => {
        const txt = item.querySelector('.accordion-header, button, .card-header')?.textContent || '';
        return txt.toLowerCase().includes('ponto');
    });

    if (!secaoPontos) return linhas;

    secaoPontos.querySelectorAll('table tbody tr').forEach((tr) => {
        const cells = [...tr.querySelectorAll('td')].map((td) => limparTexto(td.textContent));
        if (!cells.length) return;
        linhas.push({ horario: cells[0] || '' });
    });
    return linhas;
}

function extrairControles(turnoEl) {
    const controles = [];
    const secaoControles = [...turnoEl.querySelectorAll('.accordion-item, .card')].find(item => {
        const txt = item.querySelector('.accordion-header, button, .card-header')?.textContent || '';
        return txt.toLowerCase().includes('controles');
    });

    if (!secaoControles) return controles;

    secaoControles.querySelectorAll('table tbody tr').forEach((tr) => {
        const cells = [...tr.querySelectorAll('td')].map((td) => limparTexto(td.textContent));
        if (cells.length) controles.push({
            esperado: cells[0] || '',
            respondido: cells[1] || '',
            status: cells[2] || ''
        });
    });
    return controles;
}

function extrairPanicos(turnoEl) {
    const itens = [];
    const secaoPanicos = [...turnoEl.querySelectorAll('.accordion-item, .card')].find(item => {
        const txt = item.querySelector('.accordion-header, button, .card-header')?.textContent || '';
        return txt.toLowerCase().includes('pânico') || txt.toLowerCase().includes('panico');
    }) || turnoEl;

    secaoPanicos.querySelectorAll('.list-group-item').forEach((item) => {
        const tempo = limparTexto(item.querySelector('small')?.textContent || '');
        const texto = limparTexto(item.querySelector('span')?.textContent || '');
        const local = limparTexto(item.querySelector('a')?.textContent || 'Localização não informada');
        const link = item.querySelector('a')?.getAttribute('href') || '';
        itens.push({
            texto,
            tempo,
            local,
            link
        });
    });
    return itens;
}

function extrairRondas(turnoEl) {
    const rondas = [];
    const secaoRondas = [...turnoEl.querySelectorAll('.accordion-item, .card')].find(item => {
        const txt = item.querySelector('.accordion-header, button, .card-header')?.textContent || '';
        return txt.toLowerCase().includes('ronda');
    });

    if (!secaoRondas) return rondas;

    secaoRondas.querySelectorAll('table tbody tr').forEach((tr) => {
        const tds = [...tr.querySelectorAll('td')].map((td) => limparTexto(td.textContent));
        if (tds.length) rondas.push({ descricao: tds[0] || 'Ronda', horario: tds[1] || '' });
    });

    return rondas;
}

// NOVA FUNÇÃO: Extrai o texto da ocorrência do turno
function extrairOcorrencias(turnoEl) {
    const ocorrencias = [];
    // Busca divs de alerta que contenham ocorrências
    const alertEl = turnoEl.querySelector('.alert');
    if (alertEl) {
        let texto = limparTexto(alertEl.textContent);
        // Remove o prefixo "Ocorrência:" se presente
        texto = texto.replace(/^Ocorrência:\s*/i, '');
        if (texto) {
            ocorrencias.push({ descricao: texto });
        }
    }
    return ocorrencias;
}

function extrairEstruturaCompleta() {
    const dadosGerais = [];

    document.querySelectorAll('#accordion-segurancas > .accordion-item').forEach((segurancaItem) => {
        const nomeSeguranca = limparTexto(segurancaItem.dataset.segurancaNome || 'Segurança');
        const turnosEl = segurancaItem.querySelectorAll('[data-turno-inicio]');

        if (!turnosEl.length) return;

        const turnos = [];
        turnosEl.forEach((turnoEl) => {
            const inicio = turnoEl.dataset.turnoInicio || '';
            const fim = turnoEl.dataset.turnoFim;
            const turnoTitulo = `${inicio}${fim && fim !== 'Em andamento' ? ' até ' + fim : ' - em andamento'}`;

            const ocorrencias = extrairOcorrencias(turnoEl);

            turnos.push({
                titulo: turnoTitulo,
                resumo: {
                    pontos: turnoEl.dataset.pontos || 0,
                    panicos: turnoEl.dataset.panicos || 0,
                    controles: turnoEl.dataset.controles || 0,
                    rondas: turnoEl.dataset.rondas || 0,
                    ocorrencias: ocorrencias.length
                },
                pontos: extrairPontos(turnoEl),
                controles: extrairControles(turnoEl),
                panicos: extrairPanicos(turnoEl),
                rondas: extrairRondas(turnoEl),
                ocorrencias: ocorrencias // Adicionado ao objeto do turno
            });
        });

        dadosGerais.push({
            seguranca: nomeSeguranca,
            turnos: turnos
        });
    });

    return dadosGerais;
}

function prepararGeracaoSeguranca(tipo) {
    const nomeEmpresaEl = document.querySelector('[data-nome-empresa]');
    const empresa = nomeEmpresaEl ? nomeEmpresaEl.dataset.nomeEmpresa : 'Gestor Office';
    const dataInicial = document.querySelector('input[name="filtro_hora_inicio"]')?.value;
    const dataFinal = document.querySelector('input[name="filtro_hora_final"]')?.value;
    const nomeSeguranca = document.querySelector('select[name="filtro_seguranca"]');
    const nomeSelecionado = nomeSeguranca && nomeSeguranca.value
        ? nomeSeguranca.options[nomeSeguranca.selectedIndex]?.text || 'Segurança'
        : 'Todos';

    const periodo = [];
    if (dataInicial) periodo.push(`Início: ${formatarDataParaTexto(dataInicial)}`);
    if (dataFinal) periodo.push(`Fim: ${formatarDataParaTexto(dataFinal)}`);

    const titulo = `Relatório de Segurança - ${nomeSelecionado}`;
    const subtitulo = periodo.join(' | ') || 'Período completo';

    if (tipo === 'pdf') {
        gerarPdfSeguranca(titulo, subtitulo, empresa);
        return;
    }

    gerarExcelSeguranca(titulo, subtitulo, empresa);
}

function gerarPdfSeguranca(titulo, subtitulo, empresa) {
    if (typeof window.jspdf === 'undefined' || typeof window.jspdf.jsPDF === 'undefined') {
        alert('Biblioteca jsPDF não foi carregada.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

    const autoTableFn = pdf.autoTable || window.autoTable;
    if (!autoTableFn) {
        alert('Erro: Plugin AutoTable não encontrado.');
        return;
    }

    const margem = 12;
    const largura = pdf.internal.pageSize.getWidth() - margem * 2;
    let y = 18;

    pdf.setFontSize(16);
    pdf.setFont('helvetica', 'bold');
    pdf.text(empresa || 'Gestor Office', margem, y);
    y += 7;

    pdf.setFontSize(12);
    pdf.text(titulo, margem, y);
    y += 6;

    pdf.setFontSize(9);
    pdf.setFont('helvetica', 'normal');
    pdf.text(subtitulo, margem, y);
    y += 8;

    const estrutura = extrairEstruturaCompleta();

    if (!estrutura.length) {
        pdf.setFontSize(10);
        pdf.text('Nenhum dado encontrado para o período informado.', margem, y);
        pdf.save('relatorio_seguranca.pdf');
        return;
    }

    estrutura.forEach((seg) => {
        if (y > pdf.internal.pageSize.getHeight() - 35) {
            pdf.addPage();
            y = 15;
        }

        // Cabeçalho Principal do Segurança
        pdf.setFillColor(40, 50, 60);
        pdf.rect(margem, y, largura, 7, 'F');
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(255, 255, 255);
        pdf.text(`SEGURANÇA: ${seg.seguranca.toUpperCase()}`, margem + 3, y + 5);
        pdf.setTextColor(0, 0, 0);
        y += 10;

        seg.turnos.forEach((t) => {
            if (y > pdf.internal.pageSize.getHeight() - 35) {
                pdf.addPage();
                y = 15;
            }

            // Faixa do Turno (Cinza)
            pdf.setFillColor(230, 235, 240);
            pdf.rect(margem, y, largura, 10, 'F');
            pdf.setFontSize(9);
            pdf.setFont('helvetica', 'bold');
            pdf.text(`TURNO: ${t.titulo}`, margem + 3, y + 4.5);
            pdf.setFont('helvetica', 'normal');
            pdf.text(`Pontos (${t.resumo.pontos}) | Controles (${t.resumo.controles}) | Pânicos (${t.resumo.panicos}) | Rondas (${t.resumo.rondas}) | Ocorrências (${t.resumo.ocorrencias})`, margem + 3, y + 8.5);
            y += 12;

            // 1. Tabela de Ocorrências (Cabeçalho Roxo/Azul Escuro)
            if (t.ocorrencias && t.ocorrencias.length) {
                if (y > pdf.internal.pageSize.getHeight() - 25) { pdf.addPage(); y = 15; }
                autoTableFn.call(pdf, {
                    startY: y,
                    margin: { left: margem, right: margem },
                    head: [['Descrição da Ocorrência']],
                    body: t.ocorrencias.map(o => [o.descricao]),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 1.5 },
                    headStyles: { fillColor: [102, 16, 242], textColor: [255, 255, 255], fontStyle: 'bold' },
                });
                y = pdf.lastAutoTable.finalY + 3;
            }

            if (t.controles.length) {
                if (y > pdf.internal.pageSize.getHeight() - 25) { pdf.addPage(); y = 15; }
                autoTableFn.call(pdf, {
                    startY: y,
                    margin: { left: margem, right: margem },
                    head: [['Horário esperado', 'Horário respondido', 'Status']],
                    body: t.controles.map(c => [c.esperado, c.respondido, c.status]),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 1.5 },
                    headStyles: { fillColor: [13, 202, 240], textColor: [255, 255, 255], fontStyle: 'bold' },
                });
                y = pdf.lastAutoTable.finalY + 3;
            }

            // 2. Tabela de Pânicos (Cabeçalho Vermelho)
            if (t.panicos.length) {
                if (y > pdf.internal.pageSize.getHeight() - 25) { pdf.addPage(); y = 15; }
                autoTableFn.call(pdf, {
                    startY: y,
                    margin: { left: margem, right: margem },
                    head: [['Tempo', 'Localização']],
                    body: t.panicos.map(p => [p.tempo, p.local]),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 1.5 },
                    headStyles: { fillColor: [220, 53, 69], textColor: [255, 255, 255], fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: 50 },
                        1: { cellWidth: largura - 50 }
                    }
                });
                y = pdf.lastAutoTable.finalY + 3;
            }

            // 3. Tabela de Rondas
            if (t.rondas.length) {
                if (y > pdf.internal.pageSize.getHeight() - 25) { pdf.addPage(); y = 15; }
                autoTableFn.call(pdf, {
                    startY: y,
                    margin: { left: margem, right: margem },
                    head: [['Descrição', 'Horário']],
                    body: t.rondas.map(r => [r.descricao, r.horario]),
                    theme: 'grid',
                    styles: { fontSize: 8, cellPadding: 1.5 },
                    headStyles: { fillColor: [13, 110, 253], textColor: [255, 255, 255], fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: largura - 50 },
                        1: { cellWidth: 50 }
                    }
                });
                y = pdf.lastAutoTable.finalY + 3;
            }

            y += 3;
        });
    });

    // Numeração de Páginas
    const totalPages = pdf.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
        pdf.setPage(i);
        const pageHeight = pdf.internal.pageSize.height;
        pdf.setFontSize(8);
        pdf.setTextColor(100);
        pdf.text('Gestor Office — Relatório de Segurança', margem, pageHeight - 8);
        pdf.text(`Página ${i} de ${totalPages}`, pdf.internal.pageSize.getWidth() - margem - 20, pageHeight - 8);
    }

    pdf.save('relatorio_seguranca.pdf');
}

function gerarExcelSeguranca(titulo, subtitulo, empresa) {
    if (typeof XLSX === 'undefined') {
        alert('Biblioteca XLSX (xlsx-js-style) não foi carregada.');
        return;
    }

    const estrutura = extrairEstruturaCompleta();
    const dadosAoA = [];
    const rowsMeta = [];

    // Estilos de Células
    const estEmpresa = { font: { bold: true, sz: 14, name: 'Arial' } };
    const estTitulo = { font: { bold: true, sz: 12, name: 'Arial' } };
    const estSubtitulo = { font: { sz: 10, color: { rgb: '555555' }, name: 'Arial' } };

    const estSeguranca = {
        fill: { fgColor: { rgb: '28323C' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 11, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estTurno = {
        fill: { fgColor: { rgb: 'E6EBF0' } },
        font: { bold: true, color: { rgb: '000000' }, sz: 10, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estHeaderOcorrencia = {
        fill: { fgColor: { rgb: '6610F2' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estHeaderControle = {
        fill: { fgColor: { rgb: '0DCAF0' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estHeaderPanico = {
        fill: { fgColor: { rgb: 'DC3545' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estHeaderPonto = {
        fill: { fgColor: { rgb: '0D6EFD' } },
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 9, name: 'Arial' },
        alignment: { vertical: 'center' }
    };

    const estDado = { font: { sz: 9, name: 'Arial' } };

    // Função auxiliar para aplicar estilo a uma linha inteira
    function criarLinhaEstilizada(valores, estilo, nivel = 0) {
        rowsMeta.push({ level: nivel });
        return valores.map(val => ({ v: val || '', s: estilo }));
    }

    // Cabeçalho do Relatório
    dadosAoA.push([{ v: empresa, s: estEmpresa }]);
    rowsMeta.push({ level: 0 });
    dadosAoA.push([{ v: titulo, s: estTitulo }]);
    rowsMeta.push({ level: 0 });
    dadosAoA.push([{ v: subtitulo, s: estSubtitulo }]);
    rowsMeta.push({ level: 0 });
    dadosAoA.push([]);
    rowsMeta.push({ level: 0 });

    if (!estrutura.length) {
        dadosAoA.push([{ v: 'Nenhum dado encontrado para o período informado.', s: estDado }]);
        rowsMeta.push({ level: 0 });
    } else {
        estrutura.forEach((seg) => {
            dadosAoA.push(criarLinhaEstilizada([`${seg.seguranca.toUpperCase()}`, '', '', '', ''], estSeguranca));

            seg.turnos.forEach((t) => {
                dadosAoA.push(criarLinhaEstilizada([
                    '',
                    `TURNO: ${t.titulo}`,
                    `Resumo: Pontos (${t.resumo.pontos}) | Controles (${t.resumo.controles}) | Pânicos (${t.resumo.panicos}) | Rondas (${t.resumo.rondas}) | Ocorrências (${t.resumo.ocorrencias})`,
                    '',
                    ''
                ], estTurno));

                // 1. Ocorrências
                if (t.ocorrencias && t.ocorrencias.length) {
                    dadosAoA.push(criarLinhaEstilizada(['', '', 'Descrição da Ocorrência', ''], estHeaderOcorrencia, 1));
                    t.ocorrencias.forEach((o) => {
                        dadosAoA.push(criarLinhaEstilizada(['', '', o.descricao, ''], estDado, 1));
                    });
                }

                if (t.controles.length) {
                    dadosAoA.push(criarLinhaEstilizada(['', '', 'Horário esperado', 'Horário respondido', 'Status'], estHeaderControle, 1));
                    t.controles.forEach((c) => {
                        dadosAoA.push(criarLinhaEstilizada(['', '', c.esperado, c.respondido, c.status], estDado, 1));
                    });
                }

                // 3. Pânicos
                if (t.panicos.length) {
                    dadosAoA.push(criarLinhaEstilizada(['', '', 'Tempo', 'Localização', 'Link'], estHeaderPanico, 1));
                    t.panicos.forEach((p) => {
                        dadosAoA.push(criarLinhaEstilizada(['', '', p.tempo, p.local, p.link], estDado, 1));
                    });
                }

                // 4. Rondas
                if (t.rondas.length) {
                    dadosAoA.push(criarLinhaEstilizada(['', '', 'Descrição', 'Horário', ''], estHeaderPonto, 1));
                    t.rondas.forEach((r) => {
                        dadosAoA.push(criarLinhaEstilizada(['', '', r.descricao, r.horario, ''], estDado, 1));
                    });
                }

                dadosAoA.push([]);
                rowsMeta.push({ level: 0 });
            });
        });
    }

    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(dadosAoA);

    worksheet['!cols'] = [
        { wch: 22 },
        { wch: 35 },
        { wch: 35 },
        { wch: 35 },
        { wch: 25 },
        { wch: 30 }
    ];

    worksheet['!rows'] = rowsMeta.map((m) => ({
        hpt: 20,
        level: m.level
    }));

    XLSX.utils.book_append_sheet(workbook, worksheet, 'Relatório Segurança');
    XLSX.writeFile(workbook, 'relatorio_seguranca.xlsx');
}