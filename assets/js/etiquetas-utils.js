// ======================================================
// Utilitarios compartilhados de geracao de etiquetas
// (usado por OPE PRECOS ALTERADOS e OPE GERADOR ETIQUETAS)
// ======================================================

function ajustarEAN13Etiqueta(codigo) {

    codigo = (codigo || '').toString().replace(/\D/g, '');

    if (codigo.length > 13) {
        codigo = codigo.substring(0, 13);
    }

    if (codigo.length === 12) {
        let sum = 0;
        for (let i = 0; i < 12; i++) {
            sum += parseInt(codigo[i]) * (i % 2 === 0 ? 1 : 3);
        }
        let check = (10 - (sum % 10)) % 10;
        codigo = codigo + check.toString();
    }

    return codigo;
}

// lista: array de { codigobarra, descricao, preco_clube, limitacao_clube,
//                    preco_max, limitacao_max, preco_varejo, data_registro }
function gerarEtiquetasPDF(lista) {

    if (!lista || !lista.length) {
        toastr.warning('Nenhum produto encontrado para gerar etiquetas.', 'Etiquetas');
        return;
    }

    let etiquetasHtml = '';
    let scriptsBarcode = '';

    lista.forEach((val, idx) => {

        let codigoBarra = Array.isArray(val.codigobarra) ? val.codigobarra[0] : val.codigobarra;
        let codigoAjustado = ajustarEAN13Etiqueta(codigoBarra);
        let codigoValido = codigoAjustado.length === 13;
        let descricao = (val.descricao || '').replace(/"/g, '&quot;');

        let partesDataHoraEtq = (val.data_registro || '').split(' ');
        let dataHoraVertical = `${Utils.formatarData(partesDataHoraEtq[0]) || ''} ${partesDataHoraEtq[1] || ''}`.trim();

        etiquetasHtml += `
            <div class="etiqueta">
                <div class="etiqueta-data">${dataHoraVertical}</div>
                <div class="etiqueta-conteudo">
                    <div class="descricao">${descricao}</div>
                    ${codigoValido
                        ? `<svg id="barcode-${idx}"></svg>`
                        : `<div class="sem-codigo">Sem código de barras válido</div>`}
                    <div class="precos">
                        <span>Clube e Super: ${Utils.formatarMoeda(val.preco_clube)}${val.limitacao_clube ? ` (Lim: ${val.limitacao_clube}/${val.limitacao_clube * 2})` : ''}</span>
                        <span>Clube Max: ${Utils.formatarMoeda(val.preco_max)}${val.limitacao_max ? ` (Lim: ${val.limitacao_max})` : ''}</span>
                        <span>Varejo: ${Utils.formatarMoeda(val.preco_varejo)}</span>
                    </div>
                </div>
            </div>
        `;

        if (codigoValido) {
            scriptsBarcode += `
                try {
                    JsBarcode("#barcode-${idx}", "${codigoAjustado}", {
                        format: "EAN13",
                        displayValue: true,
                        fontSize: 11,
                        height: 31,
                        width: 1.56,
                        margin: 2.4
                    });
                } catch (e) {
                    console.error("Falha ao gerar codigo de barras do item ${idx}:", e);
                }
            `;
        }
    });

    let win = window.open('', '', 'width=900,height=700');

    win.document.write(`
        <html>
        <head>
            <script src="${Utils.appBase()}/assets/plugins/jsbarcode/JsBarcode.all.min.js"><\/script>
            <style>
                @page { size: A4; margin: 6mm; }
                body { margin: 0; font-family: Arial; }
                .etiquetas-wrap {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    grid-auto-rows: min-content;
                    column-gap: 3mm;
                    row-gap: 1mm;
                }
                .etiqueta {
                    box-sizing: border-box;
                    width: 100%;
                    text-align: center;
                    border: 1px dashed #999;
                    padding: 1.5mm;
                    page-break-inside: avoid;
                    display: flex;
                    flex-direction: row;
                    align-items: stretch;
                    gap: 1mm;
                }
                .etiqueta-data {
                    writing-mode: vertical-rl;
                    text-orientation: mixed;
                    transform: rotate(180deg);
                    font-size: 7px;
                    color: #555;
                    white-space: nowrap;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .etiqueta-conteudo {
                    flex: 1;
                    min-width: 0;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: flex-start;
                    gap: 0.5mm;
                    border-left: 1px dashed #ccc;
                    padding-left: 1mm;
                }
                .etiqueta svg {
                    display: block;
                    margin: 0 auto;
                    max-width: 100%;
                    height: auto;
                }
                .sem-codigo {
                    font-size: 9px;
                    color: #c00;
                    padding: 8px 0;
                }
                .descricao {
                    font-size: 9px;
                    font-weight: bold;
                    word-break: break-word;
                }
                .precos {
                    display: flex;
                    flex-direction: column;
                    font-size: 9px;
                    line-height: 1.15;
                }
            </style>
        </head>
        <body>
            <div class="etiquetas-wrap">
                ${etiquetasHtml}
            </div>
            <script>
                ${scriptsBarcode}
                window.onafterprint = function() { window.close(); };
                setTimeout(function() { window.print(); }, 300);
            <\/script>
        </body>
        </html>
    `);

    win.document.close();
}
