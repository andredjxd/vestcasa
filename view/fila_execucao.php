
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Fila de Execução</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f9;
    padding: 20px;
}

.card {
    background: #fff;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.barra {
    width: 100%;
    background: #e0e0e0;
    border-radius: 20px;
    overflow: hidden;
    height: 20px;
    margin-top: 8px;
}

.progresso {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #28a745, #00c851);
    transition: width 0.5s ease;
    text-align: center;
    color: white;
    font-size: 12px;
}
</style>
</head>
<body>

<h2>📊 Fila de Execução</h2>
<div id="lista"></div>

<script>
const listaDiv = document.getElementById("lista");

// SSE conexão tempo real
const evtSource = new EventSource("../controller/back_fila_execucao_sse.php");

evtSource.onmessage = function(event) {
    const dados = JSON.parse(event.data);

    listaDiv.innerHTML = "";

    dados.forEach(proc => {

        let statusCor = "#6c757d";
        if (proc.status == 1) statusCor = "#ffc107";
        if (proc.status == 2) statusCor = "#28a745";
        if (proc.status == 3) statusCor = "#dc3545";

        const card = `
            <div class="card">
                <strong>Processo #${proc.id}</strong> - ${proc.nome_processo}<br>
                Status: <span style="color:${statusCor}">
                    ${getStatus(proc.status)}
                </span><br>
                ${proc.processados} / ${proc.total}

                <div class="barra">
                    <div class="progresso" 
                        style="width:${Number(proc.progresso)}%">
                        ${Number(proc.progresso).toFixed(1)}%
                    </div>
                </div>
            </div>
        `;

        listaDiv.innerHTML += card;
    });
};

function getStatus(status){
    if(status == 0) return "Na fila";
    if(status == 1) return "Executando";
    if(status == 2) return "Concluído";
    if(status == 3) return "Erro";
    return "Desconhecido";
}
</script>

</body>
</html>
