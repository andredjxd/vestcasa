from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException
from selenium.common.exceptions import StaleElementReferenceException
from webdriver_manager.chrome import ChromeDriverManager


from dotenv import load_dotenv
from datetime import datetime
from contextlib import contextmanager

import mysql.connector
import traceback
import logging
import sys
import os
import time
import platform
import re
import gc

# ======================================================
# CONFIGURAcaO LOG
# ======================================================

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s => %(message)s',
    datefmt='%d/%m/%Y %H:%M:%S'
)

# ======================================================
# FILA / PARÂMETROS
# ======================================================

if len(sys.argv) < 3:

    logging.error(
        "Parâmetros obrigatorios nao informados. "
        "Uso correto: python script.py <fila_id> <codigo_avaria>"
    )

    sys.exit(1)

try:

    fila_id = int(
        sys.argv[1]
    )

    codigoAvaria = str(
        sys.argv[2]
    ).replace(".", "").strip()

except Exception as e:

    logging.error(
        f"Erro ao ler parâmetros: {e}"
    )

    sys.exit(1)

# ======================================================
# CARREGA .ENV
# ======================================================

load_dotenv()

DB_HOST = os.getenv("DB_HOST")
DB_USER = os.getenv("DB_USER")
DB_PASS = os.getenv("DB_PASS")
DB_BASE = os.getenv("DB_BASE")

USER_AVARIA = os.getenv("VEST_USER_AVARIA")
PASS_AVARIA = os.getenv("VEST_PASS_AVARIA")

LINK = "https://avarias.ddns.com.br:50080/auth/login"

# 0 desativa. O refresh estava derrubando a SPA para a tela de login no
# meio das insercoes, mesmo mantendo a URL /home/dashboard.
REFRESH_PREVENTIVO_INSERCOES = 0

# ======================================================
# MYSQL
# ======================================================

@contextmanager
def mysql_connection():
    conn = None

    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_BASE,
            port=3306,
            autocommit=False
        )

        yield conn

    finally:
        if conn and conn.is_connected():
            conn.close()

# ======================================================
# TEMPO EXECUcaO
# ======================================================

def calcular_tempo_execucao(inicio, fim):

    delta = fim - inicio
    total_segundos = int(delta.total_seconds())

    horas = total_segundos // 3600
    minutos = (total_segundos % 3600) // 60
    segundos = total_segundos % 60

    return f"{horas:02}:{minutos:02}:{segundos:02}", total_segundos

# ======================================================
# ATUALIZAR STATUS
# ======================================================

def atualizar_status(
    conn,
    fila_id,
    status=None,
    progresso=None,
    processados=None,
    total=None,
    log_texto=None,
    finalizar=False
):

    campos = []
    valores = []

    if status is not None:
        campos.append("status=%s")
        valores.append(status)

    if progresso is not None:
        campos.append("progresso=%s")
        valores.append(progresso)

    if processados is not None:
        campos.append("processados=%s")
        valores.append(processados)

    if total is not None:
        campos.append("total=%s")
        valores.append(total)

    if log_texto is not None:
        campos.append("log=%s")
        valores.append(log_texto[:5000])

    if finalizar:
        campos.append("data_fim=NOW()")

    sql = f"""
        UPDATE vest_relatorio_fila_execucao
        SET {", ".join(campos)}
        WHERE id=%s
    """

    valores.append(fila_id)

    cursor = conn.cursor()
    cursor.execute(sql, tuple(valores))
    conn.commit()
    cursor.close()


def aguardar_portal_estabilizar(driver):

    wait = WebDriverWait(driver, 120)

    logging.info(
        "Aguardando portal estabilizar..."
    )

    # documento
    wait.until(
        lambda d: d.execute_script(
            "return document.readyState"
        ) == "complete"
    )

    # React/DataGrid
    wait.until(
        lambda d: d.execute_script("""
            return !!document.querySelector(
                '.MuiDataGrid-root'
            );
        """)
    )

    # loading invisível
    wait.until(
        lambda d: d.execute_script("""
            return !document.querySelector(
                '.MuiBackdrop-root'
            )
        """)
    )

    # progress invisível
    wait.until(
        lambda d: d.execute_script("""
            return !document.querySelector(
                '.MuiCircularProgress-root'
            )
        """)
    )

    logging.info(
        "Portal estabilizado."
    )

# ======================================================
# CLICK UNIVERSAL
# ======================================================

def clicar_elemento(
    driver,
    elemento,
    descricao="elemento"
):

    # ==================================================
    # SCROLL
    # ==================================================

    try:

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            elemento
        )

        time.sleep(0.3)

    except Exception:
        pass

    # ==================================================
    # CLICK NORMAL
    # ==================================================

    try:

        elemento.click()

        logging.info(
            f"{descricao} clicado com click normal."
        )

        return True

    except Exception as e_click:

        logging.warning(
            f"Falha click normal "
            f"{descricao}: {repr(e_click)}"
        )

    # ==================================================
    # ACTION CHAINS
    # ==================================================

    try:

        ActionChains(driver)\
            .move_to_element(elemento)\
            .pause(0.2)\
            .click()\
            .perform()

        logging.info(
            f"{descricao} clicado "
            f"com ActionChains."
        )

        return True

    except Exception as e_action:

        logging.warning(
            f"Falha ActionChains "
            f"{descricao}: {repr(e_action)}"
        )

    # ==================================================
    # JS CLICK
    # ==================================================

    try:

        driver.execute_script(
            "arguments[0].click();",
            elemento
        )

        logging.info(
            f"{descricao} clicado "
            f"com JS click."
        )

        return True

    except Exception as e_js:

        logging.error(
            f"Falha JS click "
            f"{descricao}: {repr(e_js)}"
        )

        return False    

# ======================================================
# ATUALIZAR PROGRESSO
# ======================================================
def atualizar_progresso(cursor, conn, fila_id, processados, total):

    if total <= 0:
        progresso = 0
    else:
        progresso = round((processados / total) * 100, 2)

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET 
            processados = %s,
            total = %s,
            progresso = %s
        WHERE id = %s
    """, (
        processados,
        total,
        progresso,
        fila_id
    ))

    conn.commit()

    logging.info(
        f"#==========>   Progresso atualizado: {processados}/{total} - {progresso}%"
    )



# ======================================================
# CHROME
# ======================================================

def criar_driver():

    options = webdriver.ChromeOptions()

    # HEADLESS
    options.add_argument("--headless=new")

    # PERFORMANCE
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--no-sandbox")

    # VISUAL / VIEWPORT DESKTOP
    # No headless, start-maximized sozinho nao funciona bem.
    options.add_argument("--window-size=1920,1080")
    options.add_argument("--force-device-scale-factor=1")
    options.add_argument("--high-dpi-support=1")
    options.add_argument("--start-maximized")

    # MEMORIA: expoe gc() no JS para liberar memoria periodicamente
    options.add_argument("--js-flags=--expose-gc")
    options.add_argument("--aggressive-cache-discard")

    # ESTABILIDADE
    options.add_argument("--disable-notifications")
    options.add_argument("--disable-popup-blocking")
    options.add_argument("--disable-extensions")
    options.add_argument("--ignore-certificate-errors")
    options.add_argument("--allow-running-insecure-content")

    # ANTI DETECcaO
    options.add_argument("--disable-blink-features=AutomationControlled")

    options.add_experimental_option(
        "excludeSwitches",
        ["enable-automation"]
    )

    options.add_experimental_option(
        "useAutomationExtension",
        False
    )

    # USER AGENT
    options.add_argument(
        "--user-agent=Mozilla/5.0 "
        "(Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 "
        "(KHTML, like Gecko) "
        "Chrome/136.0.0.0 Safari/537.36"
    )

    # ==================================================
    # DESATIVA POPUPS CHROME
    # ==================================================

    prefs = {

        # popup salvar senha
        "credentials_enable_service": False,

        # gerenciador senhas
        "profile.password_manager_enabled": False,

        # deteccao vazamento
        "profile.password_manager_leak_detection": False,

        # notificacões
        "profile.default_content_setting_values.notifications": 2
    }

    options.add_experimental_option(
        "prefs",
        prefs
    )

    # desativa popup salvar senha
    options.add_argument(
        "--disable-save-password-bubble"
    )

    # desativa password manager
    options.add_argument(
        "--disable-features=PasswordManagerOnboarding,PasswordCheck"
    )

    # ==================================================
    # DRIVER
    # ==================================================

    driver = webdriver.Chrome(

        service=Service(
            ChromeDriverManager().install()
        ),

        options=options
    )

    # Garante viewport desktop mesmo em headless
    driver.set_window_size(1920, 1080)

    # remove webdriver
    driver.execute_script("""
        Object.defineProperty(navigator, 'webdriver', {
            get: () => undefined
        })
    """)

    driver.set_page_load_timeout(60)

    # timeout maximo para execute_script
    driver.set_script_timeout(15)

    return driver

# ======================================================
# GC CHROME + PYTHON
# Libera handles CDP acumulados e memoria do Chrome.
# Requer --js-flags=--expose-gc no ChromeOptions.
# ======================================================

def forcar_gc_chrome(driver):
    try:
        driver.execute_script(
            "if (typeof gc === 'function') { gc(); }"
        )
        gc.collect()
        logging.info("GC forcado (Chrome + Python).")
    except Exception as e:
        logging.warning(f"Erro ao forcar GC: {repr(e)}")

# ======================================================
# SCREENSHOT
# ======================================================

def salvar_screenshot(driver, nome):

    pasta = "screenshots"

    os.makedirs(pasta, exist_ok=True)

    arquivo = os.path.join(
        pasta,
        f"{nome}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
    )

    driver.save_screenshot(arquivo)

    logging.info(f"Screenshot salva: {arquivo}")

# ======================================================
# AGUARDAR ELEMENTO
# ======================================================

def esperar_elemento(wait, by, value, timeout=30):

    return WebDriverWait(wait._driver, timeout).until(
        EC.presence_of_element_located((by, value))
    )

def formulario_login_visivel(driver):

    try:

        return bool(
            driver.execute_script("""
                const inputs = Array.from(document.querySelectorAll("input"));

                const normalizar = (valor) => (valor || "")
                    .toString()
                    .trim()
                    .toLowerCase();

                const email = inputs.find((input) => {
                    const nome = normalizar(input.name);
                    const tipo = normalizar(input.type);
                    const placeholder = normalizar(input.placeholder);

                    return nome.includes("email") ||
                        tipo === "email" ||
                        placeholder.includes("mail");
                });

                const senha = inputs.find((input) => {
                    const nome = normalizar(input.name);
                    const tipo = normalizar(input.type);
                    const placeholder = normalizar(input.placeholder);

                    return nome.includes("password") ||
                        nome.includes("senha") ||
                        tipo === "password" ||
                        placeholder.includes("senha");
                });

                const visivel = (el) => !!(
                    el &&
                    !el.disabled &&
                    (
                        el.offsetWidth ||
                        el.offsetHeight ||
                        el.getClientRects().length
                    )
                );

                return visivel(email) && visivel(senha);
            """)
        )

    except Exception:

        return False

def portal_autenticado_visivel(driver):

    try:

        return bool(
            driver.execute_script("""
                const texto = (document.body && document.body.innerText || "")
                    .toUpperCase();

                const temMenu = texto.includes("ESTOQUE") ||
                    texto.includes("CONTROLE DE AVARIA");

                const temGrid = !!document.querySelector(
                    ".MuiDataGrid-root, .MuiDataGrid-row"
                );

                return !(
                    texto.includes("DIGITE SUAS CREDENCIAIS") ||
                    texto.includes("ENDERECO DE E-MAIL") ||
                    texto.includes("ENDEREÇO DE E-MAIL")
                ) && (temMenu || temGrid);
            """)
        )

    except Exception:

        return False

# ======================================================
# LOGIN
# ======================================================

def fazer_login(driver):

    logging.info("Abrindo sistema...")

    driver.get(LINK)

    wait = WebDriverWait(driver, 60)

    # ==================================================
    # AGUARDA REACT
    # ==================================================

    wait.until(
        lambda d: d.execute_script(
            "return document.readyState"
        ) == "complete"
    )

    time.sleep(3)

    # salvar_screenshot(driver, "pagina_login")

    logging.info(f"URL atual: {driver.current_url}")

    # ==================================================
    # AGUARDA LOGIN OU PORTAL AUTENTICADO
    # ==================================================

    try:

        wait.until(
            lambda d: (
                formulario_login_visivel(d)
                or
                portal_autenticado_visivel(d)
            )
        )

    except Exception:

        salvar_screenshot(
            driver,
            "erro_estado_login_indefinido"
        )

        raise

    # ==================================================
    # JA ESTA LOGADO
    # ==================================================

    if portal_autenticado_visivel(driver):

        logging.info(
            "Portal autenticado visivel. Sessao ja autenticada."
        )

        return True

    if not formulario_login_visivel(driver):

        salvar_screenshot(
            driver,
            "erro_formulario_login_nao_encontrado"
        )

        raise Exception(
            "Formulario de login nao encontrado e portal nao autenticado."
        )

    # ==================================================
    # INPUT EMAIL
    # ==================================================

    campo_email = wait.until(
        EC.visibility_of_element_located(
            (
                By.NAME,
                "email"
            )
        )
    )

    campo_email.clear()

    campo_email.send_keys(USER_AVARIA)

    logging.info("Email preenchido.")

    # ==================================================
    # INPUT SENHA
    # ==================================================

    campo_senha = wait.until(
        EC.visibility_of_element_located(
            (
                By.NAME,
                "password"
            )
        )
    )

    campo_senha.clear()

    campo_senha.send_keys(PASS_AVARIA)

    logging.info("Senha preenchida.")

    # salvar_screenshot(driver, "credenciais")

    # ==================================================
    # BOTaO LOGIN
    # ==================================================

    botao_login = wait.until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                "//button[contains(., 'Login')]"
            )
        )
    )

    # scroll
    driver.execute_script(
        "arguments[0].scrollIntoView(true);",
        botao_login
    )

    time.sleep(0.2)

    # click JS evita problema Material UI
    clicar_elemento(
        driver,
        botao_login,
        "Botao login"
    )

    logging.info("Botao login clicado.")

    # ==================================================
    # AGUARDA LOGIN
    # ==================================================

    time.sleep(8)

    try:

        wait.until(
            lambda d: (
                portal_autenticado_visivel(d)
                or
                (
                    "/auth/login" not in d.current_url
                    and
                    not formulario_login_visivel(d)
                )
            )
        )

    except Exception:

        pass

    # salvar_screenshot(driver, "apos_login")

    logging.info(f"URL final: {driver.current_url}")

    # ==================================================
    # VALIDA LOGIN
    # ==================================================

    if formulario_login_visivel(driver) or "/auth/login" in driver.current_url:

        raise Exception(
            "Login falhou. Ainda esta na tela login."
        )

    logging.info("Login realizado com sucesso.")

    return True

# ======================================================
# MENU ESTOQUE
# ======================================================

def abrir_menu_estoque(driver):

    wait = WebDriverWait(driver, 30)

    logging.info("Abrindo menu Estoque...")

    menu_estoque = wait.until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                "//div[contains(text(),'Estoque')]"
            )
        )
    )

    # scroll
    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        menu_estoque
    )

    time.sleep(1)

    # click js
    clicar_elemento(
        driver,
        menu_estoque,
        "Menu estoque"
    )

    logging.info("Menu Estoque aberto.")

    # salvar_screenshot(driver, "menu_estoque")

    time.sleep(2)

# ======================================================
# MENU CONTROLE AVARIA
# ======================================================

def abrir_controle_avaria(driver):

    wait = WebDriverWait(driver, 30)

    logging.info("Abrindo Controle de Avaria...")

    menu_avaria = wait.until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                "//div[contains(text(),'Controle de Avaria')]"
            )
        )
    )

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        menu_avaria
    )

    time.sleep(1)

    clicar_elemento(
        driver,
        menu_avaria,
        "Menu Avaria"
    )

    logging.info("Controle de Avaria aberto.")

    # salvar_screenshot(driver, "controle_avaria")

    time.sleep(3)

# ======================================================
# MARCAR / DESMARCAR CHECKBOX
# ======================================================

def testar_checkbox(driver):

    wait = WebDriverWait(driver, 30)

    logging.info("Localizando checkbox...")

    checkbox = wait.until(
        EC.presence_of_element_located(
            (
                By.XPATH,
                "//input[@type='checkbox' and @aria-label='Selecionar linha']"
            )
        )
    )

    # ==================================================
    # SCROLL
    # ==================================================

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        checkbox
    )

    time.sleep(1)

    # ==================================================
    # MARCAR
    # ==================================================

    logging.info("Marcando checkbox...")

    clicar_elemento(
        driver,
        checkbox,
        "Checkbox"
    )

    time.sleep(2)

    # salvar_screenshot(driver, "checkbox_marcado")

    logging.info(
        f"Checkbox marcado: {checkbox.is_selected()}"
    )

    # ==================================================
    # DESMARCAR
    # ==================================================

    logging.info("Desmarcando checkbox...")

    clicar_elemento(
        driver,
        checkbox,
        "Checkbox"
    )

    time.sleep(2)

    # salvar_screenshot(driver, "checkbox_desmarcado")

    logging.info(
        f"Checkbox marcado: {checkbox.is_selected()}"
    )

# ======================================================
# LISTAR TABELA AVARIAS
# ======================================================

def listar_tabela_avarias(driver):

    wait = WebDriverWait(driver, 30)

    logging.info("Lendo tabela de avarias...")

    # ==================================================
    # AGUARDA GRID
    # ==================================================

    wait.until(
        EC.presence_of_element_located(
            (
                By.CLASS_NAME,
                "MuiDataGrid-row"
            )
        )
    )

    time.sleep(2)

    # salvar_screenshot(driver, "tabela_avarias")

    # ==================================================
    # LINHAS
    # ==================================================

    linhas = driver.find_elements(
        By.XPATH,
        "//div[contains(@class,'MuiDataGrid-row')]"
    )

    logging.info(f"Total linhas encontradas: {len(linhas)}")

    dados = []

    # ==================================================
    # LOOP LINHAS
    # ==================================================

    for index, linha in enumerate(linhas):

        try:

            item = {}

            # ==========================================
            # ID AVARIA
            # ==========================================

            item["avaria"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='id']"
            ).text.strip()

            # ==========================================
            # SITUAcaO
            # ==========================================

            item["situacao"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='situacao']"
            ).text.strip()

            # ==========================================
            # CRIADO POR
            # ==========================================

            item["criado_por"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='descr_usuario_criacao']"
            ).text.strip()

            # ==========================================
            # DATA
            # ==========================================

            item["data_criacao"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='data_criacao']"
            ).text.strip()

            # ==========================================
            # DESCRIcaO
            # ==========================================

            item["descricao"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='descricao']"
            ).text.strip()

            # ==========================================
            # EMPRESA
            # ==========================================

            item["empresa"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='empresa']"
            ).text.strip()

            # ==========================================
            # DEPoSITO
            # ==========================================

            item["deposito"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='deposito']"
            ).text.strip()

            # ==========================================
            # QUANTIDADE
            # ==========================================

            item["quantidade_produto"] = linha.find_element(
                By.XPATH,
                ".//div[@data-field='quantidade_produto']"
            ).text.strip()

            dados.append(item)

            logging.info(
                f"[{index+1}] "
                f"Avaria: {item['avaria']} | "
                f"Situacao: {item['situacao']}"
            )

        except Exception as e:

            logging.warning(
                f"Erro ao ler linha {index+1}: {e}"
            )

    # ==================================================
    # PRINT FINAL
    # ==================================================

    # print("\n================ TABELA AVARIAS ================\n")

    # for item in dados:

    #     print(item)

    # print("\n================================================\n")

    return dados

# ======================================================
# SALVAR AVARIAS MYSQL
# ======================================================

def salvar_avarias_mysql(conn, dados):

    cursor = conn.cursor()

    total_inseridos = 0
    total_erros = 0

    logging.info(
        "Iniciando gravacao das avarias..."
    )

    for item in dados:

        try:

            # ==========================================
            # TRATAMENTO DADOS
            # ==========================================

            codigo_avaria = (
                item["avaria"]
                .replace(".", "")
                .strip()
            )

            data_criacao = datetime.strptime(
                item["data_criacao"],
                "%d/%m/%Y %H:%M"
            )

            quantidade_produto = int(
                item["quantidade_produto"]
            )

            # ==========================================
            # INSERT / UPDATE
            # ==========================================

            sql = """
                INSERT INTO vest_avarias_relatorio (

                    codigo_avaria,
                    situacao,
                    criado_por,
                    data_criacao,
                    descricao,
                    empresa,
                    deposito,
                    quantidade_produto,
                    updated_at

                ) VALUES (

                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    NOW()

                )

                ON DUPLICATE KEY UPDATE

                    situacao = VALUES(situacao),
                    criado_por = VALUES(criado_por),
                    data_criacao = VALUES(data_criacao),
                    descricao = VALUES(descricao),
                    empresa = VALUES(empresa),
                    deposito = VALUES(deposito),
                    quantidade_produto = VALUES(quantidade_produto),
                    updated_at = NOW()
            """

            valores = (

                codigo_avaria,
                item["situacao"],
                item["criado_por"],
                data_criacao,
                item["descricao"],
                item["empresa"],
                item["deposito"],
                quantidade_produto

            )

            cursor.execute(sql, valores)

            total_inseridos += 1

            logging.info(
                f"Avaria salva: {codigo_avaria}"
            )

        except Exception as e:

            total_erros += 1

            logging.error(
                f"Erro ao salvar avaria "
                f"{item.get('avaria')}: {e}"
            )

    # ==============================================
    # COMMIT
    # ==============================================

    conn.commit()

    cursor.close()

    logging.info(
        f"Total salvos: {total_inseridos}"
    )

    logging.info(
        f"Total erros: {total_erros}"
    )

# ======================================================
# ABRIR DETALHE AVARIA
# ======================================================

def abrir_detalhe_avaria(driver, codigo_avaria):

    wait = WebDriverWait(driver, 30)

    logging.info(
        f"Procurando avaria {codigo_avaria}..."
    )

    # ==================================================
    # AGUARDA LINHAS
    # ==================================================

    linhas = wait.until(
        EC.presence_of_all_elements_located(
            (
                By.XPATH,
                "//div[contains(@class,'MuiDataGrid-row')]"
            )
        )
    )

    encontrou = False

    # ==================================================
    # LOOP LINHAS
    # ==================================================

    for linha in linhas:

        try:

            # ==========================================
            # COLUNA AVARIA
            # ==========================================

            avaria = linha.find_element(
                By.XPATH,
                ".//div[@data-field='id']"
            ).text.strip()

            # remove pontos
            avaria = avaria.replace(".", "")

            logging.info(f"Avaria encontrada: {avaria}")

            # ==========================================
            # VALIDA AVARIA
            # ==========================================

            if avaria == str(codigo_avaria):

                logging.info(
                    f"Avaria {codigo_avaria} localizada."
                )

                # ======================================
                # BOTaO DETALHAR
                # ======================================

                botao_detalhar = linha.find_element(
                    By.XPATH,
                    ".//span[@aria-label='Detalhar']//button"
                )

                # scroll
                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    botao_detalhar
                )

                time.sleep(1)

                clicar_elemento(
                    driver,
                    botao_detalhar,
                    "Botao Detalhar"
                )

                logging.info(
                    f"Detalhe da avaria "
                    f"{codigo_avaria} aberto."
                )

                encontrou = True

                break

        except Exception as e:

            logging.warning(
                f"Erro linha avaria: {e}"
            )

    # ==================================================
    # NaO ENCONTROU
    # ==================================================

    if not encontrou:

        raise Exception(
            f"Avaria {codigo_avaria} nao encontrada."
        )

def reabrir_detalhe_avaria(driver, codigo_avaria, tentativas=3):

    ultimo_erro = None

    for tentativa in range(1, tentativas + 1):

        try:

            logging.info(
                f"Reabrindo detalhe da avaria {codigo_avaria} "
                f"({tentativa}/{tentativas})..."
            )

            if (
                "/auth/login" in driver.current_url
                or
                formulario_login_visivel(driver)
            ):
                fazer_login(driver)

            wait_recuperacao = WebDriverWait(driver, 60)

            wait_recuperacao.until(
                lambda d: d.execute_script(
                    "return document.readyState"
                ) == "complete"
            )

            wait_recuperacao.until(
                lambda d: d.execute_script("""
                    return !document.querySelector(
                        '.MuiBackdrop-root'
                    )
                """)
            )

            wait_recuperacao.until(
                lambda d: d.execute_script("""
                    return !document.querySelector(
                        '.MuiCircularProgress-root'
                    )
                """)
            )

            abrir_menu_estoque(driver)

            abrir_controle_avaria(driver)

            WebDriverWait(driver, 30).until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[contains(@class,'MuiDataGrid-row')]"
                    )
                )
            )

            abrir_detalhe_avaria(
                driver,
                codigo_avaria
            )

            WebDriverWait(driver, 30).until(
                lambda d: (
                    formulario_login_visivel(d)
                    or
                    d.find_elements(By.NAME, "codigo_barras")
                )
            )

            if formulario_login_visivel(driver):

                raise Exception(
                    "Sessao caiu para a tela de login ao abrir detalhe."
                )

            WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable(
                    (
                        By.NAME,
                        "codigo_barras"
                    )
                )
            )

            logging.info(
                "Tela de detalhe reaberta e pronta para insercao."
            )

            return True

        except Exception as e:

            ultimo_erro = e

            logging.warning(
                f"Falha ao reabrir detalhe da avaria: {repr(e)}"
            )

            salvar_screenshot(
                driver,
                f"erro_reabrir_detalhe_{tentativa}"
            )

            time.sleep(1)

            try:
                fazer_login(driver)
            except Exception as e_login:
                logging.warning(
                    f"Falha ao refazer login para recuperar tela: {repr(e_login)}"
                )

    raise Exception(
        f"Nao foi possivel reabrir detalhe da avaria {codigo_avaria}: "
        f"{repr(ultimo_erro)}"
    )

# ======================================================
# CONSULTAR ITENS AVARIA
# ======================================================

def consultar_itens_avaria(conn, codigo_avaria):

    cursor = conn.cursor(dictionary=True)

    sql = """
        SELECT
            id,
            codigo_avaria,
            codigo_barras,
            tipo_avaria,
            quantidade,
            observacao
        FROM vest_avarias_relatorio_itens
        WHERE codigo_avaria = %s
        ORDER by id ASC
    """

    cursor.execute(
        sql,
        (str(codigo_avaria),)
    )

    dados = cursor.fetchall()

    cursor.close()

    logging.info(
        f"Itens encontrados: {len(dados)}"
    )

    for item in dados:

        logging.info(
            f"ID: {item.get('id')} | "
            f"Avaria: {item.get('codigo_avaria')} | "
            f"Codigo: {item.get('codigo_barras')} | "
            f"Tipo: {item.get('tipo_avaria')} | "
            f"Qtd: {item.get('quantidade')} | "
            f"OBS.: {item.get('observacao')}"
        )

    return dados

def resetar_upload_fotos_sem_refresh(driver):
    """
    Reseta o estado interno do upload de fotos do portal sem recarregar a pagina.
    Estratégia:
    1. Clica na area/botao de upload.
    2. Fecha/cancela o seletor com ESC.
    3. Limpa o input file via JS.
    """

    try:
        wait = WebDriverWait(driver, 10)

        # area do upload / botao de selecionar foto
        seletores_upload = [
            "//input[@type='file']/ancestor::div[contains(@class,'MuiBox-root')]",
            "//div[contains(., 'Arraste ou') and contains(., 'Selecione')]",
            "//div[contains(., 'Arraste') and contains(., 'Foto')]",
            "//input[@type='file']"
        ]

        clicou = False

        for seletor in seletores_upload:
            try:
                elemento = wait.until(
                    EC.presence_of_element_located((By.XPATH, seletor))
                )

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    elemento
                )

                time.sleep(0.3)

                clicar_elemento(
                    driver,
                    elemento,
                    "Elemento"
                )

                clicou = True

                logging.info("area/botao de upload clicado para resetar estado.")

                break

            except Exception:
                continue

        if not clicou:
            logging.warning("Nao conseguiu clicar na area de upload para reset.")
            return False

        time.sleep(1)

        # tenta cancelar a janela de selecao com ESC
        try:
            driver.switch_to.active_element.send_keys(Keys.ESCAPE)
            logging.info("ESC enviado para cancelar seletor de arquivo.")
        except Exception as e:
            logging.warning(f"Nao conseguiu enviar ESC: {repr(e)}")

        time.sleep(1)

        # limpa input file no DOM
        driver.execute_script("""
            const inputs = document.querySelectorAll("input[type='file']");
            inputs.forEach(input => {
                input.value = "";
                input.dispatchEvent(new Event("change", { bubbles: true }));
            });
        """)

        logging.info("Input de upload limpo sem refresh.")

        time.sleep(1)

        return True

    except Exception as e:
        logging.warning(f"Erro ao resetar upload sem refresh: {repr(e)}")
        return False

# ======================================================
# CONSULTAR FOTOS DO ITEM
# ======================================================

def consultar_fotos_item_avaria(conn, item_id):

    if not item_id:

        logging.warning(
            "ID do item nao informado para buscar fotos."
        )

        return []

    cursor = conn.cursor(dictionary=True)

    sql = """
        SELECT
            id,
            item_id,
            codigo_avaria,
            codigo_barras,
            caminho_arquivo,
            url_arquivo,
            thumb_arquivo
        FROM vest_avarias_relatorio_fotos
        WHERE item_id = %s
        ORDER BY id ASC
    """

    cursor.execute(
        sql,
        (int(item_id),)
    )

    fotos = cursor.fetchall()

    cursor.close()

    logging.info(
        f"Fotos encontradas para item {item_id}: {len(fotos)}"
    )

    return fotos

# ======================================================
# CAMINHO FISICO FOTO
# ======================================================

def montar_caminho_fisico_foto(caminho_relativo):

    if not caminho_relativo:
        return None

    caminho_relativo = str(caminho_relativo).strip().lstrip("/\\")

    # remove prefixo do projeto se vier junto
    caminho_relativo = caminho_relativo.replace("vestcasa/", "")
    caminho_relativo = caminho_relativo.replace("vestcasa\\", "")

    # remove prefixo app/web/ que o banco pode ter gravado incorretamente
    # caminho real: uploads/avarias/... nao app/web/uploads/avarias/...
    caminho_relativo = caminho_relativo.replace("app/web/", "")
    caminho_relativo = caminho_relativo.replace("app\\web\\", "")

    sistema = platform.system().lower()

    if sistema == "windows":

        base_projeto = r"C:\xampp\htdocs\vestcasa"

    else:

        base_projeto = "/var/www/html/vestcasa"

    caminho_fisico = os.path.join(
        base_projeto,
        caminho_relativo
    )

    caminho_fisico = os.path.abspath(
        caminho_fisico
    )

    if os.path.isfile(caminho_fisico):
        return caminho_fisico

    # fallback: portal local pode ainda rodar em C:\xampp\htdocs\app\web\
    # (instalacao anterior ao vestcasa); uploads novos vao para esse caminho
    bases_fallback = [
        r"C:\xampp\htdocs\app\web",
        "/var/www/html/app/web",
    ]

    for base_fb in bases_fallback:
        caminho_fb = os.path.abspath(
            os.path.join(base_fb, caminho_relativo)
        )
        if os.path.isfile(caminho_fb):
            logging.info(
                f"Foto encontrada no caminho alternativo: {caminho_fb}"
            )
            return caminho_fb

    logging.warning(
        f"Foto nao encontrada no disco: {caminho_fisico}"
    )

    return None

# ======================================================
# LIMPAR PREVIEWS DE FOTOS DO PORTAL
# Remove todos os previews do UL de fotos
# ======================================================

def limpar_previews_fotos_portal(driver):

    wait = WebDriverWait(driver, 15)

    try:

        logging.info("Limpando previews de fotos do portal...")

        total_removidas = 0

        xpath_ul_fotos = "//form//ul[.//img[@alt='preview']]"

        # tenta localizar o UL
        try:

            ul_fotos = wait.until(
                EC.presence_of_element_located(
                    (
                        By.XPATH,
                        xpath_ul_fotos
                    )
                )
            )

        except Exception:

            logging.info(
                "UL de fotos nao encontrado. Nenhum preview para limpar."
            )

            return True

        while True:

            # sempre relê os itens para evitar stale element
            itens_preview = ul_fotos.find_elements(
                By.XPATH,
                ".//div[contains(@class,'MuiListItem-root')]"
            )

            # filtra somente itens que possuem img preview blob
            itens_com_preview = []

            for item in itens_preview:

                try:

                    imgs = item.find_elements(
                        By.XPATH,
                        ".//img[@alt='preview' and starts-with(@src,'blob:')]"
                    )

                    if len(imgs) > 0:

                        itens_com_preview.append(item)

                except Exception:

                    continue

            qtd_antes = len(itens_com_preview)

            logging.info(
                f"Previews encontrados antes da limpeza: {qtd_antes}"
            )

            if qtd_antes == 0:

                break

            item_preview = itens_com_preview[0]

            try:

                botao_remover = item_preview.find_element(
                    By.XPATH,
                    ".//button[contains(@class,'MuiIconButton-root')]"
                )

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    botao_remover
                )

                time.sleep(0.5)

                # click real + eventos mouse
                driver.execute_script(
                    """
                    const btn = arguments[0];

                    btn.dispatchEvent(new MouseEvent('mouseover', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    }));

                    btn.dispatchEvent(new MouseEvent('mousedown', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    }));

                    btn.dispatchEvent(new MouseEvent('mouseup', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    }));

                    btn.dispatchEvent(new MouseEvent('click', {
                        bubbles: true,
                        cancelable: true,
                        view: window
                    }));
                    """,
                    botao_remover
                )

                logging.info(
                    "Clique no botao remover preview executado."
                )

                time.sleep(1)

                # aguarda reduzir a quantidade
                try:

                    wait.until(
                        lambda d: len(
                            d.find_elements(
                                By.XPATH,
                                xpath_ul_fotos + "//img[@alt='preview' and starts-with(@src,'blob:')]"
                            )
                        ) < qtd_antes
                    )

                    total_removidas += 1

                    logging.info(
                        f"Preview removido. Total removidas: {total_removidas}"
                    )

                except Exception:

                    logging.warning(
                        "Quantidade de previews nao reduziu apos clique."
                    )

                    # fallback: tenta clicar no SVG/path
                    try:

                        svg = item_preview.find_element(
                            By.XPATH,
                            ".//*[name()='svg']"
                        )

                        driver.execute_script(
                            """
                            arguments[0].dispatchEvent(new MouseEvent('click', {
                                bubbles: true,
                                cancelable: true,
                                view: window
                            }));
                            """,
                            svg
                        )

                        time.sleep(1)

                    except Exception as e_svg:

                        logging.warning(
                            f"Falha ao clicar no SVG remover: {repr(e_svg)}"
                        )

                        break

            except Exception as e:

                logging.warning(
                    f"Erro ao remover preview: {repr(e)}"
                )

                break

        qtd_final = len(
            driver.find_elements(
                By.XPATH,
                xpath_ul_fotos + "//img[@alt='preview' and starts-with(@src,'blob:')]"
            )
        )

        logging.info(
            f"Limpeza finalizada. Removidas: {total_removidas} | Restantes: {qtd_final}"
        )

        # libera blob URLs acumuladas no browser para evitar vazamento de memoria
        try:
            driver.execute_script("""
                document.querySelectorAll("img[src^='blob:']").forEach(function(img) {
                    try { URL.revokeObjectURL(img.src); } catch(e) {}
                });
            """)
        except Exception:
            pass

        return qtd_final == 0

    except Exception as e:

        logging.warning(
            f"Erro geral ao limpar previews de fotos: {repr(e)}"
        )

        return False

# ======================================================
# ANEXAR FOTOS NO PORTAL
# ======================================================

def anexar_fotos_item_portal(driver, caminhos_fotos):

    wait = WebDriverWait(driver, 30)

    if not caminhos_fotos:

        logging.warning(
            "Nenhuma foto para anexar."
        )

        # mesmo sem fotos, limpa qualquer preview antigo
        limpar_previews_fotos_portal(driver)

        return True

    try:

        logging.info(
            f"Anexando {len(caminhos_fotos)} foto(s) no portal..."
        )

        # ==================================================
        # LIMPA PREVIEWS ANTIGOS ANTES DE NOVO UPLOAD
        # ==================================================

        limpar_previews_fotos_portal(driver)
        limpar_input_upload_fotos_portal(driver)

        time.sleep(1)

        # ==================================================
        # TENTA LOCALIZAR INPUT FILE
        # ==================================================

        input_file = None

        seletores_input = [
            "//input[@type='file']",
            "//input[contains(@accept,'image')]",
            "//input[contains(@name,'foto')]",
            "//input[contains(@name,'file')]",
            "//input[contains(@id,'foto')]",
            "//input[contains(@id,'file')]"
        ]

        for seletor in seletores_input:

            try:

                input_file = wait.until(
                    EC.presence_of_element_located(
                        (
                            By.XPATH,
                            seletor
                        )
                    )
                )

                logging.info(
                    f"Input file localizado: {seletor}"
                )

                break

            except Exception:

                continue

        if not input_file:

            logging.error(
                "Input file nao encontrado no portal."
            )

            salvar_screenshot(
                driver,
                "erro_input_file_foto"
            )

            return False

        # ==================================================
        # VALIDA ARQUIVOS
        # ==================================================

        arquivos_validos = []

        for caminho in caminhos_fotos:

            if caminho and os.path.isfile(caminho):

                arquivos_validos.append(
                    caminho
                )

            else:

                logging.warning(
                    f"Arquivo invalido ignorado: {caminho}"
                )

        if not arquivos_validos:

            logging.error(
                "Nenhum arquivo fisico valido para upload."
            )

            salvar_screenshot(
                driver,
                "erro_fotos_invalidas_upload"
            )

            return False

        # ==================================================
        # ENVIA ARQUIVOS
        # Selenium aceita mUltiplos arquivos separados por \n
        # ==================================================

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            input_file
        )

        time.sleep(0.5)

        input_file.send_keys(
            "\n".join(arquivos_validos)
        )

        logging.info(
            "Fotos enviadas para o input file."
        )

        try:

            wait.until(
                lambda d: d.execute_script(
                    "return arguments[0].files ? arguments[0].files.length : 0;",
                    input_file
                ) >= len(arquivos_validos)
            )

            logging.info(
                f"Input file confirmou {len(arquivos_validos)} foto(s)."
            )

        except Exception:

            logging.warning(
                "Input file nao confirmou todas as fotos. Vou aguardar previews mesmo assim."
            )

        previews_ok = aguardar_previews_fotos_carregarem(
            driver,
            len(arquivos_validos),
            timeout=90
        )

        if not previews_ok:

            logging.error(
                "Portal nao carregou os previews das fotos."
            )

            return False

        aguardar_processamento_final_fotos(
            len(arquivos_validos)
        )

        return True

    except Exception as e:

        logging.error(
            f"Erro ao anexar fotos: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_anexar_fotos"
        )

        return False

def aguardar_preview_limpar(driver, timeout=8):
    try:
        WebDriverWait(driver, timeout).until(
            lambda d: len(d.find_elements(
                By.CSS_SELECTOR,
                "ul img[alt='preview'], ul img"
            )) == 0
        )

        logging.info("Preview antigo limpo.")
        return True

    except Exception:
        logging.warning("Preview antigo nao limpou totalmente.")
        return False

def limpar_previews_fotos_portal_js(driver):

    try:

        logging.info("Limpando previews via JavaScript direto...")

        total_removidas = 0

        while True:

            qtd_antes = driver.execute_script(
                """
                return document.querySelectorAll(
                    "form ul img[alt='preview'][src^='blob:']"
                ).length;
                """
            )

            logging.info(
                f"Previews encontrados JS: {qtd_antes}"
            )

            if qtd_antes == 0:

                break

            clicou = driver.execute_script(
                """
                const ul = document.querySelector(
                    "form ul"
                );

                if (!ul) {
                    return false;
                }

                const img = ul.querySelector("img[alt='preview'][src^='blob:']");

                if (!img) {
                    return false;
                }

                const item = img.closest(".MuiListItem-root");

                if (!item) {
                    return false;
                }

                const btn = item.querySelector("button.MuiIconButton-root");

                if (!btn) {
                    return false;
                }

                btn.scrollIntoView({
                    block: "center"
                });

                btn.dispatchEvent(new MouseEvent("mouseover", {
                    bubbles: true,
                    cancelable: true,
                    view: window
                }));

                btn.dispatchEvent(new MouseEvent("mousedown", {
                    bubbles: true,
                    cancelable: true,
                    view: window
                }));

                btn.dispatchEvent(new MouseEvent("mouseup", {
                    bubbles: true,
                    cancelable: true,
                    view: window
                }));

                btn.dispatchEvent(new MouseEvent("click", {
                    bubbles: true,
                    cancelable: true,
                    view: window
                }));

                return true;
                """
            )

            if not clicou:

                logging.warning(
                    "JS nao conseguiu clicar no botao remover."
                )

                break

            time.sleep(1)

            qtd_depois = driver.execute_script(
                """
                return document.querySelectorAll(
                    "form ul img[alt='preview'][src^='blob:']"
                ).length;
                """
            )

            logging.info(
                f"Depois do clique JS: {qtd_depois}"
            )

            if qtd_depois < qtd_antes:

                total_removidas += 1

            else:

                logging.warning(
                    "Preview nao reduziu apos clique JS."
                )

                break

        qtd_final = driver.execute_script(
            """
            return document.querySelectorAll(
                "form ul img[alt='preview'][src^='blob:']"
            ).length;
            """
        )

        logging.info(
            f"Limpeza JS finalizada. Removidas: {total_removidas} | Restantes: {qtd_final}"
        )

        # libera blob URLs acumuladas no browser para evitar vazamento de memoria
        try:
            driver.execute_script("""
                document.querySelectorAll("img[src^='blob:']").forEach(function(img) {
                    try { URL.revokeObjectURL(img.src); } catch(e) {}
                });
            """)
        except Exception:
            pass

        return qtd_final == 0

    except Exception as e:

        logging.warning(
            f"Erro limpar previews JS: {repr(e)}"
        )

        return False

# ======================================================
# BOTaO DESABILITADO
# ======================================================

def botao_desabilitado(botao):

    try:

        disabled = botao.get_attribute(
            "disabled"
        )

        classe = botao.get_attribute(
            "class"
        ) or ""

        aria_disabled = botao.get_attribute(
            "aria-disabled"
        )

        return (
            disabled is not None
            or
            aria_disabled == "true"
            or
            "Mui-disabled" in classe
        )

    except Exception:

        return True

# ======================================================
# LOCALIZAR BOTaO PAGINAcaO
# ======================================================

def localizar_botao_paginacao(driver, direcao):

    direcao = normalizar_texto(direcao)

    return driver.execute_script(
        """
        const direcao = arguments[0];

        const normalizar = (valor) => (valor || "")
            .normalize("NFD")
            .replace(/[\\u0300-\\u036f]/g, "")
            .toLowerCase();

        const botoes = Array.from(
            document.querySelectorAll("button, [role='button']")
        );

        return botoes.find((botao) => {
            const texto = normalizar([
                botao.getAttribute("aria-label"),
                botao.getAttribute("title"),
                botao.innerText,
                botao.textContent
            ].filter(Boolean).join(" "));

            if (direcao === "ANTERIOR") {
                return texto.includes("pagina anterior") ||
                       texto.includes("anterior") ||
                       texto.includes("previous");
            }

            return texto.includes("proxima pagina") ||
                   texto.includes("proximo") ||
                   texto.includes("proxima") ||
                   texto.includes("next");
        }) || null;
        """,
        direcao
    )

# ======================================================
# VOLTAR PRIMEIRA PaGINA
# ======================================================

def voltar_primeira_pagina(driver):

    while True:

        try:

            botao_anterior = localizar_botao_paginacao(
                driver,
                "ANTERIOR"
            )

            if not botao_anterior:

                logging.info(
                    "Botao anterior nao localizado; assumindo primeira pagina."
                )

                break

            if botao_desabilitado(botao_anterior):

                break

            primeira_linha_antes = ""

            try:
                primeira_linha_antes = driver.find_element(
                    By.XPATH,
                    "(//div[@role='row' and @data-id])[1]"
                ).get_attribute("data-id")
            except Exception:
                primeira_linha_antes = ""

            logging.info(
                "Voltando pagina..."
            )

            clicar_elemento(
                driver,
                botao_anterior,
                "Botao anterior"
            )

            try:

                WebDriverWait(driver, 10).until(
                    lambda d: (
                        botao_desabilitado(
                            localizar_botao_paginacao(d, "ANTERIOR")
                        )
                        or
                        (
                            primeira_linha_antes
                            and
                            d.find_element(
                                By.XPATH,
                                "(//div[@role='row' and @data-id])[1]"
                            ).get_attribute("data-id") != primeira_linha_antes
                        )
                    )
                )

            except Exception:

                time.sleep(2)

        except Exception:

            break

# ======================================================
# IR PARA PaGINA ESPECIFICA
# ======================================================

def ir_para_pagina(driver, pagina_destino):

    voltar_primeira_pagina(driver)

    pagina_atual = 1

    while pagina_atual < int(pagina_destino):

        try:

            botao_proximo = localizar_botao_paginacao(
                driver,
                "PROXIMA"
            )

            if not botao_proximo:

                return False

            if botao_desabilitado(botao_proximo):

                return False

            logging.info(
                f"Indo para pagina {pagina_atual + 1}..."
            )

            clicar_elemento(
                driver,
                botao_proximo,
                "Botao proximo"
            )


            pagina_atual += 1

            time.sleep(3)

        except Exception as e:

            logging.error(
                f"Erro ir para pagina {pagina_destino}: {e}"
            )

            return False

    return True

# ======================================================
# NORMALIZAR TEXTO
# ======================================================

def normalizar_texto(valor, upper=True):

    if valor is None:
        return ""

    texto = (
        str(valor)
        .replace("\xa0", " ")
        .replace("\n", " ")
        .replace("\r", " ")
        .strip()
    )

    if upper:
        texto = texto.upper()

    return texto

# ======================================================
# NORMALIZAR DECIMAL
# ======================================================

def normalizar_decimal(valor):

    try:

        if valor is None:

            return None

        valor = normalizar_texto(valor)

        if not valor:

            return None

        valor = (
            valor
            .replace("R$", "")
            .replace(" ", "")
            .strip()
        )

        if not valor or valor.upper() == "NULL":

            return None

        # formato brasileiro: 1.234,56
        if "," in valor:

            valor = valor.replace(".", "")
            valor = valor.replace(",", ".")

        # formato americano/sistema: 7.48000
        return round(
            float(valor),
            2
        )

    except Exception as e:

        logging.warning(
            f"Erro normalizar decimal [{valor}]: {e}"
        )

        return None

# ======================================================
# ROLAR GRID PARA DIREITA / ESQUERDA
# ======================================================

def rolar_grid_horizontal(driver, direcao="direita"):

    try:

        scroller = driver.find_element(
            By.XPATH,
            "//div[contains(@class,'MuiDataGrid-virtualScroller')]"
        )

        if direcao == "direita":

            driver.execute_script(
                "arguments[0].scrollLeft = arguments[0].scrollWidth;",
                scroller
            )

        else:

            driver.execute_script(
                "arguments[0].scrollLeft = 0;",
                scroller
            )

        time.sleep(1)

        return True

    except Exception as e:

        logging.warning(
            f"Nao conseguiu rolar grid horizontal: {e}"
        )

        return False

# ======================================================
# LER TEXTO CÉLULA POR DATA-FIELD
# ======================================================

def ler_celula_grid(linha, campo):

    try:

        return normalizar_texto(
            linha.find_element(
                By.XPATH,
                f".//div[@data-field='{campo}']"
            ).text
        )

    except Exception:

        return ""

# ======================================================
# AGUARDAR PREVIEWS DAS FOTOS CARREGAREM
# ======================================================

def anexar_fotos_modal_galeria_item_unico(driver, caminhos_fotos):
    wait = WebDriverWait(driver, 60)

    try:
        if not caminhos_fotos:
            logging.warning("Nenhuma foto para anexar na galeria.")
            return True

        fotos_validas = []

        for caminho in caminhos_fotos:
            if caminho and os.path.isfile(caminho):
                fotos_validas.append(caminho)
            else:
                logging.warning(f"Foto invalida ignorada na galeria: {caminho}")

        if not fotos_validas:
            logging.warning("Nenhuma foto valida para anexar na galeria.")
            return True

        total_fotos = len(fotos_validas)

        logging.info(
            f"Anexando todas as fotos de uma vez na galeria: {total_fotos} foto(s)."
        )

        # ==================================================
        # AGUARDA MODAL
        # ==================================================
        wait.until(
            EC.visibility_of_element_located(
                (
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                )
            )
        )

        logging.info("Modal de galeria visivel.")

        # ==================================================
        # CONTA IMAGENS EXISTENTES ANTES DO UPLOAD
        # ==================================================
        qtd_inicial_galeria = contar_imagens_galeria_modal(driver)

        qtd_esperada_galeria = qtd_inicial_galeria + total_fotos

        logging.info(
            f"Galeria antes do upload: {qtd_inicial_galeria}. "
            f"Esperado apos salvar: {qtd_esperada_galeria}."
        )

        # ==================================================
        # LOCALIZA INPUT FILE DIRETO
        # ==================================================
        input_file = wait.until(
            lambda d: d.execute_script(
                """
                const dialogs = Array.from(document.querySelectorAll("div[role='dialog']"));

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return null;
                }

                const input = dialog.querySelector(
                    "input[type='file'][accept*='image'], input[type='file']"
                );

                return input || null;
                """
            )
        )

        logging.info("Input de fotos localizado no modal.")

        # ==================================================
        # ENVIA TODAS AS FOTOS UMA UNICA VEZ
        # ==================================================
        input_file.send_keys(
            "\n".join(fotos_validas)
        )

        logging.info(
            f"{total_fotos} foto(s) enviada(s) para o input da galeria."
        )

        # ==================================================
        # CONFIRMA INPUT
        # ==================================================
        try:
            wait.until(
                lambda d: d.execute_script(
                    """
                    const input = arguments[0];
                    return input.files ? input.files.length : 0;
                    """,
                    input_file
                ) >= total_fotos
            )

            logging.info(
                f"Input confirmou {total_fotos} arquivo(s)."
            )

        except Exception:
            logging.warning(
                "Input nao confirmou todas as fotos, tentando salvar mesmo assim."
            )

        time.sleep(3)

        # ==================================================
        # LOCALIZA BOTAO SALVAR
        # O botao so aparece depois das fotos anexadas
        # ==================================================
        botao_salvar = wait.until(
            lambda d: d.execute_script(
                """
                const dialogs = Array.from(document.querySelectorAll("div[role='dialog']"));

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return null;
                }

                const buttons = Array.from(dialog.querySelectorAll("button"));

                const salvar = buttons.find((button) => {
                    const texto = (button.innerText || "").trim();

                    const disabled =
                        button.disabled ||
                        button.getAttribute("aria-disabled") === "true" ||
                        (button.className || "").includes("Mui-disabled");

                    return texto === "Salvar" && !disabled;
                });

                return salvar || null;
                """
            )
        )

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            botao_salvar
        )

        time.sleep(1)

        clicar_elemento(
            driver,
            botao_salvar,
            "Botao Salva"
        )

        logging.info("Botao Salvar da galeria clicado.")

        # ==================================================
        # FUNCOES AUXILIARES DO MODAL
        # ==================================================
        def modal_aberto(d):
            return len(
                d.find_elements(
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                )
            ) > 0

        def tem_loading_modal(d):
            return d.execute_script(
                """
                const dialogs = Array.from(document.querySelectorAll("div[role='dialog']"));

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return false;
                }

                return !!dialog.querySelector(
                    "[role='progressbar'], .MuiCircularProgress-root, .MuiLinearProgress-root"
                );
                """
            )

        # ==================================================
        # AGUARDA PROCESSAMENTO INICIAR OU CONTAGEM AUMENTAR
        # ==================================================
        try:
            WebDriverWait(driver, 240).until(
                lambda d: (
                    not modal_aberto(d)
                    or contar_imagens_galeria_modal(d) >= qtd_esperada_galeria
                    or tem_loading_modal(d)
                )
            )

            logging.info(
                f"Upload/processamento detectado. "
                f"Imagens: {contar_imagens_galeria_modal(driver)}/{qtd_esperada_galeria}"
            )

        except Exception:
            logging.warning(
                "Nao confirmou inicio do processamento ou aumento da galeria."
            )

        # ==================================================
        # AGUARDA FINALIZAR
        # ==================================================
        try:
            WebDriverWait(driver, 300).until(
                lambda d: (
                    not modal_aberto(d)
                    or (
                        not tem_loading_modal(d)
                        and contar_imagens_galeria_modal(d) >= qtd_esperada_galeria
                    )
                )
            )

            logging.info(
                f"Processamento finalizado. "
                f"Imagens: {contar_imagens_galeria_modal(driver)}/{qtd_esperada_galeria}"
            )

        except Exception:
            logging.warning(
                f"Nao confirmou fim do processamento ou total esperado "
                f"de {qtd_esperada_galeria} imagens."
            )

        # tempo extra para backend gravar
        time.sleep(8)

        # ==================================================
        # FECHA MODAL
        # ==================================================
        try:
            if modal_aberto(driver):
                botao_fechar = driver.find_element(
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]//button[.//*[name()='svg']]"
                )

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    botao_fechar
                )

                time.sleep(0.5)

                clicar_elemento(
                    driver,
                    botao_fechar,
                    "Botao Fechar"
                )


                logging.info("Modal de galeria fechado pelo botao X.")

                WebDriverWait(driver, 30).until(
                    EC.invisibility_of_element_located(
                        (
                            By.XPATH,
                            "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                        )
                    )
                )

        except Exception as e:
            logging.warning(
                f"Nao conseguiu fechar modal pelo X. Tentando ESC. Erro: {repr(e)}"
            )

            try:
                driver.switch_to.active_element.send_keys(Keys.ESCAPE)

                WebDriverWait(driver, 10).until(
                    EC.invisibility_of_element_located(
                        (
                            By.XPATH,
                            "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                        )
                    )
                )

            except Exception as e_esc:
                logging.warning(
                    f"ESC tambem nao fechou modal: {repr(e_esc)}"
                )

        time.sleep(2)

        return True

    except Exception as e:
        logging.error(
            f"Erro ao anexar fotos de uma vez na galeria: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_anexar_fotos_galeria_unico"
        )

        return False

def aguardar_previews_fotos_carregarem(driver, total_esperado, timeout=60):
    try:
        logging.info(
            f"Aguardando carregar previews das fotos. Esperado: {total_esperado}"
        )

        wait = WebDriverWait(driver, timeout)

        def previews_ok(d):
            previews = d.find_elements(
                By.XPATH,
                "//ul//img[@alt='preview']"
            )

            qtd = len(previews)

            logging.info(
                f"Previews carregados: {qtd}/{total_esperado}"
            )

            return qtd >= total_esperado

        wait.until(previews_ok)

        time.sleep(2)

        logging.info(
            "Todos os previews das fotos foram carregados."
        )

        return True

    except Exception as e:
        logging.warning(
            f"Nem todos os previews carregaram no tempo esperado: {repr(e)}"
        )

        salvar_screenshot(
            driver,
            "erro_previews_fotos_nao_carregaram"
        )

        return False

# ======================================================
# VALIDAR ITENS EXISTENTES
# COM PAGINAcaO, OBSERVAcaO E PREcO
# ======================================================

def validar_itens_existentes(
    driver,
    ler_detalhes=True,
    log_linhas=True
):

    try:

        wait = WebDriverWait(driver, 30)

        itens_existentes = []

        logging.info(
            "Lendo itens ja inseridos no portal."
        )

        time.sleep(2)

        # ==========================================
        # VOLTA PRIMEIRA PaGINA
        # ==========================================

        voltar_primeira_pagina(driver)

        pagina = 1

        # ==========================================
        # LOOP PAGINAcaO
        # ==========================================

        while True:

            logging.info(
                f"Lendo pagina {pagina}."
            )

            time.sleep(2 if ler_detalhes else 0.5)

            # volta grid para esquerda para ler campos principais
            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            logging.info(
                f"Total linhas pagina: {len(linhas)}"
            )

            dados_linhas = []

            # ======================================
            # PRIMEIRA PASSAGEM:
            # LÊ CoDIGO / QTD / TIPO
            # ======================================

            for index, linha in enumerate(linhas):

                try:

                    codigo_barras = ler_celula_grid_completa(
                        linha,
                        "codigo_barras"
                    )

                    if not codigo_barras:

                        codigo_barras = ler_celula_grid_completa(
                            linha,
                            "codigoBarras"
                        )

                    quantidade_texto = ler_celula_grid_completa(
                        linha,
                        "quantidade"
                    )

                    quantidade = int(
                        float(
                            quantidade_texto
                            .replace(",", ".")
                        )
                    ) if quantidade_texto else 0

                    dados_linhas.append(
                        {
                            "index": index,
                            "codigo_barras": normalizar_texto(
                                codigo_barras
                            ),
                            "quantidade": quantidade,
                            "tipo_avaria": "",
                            "observacao": "",
                            "preco_contrapartida": None,
                            "pagina": pagina
                        }
                    )

                except Exception as e:

                    logging.error(
                        f"Erro lendo campos principais linha {index}: {repr(e)}"
                    )

            # ======================================
            # SEGUNDA PASSAGEM:
            # ROLA PARA DIREITA E LÊ TIPO AVARIA
            # (sempre, pois fica fora da viewport
            # virtualizada quando a grid esta a
            # esquerda) E OBSERVAcaO/PREcO
            # (se ler_detalhes)
            # ======================================

            rolar_grid_horizontal(
                driver,
                "direita"
            )

            time.sleep(0.5)

            linhas_direita = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            for item in dados_linhas:

                try:

                    index = item["index"]

                    if index >= len(linhas_direita):

                        continue

                    linha_direita = linhas_direita[index]

                    tipo_avaria = ler_celula_grid_completa(
                        linha_direita,
                        "tipoAvaria"
                    )

                    if not tipo_avaria:

                        tipo_avaria = ler_celula_grid_completa(
                            linha_direita,
                            "tipo_avaria"
                        )

                    if not tipo_avaria:

                        tipo_avaria = ler_celula_grid_completa(
                            linha_direita,
                            "descricao_avaria"
                        )

                    item["tipo_avaria"] = normalizar_texto(
                        tipo_avaria
                    )

                    if ler_detalhes:

                        observacao = ler_celula_grid_completa(
                            linha_direita,
                            "observacao"
                        )

                        preco_texto = ler_celula_grid_completa(
                            linha_direita,
                            "preco_contratipo"
                        )

                        if not preco_texto:

                            preco_texto = ler_celula_grid_completa(
                                linha_direita,
                                "preco_contrapartida"
                            )

                        item["observacao"] = normalizar_texto(
                            observacao
                        )

                        item["preco_contrapartida"] = normalizar_decimal(
                            preco_texto
                        )

                        if log_linhas:

                            logging.info(
                                f"Preco bruto site => "
                                f"Codigo: {item['codigo_barras']} | "
                                f"Valor: [{preco_texto}]"
                            )

                except Exception as e:

                    logging.warning(
                        f"Erro lendo tipo/observacao/preco linha {item['index']}: {repr(e)}"
                    )

            # ======================================
            # SALVA RESULTADO
            # ======================================

            for item in dados_linhas:

                if not item.get("codigo_barras"):

                    logging.warning(
                        f"Linha ignorada sem codigo de barras na pagina {pagina}."
                    )

                    continue

                itens_existentes.append(
                    item
                )

                if log_linhas:

                    logging.info(
                        f"Existente => "
                        f"Codigo: {item['codigo_barras']} | "
                        f"Qtd: {item['quantidade']} | "
                        f"Tipo: {item['tipo_avaria']} | "
                        f"OBS.: {item['observacao']} | "
                        f"Preco: {item['preco_contrapartida']} | "
                        f"Pagina: {item['pagina']}"
                    )

            # volta para esquerda antes de paginar
            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            # ======================================
            # PRoXIMA PaGINA
            # ======================================

            try:

                logging.info(
                    "Tentando localizar botão próxima..."
                )

                botao_proximo = WebDriverWait(driver, 15).until(
                    EC.visibility_of_element_located(
                        (
                            By.XPATH,
                            "//button[@aria-label='Ir para a próxima página']"
                        )
                    )
                )

                logging.info(
                    "Botão próxima localizado."
                )

                disabled = botao_proximo.get_attribute(
                    "disabled"
                )

                classe = botao_proximo.get_attribute(
                    "class"
                ) or ""

                # logging.info(
                #     f"disabled={disabled} | class={classe}"
                # )

                if (
                    disabled is not None
                    or
                    "Mui-disabled" in classe
                ):

                    logging.info(
                        "Última página."
                    )

                    break

                driver.execute_script(
                    "arguments[0].click();",
                    botao_proximo
                )

                logging.info(
                    "Clique próxima executado."
                )

                pagina += 1

                time.sleep(4 if ler_detalhes else 1)

            except Exception as e:

                logging.error(
                    f"ERRO PAGINACAO REAL: {repr(e)}"
                )

                break

        logging.info(
            f"Total itens encontrados no portal: {len(itens_existentes)}"
        )

        return itens_existentes

    except TimeoutException:

        logging.info(
            "Nenhum item encontrado no portal ou grid vazio."
        )

        return []

    except Exception as e:

        logging.error(
            f"Erro validar itens existentes: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_validar_itens"
        )

        return []

# ======================================================
# REMOVER ITEM POR CoDIGO E TIPO
# PROCURA EM TODAS AS PaGINAS
# ======================================================

def confirmar_modal_exclusao(driver, timeout=30):

    wait = WebDriverWait(driver, timeout)

    try:

        botao_sim = wait.until(
            lambda d: d.execute_script("""
                const candidatos = Array.from(document.querySelectorAll(
                    "div[role='dialog'] button, .MuiDialog-root button, " +
                    ".MuiModal-root button, [role='presentation'] button, button"
                ));

                return candidatos.find((botao) => {
                    const texto = (botao.innerText || botao.textContent || "")
                        .trim()
                        .toUpperCase();

                    const visivel = !!(
                        botao.offsetWidth ||
                        botao.offsetHeight ||
                        botao.getClientRects().length
                    );

                    const desabilitado = botao.disabled ||
                        botao.getAttribute("aria-disabled") === "true";

                    return visivel &&
                        !desabilitado &&
                        ["SIM", "CONFIRMAR", "OK"].includes(texto);
                }) || null;
            """)
        )

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            botao_sim
        )

        time.sleep(0.5)

        clicar_elemento(
            driver,
            botao_sim,
            "botao_sim"
        )

        logging.info(
            "Botao SIM clicado."
        )

        try:
            WebDriverWait(driver, 15).until(
                lambda d: not d.execute_script("""
                    return Array.from(document.querySelectorAll(
                        "div[role='dialog'], .MuiDialog-root, .MuiModal-root"
                    )).some((item) => {
                        const visivel = !!(
                            item.offsetWidth ||
                            item.offsetHeight ||
                            item.getClientRects().length
                        );
                        const texto = (item.innerText || item.textContent || "")
                            .toUpperCase();
                        return visivel && (
                            texto.includes("EXCLUIR") ||
                            texto.includes("EXCLUS") ||
                            texto.includes("SIM")
                        );
                    });
                """)
            )

            logging.info(
                "Modal fechado."
            )

        except Exception:

            logging.warning(
                "Modal nao fechou no tempo esperado; seguindo para validar a tabela."
            )

        return True

    except Exception as e:

        logging.error(
            f"Nao foi possivel confirmar exclusao: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_confirmar_exclusao"
        )

        return False

def remover_item_linha(
    driver,
    codigo_barras,
    tipo_avaria,
    pagina_inicial=None
):

    wait = WebDriverWait(driver, 30)

    try:

        codigo_barras = str(
            codigo_barras
        ).strip()

        tipo_avaria = normalizar_texto(
            tipo_avaria
        )

        logging.info(
            f"Removendo => {codigo_barras} | {tipo_avaria}"
        )

        if pagina_inicial:

            ir_para_pagina(
                driver,
                pagina_inicial
            )

            pagina = int(
                pagina_inicial
            )

        else:

            voltar_primeira_pagina(
                driver
            )

            pagina = 1

        while True:

            logging.info(
                f"Procurando item na pagina {pagina}..."
            )

            time.sleep(3)

            # ==========================================
            # PRE-LEITURA: codigo_barras (esquerda) e
            # tipo_avaria (direita), pois nao ficam
            # ambos na viewport virtualizada ao mesmo
            # tempo
            # ==========================================

            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            time.sleep(0.5)

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            codigos = []

            for linha_codigo in linhas:

                try:

                    codigos.append(
                        linha_codigo.find_element(
                            By.XPATH,
                            ".//div[@data-field='codigo_barras' or @data-field='codigoBarras']"
                        ).text.strip()
                    )

                except Exception:

                    codigos.append("")

            rolar_grid_horizontal(
                driver,
                "direita"
            )

            time.sleep(0.5)

            linhas_direita = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            tipos = []

            for linha_tipo in linhas_direita:

                try:

                    tipos.append(
                        normalizar_texto(
                            linha_tipo.find_element(
                                By.XPATH,
                                ".//div[@data-field='tipoAvaria' or @data-field='tipo_avaria' or @data-field='descricao_avaria']"
                            ).text.strip()
                        )
                    )

                except Exception:

                    tipos.append("")

            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            time.sleep(0.5)

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            logging.info(
                f"Linhas na pagina: {len(linhas)}"
            )

            for idx, linha in enumerate(linhas):

                encontrou_linha_para_remover = False

                try:

                    codigo = codigos[idx] if idx < len(codigos) else ""

                    tipo = tipos[idx] if idx < len(tipos) else ""

                    logging.info(
                        f"Comparando linha => {codigo} | {tipo}"
                    )

                    if (
                        codigo == codigo_barras
                        and
                        tipo == tipo_avaria
                    ):

                        encontrou_linha_para_remover = True

                        logging.info(
                            "Linha encontrada para remocao."
                        )

                        data_id = linha.get_attribute(
                            "data-id"
                        )

                        botao_excluir = linha.find_element(
                            By.XPATH,
                            ".//span[@aria-label='Excluir']//button"
                        )

                        driver.execute_script(
                            "arguments[0].scrollIntoView({block:'center'});",
                            botao_excluir
                        )

                        time.sleep(1)

                        clicar_elemento(
                            driver,
                            botao_excluir,
                            "Botao Excluir"
                        )

                        logging.info(
                            "Botao excluir clicado."
                        )

                        if not confirmar_modal_exclusao(driver):

                            logging.error(
                                "Falha ao confirmar modal de exclusao."
                            )

                            return False

                        time.sleep(3)

                        try:
                            WebDriverWait(driver, 15).until(
                                lambda d: not d.find_elements(
                                    By.XPATH,
                                    f"//div[@role='row' and @data-id='{data_id}']"
                                )
                            )

                        except Exception:

                            logging.warning(
                                "Linha ainda aparece apos confirmar exclusao; "
                                "a validacao externa vai conferir a quantidade."
                            )

                        logging.info(
                            "Item removido com sucesso."
                        )

                        return True

                except StaleElementReferenceException:

                    logging.info(
                        "Linha ficou stale, considerando removida."
                    )

                    return True

                except Exception as e:

                    if encontrou_linha_para_remover:

                        logging.error(
                            f"Erro ao tentar remover linha encontrada: {repr(e)}",
                            exc_info=True
                        )

                        salvar_screenshot(
                            driver,
                            "erro_remover_linha_encontrada"
                        )

                        return False

                    logging.warning(
                        f"Erro ao ler linha para remocao: {repr(e)}"
                    )

                    continue

            try:

                botao_proxima = driver.find_element(
                    By.XPATH,
                    "//button[@aria-label='Ir para a proxima pagina']"
                )

                if botao_desabilitado(botao_proxima):

                    logging.warning(
                        "Item nao encontrado em nenhuma pagina."
                    )

                    return False

                logging.info(
                    "Indo para proxima pagina para remover..."
                )

                clicar_elemento(
                    driver,
                    botao_proxima,
                    "botao_proxima"
                )


                pagina += 1

                time.sleep(3)

            except Exception:

                logging.warning(
                    "Paginacao nao encontrada ou Ultima pagina."
                )

                return False

    except Exception as e:

        erro = str(
            e
        ).lower()

        if "stale element reference" in erro:

            logging.info(
                "Elemento stale apos remocao, considerando sucesso."
            )

            return True

        logging.error(
            f"Erro remover item: {e}"
        )

        salvar_screenshot(
            driver,
            "erro_remover_item"
        )

        return False


def contar_imagens_galeria_modal(d):
    return d.execute_script(
        """
        const dialogs = Array.from(document.querySelectorAll("div[role='dialog']"));

        const dialog = dialogs.find((item) => {
            const text = item.innerText || "";
            return text.includes("Edição Detalhes Produtos") ||
                   (text.includes("Detalhes") && text.includes("Produtos"));
        });

        if (!dialog) {
            return 0;
        }

        return dialog.querySelectorAll(
            "ul.thumbs li.thumb img, ul img, img[src^='blob:']"
        ).length;
        """
    )

# ======================================================
# LOCALIZAR ITEM E ABRIR GALERIA
# ======================================================

def abrir_galeria_item_linha(
    driver,
    codigo_barras,
    tipo_avaria,
    quantidade=None,
    pagina_inicial=None
):

    wait = WebDriverWait(driver, 30)

    try:

        codigo_barras = str(codigo_barras).strip()
        tipo_avaria = normalizar_texto(tipo_avaria)

        quantidade_normalizada = None

        if quantidade is not None:

            try:
                quantidade_normalizada = int(float(quantidade))
            except Exception:
                quantidade_normalizada = None

        logging.info(
            f"Abrindo galeria => {codigo_barras} | "
            f"{tipo_avaria} | Qtd: {quantidade_normalizada}"
        )

        if pagina_inicial:

            ir_para_pagina(driver, pagina_inicial)
            pagina = int(pagina_inicial)

        else:

            voltar_primeira_pagina(driver)
            pagina = 1

        while True:

            logging.info(
                f"Procurando item para galeria na pagina {pagina}..."
            )

            time.sleep(3)

            rolar_grid_horizontal(driver, "esquerda")

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            # ==================================================
            # LE TIPO AVARIA COM A GRID ROLADA PARA A DIREITA,
            # POIS A COLUNA FICA FORA DA VIEWPORT VIRTUALIZADA
            # QUANDO A GRID ESTA A ESQUERDA
            # ==================================================

            rolar_grid_horizontal(driver, "direita")

            time.sleep(0.5)

            linhas_direita = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            tipos = []

            for linha_tipo in linhas_direita:

                tipos.append(
                    normalizar_texto(
                        ler_celula_grid_completa(linha_tipo, "tipoAvaria")
                        or ler_celula_grid_completa(linha_tipo, "tipo_avaria")
                        or ler_celula_grid_completa(linha_tipo, "descricao_avaria")
                    )
                )

            rolar_grid_horizontal(driver, "esquerda")

            time.sleep(0.5)

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            candidatos = []
            candidatos_tipo_vazio = []
            candidatos_barcode = []  # fallback: barcode match, tipo vazio, qtd qualquer

            for index, linha in enumerate(linhas):

                try:

                    codigo = (
                        ler_celula_grid_completa(linha, "codigo_barras")
                        or ler_celula_grid_completa(linha, "codigoBarras")
                    )

                    tipo = tipos[index] if index < len(tipos) else ""

                    qtd_texto = (
                        ler_celula_grid_completa(linha, "quantidade")
                        or ler_celula_grid_completa(linha, "qtd")
                    )

                    qtd_linha = None

                    try:
                        if qtd_texto:
                            qtd_linha = int(
                                float(
                                    qtd_texto
                                    .replace(".", "")
                                    .replace(",", ".")
                                )
                            )
                    except Exception:
                        qtd_linha = None

                    codigo = str(codigo).strip()
                    tipo = normalizar_texto(tipo)

                    logging.info(
                        f"Comparando galeria => {codigo} | "
                        f"{tipo} | Qtd: {qtd_linha}"
                    )

                    if codigo != codigo_barras:
                        continue

                    item_linha = {
                        "data_id": linha.get_attribute("data-id"),
                        "quantidade": qtd_linha
                    }

                    if tipo == tipo_avaria:

                        candidatos.append(item_linha)

                    elif (
                        not tipo
                        and quantidade_normalizada is not None
                        and qtd_linha == quantidade_normalizada
                    ):

                        candidatos_tipo_vazio.append(item_linha)

                    elif not tipo:

                        # tipo vazio e qtd nao bate — pode ser campo diferente no modal
                        candidatos_barcode.append(item_linha)

                except Exception as e:

                    logging.warning(
                        f"Erro lendo linha para galeria: {repr(e)}"
                    )

            candidato = None

            if candidatos:

                if quantidade_normalizada is not None:

                    for item in reversed(candidatos):

                        if item["quantidade"] == quantidade_normalizada:

                            candidato = item
                            break

                if not candidato:

                    candidato = candidatos[-1]

            elif candidatos_tipo_vazio:

                candidato = candidatos_tipo_vazio[-1]

                logging.warning(
                    "Linha encontrada para galeria com tipo avaria vazio. "
                    "Usando fallback por codigo de barras e quantidade."
                )

            elif candidatos_barcode:

                # Fallback: grid do modal nao expoe tipo — pega maior data-id (mais recente)
                try:
                    candidato = max(
                        candidatos_barcode,
                        key=lambda x: int(x["data_id"] or 0)
                    )
                except Exception:
                    candidato = candidatos_barcode[-1]

                logging.warning(
                    f"Fallback barcode-only: tipo nao lido no grid do modal "
                    f"(data-field diferente). data-id={candidato['data_id']} | "
                    f"qtd_portal={candidato['quantidade']} | qtd_esperada={quantidade_normalizada}"
                )

            if candidato:

                data_id = candidato["data_id"]

                logging.info(
                    f"Linha encontrada para galeria. data-id={data_id}"
                )

                rolar_grid_horizontal(driver, "esquerda")

                time.sleep(1)

                linha_galeria = wait.until(
                    EC.presence_of_element_located(
                        (
                            By.XPATH,
                            f"//div[@role='row' and @data-id='{data_id}']"
                        )
                    )
                )

                botao_galeria = driver.execute_script(
                    """
                    const row = arguments[0];

                    const actionCell =
                        row.querySelector("[data-field='acoes']") ||
                        row.querySelector("[data-field='acoesProduto']") ||
                        row.querySelector("[data-field='actions']") ||
                        row;

                    const buttons = Array.from(
                        actionCell.querySelectorAll(
                            "button, [role='button'], [aria-label]"
                        )
                    )
                    .map((el) => el.tagName === "BUTTON"
                        ? el
                        : (el.querySelector("button") || el.closest("button") || el)
                    )
                    .filter((el, index, arr) => (
                        el &&
                        arr.indexOf(el) === index &&
                        el.getBoundingClientRect().width > 0 &&
                        el.getBoundingClientRect().height > 0
                    ));

                    const isDelete = (button) => {
                        const label = (
                            button.getAttribute("aria-label") ||
                            button.closest("[aria-label]")?.getAttribute("aria-label") ||
                            button.innerText ||
                            ""
                        ).toLowerCase();

                        const cls = button.className || "";
                        const paths = Array.from(button.querySelectorAll("svg path"))
                            .map((path) => path.getAttribute("d") || "")
                            .join(" ");

                        return label.includes("excluir") ||
                               label.includes("remover") ||
                               cls.includes("Error") ||
                               paths.includes("M6 19") ||
                               paths.includes("M16 9v10");
                    };

                    const isGallery = (button) => {
                        const label = (
                            button.getAttribute("aria-label") ||
                            button.closest("[aria-label]")?.getAttribute("aria-label") ||
                            button.getAttribute("title") ||
                            ""
                        ).toLowerCase();

                        const cls = button.className || "";
                        const paths = Array.from(button.querySelectorAll("svg path"))
                            .map((path) => path.getAttribute("d") || "");

                        return label.includes("galeria") ||
                               label.includes("foto") ||
                               label.includes("imagem") ||
                               cls.includes("Info") ||
                               paths.some((d) => (
                                   d.startsWith("M18 3H6") ||
                                   d.startsWith("M21 19V5") ||
                                   d.startsWith("M19 3H5")
                               ));
                    };

                    return buttons.find((button) => (
                        isGallery(button) && !isDelete(button)
                    )) || buttons.find((button) => !isDelete(button)) || null;
                    """,
                    linha_galeria
                )

                if not botao_galeria:

                    logging.error(
                        "Botao Galeria nao encontrado na linha localizada."
                    )

                    salvar_screenshot(
                        driver,
                        "erro_botao_galeria_nao_encontrado"
                    )

                    return False

                abriu_modal = False

                for tentativa in range(1, 5):

                    try:

                        logging.info(
                            f"Tentativa {tentativa} de clicar no botao Galeria."
                        )

                        clicar_elemento(
                            driver,
                            botao_galeria,
                            "botao_galeria"
                        )

                        WebDriverWait(driver, 10).until(
                            EC.visibility_of_element_located(
                                (
                                    By.XPATH,
                                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                                )
                            )
                        )

                        abriu_modal = True
                        break

                    except Exception as e:

                        logging.warning(
                            f"Tentativa {tentativa} nao abriu modal da galeria: {repr(e)}"
                        )

                        time.sleep(1)

                if not abriu_modal:

                    logging.error(
                        "Botao Galeria foi localizado, mas o modal nao abriu."
                    )

                    salvar_screenshot(
                        driver,
                        "erro_modal_galeria_nao_abriu"
                    )

                    return False

                logging.info("Modal de galeria aberto.")

                return True

            botao_proximo = localizar_botao_paginacao(driver, "PROXIMA")

            if not botao_proximo or botao_desabilitado(botao_proximo):

                logging.info(
                    "Item para galeria nao encontrado ate a ultima pagina."
                )

                break

            clicar_elemento(
                driver,
                botao_proximo,
                "botao_proximo_galeria"
            )

            pagina += 1

            time.sleep(3)

        logging.warning(
            f"Item nao encontrado na galeria apos {pagina} pagina(s): "
            f"codigo={codigo_barras} | tipo={tipo_avaria} | qtd={quantidade_normalizada}"
        )

        salvar_screenshot(driver, "erro_item_galeria_nao_encontrado")

        return False

    except Exception as e:

        logging.error(
            f"Erro ao abrir galeria do item: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(driver, "erro_abrir_galeria_item")

        return False

# ======================================================
# ANEXAR FOTOS NO MODAL DE GALERIA DO ITEM
# ======================================================

def clicar_botao_adicionar_fotos_modal(driver, timeout=60):
    wait = WebDriverWait(driver, timeout)

    botao_adicionar = wait.until(
        lambda d: d.execute_script(
            """
            const dialogs = Array.from(document.querySelectorAll("div[role='dialog']"));

            const dialog = dialogs.find((item) => {
                const text = item.innerText || "";
                return text.includes("Edição Detalhes Produtos") ||
                       (text.includes("Detalhes") && text.includes("Produtos"));
            });

            if (!dialog) {
                return null;
            }

            const loading = dialog.querySelector(
                "[role='progressbar'], .MuiCircularProgress-root, .MuiLinearProgress-root"
            );

            if (loading) {
                return null;
            }

            const candidates = Array.from(
                dialog.querySelectorAll("button, label, div")
            );

            const botao = candidates.find((el) => {
                const text = (el.innerText || "").trim().toLowerCase();

                if (!text) {
                    return false;
                }

                return (
                    text.includes("adicionar fotos") ||
                    text.includes("adicionar foto") ||
                    text.includes("selecionar fotos") ||
                    text.includes("selecionar foto") ||
                    text.includes("anexar fotos") ||
                    text.includes("anexar foto")
                );
            });

            if (botao) {
                return botao;
            }

            const input = dialog.querySelector(
                "input[type='file'][accept*='image'], input[type='file']"
            );

            if (!input) {
                return null;
            }

            const label = input.closest("label");

            if (label) {
                return label;
            }

            // Nao clicar diretamente no input file.
            // No Linux/Windows isso pode abrir janela nativa e travar o Selenium.
            return null;
            """
        )
    )

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});",
        botao_adicionar
    )

    time.sleep(0.5)

    try:
        ActionChains(driver).move_to_element(botao_adicionar).pause(0.2).click().perform()
    except Exception:
        driver.execute_script(
            """
            const el = arguments[0];

            el.dispatchEvent(new MouseEvent('mouseover', {
                bubbles: true,
                cancelable: true,
                view: window
            }));

            el.dispatchEvent(new MouseEvent('mousedown', {
                bubbles: true,
                cancelable: true,
                view: window
            }));

            el.dispatchEvent(new MouseEvent('mouseup', {
                bubbles: true,
                cancelable: true,
                view: window
            }));

            el.dispatchEvent(new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            }));
            """,
            botao_adicionar
        )

    # tenta fechar seletor nativo, se abrir
    try:
        time.sleep(0.5)
        driver.switch_to.active_element.send_keys(Keys.ESCAPE)
        logging.info("ESC enviado para fechar seletor de arquivo, se abriu.")
    except Exception as e:
        logging.warning(f"Nao conseguiu enviar ESC para seletor: {repr(e)}")

    logging.info("Botao/area Adicionar Fotos clicado.")

    time.sleep(1)

    return True

def anexar_fotos_modal_galeria_item(driver, caminhos_fotos):
    wait = WebDriverWait(driver, 60)

    try:
        fotos_validas = []

        for caminho in caminhos_fotos:
            if caminho and os.path.isfile(caminho):
                fotos_validas.append(caminho)
            else:
                logging.warning(f"Foto invalida para galeria: {caminho}")

        if not fotos_validas:
            logging.warning("Nenhuma foto valida para anexar na galeria.")
            return True

        total_fotos = len(fotos_validas)

        logging.info(
            f"Anexando todas as fotos de uma vez na galeria: {total_fotos} foto(s)."
        )

        # ==================================================
        # AGUARDA MODAL
        # ==================================================
        wait.until(
            EC.visibility_of_element_located(
                (
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                )
            )
        )

        logging.info("Modal de galeria visivel.")

        # ==================================================
        # CONTA IMAGENS QUE JA EXISTEM NA GALERIA
        # ==================================================
        qtd_inicial_galeria = contar_imagens_galeria_modal(driver)
        qtd_esperada_galeria = qtd_inicial_galeria + total_fotos

        logging.info(
            f"Galeria antes do upload: {qtd_inicial_galeria}. "
            f"Fotos novas: {total_fotos}. "
            f"Esperado apos salvar: {qtd_esperada_galeria}."
        )

        # ==================================================
        # LOCALIZA INPUT FILE
        # ==================================================
        input_file = wait.until(
            lambda d: d.execute_script(
                """
                const dialogs = Array.from(
                    document.querySelectorAll("div[role='dialog']")
                );

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return null;
                }

                const loading = dialog.querySelector(
                    "[role='progressbar'], .MuiCircularProgress-root, .MuiLinearProgress-root"
                );

                if (loading) {
                    return null;
                }

                return dialog.querySelector(
                    "input[type='file'][accept*='image'], input[type='file']"
                );
                """
            )
        )

        logging.info("Input de fotos do modal de galeria localizado.")

        # ==================================================
        # LIMPA ESTADO ANTERIOR DO UPLOAD
        # ==================================================

        try:

            driver.execute_script("""
                const dialogs = Array.from(
                    document.querySelectorAll("div[role='dialog']")
                );

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";

                    return text.includes("Edição Detalhes Produtos")
                        || (
                            text.includes("Detalhes")
                            && text.includes("Produtos")
                        );
                });

                if (!dialog) {
                    return;
                }

                // limpa previews blob
                dialog.querySelectorAll(
                    "img[src^='blob:']"
                ).forEach((img) => {

                    const li = img.closest("li");

                    if (li) {
                        li.remove();
                    }
                });

                // limpa inputs file
                dialog.querySelectorAll(
                    "input[type='file']"
                ).forEach((input) => {

                    input.value = "";

                    input.dispatchEvent(
                        new Event("change", {
                            bubbles: true
                        })
                    );
                });

            """)

            logging.info(
                "Estado anterior upload limpo."
            )

            time.sleep(2)

        except Exception as e:

            logging.warning(
                f"Erro limpar estado upload: {repr(e)}"
            )

        # ==================================================
        # ENVIA TODAS AS FOTOS UMA UNICA VEZ
        # ==================================================
        input_file.send_keys(
            "\n".join(fotos_validas)
        )

        logging.info(
            f"Fotos enviadas para o input do modal: {total_fotos}"
        )

        # ==================================================
        # CONFIRMA QUE O INPUT RECEBEU OS ARQUIVOS
        # ==================================================
        try:
            wait.until(
                lambda d: d.execute_script(
                    """
                    const input = arguments[0];
                    return input.files ? input.files.length : 0;
                    """,
                    input_file
                ) >= total_fotos
            )

            logging.info(
                f"Input confirmou {total_fotos} arquivo(s)."
            )

        except Exception:
            logging.warning(
                "Input nao confirmou todos os arquivos, mas vou tentar salvar mesmo assim."
            )

        time.sleep(2)

        # ==================================================
        # LOCALIZA BOTAO SALVAR
        # O botao aparece depois que as fotos entram no input
        # ==================================================
        botao_salvar = wait.until(
            lambda d: d.execute_script(
                """
                const dialogs = Array.from(
                    document.querySelectorAll("div[role='dialog']")
                );

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return null;
                }

                const buttons = Array.from(
                    dialog.querySelectorAll("button")
                );

                const salvar = buttons.find((button) => {
                    const texto = (button.innerText || "").trim();

                    const disabled =
                        button.disabled ||
                        button.getAttribute("aria-disabled") === "true" ||
                        (button.className || "").includes("Mui-disabled");

                    return texto === "Salvar" && !disabled;
                });

                return salvar || null;
                """
            )
        )

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            botao_salvar
        )

        time.sleep(1)

        clicar_elemento(
            driver,
            botao_salvar,
            "botao_salvar"
        )

        logging.info(
            "Botao Salvar da galeria clicado. Aguardando processamento das fotos."
        )

        # ==================================================
        # FUNCOES AUXILIARES DO MODAL
        # ==================================================
        def modal_aberto(d):
            return len(
                d.find_elements(
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                )
            ) > 0

        def tem_loading_modal(d):
            return d.execute_script(
                """
                const dialogs = Array.from(
                    document.querySelectorAll("div[role='dialog']")
                );

                const dialog = dialogs.find((item) => {
                    const text = item.innerText || "";
                    return text.includes("Edição Detalhes Produtos") ||
                           (text.includes("Detalhes") && text.includes("Produtos"));
                });

                if (!dialog) {
                    return false;
                }

                return !!dialog.querySelector(
                    "[role='progressbar'], .MuiCircularProgress-root, .MuiLinearProgress-root"
                );
                """
            )

        # ==================================================
        # AGUARDA PROCESSAMENTO INICIAR
        # ==================================================
        try:
            WebDriverWait(driver, 240).until(
                lambda d: (
                    not modal_aberto(d)
                    or tem_loading_modal(d)
                    or contar_imagens_galeria_modal(d) >= qtd_esperada_galeria
                )
            )

            logging.info(
                f"Upload/processamento detectado. "
                f"Imagens: {contar_imagens_galeria_modal(driver)}/{qtd_esperada_galeria}"
            )

        except Exception:
            logging.warning(
                "Nao consegui confirmar inicio do processamento ou aumento da galeria."
            )

        # ==================================================
        # AGUARDA PROCESSAMENTO FINALIZAR
        # ==================================================
        try:
            WebDriverWait(driver, 300).until(
                lambda d: (
                    not modal_aberto(d)
                    or (
                        not tem_loading_modal(d)
                        and contar_imagens_galeria_modal(d) >= qtd_esperada_galeria
                    )
                )
            )

            logging.info(
                f"Processamento da galeria finalizado. "
                f"Imagens: {contar_imagens_galeria_modal(driver)}/{qtd_esperada_galeria}"
            )

        except Exception:
            logging.warning(
                f"Nao consegui confirmar fim do processamento ou total esperado "
                f"de {qtd_esperada_galeria} imagens."
            )

        # tempo extra para backend gravar
        time.sleep(8)

        # ==================================================
        # FECHA MODAL SOMENTE DEPOIS DO PROCESSAMENTO
        # ==================================================
        try:
            if modal_aberto(driver):
                botao_fechar = driver.find_element(
                    By.XPATH,
                    "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]//button[.//*[name()='svg']]"
                )

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    botao_fechar
                )

                time.sleep(0.5)

                clicar_elemento(
                    driver,
                    botao_fechar,
                    "botao_fechar"
                )


                logging.info(
                    "Modal de galeria fechado pelo botao X apos processamento."
                )

                WebDriverWait(driver, 30).until(
                    EC.invisibility_of_element_located(
                        (
                            By.XPATH,
                            "//div[@role='dialog' and .//*[contains(normalize-space(), 'Detalhes') and contains(normalize-space(), 'Produtos')]]"
                        )
                    )
                )

        except Exception:
            logging.warning(
                "Nao foi possivel fechar modal pelo botao X. Tentando ESC."
            )

            try:
                driver.switch_to.active_element.send_keys(Keys.ESCAPE)
            except Exception as e:
                logging.warning(
                    f"Nao foi possivel enviar ESC no modal: {repr(e)}"
                )

        time.sleep(2)

        return True

    except Exception as e:
        logging.error(
            f"Erro ao anexar fotos no modal de galeria: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_anexar_fotos_modal_galeria"
        )

        return False
       
def anexar_fotos_item_cadastrado_portal(
    driver,
    codigo_barras,
    tipo_avaria,
    caminhos_fotos,
    quantidade=None
):
    if not caminhos_fotos:
        logging.info("Item sem fotos para anexar via galeria.")
        return True

    abriu = abrir_galeria_item_linha(
        driver,
        codigo_barras,
        tipo_avaria,
        quantidade=quantidade
    )

    if not abriu:
        return False

    return anexar_fotos_modal_galeria_item(
        driver,
        caminhos_fotos
    )

# ======================================================
# REMOVER ITENS DO SITE QUE NaO EXISTEM NO BANCO
# ======================================================

def remover_itens_nao_existentes_no_banco(
    driver,
    itens_banco
):

    try:

        logging.info(
            "Verificando itens do site que nao existem no banco..."
        )

        # ==========================================
        # MONTA MAPA DO BANCO
        # ==========================================

        mapa_banco = set()

        for item_banco in itens_banco:

            codigo_barras = str(
                item_banco["codigo_barras"]
            ).strip()

            tipo_avaria = normalizar_texto(
                item_banco["tipo_avaria"]
            )

            chave = (
                codigo_barras,
                tipo_avaria
            )

            mapa_banco.add(
                chave
            )

        logging.info(
            f"Total itens Unicos no banco: {len(mapa_banco)}"
        )

        # ==========================================
        # LOOP ATÉ NaO TER MAIS ITEM SOBRANDO
        # ==========================================

        tentativas_sem_remover = 0

        while True:

            existentes_site = validar_itens_existentes(
                driver,
                ler_detalhes=False,
                log_linhas=False
            )

            if len(existentes_site) == 0:

                logging.info(
                    "Site sem itens cadastrados. Nada para remover."
                )

                break

            item_para_remover = None

            # ======================================
            # PROCURA ITEM QUE EXISTE NO SITE
            # MAS NaO EXISTE NO BANCO
            # ======================================

            for item_site in existentes_site:

                codigo_site = str(
                    item_site["codigo_barras"]
                ).strip()

                tipo_site = normalizar_texto(
                    item_site["tipo_avaria"]
                )

                chave_site = (
                    codigo_site,
                    tipo_site
                )

                if chave_site not in mapa_banco:

                    item_para_remover = item_site

                    logging.info(
                        f"Item fora do banco encontrado no site => "
                        f"Codigo: {codigo_site} | "
                        f"Tipo: {tipo_site} | "
                        f"Qtd: {item_site['quantidade']} | "
                        f"Pagina: {item_site.get('pagina')}"
                    )

                    break

            # ======================================
            # NaO TEM MAIS SOBRA
            # ======================================

            if not item_para_remover:

                logging.info(
                    "Nenhum item fora do banco encontrado no site."
                )

                break

            # ======================================
            # QUANTIDADE ANTES
            # ======================================

            quantidade_antes = 0

            for item_site in existentes_site:

                if (
                    str(item_site["codigo_barras"]).strip()
                    == str(item_para_remover["codigo_barras"]).strip()
                    and
                    normalizar_texto(item_site["tipo_avaria"])
                    == normalizar_texto(item_para_remover["tipo_avaria"])
                ):

                    quantidade_antes += int(
                        item_site["quantidade"]
                    )

            # ======================================
            # REMOVE
            # ======================================

            removido = remover_item_linha(
                driver,
                item_para_remover["codigo_barras"],
                item_para_remover["tipo_avaria"],
                item_para_remover.get("pagina")
            )

            time.sleep(2)

            # ======================================
            # VALIDA SE REMOVEU
            # ======================================

            existentes_depois = validar_itens_existentes(
                driver,
                ler_detalhes=False,
                log_linhas=False
            )

            quantidade_depois = 0

            for item_site in existentes_depois:

                if (
                    str(item_site["codigo_barras"]).strip()
                    == str(item_para_remover["codigo_barras"]).strip()
                    and
                    normalizar_texto(item_site["tipo_avaria"])
                    == normalizar_texto(item_para_remover["tipo_avaria"])
                ):

                    quantidade_depois += int(
                        item_site["quantidade"]
                    )

            logging.info(
                f"Quantidade antes: {quantidade_antes} | "
                f"Depois: {quantidade_depois}"
            )

            if quantidade_depois < quantidade_antes:

                tentativas_sem_remover = 0

                logging.info(
                    "Item fora do banco removido com sucesso."
                )

            else:

                tentativas_sem_remover += 1

                logging.warning(
                    f"Item nao reduziu apos remover. "
                    f"Tentativa {tentativas_sem_remover}/3"
                )

                if tentativas_sem_remover >= 3:

                    logging.error(
                        "Nao foi possIvel remover item fora do banco apos 3 tentativas."
                    )

                    break

        logging.info(
            "Verificacao de itens fora do banco finalizada."
        )

    except Exception as e:

        logging.error(
            f"Erro remover itens fora do banco: {e}"
        )

        salvar_screenshot(
            driver,
            "erro_remover_fora_banco"
        )

# ======================================================
# SINCRONIZAR ITENS
# ======================================================

def chave_item_quantidade(codigo_barras, tipo_avaria):

    return (
        str(codigo_barras).strip(),
        normalizar_texto(tipo_avaria)
    )

def somar_quantidade_cache(itens, codigo_barras, tipo_avaria):

    chave = chave_item_quantidade(
        codigo_barras,
        tipo_avaria
    )

    total = 0

    for item in itens or []:

        if chave_item_quantidade(
            item.get("codigo_barras", ""),
            item.get("tipo_avaria", "")
        ) == chave:

            total += int(
                item.get("quantidade") or 0
            )

    return total

def localizar_item_cache(itens, codigo_barras, tipo_avaria):

    chave = chave_item_quantidade(
        codigo_barras,
        tipo_avaria
    )

    for item in reversed(itens or []):

        if chave_item_quantidade(
            item.get("codigo_barras", ""),
            item.get("tipo_avaria", "")
        ) == chave:

            return item

    return None

def remover_item_cache(itens, codigo_barras, tipo_avaria):

    chave = chave_item_quantidade(
        codigo_barras,
        tipo_avaria
    )

    return [
        item
        for item in itens or []
        if chave_item_quantidade(
            item.get("codigo_barras", ""),
            item.get("tipo_avaria", "")
        ) != chave
    ]

def sincronizar_itens_avaria(
    driver,
    conn,
    itens_banco,
    fila_id=None,
    processados_inicial=0,
    total_progresso=None,
    codigo_avaria=None
):

    total_itens = len(itens_banco)

    if total_progresso is None:
        total_progresso = total_itens

    processados = processados_inicial

    cursor_progresso = None

    if fila_id:
        cursor_progresso = conn.cursor()

        atualizar_progresso(
            cursor_progresso,
            conn,
            fila_id,
            processados,
            total_progresso
        )

    try:

        logging.info(
            "Carregando resumo inicial dos itens do portal."
        )

        existentes_cache = validar_itens_existentes(
            driver,
            ler_detalhes=False,
            log_linhas=False
        )

        indice_item = 0

        for indice_item, item_banco in enumerate(itens_banco, start=1):

            codigo_barras = str(
                item_banco["codigo_barras"]
            ).strip()

            tipo_avaria = normalizar_texto(
                item_banco["tipo_avaria"]
            )

            quantidade_banco = int(
                float(item_banco["quantidade"])
            )

            observacao = normalizar_texto(
                item_banco.get(
                    "observacao",
                    ""
                )
            )

            item_id = item_banco.get("id")

            caminhos_fotos = []

            # ==========================================
            # LÊ ITENS ATUAIS DO SITE
            # ==========================================

            quantidade_existente = somar_quantidade_cache(
                existentes_cache,
                codigo_barras,
                tipo_avaria
            )

            logging.info(
                f"CODIGO: {codigo_barras} | "
                f"Banco: {quantidade_banco} | "
                f"Existente: {quantidade_existente}"
            )

            # ==========================================
            # ITEM Ja SINCRONIZADO
            # Conta como processado, mesmo sem alteracao
            # ==========================================

            if quantidade_existente == quantidade_banco:

                logging.info(
                    "Item ja sincronizado. Nenhuma alteracao necessaria."
                )

                processados += 1

                if cursor_progresso:

                    atualizar_progresso(
                        cursor_progresso,
                        conn,
                        fila_id,
                        processados,
                        total_progresso
                    )

                continue

            # ==========================================
            # REMOVE TODOS SE EXISTIR NO SITE
            # ==========================================

            if quantidade_existente > 0:

                logging.info(
                    "Quantidade divergente."
                )

                logging.info(
                    "Removendo itens existentes..."
                )

                quantidade_atual = quantidade_existente

                item_para_remover = localizar_item_cache(
                    existentes_cache,
                    codigo_barras,
                    tipo_avaria
                )

                logging.info(
                    f"Quantidade atual antes remover: {quantidade_atual}"
                )

                if not item_para_remover:

                    logging.warning(
                        "Item nao localizado no cache. Recarregando resumo rapido."
                    )

                    existentes_cache = validar_itens_existentes(
                        driver,
                        ler_detalhes=False,
                        log_linhas=False
                    )

                    item_para_remover = localizar_item_cache(
                        existentes_cache,
                        codigo_barras,
                        tipo_avaria
                    )

                if not item_para_remover:

                    logging.warning(
                        "Item nao localizado para remover."
                    )

                else:

                    pagina_item = item_para_remover.get(
                        "pagina"
                    )

                    removido = remover_item_linha(
                        driver,
                        codigo_barras,
                        tipo_avaria,
                        pagina_item
                    )

                    if not removido:

                        logging.error(
                            "Nao foi possIvel remover o item divergente."
                        )

                        return False

                    time.sleep(2)

                    existentes_cache = remover_item_cache(
                        existentes_cache,
                        codigo_barras,
                        tipo_avaria
                    )

                    logging.info(
                        "Item removido do portal e do cache local."
                    )
            

            # ==========================================
            # CONFERE SE REMOVEU MESMO ANTES DE INSERIR
            # ==========================================

            quantidade_final_site = somar_quantidade_cache(
                existentes_cache,
                codigo_barras,
                tipo_avaria
            )

            if quantidade_final_site > 0:

                logging.error(
                    f"Item ainda existe no site apos remocao. "
                    f"Codigo: {codigo_barras} | "
                    f"Qtd: {quantidade_final_site}. "
                    f"Nao vou inserir para evitar duplicidade."
                )

                return False

            # ==========================================
            # INSERE NOVAMENTE
            # ==========================================

            logging.info(
                f"Inserindo {quantidade_banco} itens..."
            )

            if not item_id:

                logging.warning(
                    f"Item sem ID. Fotos nao serao buscadas. Codigo: {codigo_barras}"
                )

                fotos_item = []

            else:

                fotos_item = consultar_fotos_item_avaria(
                    conn,
                    item_id
                )

            for foto in fotos_item:

                caminho_relativo = (
                    foto.get("caminho_arquivo")
                    or
                    foto.get("url_arquivo")
                )

                caminho_fisico = montar_caminho_fisico_foto(
                    caminho_relativo
                )

                if caminho_fisico:

                    caminhos_fotos.append(
                        caminho_fisico
                    )

            logging.info(
                f"Fotos validas para upload: {len(caminhos_fotos)}"
            )

            inserido = inserir_itens_avaria(
                driver,
                [
                    {
                        "codigo_barras": codigo_barras,
                        "tipo_avaria": tipo_avaria,
                        "quantidade": quantidade_banco,
                        "observacao": observacao,
                        "fotos": caminhos_fotos
                    }
                ],
                codigo_avaria=codigo_avaria
            )

            if inserido:

                logging.info(
                    "Itens inseridos."
                )

                existentes_cache.append(
                    {
                        "codigo_barras": codigo_barras,
                        "tipo_avaria": tipo_avaria,
                        "quantidade": quantidade_banco,
                        "observacao": observacao,
                        "preco_contrapartida": None,
                        "pagina": None
                    }
                )

            else:

                logging.error(
                    f"Falha ao inserir item: "
                    f"{codigo_barras} | {tipo_avaria}"
                )

                return False

            time.sleep(1)

            # ==================================================
            # ATUALIZA PROGRESSO AO FINAL DO ITEM
            # ==================================================

            processados += 1

            if cursor_progresso:

                atualizar_progresso(
                    cursor_progresso,
                    conn,
                    fila_id,
                    processados,
                    total_progresso
                )

            # GC a cada 10 itens para liberar handles CDP acumulados no Chrome
            if indice_item % 10 == 0:
                forcar_gc_chrome(driver)

        logging.info(
            "Sincronizacao finalizada."
        )

        return True
    
    except Exception as e:

        logging.error(
            f"Erro ao processar item {indice_item}: {repr(e)}",
            exc_info=True
        )

        return False

    finally:

        if cursor_progresso:

            atualizar_progresso(
                cursor_progresso,
                conn,
                fila_id,
                processados,
                total_progresso
            )

            cursor_progresso.close()

# ======================================================
# SELECIONAR TIPO AVARIA
# COM FALLBACK TECLADO
# ======================================================

def selecionar_tipo_avaria(
    driver,
    tipo_avaria
):

    wait = WebDriverWait(driver, 20)

    tipo_avaria = normalizar_texto(
        tipo_avaria
    )

    # ==================================================
    # LIMPA AUTOCOMPLETE ORFAO
    # ==================================================

    try:

        driver.execute_script("""
            const poppers = document.querySelectorAll(
                '.MuiAutocomplete-popper'
            );

            poppers.forEach(p => p.remove());
        """)

        logging.info(
            "Dropdowns autocomplete removidos."
        )

    except Exception as e:

        logging.warning(
            f"Erro limpar autocomplete: {repr(e)}"
        )

    time.sleep(0.2)

    try:

        logging.info(
            f"Iniciando selecao tipo avaria: {tipo_avaria}"
        )

        # ==========================================
        # CAMPO TIPO AVARIA
        # ==========================================

        campo_tipo = wait.until(
            EC.presence_of_element_located(
                (
                    By.ID,
                    "tipo_avaria"
                )
            )
        )

        valor_atual = normalizar_texto(
            campo_tipo.get_attribute("value")
        )

        if valor_atual == tipo_avaria:

            logging.info(
                f"Tipo avaria ja selecionado: {tipo_avaria}"
            )

            return True

        try:
            driver.execute_script(
                "arguments[0].scrollIntoView({block:'center'});",
                campo_tipo
            )
        except Exception as e:
            logging.warning(
                f"Nao conseguiu rolar ate tipo avaria: {repr(e)}"
            )

        time.sleep(0.3)

        # ==========================================
        # LIMPA E DIGITA COM ACOES NATIVAS
        # ==========================================

        campo_tipo.click()

        campo_tipo.send_keys(
            Keys.CONTROL,
            "a"
        )

        campo_tipo.send_keys(
            Keys.DELETE
        )

        time.sleep(0.5)

        campo_tipo.send_keys(
            tipo_avaria
        )

        logging.info(
            f"Tipo avaria digitado: {tipo_avaria}"
        )

        time.sleep(0.5)

        try:

            campo_tipo.send_keys(
                Keys.ENTER
            )

            time.sleep(0.5)

            valor_final = normalizar_texto(
                campo_tipo.get_attribute("value")
            )

            if valor_final == tipo_avaria:

                logging.info(
                    f"Tipo avaria selecionado via enter: {tipo_avaria}"
                )

                return True

        except Exception as e:

            logging.warning(
                f"Selecao rapida via enter falhou: {repr(e)}"
            )

        # ==========================================
        # ABRE SOMENTE O AUTOCOMPLETE DO CAMPO TIPO
        # ==========================================

        try:

            container_tipo = campo_tipo.find_element(
                By.XPATH,
                "./ancestor::div[contains(@class,'MuiAutocomplete-root')]"
            )

            botao_open = container_tipo.find_element(
                By.XPATH,
                ".//button[@aria-label='Open' or @aria-label='Abrir']"
            )

            clicar_elemento(
                driver,
                botao_open,
                "botao_open"
            )

            logging.info(
                "Dropdown tipo avaria aberto."
            )

            time.sleep(0.5)

        except Exception as e:

            logging.warning(
                f"Nao localizou botao Open do tipo avaria: {repr(e)}"
            )

        # ==========================================
        # LOCALIZA OPÇÕES
        # ==========================================

        try:

            opcao_js = WebDriverWait(driver, 4).until(
                lambda d: d.execute_script(
                    """
                    const esperado = arguments[0];

                    const normalizar = (valor) => (valor || "")
                        .normalize("NFD")
                        .replace(/[\\u0300-\\u036f]/g, "")
                        .toUpperCase()
                        .trim();

                    const itens = Array.from(
                        document.querySelectorAll(
                            ".MuiAutocomplete-popper li, [role='option']"
                        )
                    );

                    return itens.find((item) => (
                        normalizar(item.innerText || item.textContent) === esperado
                    )) || null;
                    """,
                    tipo_avaria
                )
            )

            driver.execute_script(
                "arguments[0].click();",
                opcao_js
            )

            time.sleep(0.3)

            driver.execute_script("""
                const poppers = document.querySelectorAll(
                    '.MuiAutocomplete-popper'
                );

                poppers.forEach(p => {
                    p.innerHTML = '';
                    p.remove();
                });
            """)

            valor_js = normalizar_texto(
                campo_tipo.get_attribute("value")
            )

            if valor_js == tipo_avaria:

                logging.info(
                    f"Tipo avaria selecionado via JS: {tipo_avaria}"
                )

                return True

            logging.warning(
                f"Clique JS na opcao nao atualizou o campo "
                f"(valor atual: '{valor_js}'). Tentando outro fallback."
            )

        except Exception as e:

            logging.warning(
                f"Selecao rapida por JS falhou: {repr(e)}"
            )

        # clica diretamente via JS: evita criar lista Python de WebElement
        # handles CDP que acumulavam memoria a cada insercao do autocomplete
        clicou = WebDriverWait(driver, 12).until(
            lambda d: d.execute_script(
                """
                const esperado = arguments[0];

                const normalizar = (v) => (v || "")
                    .normalize("NFD")
                    .replace(/[̀-ͯ]/g, "")
                    .toUpperCase()
                    .trim();

                const item = Array.from(
                    document.querySelectorAll(
                        ".MuiAutocomplete-popper li, [role='option']"
                    )
                ).find(el => normalizar(
                    el.innerText || el.textContent
                ) === esperado);

                if (item) {
                    item.click();
                    return true;
                }

                return null;
                """,
                tipo_avaria
            )
        )

        if clicou:

            time.sleep(0.3)

            driver.execute_script("""
                const poppers = document.querySelectorAll(
                    '.MuiAutocomplete-popper'
                );

                poppers.forEach(p => {
                    p.innerHTML = '';
                    p.remove();
                });
            """)

            gc.collect()

            valor_clicou = normalizar_texto(
                campo_tipo.get_attribute("value")
            )

            if valor_clicou == tipo_avaria:

                logging.info(
                    f"Tipo avaria selecionado: {tipo_avaria}"
                )

                return True

            logging.warning(
                f"Clique direto via JS nao atualizou o campo "
                f"(valor atual: '{valor_clicou}'). Tentando fallback teclado."
            )

        # ==========================================
        # FALLBACK TECLADO
        # ==========================================

        campo_tipo.click()

        campo_tipo.send_keys(
            Keys.ARROW_DOWN
        )

        campo_tipo.send_keys(
            Keys.ENTER
        )

        time.sleep(1)

        valor_final = normalizar_texto(
            campo_tipo.get_attribute("value")
        )

        logging.info(
            f"Valor final tipo avaria apos teclado: {valor_final}"
        )

        if valor_final == tipo_avaria:

            logging.info(
                f"Tipo avaria selecionado via teclado: {tipo_avaria}"
            )

            return True

        # ==========================================
        # SE NaO ACHOU, NaO CONTINUA
        # ==========================================

        logging.error(
            f"Tipo avaria nao encontrado. "
            f"Esperado: {tipo_avaria}"
        )

        salvar_screenshot(
            driver,
            "erro_tipo_avaria_nao_encontrado"
        )

        return False

    except Exception as e:

        logging.error(
            f"Erro selecionar tipo avaria: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_tipo_avaria"
        )

        return False

# ======================================================
# PREENCHER OBSERVAcaO
# ======================================================

def preencher_observacao_avaria(
    driver,
    observacao
):

    wait = WebDriverWait(driver, 20)

    observacao = str(
        observacao or ""
    ).strip()

    try:

        campo_observacao = wait.until(
            EC.presence_of_element_located(
                (
                    By.NAME,
                    "observacao"
                )
            )
        )

        if not observacao and not str(
            campo_observacao.get_attribute("value") or ""
        ).strip():

            logging.info(
                "Observacao vazia, campo ja limpo."
            )

            return True

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            campo_observacao
        )

        time.sleep(1)

        campo_observacao.click()

        campo_observacao.send_keys(
            Keys.CONTROL,
            "a"
        )

        campo_observacao.send_keys(
            Keys.DELETE
        )

        driver.execute_script(
            """
            arguments[0].value = '';
            arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
            arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
            """,
            campo_observacao
        )

        time.sleep(1)

        if observacao:

            campo_observacao.send_keys(
                observacao
            )

            driver.execute_script(
                """
                arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
                arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
                """,
                campo_observacao
            )

            logging.info(
                f"Observacao preenchida: {observacao}"
            )

        else:

            logging.info(
                "Observacao vazia, seguindo sem preencher."
            )

        time.sleep(1)

        return True

    except Exception as e:

        logging.warning(
            f"Campo observacao nao preenchido: {repr(e)}"
        )

        return False

# ======================================================
# AGUARDAR ITEM APARECER NA TABELA APoS ADICIONAR
# ======================================================

def aguardar_item_aparecer_na_tabela(
    driver,
    codigo_barras,
    tipo_avaria,
    timeout=60
):

    codigo_barras = str(codigo_barras).strip()
    tipo_avaria = normalizar_texto(tipo_avaria)

    logging.info(
        f"Aguardando item aparecer na tabela: {codigo_barras} | {tipo_avaria}"
    )

    try:

        wait = WebDriverWait(driver, timeout)

        def item_apareceu(d):

            try:

                linhas = d.find_elements(
                    By.XPATH,
                    "//table//tbody//tr | //div[@role='row' and @data-id]"
                )

                for linha in linhas:

                    texto = normalizar_texto(linha.text)

                    if (
                        codigo_barras in texto
                        and
                        tipo_avaria in texto
                    ):

                        return True

                return False

            except Exception:

                return False

        wait.until(item_apareceu)

        logging.info(
            "Item apareceu na tabela apos adicionar."
        )

        return True

    except Exception as e:

        logging.warning(
            f"Item nao apareceu na tabela no tempo esperado: {repr(e)}"
        )

        salvar_screenshot(
            driver,
            "erro_item_nao_apareceu_apos_adicionar"
        )

        return False

# ======================================================
# AGUARDAR PROCESSAMENTO FINAL DAS FOTOS
# ======================================================

def aguardar_processamento_final_fotos(total_fotos):

    tempo = 5

    logging.info(
        f"Aguardando processamento final das fotos antes de adicionar: {tempo}s"
    )

    time.sleep(tempo)

# ======================================================
# CLICAR BOTaO ADICIONAR
# ======================================================

def clicar_botao_adicionar_avaria(
    driver
):

    wait = WebDriverWait(driver, 30)

    try:

        # localiza via JS para nao criar lista de WebElement handles
        botao = wait.until(
            lambda d: d.execute_script("""
                const bts = Array.from(
                    document.querySelectorAll('button')
                );
                return bts.find(b => {
                    const t = (
                        b.innerText || b.textContent || ''
                    ).trim().toUpperCase();
                    return t === 'ADICIONAR'
                        && !b.disabled
                        && b.getAttribute('aria-disabled') !== 'true';
                }) || null;
            """)
        )

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            botao
        )

        time.sleep(1)

        clicar_elemento(
            driver,
            botao,
            "Botao adicionar"
        )

        logging.info(
            "Botao adicionar clicado."
        )

        return True

    except Exception as e:

        logging.error(
            f"Erro clicar botao adicionar: {repr(e)}"
        )

        salvar_screenshot(
            driver,
            "erro_botao_adicionar"
        )

        return False

# ======================================================
# LIMPAR INPUT DE UPLOAD DE FOTOS DO PORTAL
# ======================================================

def limpar_input_upload_fotos_portal(driver):
    try:

        logging.info("Limpando input e previews de fotos do portal...")

        driver.execute_script("""
            const inputs = document.querySelectorAll("input[type='file']");

            inputs.forEach(input => {
                try {
                    input.value = "";
                } catch(e) {}

                input.dispatchEvent(new Event("input", { bubbles: true }));
                input.dispatchEvent(new Event("change", { bubbles: true }));
            });
        """)

        time.sleep(0.5)

        logging.info("Input de upload de fotos limpo.")

        return True

    except Exception as e:

        logging.warning(
            f"Erro ao limpar input upload fotos: {repr(e)}"
        )

        return False

# ======================================================
# VERIFICAR TOAST: CODIGO DE BARRAS NAO ENCONTRADO
# ======================================================

def verificar_codigo_barras_nao_encontrado(driver):

    try:

        return driver.execute_script(
            """
            const normalizar = (valor) => (valor || "")
                .normalize("NFD")
                .replace(/[\\u0300-\\u036f]/g, "")
                .toLowerCase();

            const elementos = Array.from(
                document.querySelectorAll("body *")
            );

            return elementos.some((el) => {

                if (el.children.length > 0) return false;

                if (!el.offsetParent) return false;

                const texto = normalizar(el.textContent);

                return texto.includes("codigo de barras") &&
                       texto.includes("nao encontrado");
            });
            """
        )

    except Exception:

        return False

# ======================================================
# INSERIR ITENS AVARIA
# ======================================================

def inserir_itens_avaria(
    driver,
    itens,
    codigo_avaria=None
):

    wait = WebDriverWait(driver, 30)

    logging.info(
        f"Iniciando insercao de "
        f"{len(itens)} itens..."
    )

    # ==================================================
    # CONTADOR GLOBAL DE INSERCOES
    # ==================================================

    contador_insercoes = 0

    itens_pendentes = []

    try:

        for index, item in enumerate(itens):

            codigo_barras = str(
                item["codigo_barras"]
            ).strip()

            tipo_avaria = str(
                item["tipo_avaria"]
            ).strip()

            quantidade = int(
                float(item["quantidade"])
            )

            observacao = str(
                item.get(
                    "observacao",
                    ""
                )
            ).strip()

            caminhos_fotos = item.get(
                "fotos",
                []
            )

            logging.info(
                f"Inserindo item | "
                f"Codigo: {codigo_barras} | "
                f"Tipo: {tipo_avaria} | "
                f"Qtd: {quantidade} | "
                f"OBS.: {observacao}"
            )

            # ======================================
            # LOOP QUANTIDADE
            # ======================================

            item_invalido = False

            for i in range(quantidade):

                # ======================================
                # CONTADOR GLOBAL
                # ======================================

                contador_insercoes += 1

                logging.info(
                    f"Insercao global "
                    f"{contador_insercoes}"
                )

                # ==================================================
                # REFRESH PREVENTIVO
                # ==================================================

                if (
                    REFRESH_PREVENTIVO_INSERCOES
                    and
                    contador_insercoes > 0
                    and
                    contador_insercoes % REFRESH_PREVENTIVO_INSERCOES == 0
                ):

                    logging.info(
                        f"Refresh preventivo apos "
                        f"{contador_insercoes} insercoes."
                    )

                    try:

                        driver.refresh()

                        time.sleep(10)

                        # ==============================
                        # LIMPA STORAGE
                        # ==============================

                        # try:

                        #     driver.execute_script("""
                        #         window.localStorage.clear();
                        #         window.sessionStorage.clear();
                        #     """)

                        #     logging.info(
                        #         "Storage limpo."
                        #     )

                        # except Exception as e:

                        #     logging.warning(
                        #         f"Erro limpar storage: {repr(e)}"
                        #     )

                        # # ==============================
                        # # LIMPA COOKIES
                        # # ==============================

                        # try:

                        #     driver.delete_all_cookies()

                        #     logging.info(
                        #         "Cookies limpos."
                        #     )

                        # except Exception as e:

                        #     logging.warning(
                        #         f"Erro limpar cookies: {repr(e)}"
                        #     )

                        # ==============================
                        # REABRE TELA
                        # ==============================

                        reabrir_detalhe_avaria(
                            driver,
                            codigo_avaria or codigoAvaria
                        )

                        logging.info(
                            "Tela reaberta apos refresh."
                        )

                    except Exception as e:

                        logging.error(
                            f"Erro refresh preventivo: "
                            f"{repr(e)}"
                        )

                        salvar_screenshot(
                            driver,
                            "erro_refresh_preventivo"
                        )

                        return False

                logging.info(
                    f"Insercao {i + 1}/{quantidade}"
                )

                if i == 0:

                    limpar_input_upload_fotos_portal(driver)

                time.sleep(0.5)

                try:

                    # ==============================
                    # INPUT CoDIGO BARRAS
                    # ==============================

                    campo_codigo = wait.until(
                        EC.element_to_be_clickable(
                            (
                                By.NAME,
                                "codigo_barras"
                            )
                        )
                    )

                    driver.execute_script(
                        "arguments[0].scrollIntoView({block:'center'});",
                        campo_codigo
                    )

                    time.sleep(0.3)

                    campo_codigo.click()

                    campo_codigo.send_keys(
                        Keys.CONTROL,
                        "a"
                    )

                    campo_codigo.send_keys(
                        Keys.DELETE
                    )

                    driver.execute_script(
                        """
                        arguments[0].value = '';

                        arguments[0].dispatchEvent(
                            new Event('input', {
                                bubbles: true
                            })
                        );

                        arguments[0].dispatchEvent(
                            new Event('change', {
                                bubbles: true
                            })
                        );
                        """,
                        campo_codigo
                    )

                    time.sleep(0.3)

                    campo_codigo.send_keys(
                        codigo_barras
                    )

                    driver.execute_script(
                        """
                        arguments[0].dispatchEvent(
                            new Event('input', {
                                bubbles: true
                            })
                        );

                        arguments[0].dispatchEvent(
                            new Event('change', {
                                bubbles: true
                            })
                        );
                        """,
                        campo_codigo
                    )

                    logging.info(
                        "Codigo barras preenchido."
                    )

                    time.sleep(1.5)

                    # ==============================
                    # VERIFICA CODIGO NAO ENCONTRADO
                    # ==============================

                    if verificar_codigo_barras_nao_encontrado(driver):

                        logging.warning(
                            f"Codigo de barras nao encontrado no "
                            f"catalogo do portal: {codigo_barras} | "
                            f"Tipo: {tipo_avaria} | Qtd: {quantidade}. "
                            f"Item sera ignorado nesta sincronizacao."
                        )

                        salvar_screenshot(
                            driver,
                            "erro_codigo_barras_nao_encontrado"
                        )

                        itens_pendentes.append({
                            "codigo_barras": codigo_barras,
                            "tipo_avaria": tipo_avaria,
                            "quantidade": quantidade
                        })

                        item_invalido = True

                        break

                    # ==============================
                    # TIPO AVARIA
                    # ==============================

                    sucesso_tipo = False

                    for tentativa in range(3):

                        try:

                            logging.info(
                                f"Tentativa selecionar "
                                f"tipo avaria "
                                f"{tentativa + 1}/3"
                            )

                            tipo_ok = selecionar_tipo_avaria(
                                driver,
                                tipo_avaria
                            )

                            if tipo_ok:

                                sucesso_tipo = True

                                logging.info(
                                    "Tipo avaria selecionado."
                                )

                                break

                        except Exception as e:

                            logging.warning(
                                f"Falha selecionar "
                                f"tipo avaria: "
                                f"{repr(e)}"
                            )

                            salvar_screenshot(
                                driver,
                                f"erro_retry_tipo_"
                                f"{tentativa + 1}"
                            )

                            time.sleep(3)

                            try:

                                driver.refresh()

                                time.sleep(8)

                                reabrir_detalhe_avaria(
                                    driver,
                                    codigo_avaria or codigoAvaria
                                )

                                logging.info(
                                    "Tela reaberta "
                                    "apos retry."
                                )

                            except Exception as e2:

                                logging.warning(
                                    f"Erro refresh retry: "
                                    f"{repr(e2)}"
                                )

                    if not sucesso_tipo:

                        logging.error(
                            f"Falha definitiva ao "
                            f"selecionar tipo avaria: "
                            f"{tipo_avaria}"
                        )

                        salvar_screenshot(
                            driver,
                            "erro_selecionar_tipo"
                        )

                        return False

                    # ==============================
                    # OBSERVAcaO
                    # ==============================

                    preencher_observacao_avaria(
                        driver,
                        observacao
                    )

                    logging.info(
                        "Cadastro inicial do item "
                        "sem fotos. Fotos serao "
                        "anexadas pela galeria "
                        "apos salvar."
                    )

                    # ==============================
                    # BOTaO ADICIONAR
                    # ==============================

                    adicionou = clicar_botao_adicionar_avaria(
                        driver
                    )

                    if not adicionou:

                        logging.error(
                            "Falha ao clicar no "
                            "botao adicionar."
                        )

                        return False

                    logging.info(
                        "Botao adicionar clicado."
                    )

                    # ==============================
                    # AGUARDA RESET FORM
                    # ==============================

                    time.sleep(1)

                    try:

                        wait.until(
                            lambda d: d.find_element(
                                By.NAME,
                                "codigo_barras"
                            ).get_attribute("value") == ""
                        )

                        logging.info(
                            "Formulario resetado."
                        )

                    except Exception:

                        logging.warning(
                            "Formulario nao resetou "
                            "no tempo esperado."
                        )

                    # ==============================
                    # AGUARDA SALVAR
                    # ==============================

                    time.sleep(0.5)

                except Exception as e:

                    logging.error(
                        f"Erro inserir item "
                        f"{codigo_barras} "
                        f"na posicao "
                        f"{i + 1}/{quantidade}: "
                        f"{repr(e)}",
                        exc_info=True
                    )

                    salvar_screenshot(
                        driver,
                        "erro_inserir_item"
                    )

                    return False

            if item_invalido:

                continue

            # ======================================
            # ANEXAR FOTOS GALERIA
            # ======================================

            if caminhos_fotos:

                logging.info(
                    "Produto cadastrado. "
                    "Iniciando anexo das fotos "
                    "pela galeria."
                )

                fotos_galeria_ok = (
                    anexar_fotos_item_cadastrado_portal(
                        driver,
                        codigo_barras,
                        tipo_avaria,
                        caminhos_fotos,
                        quantidade=quantidade
                    )
                )

                if not fotos_galeria_ok:

                    logging.error(
                        "Falha ao anexar fotos "
                        "pela galeria do item."
                    )

                    return False

        if itens_pendentes:

            logging.warning(
                f"{len(itens_pendentes)} item(ns) ignorado(s) por "
                f"codigo de barras nao encontrado no catalogo: "
                + "; ".join(
                    f"{p['codigo_barras']} ({p['tipo_avaria']}, "
                    f"qtd={p['quantidade']})"
                    for p in itens_pendentes
                )
            )

        logging.info(
            "Todos os itens inseridos "
            "com sucesso."
        )

        return True

    except Exception as e:

        logging.error(
            f"Erro geral inserir itens: "
            f"{repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_geral_inserir_itens"
        )

        return False
# ======================================================
# VALIDAR ITENS EXISTENTES COM DADOS PARA UPDATE
# ======================================================

def validar_itens_existentes_para_update(driver):

    try:

        wait = WebDriverWait(driver, 30)

        itens_existentes = []

        logging.info(
            "Lendo itens do site para update no banco..."
        )

        time.sleep(2)

        # ==========================================
        # VOLTA PRIMEIRA PaGINA
        # ==========================================

        voltar_primeira_pagina(driver)

        pagina = 1

        # ==========================================
        # LOOP PAGINAcaO
        # ==========================================

        while True:

            logging.info(
                f"Lendo pagina {pagina} para update..."
            )

            time.sleep(3)

            # ======================================
            # PRIMEIRA PASSAGEM: ESQUERDA
            # ======================================

            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            time.sleep(1)

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            logging.info(
                f"Total linhas pagina: {len(linhas)}"
            )

            dados_linhas = []

            for index, linha in enumerate(linhas):

                try:

                    codigo_barras = ler_celula_grid_completa(
                        linha,
                        "codigo_barras"
                    )

                    if not codigo_barras:

                        codigo_barras = ler_celula_grid_completa(
                            linha,
                            "codigoBarras"
                        )

                    tipo_avaria = ler_celula_grid_completa(
                        linha,
                        "tipoAvaria"
                    )

                    if not tipo_avaria:

                        tipo_avaria = ler_celula_grid_completa(
                            linha,
                            "tipo_avaria"
                        )

                    produto = ler_celula_grid_completa(
                        linha,
                        "produto"
                    )

                    artigo = ler_celula_grid_completa(
                        linha,
                        "artigo"
                    )

                    dados_linhas.append(
                        {
                            "index": index,
                            "codigo_barras": normalizar_texto(
                                codigo_barras
                            ),
                            "tipo_avaria": normalizar_texto(
                                tipo_avaria
                            ),
                            "produto": normalizar_texto(
                                produto
                            ),
                            "artigo": normalizar_texto(
                                artigo
                            ),
                            "observacao": "",
                            "preco_contrapartida": None,
                            "pagina": pagina
                        }
                    )

                except Exception as e:

                    logging.error(
                        f"Erro lendo campos principais update linha {index}: {e}"
                    )

            # ======================================
            # SEGUNDA PASSAGEM: DIREITA
            # LÊ OBSERVAcaO E PREcO
            # ======================================

            rolar_grid_horizontal(
                driver,
                "direita"
            )

            time.sleep(1)

            linhas_direita = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            for item in dados_linhas:

                try:

                    index = item["index"]

                    if index >= len(linhas_direita):

                        continue

                    linha_direita = linhas_direita[index]

                    observacao = ler_celula_grid_completa(
                        linha_direita,
                        "observacao"
                    )

                    preco_texto = ler_celula_grid_completa(
                        linha_direita,
                        "preco_contratipo"
                    )

                    if not preco_texto:

                        preco_texto = ler_celula_grid_completa(
                            linha_direita,
                            "preco_contrapartida"
                        )

                    item["observacao"] = normalizar_texto(
                        observacao
                    )

                    item["preco_contrapartida"] = normalizar_decimal(
                        preco_texto
                    )

                    logging.info(
                        f"Preco bruto update => "
                        f"Codigo: {item['codigo_barras']} | "
                        f"Valor: [{preco_texto}]"
                    )

                except Exception as e:

                    logging.warning(
                        f"Erro lendo preco/observacao update linha {item['index']}: {e}"
                    )

            # ======================================
            # SALVA RESULTADO
            # ======================================

            for item in dados_linhas:

                itens_existentes.append(
                    item
                )

                logging.info(
                    f"Update site => "
                    f"Codigo: {item['codigo_barras']} | "
                    f"Tipo: {item['tipo_avaria']} | "
                    f"Produto: {item['produto']} | "
                    f"Artigo: {item['artigo']} | "
                    f"Preco: {item['preco_contrapartida']} | "
                    f"OBS.: {item['observacao']} | "
                    f"Pagina: {item['pagina']}"
                )

            # volta para esquerda antes de paginar
            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            # ======================================
            # PROXIMA PAGINA
            # ======================================

            try:

                logging.info(
                    f"Tentando localizar botão próxima página..."
                )

                # aguarda botão aparecer
                botao_proximo = WebDriverWait(driver, 15).until(
                    EC.presence_of_element_located(
                        (
                            By.XPATH,
                            "//button[@aria-label='Ir para a próxima página']"
                        )
                    )
                )

                logging.info(
                    "Botão próxima página localizado."
                )

                # ==================================
                # VALIDA DESABILITADO
                # ==================================

                disabled = botao_proximo.get_attribute(
                    "disabled"
                )

                classe = botao_proximo.get_attribute(
                    "class"
                ) or ""

                # logging.info(
                #     f"disabled={disabled} | class={classe}"
                # )

                if (
                    disabled is not None
                    or
                    "Mui-disabled" in classe
                ):

                    logging.info(
                        "Última página encontrada."
                    )

                    break

                # ==================================
                # PEGA PRIMEIRO ID ANTES
                # ==================================

                primeira_linha = WebDriverWait(driver, 15).until(
                    EC.presence_of_element_located(
                        (
                            By.XPATH,
                            "(//div[@role='row' and @data-id])[1]"
                        )
                    )
                )

                primeiro_id = primeira_linha.get_attribute(
                    "data-id"
                )

                logging.info(
                    f"Primeiro ID atual: {primeiro_id}"
                )

                # ==================================
                # SCROLL
                # ==================================

                driver.execute_script("""
                    arguments[0].scrollIntoView({
                        block: 'center'
                    });
                """, botao_proximo)

                time.sleep(1)

                # ==================================
                # CLICK JS DIRETO
                # ==================================

                driver.execute_script("""
                    arguments[0].click();
                """, botao_proximo)

                logging.info(
                    "Clique botão próxima executado."
                )

                # ==================================
                # AGUARDA TROCAR GRID
                # ==================================

                WebDriverWait(driver, 20).until(
                    lambda d: (
                        d.find_element(
                            By.XPATH,
                            "(//div[@role='row' and @data-id])[1]"
                        ).get_attribute("data-id")
                        != primeiro_id
                    )
                )

                pagina += 1

                logging.info(
                    f"Página {pagina} carregada."
                )

                time.sleep(2)

            except Exception as e:

                logging.error(
                    f"ERRO REAL PAGINACAO: {repr(e)}"
                )

                salvar_screenshot(
                    driver,
                    f"erro_paginacao_{pagina}"
                )

                break

        logging.info(
            f"Total itens para update encontrados: {len(itens_existentes)}"
        )

        return itens_existentes

    except Exception as e:

        logging.error(
            f"Erro validar itens para update: {e}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_validar_itens_update"
        )

        return []

# ======================================================
# ATUALIZAR PRODUTO, ARTIGO E PREcO NO BANCO
# ======================================================

def atualizar_dados_itens_banco(
    conn,
    codigo_avaria,
    itens_site
):
    cursor = None

    try:
        cursor = conn.cursor(dictionary=True)

        total_atualizados = 0
        total_ignorados = 0
        total_erros = 0

        logging.info(
            "Iniciando update dos dados dos itens no banco..."
        )

        codigo_avaria_limpo = re.sub(
            r'\D',
            '',
            str(codigo_avaria)
        )

        for item in itens_site:

            try:
                codigo_barras = str(
                    item.get(
                        "codigo_barras",
                        ""
                    )
                ).strip()

                tipo_avaria = str(
                    item.get(
                        "tipo_avaria",
                        ""
                    )
                ).strip().upper()

                produto = str(
                    item.get(
                        "produto",
                        ""
                    )
                ).strip()

                artigo = str(
                    item.get(
                        "artigo",
                        ""
                    )
                ).strip()

                observacao = str(
                    item.get(
                        "observacao",
                        ""
                    )
                ).strip()

                preco = item.get(
                    "preco_contrapartida",
                    None
                )

                # fallback caso no seu dict venha como "preco"
                if preco is None:
                    preco = item.get(
                        "preco",
                        None
                    )

                codigo_barras_limpo = re.sub(
                    r'\D',
                    '',
                    codigo_barras
                )

                if not codigo_barras_limpo:

                    logging.warning(
                        f"Codigo de barras vazio no update. Item ignorado: {item}"
                    )

                    total_ignorados += 1
                    continue

                if not tipo_avaria:

                    logging.warning(
                        f"Tipo avaria vazio no update. Codigo: {codigo_barras_limpo}"
                    )

                    total_ignorados += 1
                    continue

                # ==================================================
                # NORMALIZA PREcO
                # ==================================================

                if preco in [None, ""]:

                    preco_formatado = None

                else:

                    preco_str = str(preco).strip()

                    # remove R$, espacos etc.
                    preco_str = preco_str.replace(
                        "R$",
                        ""
                    ).replace(
                        " ",
                        ""
                    )

                    # caso venha 1.234,56
                    if "," in preco_str and "." in preco_str:
                        preco_str = preco_str.replace(
                            ".",
                            ""
                        ).replace(
                            ",",
                            "."
                        )

                    # caso venha 71,81
                    elif "," in preco_str:
                        preco_str = preco_str.replace(
                            ",",
                            "."
                        )

                    try:
                        preco_formatado = float(preco_str)
                    except ValueError:
                        preco_formatado = None

                # ==================================================
                # UPDATE PRINCIPAL
                # ==================================================

                cursor.execute(
                    """
                    UPDATE vest_avarias_relatorio_itens
                    SET
                        produto = %s,
                        artigo = %s,
                        preco_contrapartida = %s,
                        observacao = %s,
                        updated_at = NOW()
                    WHERE REPLACE(REPLACE(REPLACE(codigo_avaria, '.', ''), '-', ''), ' ', '') = %s
                      AND REPLACE(REPLACE(REPLACE(codigo_barras, '.', ''), '-', ''), ' ', '') = %s
                      AND UPPER(TRIM(tipo_avaria)) = %s
                    """,
                    (
                        produto,
                        artigo,
                        preco_formatado,
                        observacao,
                        codigo_avaria_limpo,
                        codigo_barras_limpo,
                        tipo_avaria
                    )
                )

                if cursor.rowcount > 0:

                    total_atualizados += cursor.rowcount

                    logging.info(
                        f"Registro atualizado => "
                        f"Codigo: {codigo_barras_limpo} | "
                        f"Tipo: {tipo_avaria} | "
                        f"Preco: {preco_formatado}"
                    )

                else:

                    total_ignorados += 1

                    logging.warning(
                        f"Nenhum registro atualizado => "
                        f"Codigo: {codigo_barras_limpo} | "
                        f"Tipo: {tipo_avaria}"
                    )

                    # ==================================================
                    # DIAGNoSTICO: MOSTRA O QUE EXISTE NO BANCO
                    # ==================================================

                    cursor.execute(
                        """
                        SELECT
                            id,
                            codigo_avaria,
                            codigo_barras,
                            tipo_avaria,
                            quantidade,
                            produto,
                            artigo,
                            preco_contrapartida
                        FROM vest_avarias_relatorio_itens
                        WHERE REPLACE(REPLACE(REPLACE(codigo_avaria, '.', ''), '-', ''), ' ', '') = %s
                          AND REPLACE(REPLACE(REPLACE(codigo_barras, '.', ''), '-', ''), ' ', '') = %s
                        """,
                        (
                            codigo_avaria_limpo,
                            codigo_barras_limpo
                        )
                    )

                    registros = cursor.fetchall()

                    if registros:

                        logging.warning(
                            f"Registro encontrado pelo codigo, mas tipo pode estar diferente. "
                            f"Total encontrados: {len(registros)}"
                        )

                        for reg in registros:

                            logging.warning(
                                f"Banco => "
                                f"ID: {reg.get('id')} | "
                                f"Avaria: {reg.get('codigo_avaria')} | "
                                f"Codigo: {reg.get('codigo_barras')} | "
                                f"Tipo: {reg.get('tipo_avaria')} | "
                                f"Qtd: {reg.get('quantidade')}"
                            )

                    else:

                        logging.warning(
                            f"Nenhum registro encontrado no banco para "
                            f"Avaria: {codigo_avaria_limpo} | "
                            f"Codigo: {codigo_barras_limpo}"
                        )

            except Exception as e:

                total_erros += 1

                logging.error(
                    f"Erro ao atualizar item no banco: {repr(e)} | Item: {item}",
                    exc_info=True
                )

        conn.commit()

        logging.info(
            "Update dos itens finalizado."
        )

        logging.info(
            f"Total atualizados: {total_atualizados}"
        )

        logging.info(
            f"Total ignorados: {total_ignorados}"
        )

        logging.info(
            f"Total erros: {total_erros}"
        )

        return {
            "atualizados": total_atualizados,
            "ignorados": total_ignorados,
            "erros": total_erros
        }

    except Exception as e:

        try:
            conn.rollback()
        except Exception:
            pass

        logging.error(
            f"Erro geral ao atualizar dados dos itens no banco: {repr(e)}",
            exc_info=True
        )

        return {
            "atualizados": 0,
            "ignorados": 0,
            "erros": 1
        }

    finally:

        if cursor:
            cursor.close()

# ======================================================
# PROCESSAR UPDATE DOS DADOS DO SITE PARA O BANCO
# ======================================================

def processar_update_dados_site_para_banco(
    driver,
    conn,
    codigo_avaria
):

    try:

        logging.info(
            "Iniciando processo de update dos dados do site para o banco..."
        )

        itens_site = validar_itens_existentes_para_update(
            driver
        )

        if not itens_site:

            logging.warning(
                "Nenhum item encontrado no site para update."
            )

            return False

        sucesso = atualizar_dados_itens_banco(
            conn,
            codigo_avaria,
            itens_site
        )

        if sucesso:

            logging.info(
                "Update dos dados do site para o banco finalizado com sucesso."
            )

        else:

            logging.error(
                "Falha ao atualizar dados do site para o banco."
            )

        return sucesso

    except Exception as e:

        logging.error(
            f"Erro processo update site para banco: {e}"
        )

        salvar_screenshot(
            driver,
            "erro_update_site_banco"
        )

        return False

# ======================================================
# NORMALIZAR TIPO AVARIA
# ======================================================

def normalizar_tipo_avaria(valor):

    return normalizar_texto(
        valor
    ).upper()

# ======================================================
# LER CÉLULA GRID COMPLETA
# ======================================================

def ler_celula_grid_completa(linha, campo):

    try:

        celula = linha.find_element(
            By.XPATH,
            f".//div[@data-field='{campo}']"
        )

        texto = celula.text

        if texto and texto.strip():

            return normalizar_texto(texto)

        texto = celula.get_attribute(
            "textContent"
        )

        if texto and texto.strip():

            return normalizar_texto(texto)

        texto = celula.get_attribute(
            "innerText"
        )

        if texto and texto.strip():

            return normalizar_texto(texto)

        texto = celula.get_attribute(
            "title"
        )

        if texto and texto.strip():

            return normalizar_texto(texto)

        texto = celula.get_attribute(
            "aria-label"
        )

        if texto and texto.strip():

            return normalizar_texto(texto)

        return ""

    except Exception:

        return ""

# ======================================================
# CLICAR NA aREA DE UPLOAD PARA LIMPAR PREVIEW
# ======================================================

def clicar_area_upload_limpar_preview(driver):
    try:
        wait = WebDriverWait(driver, 20)

        area_upload = wait.until(
            EC.element_to_be_clickable(
                (
                    By.XPATH,
                    "//div[@role='presentation'][.//input[@type='file']]"
                )
            )
        )

        driver.execute_script(
            "arguments[0].scrollIntoView({block: 'center'});",
            area_upload
        )

        time.sleep(0.5)

        clicar_elemento(
            driver,
            area_upload,
            "Menu estoque"
        )

        time.sleep(1)

        logging.info("area de upload clicada para limpar preview.")

        return True

    except Exception as e:
        logging.warning(
            f"Nao foi possIvel clicar na area de upload para limpar preview: {repr(e)}"
        )
        return False

# ======================================================
# LOCALIZAR INPUT FILE DO UPLOAD
# ======================================================

def localizar_input_upload_fotos(driver):
    try:
        wait = WebDriverWait(driver, 20)

        input_file = wait.until(
            EC.presence_of_element_located(
                (
                    By.XPATH,
                    "//div[@role='presentation']//input[@type='file' and @accept='image/*']"
                )
            )
        )

        return input_file

    except Exception as e:
        logging.error(
            f"Input de upload de fotos nao localizado: {repr(e)}",
            exc_info=True
        )
        return None

# ======================================================
# ENVIAR FOTOS PARA O PORTAL SEM ACUMULAR PREVIEW
# ======================================================

def enviar_fotos_item_portal(driver, caminhos_fotos):
    try:
        if not caminhos_fotos:
            logging.info("Nenhuma foto para enviar.")
            return True

        fotos_validas = []

        for caminho in caminhos_fotos:
            if caminho and os.path.isfile(caminho):
                fotos_validas.append(caminho)
            else:
                logging.warning(
                    f"Foto nao encontrada no disco: {caminho}"
                )

        if not fotos_validas:
            logging.warning("Nenhuma foto valida encontrada para envio.")
            return True

        # ==================================================
        # CLICA NA aREA DE UPLOAD ANTES DE ENVIAR
        # Isso forca o componente a limpar preview visual
        # ==================================================
        clicar_area_upload_limpar_preview(driver)

        time.sleep(1)

        input_file = localizar_input_upload_fotos(driver)

        if not input_file:
            return False

        arquivos_para_upload = "\n".join(fotos_validas)

        input_file.send_keys(arquivos_para_upload)

        logging.info(
            f"{len(fotos_validas)} foto(s) enviada(s) para o portal."
        )

        time.sleep(2)

        return True

    except Exception as e:
        logging.error(
            f"Erro ao enviar fotos para o portal: {repr(e)}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_enviar_fotos_portal"
        )

        return False

# ======================================================
# EXECUcaO
# ======================================================

inicio_execucao = datetime.now()

print("\n+------------------------------------------------------+")
print("INICIO:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print("+------------------------------------------------------+\n")

driver = None
execucao_ok = False

try:

    with mysql_connection() as conn:

        atualizar_status(
            conn,
            fila_id,
            status=1
        )

        driver = criar_driver()

        fazer_login(driver)

        abrir_menu_estoque(driver)

        abrir_controle_avaria(driver)

        dados = listar_tabela_avarias(driver)

        salvar_avarias_mysql(conn, dados)

        testar_checkbox(driver)

        abrir_detalhe_avaria(driver, codigoAvaria)

        itens_avaria = consultar_itens_avaria(
            conn,
            codigoAvaria
        )

        total_progresso = len(itens_avaria) + 1
        processados = 0

        atualizar_status(
            conn,
            fila_id,
            status=1,
            progresso=0,
            processados=0,
            total=total_progresso
        )

        # ==================================================
        # ETAPA 1: VERIFICAR / REMOVER ITENS FORA DO BANCO
        # ==================================================

        remover_itens_nao_existentes_no_banco(
            driver,
            itens_avaria
        )

        processados += 1

        cursor_progresso = conn.cursor()

        atualizar_progresso(
            cursor_progresso,
            conn,
            fila_id,
            processados,
            total_progresso
        )

        cursor_progresso.close()

        # ==================================================
        # ETAPA 2: SINCRONIZAR ITENS DO BANCO
        # Mesmo item sem alteracao conta como processado
        # ==================================================

        sincronizado = sincronizar_itens_avaria(
            driver,
            conn,
            itens_avaria,
            fila_id=fila_id,
            processados_inicial=processados,
            total_progresso=total_progresso,
            codigo_avaria=codigoAvaria
        )

        if not sincronizado:

            logging.error(
                "Sincronizacao finalizada com erro."
            )

            raise Exception(
                "Sincronizacao finalizada com erro."
            )
        else:

            logging.info(
                "Sincronizacao finalizada com sucesso."
            )

        processar_update_dados_site_para_banco(
            driver,
            conn,
            codigoAvaria
        )

        # ==================================================
        # IMPLEMENTAR PROCESSOS AQUI
        # ==================================================

        # Exemplo:
        #
        # atualizar_status(
        #     conn,
        #     fila_id,
        #     progresso=10,
        #     processados=1,
        #     total=10
        # )

        time.sleep(3)

        # ==================================================
        # FINALIZAcaO
        # ==================================================

        fim_execucao = datetime.now()

        tempo_formatado, total_segundos = calcular_tempo_execucao(
            inicio_execucao,
            fim_execucao
        )

        logging.info(f"Fim execucao: {fim_execucao}")
        logging.info(
            f"Tempo total: {tempo_formatado} "
            f"({total_segundos}s)"
        )

        atualizar_status(
            conn,
            fila_id,
            status=2,
            progresso=100,
            processados=total_progresso,
            total=total_progresso,
            finalizar=True
        )

        logging.info("Execucao finalizada com sucesso.")

        execucao_ok = True

except KeyboardInterrupt:

    erro = "Execucao interrompida pelo usuario."

    logging.warning(erro)

    with mysql_connection() as conn:
        atualizar_status(
            conn,
            fila_id,
            status=3,
            log_texto=erro,
            finalizar=True
        )

except TimeoutException as e:

    erro = f"Timeout: {str(e)}"

    logging.error(erro)

    traceback.print_exc()

    if driver:
        salvar_screenshot(driver, "timeout")

    with mysql_connection() as conn:
        atualizar_status(
            conn,
            fila_id,
            status=3,
            log_texto=erro,
            finalizar=True
        )

except Exception as e:

    erro = traceback.format_exc()

    logging.error(erro)

    if driver:
        salvar_screenshot(driver, "erro")

    with mysql_connection() as conn:
        atualizar_status(
            conn,
            fila_id,
            status=3,
            log_texto=erro,
            finalizar=True
        )

finally:

    if driver:
        # input("\nPressione ENTER para finalizar...")
        if execucao_ok:
            print("\nExecucao finalizada com sucesso!")
        else:
            print("\nExecucao encerrada sem sucesso.")
        driver.quit()

    print("\n+------------------------------------------------------+")
    print("PROCESSO FINALIZADO")
    print("+------------------------------------------------------+\n")
