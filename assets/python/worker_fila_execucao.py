import time
import subprocess
import os
import sys
import logging
import logging.handlers
import mysql.connector
from datetime import datetime, timedelta
from dotenv import load_dotenv

# ==============================
# 📋 LOGGING
# ==============================
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOG_FILE = os.path.join(BASE_DIR, 'worker_fila_execucao.log')

_fmt = logging.Formatter('%(asctime)s [%(levelname)s] %(message)s')

_fh = logging.handlers.RotatingFileHandler(
    LOG_FILE, maxBytes=2 * 1024 * 1024, backupCount=3, encoding='utf-8'
)
_fh.setFormatter(_fmt)

_sh = logging.StreamHandler()
_sh.setFormatter(_fmt)

logging.root.setLevel(logging.INFO)
logging.root.addHandler(_fh)
logging.root.addHandler(_sh)

log = logging.getLogger(__name__)

# ==============================
# 🔐 CARREGA .ENV
# ==============================
load_dotenv()

DB_HOST = os.getenv("DB_HOST")
DB_USER = os.getenv("DB_USER")
DB_PASS = os.getenv("DB_PASS")
DB_BASE = os.getenv("DB_BASE")

# Processos presos: reseta para falha após este tempo sem concluir
MAX_EXEC_MINUTOS = 120

# ==============================
# 🔌 CONEXÃO MYSQL
# ==============================
def conectar_mysql():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_BASE,
        port=3306,
        connection_timeout=5,
        use_pure=True
    )

# ==============================
# 🛑 Verifica processo executando
# ==============================
def existe_processo_executando(cursor):
    cursor.execute("""
        SELECT id
        FROM vest_relatorio_fila_execucao
        WHERE status = 1
        LIMIT 1
    """)
    return cursor.fetchone()

# ==============================
# ⚠️  Auto-reset de processos presos
# ==============================
def verificar_processos_presos(cursor, conn):
    cursor.execute("""
        SELECT id, nome_processo, data_inicio, progresso
        FROM vest_relatorio_fila_execucao
        WHERE status = 1
          AND data_inicio < DATE_SUB(NOW(), INTERVAL %s MINUTE)
    """, (MAX_EXEC_MINUTOS,))
    presos = cursor.fetchall()
    for p in presos:
        log.warning(
            f"[AUTO-RESET] ID={p['id']} ({p['nome_processo']}) preso "
            f"há mais de {MAX_EXEC_MINUTOS}min (inicio={p['data_inicio']}, "
            f"progresso={p['progresso']}%) — resetando para status=3"
        )
        cursor.execute("""
            UPDATE vest_relatorio_fila_execucao
            SET status = 3,
                data_fim = NOW(),
                log = CONCAT(
                    IFNULL(log,''),
                    '\n[AUTO-RESET] Timeout: processo preso por mais de %s min. Resetado em ',
                    NOW()
                )
            WHERE id = %s AND status = 1
        """, (MAX_EXEC_MINUTOS, p['id']))
        conn.commit()
    return len(presos)

# ==============================
# 📦 Pega próximo da fila (FIFO)
# ==============================
def pegar_proximo_processo(cursor):
    cursor.execute("""
        SELECT id, nome_processo, parametros
        FROM vest_relatorio_fila_execucao
        WHERE status = 0
        ORDER BY id ASC
        LIMIT 1
    """)
    return cursor.fetchone()

# ==============================
# 📦 RESOLVER PARAMETROS
# ==============================
def resolver_parametros(info, proxima_execucao):
    nome = info["nome_processo"].lower()
    if "atualizavenda" in nome:
        return proxima_execucao.strftime('%Y-%m-%d')
    return info["parametros"]

# ==============================
# 📦 NORMALIZAR DATA
# ==============================
def normalizar_time(valor):
    from datetime import time, timedelta
    if isinstance(valor, time):
        return valor
    if isinstance(valor, timedelta):
        total_seconds = int(valor.total_seconds())
        horas = total_seconds // 3600
        minutos = (total_seconds % 3600) // 60
        segundos = total_seconds % 60
        return time(horas, minutos, segundos)
    return valor

# ==============================
# 🔄 Atualiza status do processo
# ==============================
def atualizar_status(cursor, processo_id, status, log_msg=None):

    if status == 1:
        cursor.execute("""
            UPDATE vest_relatorio_fila_execucao
            SET
                status = 1,
                data_inicio = NOW(),
                processados = 0,
                progresso = 0
            WHERE id = %s
        """, (processo_id,))

    else:
        cursor.execute("""
            SELECT *
            FROM vest_relatorio_fila_execucao
            WHERE id = %s
        """, (processo_id,))
        info = cursor.fetchone()

        cursor.execute("""
            UPDATE vest_relatorio_fila_execucao
            SET
                status = %s,
                data_fim = NOW(),
                log = %s
            WHERE id = %s
        """, (status, log_msg, processo_id))

        if info and info["recorrente"] == 1:

            if info["tipo_recorrencia"] == 'D':
                log.info(f"Criando próximo agendamento diário para ID={processo_id}")
                proxima_execucao = calcular_proxima_execucao(info)
                parametros = resolver_parametros(info, proxima_execucao)
                cursor.execute("""
                    INSERT INTO vest_relatorio_fila_execucao
                    (nome_processo, parametros, total, processados, progresso,
                     status, agendado, agendamento_liberado, recorrente,
                     tipo_recorrencia, hora_inicio, hora_fim, data_agendada)
                    VALUES (%s,%s,0,0,0,9,1,0,1,'D',%s,%s,%s)
                """, (
                    info["nome_processo"], parametros,
                    info["hora_inicio"], info["hora_fim"], proxima_execucao
                ))

            elif info["tipo_recorrencia"] == 'H':
                log.info(f"Criando próximo agendamento horário para ID={processo_id}")
                proxima_execucao = calcular_proxima_execucao(info)
                parametros = resolver_parametros(info, proxima_execucao)
                cursor.execute("""
                    INSERT INTO vest_relatorio_fila_execucao
                    (nome_processo, parametros, total, processados, progresso,
                     status, agendado, agendamento_liberado, recorrente,
                     tipo_recorrencia, hora_inicio, hora_fim, data_agendada)
                    VALUES (%s,%s,0,0,0,9,1,0,1,'H',%s,%s,%s)
                """, (
                    info["nome_processo"], parametros,
                    info["hora_inicio"], info["hora_fim"], proxima_execucao
                ))

# ==============================
# 📝 Atualiza somente o log
# ==============================
def atualizar_log(cursor, processo_id, log_msg):
    if log_msg is None:
        log_msg = ""
    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET log = %s
        WHERE id = %s
    """, (str(log_msg), processo_id))

# ==============================
# 🚀 Executa script python da fila
# ==============================
def executar_script(processo_id, nome_script, parametros=None):
    try:
        script_path = os.path.join(BASE_DIR, nome_script)

        if not os.path.exists(script_path):
            return False, f"Script não encontrado: {script_path}"

        comando = [sys.executable, script_path, str(processo_id)]
        if parametros is not None and str(parametros).strip() != "":
            comando.append(str(parametros).strip())

        log.info(f"Executando: {' '.join(comando)}")

        resultado = subprocess.run(
            comando,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            encoding="utf-8",
            errors="replace",
            timeout=10800  # 3 horas
        )

        stdout = resultado.stdout.strip() if resultado.stdout else ""
        stderr = resultado.stderr.strip() if resultado.stderr else ""

        log_partes = [
            f"Comando: {' '.join(comando)}",
            f"Return code: {resultado.returncode}",
        ]
        if stdout:
            log_partes += ["\n=== STDOUT ===", stdout]
        if stderr:
            log_partes += ["\n=== STDERR ===", stderr]

        log_final = "\n".join(log_partes)

        if resultado.returncode == 0:
            log.info(f"Processo ID={processo_id} finalizado com sucesso (rc=0)")
            return True, log_final

        log.error(f"Processo ID={processo_id} finalizado com erro (rc={resultado.returncode})")
        return False, log_final

    except subprocess.TimeoutExpired as e:
        log.error(f"Processo ID={processo_id} excedeu timeout de 3h — encerrado")
        try:
            e.process.kill()
        except:
            pass
        return False, "Timeout: processo encerrado após 3 horas sem concluir."

    except Exception as e:
        log.exception(f"Erro inesperado ao executar script ID={processo_id}")
        return False, str(e)

# ==============================
# ⏰ LIBERA AGENDAMENTOS
# ==============================
def verificar_agendamentos(cursor):
    cursor.execute("""
        SELECT id, nome_processo, data_agendada
        FROM vest_relatorio_fila_execucao
        WHERE agendado = 1
          AND agendamento_liberado = 0
          AND data_agendada <= NOW()
          AND status = 9
    """)
    agendamentos = cursor.fetchall()
    for ag in agendamentos:
        log.info(f"Liberando agendamento ID={ag['id']} ({ag['nome_processo']}) agendado para {ag['data_agendada']}")
        cursor.execute("""
            UPDATE vest_relatorio_fila_execucao
            SET status = 0, agendamento_liberado = 1
            WHERE id = %s
        """, (ag["id"],))
    return len(agendamentos)

# ==============================
# ⏰ CALCULAR PROXIMA EXECUÇÃO
# ==============================
def calcular_proxima_execucao(info):
    agora = datetime.now()
    if info["tipo_recorrencia"] == 'D':
        return info["data_agendada"] + timedelta(days=1)
    if info["tipo_recorrencia"] == 'H':
        hora_inicio = normalizar_time(info["hora_inicio"])
        hora_fim = normalizar_time(info["hora_fim"])
        proxima = (agora + timedelta(hours=1)).replace(minute=0, second=0, microsecond=0)
        if proxima.time() < hora_inicio:
            proxima = proxima.replace(hour=hora_inicio.hour)
        elif hora_fim and proxima.time() > hora_fim:
            proximo_dia = (proxima + timedelta(days=1)).date()
            proxima = datetime.combine(proximo_dia, hora_inicio)
        return proxima

# ==============================
# 🧠 WORKER PRINCIPAL DA FILA
# ==============================
def worker():
    pid = os.getpid()
    log.info("=" * 60)
    log.info(f"Worker da fila iniciado — PID={pid}")
    log.info(f"Script dir: {BASE_DIR}")
    log.info(f"Log file:   {LOG_FILE}")
    log.info(f"Auto-reset: processos presos > {MAX_EXEC_MINUTOS} min")
    log.info("=" * 60)

    ciclo = 0
    fila_vazia_logado = False  # evita spam de "fila vazia" a cada 15s

    while True:
        conn = None
        cursor = None
        processo_id = None

        try:
            conn = conectar_mysql()
            cursor = conn.cursor(dictionary=True)

            # Lock — garante apenas um worker processando
            cursor.execute("SELECT GET_LOCK('worker_fila', 1) as lock_status")
            lock = cursor.fetchone()["lock_status"]

            if lock != 1:
                log.warning("Outro worker já está rodando (lock não obtido)")
                cursor.close()
                conn.close()
                time.sleep(5)
                continue

            ciclo += 1

            # Auto-reset de processos presos
            presos = verificar_processos_presos(cursor, conn)

            # Verifica se há processo em execução (após auto-reset)
            processo_exec = existe_processo_executando(cursor)
            if processo_exec:
                log.info(f"Processo ID={processo_exec['id']} ainda em execução — aguardando")
                time.sleep(10)
                continue

            # Libera agendamentos cujo horário chegou
            liberados = verificar_agendamentos(cursor)
            if liberados:
                conn.commit()

            # Pega próximo da fila
            processo = pegar_proximo_processo(cursor)

            if not processo:
                if not fila_vazia_logado:
                    log.info("Fila vazia — aguardando novos processos")
                    fila_vazia_logado = True
                time.sleep(15)
                continue

            fila_vazia_logado = False
            processo_id = processo["id"]
            nome_script = processo["nome_processo"]
            parametros  = processo["parametros"]

            log.info(f"Iniciando ID={processo_id} | script={nome_script} | params={parametros}")

            # Marca como executando
            atualizar_status(cursor, processo_id, 1)
            conn.commit()

            # Executa
            t0 = time.time()
            sucesso, log_exec = executar_script(processo_id, nome_script, parametros)
            elapsed = round(time.time() - t0, 1)

            # Execucoes longas (Selenium, etc.) deixam a conexao ociosa
            # tempo suficiente para o MySQL/proxy derrubá-la. Reconecta
            # antes de salvar o status final.
            try:
                conn.ping(reconnect=True, attempts=3, delay=2)
            except mysql.connector.Error:
                conn = conectar_mysql()
            cursor = conn.cursor(dictionary=True)

            # Verifica se o script filho já finalizou o status
            cursor.execute(
                "SELECT status FROM vest_relatorio_fila_execucao WHERE id = %s",
                (processo_id,)
            )
            status_atual = cursor.fetchone()

            atualizar_log(cursor, processo_id, log_exec)

            if sucesso:
                if status_atual and status_atual["status"] == 1:
                    atualizar_status(cursor, processo_id, 2, log_exec)
                log.info(f"Concluído ID={processo_id} ({nome_script}) em {elapsed}s")
            else:
                if status_atual and status_atual["status"] == 1:
                    atualizar_status(cursor, processo_id, 3, log_exec)
                log.error(f"Falhou ID={processo_id} ({nome_script}) em {elapsed}s")

            conn.commit()

            # Heartbeat a cada 60 ciclos (~5min)
            if ciclo % 60 == 0:
                cursor.execute("SELECT COUNT(*) as n FROM vest_relatorio_fila_execucao WHERE status = 0")
                na_fila = cursor.fetchone()["n"]
                log.info(f"Heartbeat #{ciclo} — PID={pid} | aguardando={na_fila}")

        except Exception as e:
            log.error(f"Erro geral do worker (ciclo {ciclo}): {e}")
            try:
                if processo_id and cursor:
                    atualizar_status(cursor, processo_id, 3, str(e))
                    conn.commit()
            except Exception as e2:
                log.error(f"Erro ao salvar status de falha: {e2}")

        finally:
            try:
                if cursor:
                    cursor.execute("SELECT RELEASE_LOCK('worker_fila')")
            except:
                pass
            try:
                if cursor:
                    cursor.close()
                if conn:
                    conn.close()
            except:
                pass

        time.sleep(5)

# ==============================
# ▶️ START
# ==============================
if __name__ == "__main__":
    worker()
