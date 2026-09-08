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

# ======================================================
# CONFIGURAÇÃO LOG
# ======================================================

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s => %(message)s',
    datefmt='%d/%m/%Y %H:%M:%S'
)

# ======================================================
# FILA / PARÂMETROS
# ======================================================

if len(sys.argv) < 2:

    logging.error(
        "Parâmetros obrigatórios não informados. "
        "Uso correto: python script.py <fila_id>"
    )

    sys.exit(1)

try:

    fila_id = int(
        sys.argv[1]
    )

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
# TEMPO EXECUÇÃO
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

    # ANTI DETECÇÃO
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

        # detecção vazamento
        "profile.password_manager_leak_detection": False,

        # notificações
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

    return driver
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
    # BOTÃO LOGIN
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

    time.sleep(1)

    # click JS evita problema Material UI
    driver.execute_script(
        "arguments[0].click();",
        botao_login
    )

    logging.info("Botão login clicado.")

    # ==================================================
    # AGUARDA LOGIN
    # ==================================================

    time.sleep(8)

    # salvar_screenshot(driver, "apos_login")

    logging.info(f"URL final: {driver.current_url}")

    # ==================================================
    # VALIDA LOGIN
    # ==================================================

    if "/auth/login" in driver.current_url:

        raise Exception(
            "Login falhou. Ainda está na tela login."
        )

    logging.info("Login realizado com sucesso.")

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
    driver.execute_script(
        "arguments[0].click();",
        menu_estoque
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

    driver.execute_script(
        "arguments[0].click();",
        menu_avaria
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

    driver.execute_script(
        "arguments[0].click();",
        checkbox
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

    driver.execute_script(
        "arguments[0].click();",
        checkbox
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
            # SITUAÇÃO
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
            # DESCRIÇÃO
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
            # DEPÓSITO
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
                f"Situação: {item['situacao']}"
            )

        except Exception as e:

            logging.warning(
                f"Erro ao ler linha {index+1}: {e}"
            )

    # ==================================================
    # PRINT FINAL
    # ==================================================

    print("\n================ TABELA AVARIAS ================\n")

    for item in dados:

        print(item)

    print("\n================================================\n")

    return dados

# ======================================================
# SALVAR AVARIAS MYSQL
# ======================================================

def salvar_avarias_mysql(conn, dados):

    cursor = conn.cursor()

    total_inseridos = 0
    total_erros = 0

    logging.info(
        "Iniciando gravação das avarias..."
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
                    f"Foto não encontrada no disco: {caminho}"
                )

        if not fotos_validas:
            logging.warning("Nenhuma foto válida encontrada para envio.")
            return True

        # ==================================================
        # CLICA NA ÁREA DE UPLOAD ANTES DE ENVIAR
        # Isso força o componente a limpar preview visual
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
# EXECUÇÃO
# ======================================================

inicio_execucao = datetime.now()

print("\n+------------------------------------------------------+")
print("INÍCIO:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print("+------------------------------------------------------+\n")

driver = None

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
        # FINALIZAÇÃO
        # ==================================================

        fim_execucao = datetime.now()

        tempo_formatado, total_segundos = calcular_tempo_execucao(
            inicio_execucao,
            fim_execucao
        )

        logging.info(f"Fim execução: {fim_execucao}")
        logging.info(
            f"Tempo total: {tempo_formatado} "
            f"({total_segundos}s)"
        )

        atualizar_status(
            conn,
            fila_id,
            status=2,
            progresso=100,
            finalizar=True
        )

        logging.info("Execução finalizada com sucesso.")

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
        print("\nExecucao finalizada com sucesso!")
        driver.quit()

    print("\n+------------------------------------------------------+")
    print("PROCESSO FINALIZADO")
    print("+------------------------------------------------------+\n")