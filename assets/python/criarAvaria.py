from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
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

if len(sys.argv) < 3:

    logging.error(
        "Parâmetros obrigatórios não informados. "
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
    # options.add_argument("--headless=new")

    # PERFORMANCE
    options.add_argument("--disable-gpu")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--no-sandbox")

    # VISUAL
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
                # BOTÃO DETALHAR
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

                # click js
                driver.execute_script(
                    "arguments[0].click();",
                    botao_detalhar
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
    # NÃO ENCONTROU
    # ==================================================

    if not encontrou:

        raise Exception(
            f"Avaria {codigo_avaria} não encontrada."
        )

# ======================================================
# CONSULTAR ITENS AVARIA
# ======================================================

def consultar_itens_avaria(conn, codigo_avaria):

    cursor = conn.cursor(dictionary=True)

    sql = """
        SELECT

            codigo_avaria,
            codigo_barras,
            tipo_avaria,
            quantidade,
            observacao

        FROM vest_avarias_relatorio_itens

        WHERE codigo_avaria = %s
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
            f"Avaria: {item['codigo_avaria']} | "
            f"Tipo: {item['tipo_avaria']} | "
            f"Qtd: {item['quantidade']} | "
            f"OBS.: {item['observacao']}"
        )

    return dados

# ======================================================
# BOTÃO DESABILITADO
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

    except:

        return True

# ======================================================
# NORMALIZAR TEXTO
# ======================================================

def normalizar_texto(texto):

    return str(
        texto
    ).strip().upper()

# ======================================================
# VOLTAR PRIMEIRA PÁGINA
# ======================================================

def voltar_primeira_pagina(driver):

    while True:

        try:

            botao_anterior = driver.find_element(
                By.XPATH,
                "//button[@aria-label='Ir para a página anterior']"
            )

            if botao_desabilitado(botao_anterior):

                break

            logging.info(
                "Voltando página..."
            )

            driver.execute_script(
                "arguments[0].click();",
                botao_anterior
            )

            time.sleep(2)

        except:

            break

# ======================================================
# IR PARA PÁGINA ESPECÍFICA
# ======================================================

def ir_para_pagina(driver, pagina_destino):

    voltar_primeira_pagina(driver)

    pagina_atual = 1

    while pagina_atual < int(pagina_destino):

        try:

            botao_proximo = driver.find_element(
                By.XPATH,
                "//button[@aria-label='Ir para a próxima página']"
            )

            if botao_desabilitado(botao_proximo):

                return False

            logging.info(
                f"Indo para página {pagina_atual + 1}..."
            )

            driver.execute_script(
                "arguments[0].click();",
                botao_proximo
            )

            pagina_atual += 1

            time.sleep(3)

        except Exception as e:

            logging.error(
                f"Erro ir para página {pagina_destino}: {e}"
            )

            return False

    return True

# ======================================================
# NORMALIZAR TEXTO
# ======================================================

def normalizar_texto(valor):

    if valor is None:

        return ""

    return (
        str(valor)
        .replace("\xa0", " ")
        .replace("\n", " ")
        .replace("\r", " ")
        .strip()
    )

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
            f"Não conseguiu rolar grid horizontal: {e}"
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

    except:

        return ""

# ======================================================
# VALIDAR ITENS EXISTENTES
# COM PAGINAÇÃO E OBSERVAÇÃO
# ======================================================

def validar_itens_existentes(driver):

    try:

        wait = WebDriverWait(driver, 30)

        itens_existentes = []

        logging.info(
            "Lendo itens já inseridos..."
        )

        time.sleep(2)

        # ==========================================
        # VOLTA PRIMEIRA PÁGINA
        # ==========================================

        while True:

            try:

                botao_anterior = driver.find_element(
                    By.XPATH,
                    "//button[@aria-label='Ir para a página anterior']"
                )

                if (
                    botao_anterior.get_attribute("disabled")
                    or
                    "Mui-disabled" in botao_anterior.get_attribute("class")
                ):

                    break

                logging.info(
                    "Voltando página..."
                )

                driver.execute_script(
                    "arguments[0].click();",
                    botao_anterior
                )

                time.sleep(2)

            except:

                break

        pagina = 1

        # ==========================================
        # LOOP PAGINAÇÃO
        # ==========================================

        while True:

            logging.info(
                f"Lendo página {pagina}..."
            )

            time.sleep(2)

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
                f"Total linhas página: {len(linhas)}"
            )

            # ======================================
            # PRIMEIRA PASSAGEM:
            # LÊ CÓDIGO / QTD / TIPO
            # ======================================

            dados_linhas = []

            for index, linha in enumerate(linhas):

                try:

                    codigo_barras = ler_celula_grid(
                        linha,
                        "codigo_barras"
                    )

                    if not codigo_barras:

                        codigo_barras = ler_celula_grid(
                            linha,
                            "codigoBarras"
                        )

                    quantidade_texto = ler_celula_grid(
                        linha,
                        "quantidade"
                    )

                    quantidade = int(
                        float(
                            quantidade_texto
                            .replace(",", ".")
                        )
                    ) if quantidade_texto else 0

                    tipo_avaria = ler_celula_grid(
                        linha,
                        "tipoAvaria"
                    )

                    if not tipo_avaria:

                        tipo_avaria = ler_celula_grid(
                            linha,
                            "tipo_avaria"
                        )

                    dados_linhas.append(
                        {
                            "index": index,
                            "codigo_barras": codigo_barras,
                            "quantidade": quantidade,
                            "tipo_avaria": tipo_avaria,
                            "observacao": "",
                            "pagina": pagina
                        }
                    )

                except Exception as e:

                    logging.error(
                        f"Erro lendo campos principais linha {index}: {e}"
                    )

            # ======================================
            # SEGUNDA PASSAGEM:
            # ROLA PARA DIREITA E LÊ OBSERVAÇÃO
            # ======================================

            rolar_grid_horizontal(
                driver,
                "direita"
            )

            time.sleep(1)

            linhas_obs = wait.until(
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

                    if index < len(linhas_obs):

                        observacao = ler_celula_grid(
                            linhas_obs[index],
                            "observacao"
                        )

                        item["observacao"] = observacao
                    
                    preco_contratipo = ler_celula_grid_completa(
                        linhas_obs[index],
                        "preco_contratipo"
                    )

                    if not preco_contratipo:

                        preco_contratipo = ler_celula_grid(
                            linhas_obs[index],
                            "preco_contrapartida"
                        )

                    item["preco_contrapartida"] = normalizar_decimal(
                        preco_contratipo
                    )

                    logging.info(
                        f"Preço bruto site => Código: {item['codigo_barras']} | Valor: [{preco_contratipo}]"
                    )

                except Exception as e:

                    logging.warning(
                        f"Erro lendo observação linha {item['index']}: {e}"
                    )

            # ======================================
            # SALVA RESULTADO
            # ======================================

            for item in dados_linhas:

                itens_existentes.append(
                    item
                )

                logging.info(
                    f"Existente => "
                    f"Código: {item['codigo_barras']} | "
                    f"Qtd: {item['quantidade']} | "
                    f"Tipo: {item['tipo_avaria']} | "
                    f"OBS.: {item['observacao']} | "
                    f"Página: {item['pagina']}"
                )

            # volta para esquerda antes de paginar
            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            # ======================================
            # PRÓXIMA PÁGINA
            # ======================================

            try:

                botao_proximo = driver.find_element(
                    By.XPATH,
                    "//button[@aria-label='Ir para a próxima página']"
                )

                if (
                    botao_proximo.get_attribute("disabled")
                    or
                    "Mui-disabled" in botao_proximo.get_attribute("class")
                ):

                    logging.info(
                        "Última página encontrada."
                    )

                    break

                logging.info(
                    "Próxima página..."
                )

                driver.execute_script(
                    "arguments[0].click();",
                    botao_proximo
                )

                pagina += 1

                time.sleep(3)

            except:

                logging.info(
                    "Paginação finalizada."
                )

                break

        logging.info(
            f"Total itens encontrados: {len(itens_existentes)}"
        )

        return itens_existentes

    except Exception as e:

        logging.error(
            f"Erro validar itens: {e}",
            exc_info=True
        )

        salvar_screenshot(
            driver,
            "erro_validar_itens"
        )

        return []

# ======================================================
# REMOVER ITEM POR CÓDIGO E TIPO
# PROCURA EM TODAS AS PÁGINAS
# ======================================================

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
                f"Procurando item na página {pagina}..."
            )

            time.sleep(3)

            linhas = wait.until(
                EC.presence_of_all_elements_located(
                    (
                        By.XPATH,
                        "//div[@role='row' and @data-id]"
                    )
                )
            )

            logging.info(
                f"Linhas na página: {len(linhas)}"
            )

            for linha in linhas:

                try:

                    codigo = linha.find_element(
                        By.XPATH,
                        ".//div[@data-field='codigo_barras' or @data-field='codigoBarras']"
                    ).text.strip()

                    tipo = linha.find_element(
                        By.XPATH,
                        ".//div[@data-field='tipoAvaria' or @data-field='tipo_avaria' or @data-field='descricao_avaria']"
                    ).text.strip()

                    tipo = normalizar_texto(
                        tipo
                    )

                    logging.info(
                        f"Comparando linha => {codigo} | {tipo}"
                    )

                    if (
                        codigo == codigo_barras
                        and
                        tipo == tipo_avaria
                    ):

                        logging.info(
                            "Linha encontrada para remoção."
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

                        driver.execute_script(
                            "arguments[0].click();",
                            botao_excluir
                        )

                        logging.info(
                            "Botão excluir clicado."
                        )

                        wait.until(
                            EC.visibility_of_element_located(
                                (
                                    By.XPATH,
                                    "//div[@role='dialog']"
                                )
                            )
                        )

                        logging.info(
                            "Modal aberto."
                        )

                        time.sleep(1)

                        botao_sim = wait.until(
                            EC.element_to_be_clickable(
                                (
                                    By.XPATH,
                                    "//div[@role='dialog']//button[normalize-space()='Sim']"
                                )
                            )
                        )

                        driver.execute_script(
                            "arguments[0].scrollIntoView({block:'center'});",
                            botao_sim
                        )

                        time.sleep(1)

                        driver.execute_script(
                            "arguments[0].click();",
                            botao_sim
                        )

                        logging.info(
                            "Botão SIM clicado."
                        )

                        wait.until(
                            EC.invisibility_of_element_located(
                                (
                                    By.XPATH,
                                    "//div[@role='dialog']"
                                )
                            )
                        )

                        logging.info(
                            "Modal fechado."
                        )

                        time.sleep(3)

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

                    continue

            try:

                botao_proxima = driver.find_element(
                    By.XPATH,
                    "//button[@aria-label='Ir para a próxima página']"
                )

                if botao_desabilitado(botao_proxima):

                    logging.warning(
                        "Item não encontrado em nenhuma página."
                    )

                    return False

                logging.info(
                    "Indo para próxima página para remover..."
                )

                driver.execute_script(
                    "arguments[0].click();",
                    botao_proxima
                )

                pagina += 1

                time.sleep(3)

            except:

                logging.warning(
                    "Paginação não encontrada ou última página."
                )

                return False

    except Exception as e:

        erro = str(
            e
        ).lower()

        if "stale element reference" in erro:

            logging.info(
                "Elemento stale após remoção, considerando sucesso."
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
    
# ======================================================
# REMOVER ITENS DO SITE QUE NÃO EXISTEM NO BANCO
# ======================================================

def remover_itens_nao_existentes_no_banco(
    driver,
    itens_banco
):

    try:

        logging.info(
            "Verificando itens do site que não existem no banco..."
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
            f"Total itens únicos no banco: {len(mapa_banco)}"
        )

        # ==========================================
        # LOOP ATÉ NÃO TER MAIS ITEM SOBRANDO
        # ==========================================

        tentativas_sem_remover = 0

        while True:

            existentes_site = validar_itens_existentes(
                driver
            )

            if len(existentes_site) == 0:

                logging.info(
                    "Site sem itens cadastrados. Nada para remover."
                )

                break

            item_para_remover = None

            # ======================================
            # PROCURA ITEM QUE EXISTE NO SITE
            # MAS NÃO EXISTE NO BANCO
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
                        f"Código: {codigo_site} | "
                        f"Tipo: {tipo_site} | "
                        f"Qtd: {item_site['quantidade']} | "
                        f"Página: {item_site.get('pagina')}"
                    )

                    break

            # ======================================
            # NÃO TEM MAIS SOBRA
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

            time.sleep(5)

            # ======================================
            # VALIDA SE REMOVEU
            # ======================================

            existentes_depois = validar_itens_existentes(
                driver
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
                    f"Item não reduziu após remover. "
                    f"Tentativa {tentativas_sem_remover}/3"
                )

                if tentativas_sem_remover >= 3:

                    logging.error(
                        "Não foi possível remover item fora do banco após 3 tentativas."
                    )

                    break

        logging.info(
            "Verificação de itens fora do banco finalizada."
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

def sincronizar_itens_avaria(
    driver,
    itens_banco
):

    for item_banco in itens_banco:

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

        # ==========================================
        # LÊ ITENS ATUAIS DO SITE
        # ==========================================

        existentes = validar_itens_existentes(
            driver
        )

        quantidade_existente = 0

        for item_existente in existentes:

            codigo_existente = str(
                item_existente["codigo_barras"]
            ).strip()

            tipo_existente = normalizar_texto(
                item_existente["tipo_avaria"]
            )

            if (
                codigo_existente == codigo_barras
                and
                tipo_existente == tipo_avaria
            ):

                quantidade_existente += int(
                    item_existente["quantidade"]
                )

        logging.info(
            f"CODIGO: {codigo_barras} | "
            f"Banco: {quantidade_banco} | "
            f"Existente: {quantidade_existente}"
        )

        # ==========================================
        # ITEM JÁ SINCRONIZADO
        # ==========================================

        if quantidade_existente == quantidade_banco:

            logging.info(
                "Item já sincronizado."
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

            tentativas_sem_remover = 0

            while True:

                existentes_atualizados = validar_itens_existentes(
                    driver
                )

                quantidade_atual = 0

                item_para_remover = None

                for item_existente in existentes_atualizados:

                    codigo_existente = str(
                        item_existente["codigo_barras"]
                    ).strip()

                    tipo_existente = normalizar_texto(
                        item_existente["tipo_avaria"]
                    )

                    if (
                        codigo_existente == codigo_barras
                        and
                        tipo_existente == tipo_avaria
                    ):

                        quantidade_atual += int(
                            item_existente["quantidade"]
                        )

                        item_para_remover = item_existente

                logging.info(
                    f"Quantidade atual antes remover: {quantidade_atual}"
                )

                # ==============================
                # NÃO EXISTE MAIS NO SITE
                # ==============================

                if quantidade_atual == 0:

                    logging.info(
                        "Todos itens removidos."
                    )

                    break

                if not item_para_remover:

                    logging.warning(
                        "Item não localizado para remover."
                    )

                    break

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

                    tentativas_sem_remover += 1

                    logging.warning(
                        f"Remoção retornou False. "
                        f"Tentativa {tentativas_sem_remover}/3"
                    )

                    if tentativas_sem_remover >= 3:

                        logging.error(
                            "Não foi possível remover o item após 3 tentativas."
                        )

                        return False

                    time.sleep(3)

                    continue

                time.sleep(5)

                # ==============================
                # CONFIRMA SE REDUZIU
                # ==============================

                existentes_pos = validar_itens_existentes(
                    driver
                )

                quantidade_pos = 0

                for item_existente in existentes_pos:

                    codigo_existente = str(
                        item_existente["codigo_barras"]
                    ).strip()

                    tipo_existente = normalizar_texto(
                        item_existente["tipo_avaria"]
                    )

                    if (
                        codigo_existente == codigo_barras
                        and
                        tipo_existente == tipo_avaria
                    ):

                        quantidade_pos += int(
                            item_existente["quantidade"]
                        )

                logging.info(
                    f"Quantidade após remover: {quantidade_pos}"
                )

                if quantidade_pos < quantidade_atual:

                    tentativas_sem_remover = 0

                else:

                    tentativas_sem_remover += 1

                    logging.warning(
                        f"Quantidade não reduziu. "
                        f"Tentativa {tentativas_sem_remover}/3"
                    )

                    if tentativas_sem_remover >= 3:

                        logging.error(
                            "Não foi possível remover o item após 3 tentativas."
                        )

                        return False

        # ==========================================
        # CONFERE SE REMOVEU MESMO ANTES DE INSERIR
        # ==========================================

        existentes_antes_inserir = validar_itens_existentes(
            driver
        )

        quantidade_final_site = 0

        for item_existente in existentes_antes_inserir:

            codigo_existente = str(
                item_existente["codigo_barras"]
            ).strip()

            tipo_existente = normalizar_texto(
                item_existente["tipo_avaria"]
            )

            if (
                codigo_existente == codigo_barras
                and
                tipo_existente == tipo_avaria
            ):

                quantidade_final_site += int(
                    item_existente["quantidade"]
                )

        if quantidade_final_site > 0:

            logging.error(
                f"Item ainda existe no site após remoção. "
                f"Código: {codigo_barras} | "
                f"Qtd: {quantidade_final_site}. "
                f"Não vou inserir para evitar duplicidade."
            )

            return False

        # ==========================================
        # INSERE NOVAMENTE
        # ==========================================

        logging.info(
            f"Inserindo {quantidade_banco} itens..."
        )

        inserido = inserir_itens_avaria(
            driver,
            [
                {
                    "codigo_barras": codigo_barras,
                    "tipo_avaria": tipo_avaria,
                    "quantidade": quantidade_banco,
                    "observacao": observacao
                }
            ]
        )

        if inserido:

            logging.info(
                "Itens inseridos."
            )

        else:

            logging.error(
                f"Falha ao inserir item: "
                f"{codigo_barras} | {tipo_avaria}"
            )

            return False

        time.sleep(5)

    logging.info(
        "Sincronização finalizada."
    )

    return True

# ======================================================
# SELECIONAR TIPO AVARIA
# SEM FALLBACK TECLADO
# ======================================================

def selecionar_tipo_avaria(
    driver,
    tipo_avaria
):

    wait = WebDriverWait(driver, 30)

    tipo_avaria = normalizar_texto(
        tipo_avaria
    )

    try:

        logging.info(
            f"Iniciando seleção tipo avaria: {tipo_avaria}"
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

        driver.execute_script(
            "arguments[0].scrollIntoView({block:'center'});",
            campo_tipo
        )

        time.sleep(1)

        # ==========================================
        # LIMPA E DIGITA COM EVENTOS REACT
        # ==========================================

        driver.execute_script(
            """
            const input = arguments[0];
            const value = arguments[1];

            input.focus();

            const nativeInputValueSetter =
                Object.getOwnPropertyDescriptor(
                    window.HTMLInputElement.prototype,
                    'value'
                ).set;

            nativeInputValueSetter.call(input, '');

            input.dispatchEvent(
                new Event('input', { bubbles: true })
            );

            nativeInputValueSetter.call(input, value);

            input.dispatchEvent(
                new Event('input', { bubbles: true })
            );

            input.dispatchEvent(
                new Event('change', { bubbles: true })
            );
            """,
            campo_tipo,
            tipo_avaria
        )

        logging.info(
            f"Tipo avaria digitado: {tipo_avaria}"
        )

        time.sleep(2)

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

            driver.execute_script(
                "arguments[0].click();",
                botao_open
            )

            logging.info(
                "Dropdown tipo avaria aberto."
            )

            time.sleep(2)

        except Exception as e:

            logging.warning(
                f"Não localizou botão Open do tipo avaria: {repr(e)}"
            )

        # ==========================================
        # LOCALIZA OPÇÃO EXATA
        # ==========================================

        opcoes = driver.find_elements(
            By.XPATH,
            "//li[@role='option']"
        )

        logging.info(
            f"Opções encontradas no autocomplete: {len(opcoes)}"
        )

        for opcao in opcoes:

            texto = normalizar_texto(
                opcao.text
            )

            # logging.info(
            #     f"Opção autocomplete: {texto}"
            # )

            if texto == tipo_avaria:

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    opcao
                )

                time.sleep(1)

                driver.execute_script(
                    "arguments[0].click();",
                    opcao
                )

                logging.info(
                    f"Tipo avaria selecionado: {tipo_avaria}"
                )

                time.sleep(1)

                valor_final = normalizar_texto(
                    campo_tipo.get_attribute("value")
                )

                logging.info(
                    f"Valor final tipo avaria: {valor_final}"
                )

                if valor_final != tipo_avaria:

                    logging.error(
                        f"Tipo avaria final inválido. "
                        f"Esperado: {tipo_avaria} | "
                        f"Selecionado: {valor_final}"
                    )

                    return False

                return True

        # ==========================================
        # SE NÃO ACHOU, NÃO CONTINUA
        # ==========================================

        logging.error(
            f"Tipo avaria não encontrado. "
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
# PREENCHER OBSERVAÇÃO
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
                f"Observação preenchida: {observacao}"
            )

        else:

            logging.info(
                "Observação vazia, seguindo sem preencher."
            )

        time.sleep(1)

        return True

    except Exception as e:

        logging.warning(
            f"Campo observação não preenchido: {repr(e)}"
        )

        return False

# ======================================================
# CLICAR BOTÃO ADICIONAR
# ======================================================

def clicar_botao_adicionar_avaria(
    driver
):

    wait = WebDriverWait(driver, 30)

    try:

        xpaths = [

            "//button[normalize-space()='Adicionar']",

            "//button[contains(normalize-space(), 'Adicionar')]",

            "//button[.//span[contains(normalize-space(), 'Adicionar')]]",

        ]

        for xpath in xpaths:

            try:

                botao = WebDriverWait(driver, 8).until(
                    EC.element_to_be_clickable(
                        (
                            By.XPATH,
                            xpath
                        )
                    )
                )

                driver.execute_script(
                    "arguments[0].scrollIntoView({block:'center'});",
                    botao
                )

                time.sleep(1)

                driver.execute_script(
                    "arguments[0].click();",
                    botao
                )

                logging.info(
                    "Botão adicionar clicado."
                )

                return True

            except:

                pass

        # fallback por lista de botões
        botoes = driver.find_elements(
            By.TAG_NAME,
            "button"
        )

        logging.info(
            f"Total botões encontrados: {len(botoes)}"
        )

        for botao in botoes:

            try:

                texto = botao.text.strip().upper()

                if texto == "ADICIONAR":

                    driver.execute_script(
                        "arguments[0].scrollIntoView({block:'center'});",
                        botao
                    )

                    time.sleep(1)

                    driver.execute_script(
                        "arguments[0].click();",
                        botao
                    )

                    logging.info(
                        "Botão adicionar clicado."
                    )

                    return True

            except:

                pass

        logging.error(
            "Botão adicionar não encontrado."
        )

        return False

    except Exception as e:

        logging.error(
            f"Erro clicar botão adicionar: {repr(e)}"
        )

        salvar_screenshot(
            driver,
            "erro_botao_adicionar"
        )

        return False


# ======================================================
# INSERIR ITENS AVARIA
# ======================================================

def inserir_itens_avaria(
    driver,
    itens
):

    wait = WebDriverWait(driver, 30)

    logging.info(
        f"Iniciando inserção de "
        f"{len(itens)} itens..."
    )

    try:

        for item in itens:

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

            logging.info(
                f"Inserindo item | "
                f"Código: {codigo_barras} | "
                f"Tipo: {tipo_avaria} | "
                f"Qtd: {quantidade} | "
                f"OBS.: {observacao}"
            )

            # ======================================
            # LOOP QUANTIDADE
            # ======================================

            for i in range(
                quantidade
            ):

                logging.info(
                    f"Inserção {i + 1}/{quantidade}"
                )

                try:

                    # ==============================
                    # INPUT CÓDIGO BARRAS
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

                    time.sleep(1)

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
                        arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
                        arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
                        """,
                        campo_codigo
                    )

                    time.sleep(1)

                    campo_codigo.send_keys(
                        codigo_barras
                    )

                    driver.execute_script(
                        """
                        arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
                        arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
                        """,
                        campo_codigo
                    )

                    logging.info(
                        "Código barras preenchido."
                    )

                    time.sleep(2)

                    # ==============================
                    # TIPO AVARIA
                    # ==============================

                    tipo_ok = selecionar_tipo_avaria(
                        driver,
                        tipo_avaria
                    )

                    if not tipo_ok:

                        logging.error(
                            f"Falha ao selecionar tipo avaria: "
                            f"{tipo_avaria}"
                        )

                        salvar_screenshot(
                            driver,
                            "erro_selecionar_tipo"
                        )

                        return False

                    # ==============================
                    # OBSERVAÇÃO
                    # ==============================

                    preencher_observacao_avaria(
                        driver,
                        observacao
                    )

                    # ==============================
                    # BOTÃO ADICIONAR
                    # ==============================

                    adicionou = clicar_botao_adicionar_avaria(
                        driver
                    )

                    if not adicionou:

                        logging.error(
                            "Falha ao clicar no botão adicionar."
                        )

                        return False

                    # ==============================
                    # AGUARDA RESET FORM
                    # ==============================

                    time.sleep(3)

                    try:

                        wait.until(
                            lambda d: d.find_element(
                                By.NAME,
                                "codigo_barras"
                            ).get_attribute("value") == ""
                        )

                        logging.info(
                            "Formulário resetado."
                        )

                    except:

                        logging.warning(
                            "Formulário não resetou no tempo esperado."
                        )

                    time.sleep(2)

                except Exception as e:

                    logging.error(
                        f"Erro inserir item "
                        f"{codigo_barras} "
                        f"na posição {i + 1}/{quantidade}: "
                        f"{repr(e)}",
                        exc_info=True
                    )

                    salvar_screenshot(
                        driver,
                        "erro_inserir_item"
                    )

                    return False

        return True

    except Exception as e:

        logging.error(
            f"Erro geral inserir itens: {repr(e)}",
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
        # VOLTA PRIMEIRA PÁGINA
        # ==========================================

        voltar_primeira_pagina(driver)

        pagina = 1

        # ==========================================
        # LOOP PAGINAÇÃO
        # ==========================================

        while True:

            logging.info(
                f"Lendo página {pagina} para update..."
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
                f"Total linhas página: {len(linhas)}"
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
            # LÊ OBSERVAÇÃO E PREÇO
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
                        f"Preço bruto update => "
                        f"Código: {item['codigo_barras']} | "
                        f"Valor: [{preco_texto}]"
                    )

                except Exception as e:

                    logging.warning(
                        f"Erro lendo preço/observação update linha {item['index']}: {e}"
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
                    f"Código: {item['codigo_barras']} | "
                    f"Tipo: {item['tipo_avaria']} | "
                    f"Produto: {item['produto']} | "
                    f"Artigo: {item['artigo']} | "
                    f"Preço: {item['preco_contrapartida']} | "
                    f"OBS.: {item['observacao']} | "
                    f"Página: {item['pagina']}"
                )

            # volta para esquerda antes de paginar
            rolar_grid_horizontal(
                driver,
                "esquerda"
            )

            # ======================================
            # PRÓXIMA PÁGINA
            # ======================================

            try:

                botao_proximo = driver.find_element(
                    By.XPATH,
                    "//button[@aria-label='Ir para a próxima página']"
                )

                if botao_desabilitado(botao_proximo):

                    logging.info(
                        "Última página encontrada."
                    )

                    break

                logging.info(
                    "Próxima página..."
                )

                driver.execute_script(
                    "arguments[0].click();",
                    botao_proximo
                )

                pagina += 1

                time.sleep(3)

            except:

                logging.info(
                    "Paginação finalizada."
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
# ATUALIZAR PRODUTO, ARTIGO E PREÇO NO BANCO
# ======================================================

def atualizar_dados_itens_banco(
    conn,
    codigo_avaria,
    itens_site
):

    cursor = None

    try:

        cursor = conn.cursor()

        total_update = 0
        total_ignorados = 0
        total_erros = 0

        logging.info(
            "Iniciando update dos dados dos itens no banco..."
        )

        for item in itens_site:

            try:

                codigo_barras = normalizar_texto(
                    item.get("codigo_barras")
                )

                tipo_avaria = normalizar_texto(
                    item.get("tipo_avaria")
                ).upper()

                produto = normalizar_texto(
                    item.get("produto")
                )

                artigo = normalizar_texto(
                    item.get("artigo")
                )

                preco_contrapartida = item.get(
                    "preco_contrapartida"
                )

                # ==================================
                # IGNORA LINHA SEM CÓDIGO
                # ==================================

                if not codigo_barras:

                    total_ignorados += 1

                    logging.warning(
                        "Item ignorado sem código de barras."
                    )

                    continue

                # ==================================
                # UPDATE SEM OBSERVAÇÃO NO WHERE
                # ==================================
                if preco_contrapartida is not None:

                    sql = """
                        UPDATE vest_avarias_relatorio_itens
                        SET
                            produto = %s,
                            artigo = %s,
                            preco_contrapartida = %s,
                            updated_at = NOW()
                        WHERE
                            codigo_avaria = %s
                            AND codigo_barras = %s
                            AND UPPER(TRIM(tipo_avaria)) = %s
                    """

                    valores = (
                        produto if produto else None,
                        artigo if artigo else None,
                        preco_contrapartida,
                        str(codigo_avaria),
                        codigo_barras,
                        tipo_avaria
                    )

                else:

                    sql = """
                        UPDATE vest_avarias_relatorio_itens
                        SET
                            produto = %s,
                            artigo = %s,
                            updated_at = NOW()
                        WHERE
                            codigo_avaria = %s
                            AND codigo_barras = %s
                            AND UPPER(TRIM(tipo_avaria)) = %s
                    """

                    valores = (
                        produto if produto else None,
                        artigo if artigo else None,
                        str(codigo_avaria),
                        codigo_barras,
                        tipo_avaria
                    )

                cursor.execute(
                    sql,
                    valores
                )

                if cursor.rowcount > 0:

                    total_update += cursor.rowcount

                    logging.info(
                        f"Banco atualizado => "
                        f"Código: {codigo_barras} | "
                        f"Tipo: {tipo_avaria} | "
                        f"Produto: {produto} | "
                        f"Artigo: {artigo} | "
                        f"Preço: {preco_contrapartida} | "
                        f"Linhas: {cursor.rowcount}"
                    )

                else:

                    total_ignorados += 1

                    logging.warning(
                        f"Nenhum registro atualizado => "
                        f"Código: {codigo_barras} | "
                        f"Tipo: {tipo_avaria}"
                    )

            except Exception as e:

                total_erros += 1

                logging.error(
                    f"Erro update item banco: {e}",
                    exc_info=True
                )

        conn.commit()

        logging.info(
            "Update dos itens finalizado."
        )

        logging.info(
            f"Total atualizados: {total_update}"
        )

        logging.info(
            f"Total ignorados: {total_ignorados}"
        )

        logging.info(
            f"Total erros: {total_erros}"
        )

        return True

    except Exception as e:

        logging.error(
            f"Erro geral atualizar dados itens banco: {e}",
            exc_info=True
        )

        try:
            conn.rollback()
        except:
            pass

        return False

    finally:

        try:
            if cursor:
                cursor.close()
        except:
            pass

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

    except:

        return ""
    
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

        abrir_detalhe_avaria(driver, codigoAvaria)

        itens_avaria = consultar_itens_avaria(
            conn,
            codigoAvaria
        )

        remover_itens_nao_existentes_no_banco(
            driver,
            itens_avaria
        )
        
        sincronizado = sincronizar_itens_avaria(
            driver,
            itens_avaria
        )

        if not sincronizado:

            logging.error(
                "Sincronização finalizada com erro."
            )
        else:

            logging.info(
                "Sincronização finalizada com sucesso."
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
        input("\nPressione ENTER para finalizar...")
        driver.quit()

    print("\n+------------------------------------------------------+")
    print("PROCESSO FINALIZADO")
    print("+------------------------------------------------------+\n")