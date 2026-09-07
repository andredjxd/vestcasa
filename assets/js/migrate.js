$(document).ready(function () {
    $('#notifications').empty();
        const notifications = [
        { icon: 'fad fa-database', text: 'Verificar Integridade do DB', time: '' },
        // { icon: 'fa-users', text: '8 friend requests', time: '12 hours' },
        ];

        notifications.forEach(n => {
        $('#notifications').append(`
            <a href='#' class='dropdown-item-green syncbase'>
            <i class='${n.icon} mr-2'></i> ${n.text}
            <span class='float-right text-muted text-sm'>${n.time}</span>
            </a>
        `);
    });


    $('#sycnmigrations').empty();
    $('#sycnmigrations').append("<i class='fad fa-database syncbase' style='color: black;'></i>");
    $(document).on('click','.syncbase',function() {
        // toastr.success("OI");
        $('#msmsync').empty();
        $('#msmsync').css('height', '');  
        $('#modal-sync-db').modal({backdrop: 'static', keyboard: false});
    });
    $(document).on('click', '#btnMigrate', function() {
        const msmsync = document.getElementById("msmsync");

        msmsync.style.display = "block";
        msmsync.innerHTML = "";
        $('#msmsync').css('height', $(window).height() - 250);

        $(window).resize(function() {
            $('#msmsync').css('height', $(window).height() - 250);
        });

        $('body').Layout('fixLayoutHeight');

        const startLine = document.createElement("div");
        startLine.className = "log-line info";
        startLine.textContent = "⏳ Conectando...";
        msmsync.appendChild(startLine);

        if (window.migrateSource) window.migrateSource.close();

        function startSSE(url, callback) {
            const evtSource = new EventSource(url);
            window.migrateSource = evtSource;

            evtSource.onmessage = function(e) {
                if (e.data === "[FIM]") {
                    const endLine = document.createElement("div");
                    endLine.className = "log-line success";
                    endLine.textContent = "✅ Finalizado!";
                    msmsync.appendChild(endLine);
                    msmsync.scrollTop = msmsync.scrollHeight;
                    evtSource.close();
                    if (callback) callback(); // inicia próxima SSE
                    return;
                }

                let cssClass = "info";
                if (e.data.includes("✅")) cssClass = "success";
                else if (e.data.includes("❌") || e.data.includes("Erro")) cssClass = "error";
                else if (e.data.includes("🟢")) cssClass = "add";
                else if (e.data.includes("🔴")) cssClass = "remove";
                else if (e.data.includes("🟡")) cssClass = "update";

                const line = document.createElement("div");
                line.className = "log-line " + cssClass;
                line.textContent = e.data;
                msmsync.appendChild(line);
                msmsync.scrollTop = msmsync.scrollHeight;
            };

            evtSource.onerror = function() {
                const errLine = document.createElement("div");
                errLine.className = "log-line error";
                errLine.textContent = "❌ Conexão perdida ou erro no servidor.";
                msmsync.appendChild(errLine);
                msmsync.scrollTop = msmsync.scrollHeight;
                evtSource.close();
            };
        }

        // Inicia primeira SSE
        startSSE("../controller/xexportDataRunner.php", function() {
            // Quando a primeira terminar, inicia a segunda
            startSSE("../controller/a_migrate_live.php");
        });
    });


});
