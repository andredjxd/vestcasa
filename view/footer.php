<footer class="main-footer">
    <strong id="copyright"></strong>
    
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.5.2
    </div>
  </footer>
</div>
<script src="../assets/plugins/html5-qrcode/html5-qrcode.min.js"></script>
<!-- jQuery -->
<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/jquery-ui/jquery-ui.js"></script>
<!-- Bootstrap -->
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Custons -->
 <script src="../assets/js/utils.js"></script>
<!-- <script src="../assets/js/custons.js"></script> -->
<!-- <script src="../assets/js/agua.js"></script> -->
<!-- <script src="../assets/js/comsrtbi03.js"></script> -->
<script src="../assets/js/migrate.js"></script>
<!-- <script src="../assets/js/comsmgoi12.js"></script> -->
<!-- <script src="../assets/js/despesas.js"></script> -->
<!-- <script src="../assets/js/terceiros.js"></script> -->
<!-- <script src="../assets/js/quebras.js"></script> -->
<script src="../assets/js/imagen64.js"></script>
<!-- <script src="../assets/js/boletos.js"></script> -->
<!-- <script src="../assets/js/isv.js"></script> -->
 <!-- CodeMirror -->
<script src="../assets/plugins/codemirror/codemirror.js"></script>
<script src="../assets/plugins/codemirror/mode/css/css.js"></script>
<script src="../assets/plugins/codemirror/mode/xml/xml.js"></script>
<script src="../assets/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>

<script src="../assets/js/fcxdepositos.js"></script>
<script src="../assets/js/vendasdia.js"></script>
<script src="../assets/js/fcxfechamento.js"></script>
<script src="../assets/js/etiquetas-utils.js"></script>
<script src="../assets/js/opeprecosalterados.js"></script>
<script src="../assets/js/opegeradoretiquetas.js"></script>
<script src="../assets/js/fila.js"></script>
<script src="../assets/js/opeconsultaprodutos.js"></script>
<script src="../assets/js/rmerelatorios.js"></script>
<script src="../assets/js/opeconsultaprodutosnew.js"></script>
<script src="../assets/js/avariasrelatorios.js"></script>
<script src="../assets/js/mercadoriaajuste.js"></script>
<script src="../assets/js/usuarios.js"></script>

<!-- Toastr -->
<script src="../assets/plugins/toastr/toastr.min.js"></script>

<script src="../assets/plugins/maskmoney/jquery.maskMoney.min.js"></script>
<!-- overlayScrollbars -->
<script src="../assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../assets/dist/js/adminlte.js"></script>
<!-- Select2 -->
<script src="../assets/plugins/select2/js/select2.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<!-- <script src="../assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script> -->
<!-- InputMask -->
<script src="../assets/plugins/moment/moment.min.js"></script>
<script src="../assets/plugins/moment/locale/pt-br.js"></script>
<script src="../assets/plugins/inputmask/jquery.inputmask.min.js"></script>
<!-- date-range-picker -->
<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- dropzonejs -->
<script src="../assets/plugins/dropzone/dropzone.js"></script>

<!-- <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->
<!-- DataTables  & Plugins -->
<script src="../assets/plugins/datatables/jquery.dataTables.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../assets/plugins/jszip/jszip.min.js"></script>
<script src="../assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="../assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="../assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="../assets/dist/js/pages/dashboard3.js"></script> -->

<script src="../assets/plugins/popper/umd/popper.js"></script>
<!-- <script src="../assets/plugins/popper/umd/popper-utils.js"></script> -->



<!-- OPTIONAL SCRIPTS -->
<script src="../assets/plugins/apexcharts/dist/apexcharts.min.js" ></script>
<script src="../assets/plugins/chart.js/Chart.min.js"></script>
<script src="../assets/js/graficos.js"></script>
<script>
function aplicarDarkMode(isDark) {
    // aplica no HTML (rápido - evita flash)
    document.documentElement.classList.toggle('dark-mode', isDark);

    // aplica no BODY (se existir)
    if (document.body) {
        document.body.classList.toggle('dark-mode', isDark);
    }
}

function toggleDarkMode() {
    const isDark = !document.documentElement.classList.contains('dark-mode');

    aplicarDarkMode(isDark);
    localStorage.setItem('dark-mode', isDark);

    atualizarIcone(isDark);

    document.dispatchEvent(new CustomEvent('darkmodechange', { detail: { isDark } }));
}

function atualizarIcone(isDark) {
    const icon = document.getElementById('iconDark');
    if (!icon) return;

    icon.classList.toggle('fa-sun', isDark);
    icon.classList.toggle('fa-moon', !isDark);
}

// 🔥 ESSENCIAL: sincroniza quando o body carregar
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark-mode');

    aplicarDarkMode(isDark); // garante que body receba também
    atualizarIcone(isDark);
});
</script>

<!-- ═══════════════ SYNC VESTCASA ═══════════════ -->
<script>
$(function () {
  const API = '../assets/_php/sync_control.php';
  let _timer = null;

  function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  // "2026-06-10 20:07:36,174 [INFO] [LOCAL→EXT] 7493 ops — tabela:n, tabela:n, ..."
  const RE_LINE = /^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}),\d{3}\s+\[(\w+)\]\s*(.*)$/;
  const RE_DIR  = /^\[(LOCAL→EXT|EXT→LOCAL)\]\s*(.*)$/;
  const RE_OPS  = /^(\d+)\s+ops\s+—\s+(.*)$/;

  const LEVEL_COLOR = { INFO:'#9cdcfe', WARNING:'#dcdcaa', ERROR:'#f48771', CRITICAL:'#f48771' };
  const DIR_COLOR   = { 'LOCAL→EXT':'#4ec9b0', 'EXT→LOCAL':'#ce9178' };

  function colorLine(line) {
    const m = line.match(RE_LINE);
    if (!m) return '<div class="sv-line">' + escHtml(line) + '</div>';

    const [, ts, level, rest] = m;
    let body = rest;
    let head = '<span class="sv-ts">' + escHtml(ts) + '</span>'
             + '<span class="sv-level" style="color:' + (LEVEL_COLOR[level] || '#d4d4d4') + '">[' + level + ']</span>';

    const dm = body.match(RE_DIR);
    if (dm) {
      const dir = dm[1];
      body = dm[2];
      head += '<span class="sv-dir" style="color:' + (DIR_COLOR[dir] || '#d4d4d4') + '">' + dir + '</span>';
    }

    const om = body.match(RE_OPS);
    if (om) {
      const [, ops, tabelas] = om;
      const chips = tabelas.split(',').map(par => {
        const [tbl, cnt] = par.trim().split(':');
        return '<span class="sv-chip">' + escHtml(tbl) + (cnt !== undefined ? '<b>' + escHtml(cnt) + '</b>' : '') + '</span>';
      }).join('');

      return '<div class="sv-line">' + head
           + '<span class="sv-ops">' + ops + ' ops</span>'
           + '<div class="sv-chips">' + chips + '</div></div>';
    }

    const cls = (level === 'ERROR' || level === 'CRITICAL') ? ' sv-error' : '';
    return '<div class="sv-line' + cls + '">' + head + '<span class="sv-msgtxt">' + escHtml(body) + '</span></div>';
  }

  function applyStatus(data) {
    const running  = data.running;
    const dot      = document.getElementById('sync-status-dot');
    const badge    = document.getElementById('sv-badge');
    const pidEl    = document.getElementById('sv-pid');
    const btnStart = document.getElementById('sv-btn-start');
    const btnStop  = document.getElementById('sv-btn-stop');
    const logEl    = document.getElementById('sv-log');
    if (!badge) return;

    if (dot) dot.style.background = running ? '#28a745' : '#dc3545';
    badge.textContent = running ? 'ATIVO' : 'INATIVO';
    badge.className   = 'badge ml-2 badge-' + (running ? 'success' : 'danger');
    pidEl.textContent = data.pid || '—';
    btnStart.disabled = running;
    btnStop.disabled  = !running;

    if (data.log && data.log.length) {
      logEl.innerHTML = data.log.map(colorLine).join('');
      logEl.scrollTop = logEl.scrollHeight;
    }
  }

  function fetchStatus() {
    fetch(API + '?action=status')
      .then(r => r.json())
      .then(applyStatus)
      .catch(() => {});
  }

  function doAction(action) {
    const msgEl = document.getElementById('sv-msg');
    if (!msgEl) return;
    msgEl.textContent = 'Aguarde...';
    $('#sv-btn-start, #sv-btn-stop').prop('disabled', true);
    fetch(API + '?action=' + action)
      .then(r => r.json())
      .then(d => {
        msgEl.style.color = d.ok ? '#28a745' : '#dc3545';
        msgEl.textContent = d.msg || '';
        fetchStatus();
      })
      .catch(e => { msgEl.style.color='#dc3545'; msgEl.textContent = 'Erro: ' + e; });
  }

  $('#modal-sync-vestcasa')
    .on('show.bs.modal', function () { fetchStatus(); _timer = setInterval(fetchStatus, 5000); })
    .on('hide.bs.modal', function () { clearInterval(_timer); _timer = null; });

  $('#sv-btn-start').on('click', () => doAction('start'));
  $('#sv-btn-stop').on('click',  () => doAction('stop'));
  $('#sv-btn-refresh').on('click', fetchStatus);

  fetchStatus();
});
</script>
<!-- ═══════════════ FIM SYNC VESTCASA ═══════════════ -->
</body>
</html>
