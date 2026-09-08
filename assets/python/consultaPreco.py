from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from dotenv import load_dotenv
from datetime import datetime
from decimal import Decimal

import mysql.connector
import os
import time
import xml.etree.ElementTree as ET
import base64
import re
import shutil
import sys

fila_id = int(sys.argv[1])

# ======================================================
# CARREGA VARIÁVEIS DO .ENV
# ======================================================

load_dotenv()

DB_HOST = os.getenv("DB_HOST")
DB_USER = os.getenv("DB_USER")
DB_PASS = os.getenv("DB_PASS")
DB_BASE = os.getenv("DB_BASE")

if not DB_HOST or not DB_USER or not DB_BASE:
    raise Exception("Variaveis de banco nao encontradas no .env")

USUARIO = os.getenv("VEST_USER_PRECO")
SENHA   = os.getenv("VEST_PASS_PRECO")

if not USUARIO or not SENHA:
    raise Exception("Arquivo .env nao carregado ou variaveis nao encontradas.")

# ======================================================
# CONFIGURAcaO DO CHROME
# ======================================================

options = webdriver.ChromeOptions()
# options.add_argument("--start-maximized")

# Nao abre janela do navegador
options.add_argument("--headless=new")   # Chrome moderno
options.add_argument("--disable-gpu")
options.add_argument("--window-size=1920,1080")

# (opcional) evita alguns bugs em servidores / docker
options.add_argument("--no-sandbox")
options.add_argument("--disable-dev-shm-usage")

prefs = {
    # "download.default_directory": PASTA_XML_DIA,
    "download.prompt_for_download": False,
    "download.directory_upgrade": True,
    "safebrowsing.enabled": True,

    # PERMITE DOWNLOADS MuLTIPLOS AUTOMATICAMENTE
    "profile.default_content_setting_values.automatic_downloads": 1
}

options.add_experimental_option("prefs", prefs)

driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()),
    options=options
)

wait = WebDriverWait(driver, 25)

#  Marca inicio da execucao
inicio_execucao = datetime.now()
print("Inicio da execucao:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))

# ======================================================
# FUNcoES AUXILIARES
# ======================================================

def calcular_tempo_execucao(inicio, fim):
    """
    Recebe dois datetime e retorna:
    - tempo formatado HH:MM:SS
    - total de segundos
    """
    delta = fim - inicio
    total_segundos = int(delta.total_seconds())

    horas = total_segundos // 3600
    minutos = (total_segundos % 3600) // 60
    segundos = total_segundos % 60

    tempo_formatado = f"{horas:02d}:{minutos:02d}:{segundos:02d}"

    return tempo_formatado, total_segundos

def converter_decimal(valor):
    if valor:
        return float(valor.replace(".", "").replace(",", "."))
    return None

def verificar_produto(dados):

    conn = None
    cursor = None

    try:
        conn = conectar_mysql()
        cursor = conn.cursor(buffered=True)

        cursor.execute("""
            SELECT preco_clube, limitacao_clube,
                   preco_max, limitacao_max,
                   preco_varejo, limitacao_varejo
            FROM vest_relatorio_produto_precos
            WHERE sku = %s
            LIMIT 1
        """, (dados["sku"],))

        row = cursor.fetchone()

        if not row:
            return "NOVO"

        preco_clube_db, limit_clube_db, preco_max_db, limit_max_db, preco_varejo_db, limit_varejo_db = row

        preco_clube_db  = Decimal(str(preco_clube_db  or 0))
        preco_max_db    = Decimal(str(preco_max_db    or 0))
        preco_varejo_db = Decimal(str(preco_varejo_db or 0))

        preco_clube_site  = Decimal(str(converter_decimal(dados["preco_clube"])  or 0))
        preco_max_site    = Decimal(str(converter_decimal(dados["preco_max"])    or 0))
        preco_varejo_site = Decimal(str(converter_decimal(dados["preco_varejo"]) or 0))

        limit_clube_db  = int(limit_clube_db  or 0)
        limit_max_db    = int(limit_max_db    or 0)
        limit_varejo_db = int(limit_varejo_db or 0)

        limit_clube_site  = int(dados["limitacao_clube"]  or 0)
        limit_max_site    = int(dados["limitacao_max"]    or 0)
        limit_varejo_site = int(dados["limitacao_varejo"] or 0)

        if (
            preco_clube_db  != preco_clube_site  or
            preco_max_db    != preco_max_site    or
            preco_varejo_db != preco_varejo_site or
            limit_clube_db  != limit_clube_site  or
            limit_max_db    != limit_max_site    or
            limit_varejo_db != limit_varejo_site
        ):
            return "ALTERADO"

        return "IGUAL"

    except Exception as e:
        print("Erro em verificar_produto:", e)
        return "ERRO"

    finally:
        if cursor:
            try:
                cursor.fetchall()
            except:
                pass

            try:
                cursor.close()
            except:
                pass

        if conn and conn.is_connected():
            conn.close()

def salvar_historico(dados):
    
    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
        INSERT INTO vest_relatorio_produto_precos_historico
        (
            codigobarra,
            sku,
            descricao,
            preco_clube,
            limitacao_clube,
            preco_max,
            limitacao_max,
            preco_varejo,
            limitacao_varejo
        )
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)
    """

    valores = (
        dados["codigo_barra_consultado"],
        dados["sku"],
        dados["descricao_produto"],
        converter_decimal(dados["preco_clube"]),
        dados["limitacao_clube"],
        converter_decimal(dados["preco_max"]),
        dados["limitacao_max"],
        converter_decimal(dados["preco_varejo"]),
        dados["limitacao_varejo"]
    )

    cursor.execute(sql, valores)
    conn.commit()
    cursor.close()
    conn.close()

def conectar_mysql():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_BASE,
        port=3306,
        connection_timeout=5,
        use_pure=True,
        autocommit=False
    )

def buscar_codigos_mysql():
    conn = conectar_mysql()
    cursor = conn.cursor(buffered=True)

    sql = """
        SELECT sku, MIN(codigo_barras) AS codigo_barras
        FROM vest_produto_codigo_barras
        WHERE status = 1
        GROUP BY sku;
    """

    cursor.execute(sql)

    codigos = [
        {"sku": row[0], "codigo": row[1]}
        for row in cursor.fetchall()
    ]

    cursor.close()
    conn.close()

    return codigos

def salvar_preco_mysql(dados):

    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
        INSERT INTO vest_relatorio_produto_precos
        (
            codigobarra,
            sku,
            descricao,
            preco_clube,
            limitacao_clube,
            preco_max,
            limitacao_max,
            preco_varejo,
            limitacao_varejo,
            updated
        )
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())
        ON DUPLICATE KEY UPDATE
            sku = VALUES(sku),
            descricao = VALUES(descricao),
            preco_clube = VALUES(preco_clube),
            limitacao_clube = VALUES(limitacao_clube),
            preco_max = VALUES(preco_max),
            limitacao_max = VALUES(limitacao_max),
            preco_varejo = VALUES(preco_varejo),
            limitacao_varejo = VALUES(limitacao_varejo),
            updated = NOW()
    """

    valores = (
        dados["codigo_barra_consultado"],
        dados["sku"],
        dados["descricao_produto"],
        converter_decimal(dados["preco_clube"]),
        dados["limitacao_clube"],
        converter_decimal(dados["preco_max"]),
        dados["limitacao_max"],
        converter_decimal(dados["preco_varejo"]),
        dados["limitacao_varejo"]
    )

    cursor.execute(sql, valores)
    conn.commit()

    cursor.close()
    conn.close()

    print("Atualizado:", dados["codigo_barra_consultado"]," - ", dados["sku"])

def atualizar_status_codigo(codigo, status):
    try:
        conn = conectar_mysql()
        cursor = conn.cursor()

        sql = """
            UPDATE vest_relatorio_produto_codigobarra
            SET status = %s
            WHERE codigobarra = %s
        """

        cursor.execute(sql, (status, codigo))
        conn.commit()

        cursor.close()
        conn.close()

        print(f"Status do código {codigo} atualizado para {status}")

    except Exception as e:
        print(f"Erro ao atualizar status do código {codigo}:", e)

def atualizar_progresso(cursor, conn, fila_id, processados, total):
    progresso = (processados / total) * 100

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET processados=%s, total=%s, progresso=%s
        WHERE id=%s
    """, (processados, total, progresso, fila_id))
    conn.commit()

# ======================================================
# LOGIN
# ======================================================

driver.get("https://vestcasa.vestsys.com.br/entrar")

usuario = wait.until(EC.presence_of_element_located((By.ID, "input-24")))
senha   = wait.until(EC.presence_of_element_located((By.ID, "senha")))

usuario.send_keys(USUARIO)
senha.send_keys(SENHA)
senha.send_keys(Keys.ENTER)

wait.until(EC.url_changes("https://vestcasa.vestsys.com.br/entrar"))

print("Login realizado com sucesso!")
print("Página atual:", driver.current_url)

# ======================================================
# SELECIONAR LOJA
# ======================================================

label_loja = wait.until(
    EC.presence_of_element_located((By.XPATH, "//label[contains(., 'Loja')]"))
)

driver.execute_script("arguments[0].click();", label_loja)
time.sleep(0.5)

campo_loja = driver.switch_to.active_element
campo_loja.send_keys(Keys.CONTROL, "a")
campo_loja.send_keys(Keys.DELETE)

LOJA = "MEGAVEST - PALMAS (TO)"
campo_loja.send_keys(LOJA)

# Aguarda a opção aparecer na lista e clica nela
opcao_loja = wait.until(
    EC.element_to_be_clickable((
        By.XPATH,
        f"//div[contains(@class,'v-menu__content') and contains(@class,'menuable__content__active')]"
        f"//div[@role='option']//div[contains(@class,'v-list-item__content') and normalize-space()='{LOJA}']"
    ))
)

driver.execute_script("arguments[0].click();", opcao_loja)

time.sleep(1)
campo_loja.send_keys(Keys.ENTER)

print("Loja selecionada com sucesso")

checkbox = wait.until(
    EC.presence_of_element_located(
        (By.XPATH, "//label[contains(., 'Incluir Preço')]/preceding::input[@type='checkbox'][1]")
    )
)

if not checkbox.is_selected():
    driver.execute_script("arguments[0].click();", checkbox)
    print("Checkbox 'Incluir Preço' marcado")
else:
    print("Checkbox já estava marcado")


lista_codigos = buscar_codigos_mysql()

# lista_codigos = [{'sku': '154356320999999', 'codigo': '7842110000425'} 
#                   ,{'sku': '154961320999999', 'codigo': '7908871902900'} 
#                   ,{'sku': '137755320999999', 'codigo': '7908284787279'} 
#                   ,{'sku': '155643320999999', 'codigo': '7908323149778'} 
#                 #   ,{'sku': '10405377999999', 'codigo': '7908284728548'} 
#                 #   ,{'sku': '104457203999999', 'codigo': '7908284763204'} 
#                 #   ,{'sku': '10445958999999', 'codigo': '7908284763228'}
#                 ]

print(f"{len(lista_codigos)} códigos encontrados no banco")
# print(lista_codigos)

# Verifica duplicados
# if len(lista_codigos) != len(set(lista_codigos)):
#     print("Existem códigos repetidos!")

# Remove duplicados mantendo ordem
# lista_codigos = list(dict.fromkeys(lista_codigos))

# print("Lista final:", lista_codigos)

# print(f"{len(lista_codigos)} códigos sem duplicidade")

# Variaveis do processo
total = len(lista_codigos)
lista_resultados = []

conn = conectar_mysql()
cursor = conn.cursor()

# Atualiza total inicial (IMPORTANTE para barra SSE)
cursor.execute("""
    UPDATE vest_relatorio_fila_execucao
    SET total = %s,
        processados = 0,
        progresso = 0
    WHERE id = %s
""", (total, fila_id))
conn.commit()  # <- salva imediatamente

processados = 0
pos = 1

for item in lista_codigos:
    codigo = item["codigo"]
    sku = item["sku"]

    processados += 1
    atualizar_progresso(cursor, conn, fila_id, processados, total)  # <- função faz commit
    # =============================
    # ENVIAR CÓDIGO
    # =============================

    campo_codigo = wait.until(
        EC.presence_of_element_located(
            (By.XPATH, "//label[contains(., 'Selecionar Código de Barras')]/following::input[1]")
        )
    )

     # Limpa campo
    driver.execute_script("""
        arguments[0].value = '';
        arguments[0].dispatchEvent(new Event('input'));
    """, campo_codigo)

    # Envia código
    driver.execute_script("""
        arguments[0].value = arguments[1];
        arguments[0].dispatchEvent(new Event('input'));
    """, campo_codigo, codigo)

    time.sleep(0.5)
    print("+---------------------------------------------------------------------------------+")
    print(f"Posição {pos}")
    print(f"Código {codigo} enviado")

    # =============================
    # ESPERAR ATUALIZAR ETIQUETA
    # =============================
    texto_anterior = driver.find_element(
        By.XPATH,
        "//div[contains(@class,'font-weight-bold')]//div"
    ).text

    try:
        wait.until(
            lambda d: d.find_element(
                By.XPATH,
                "//div[contains(@class,'font-weight-bold')]//div"
            ).text != texto_anterior
        )

    except TimeoutException:
        print(f"Código {codigo} não encontrou etiqueta. Pulando...")
        # atualizar_status_codigo(codigo, 2)
        continue  # Vai para o próximo código



    time.sleep(0.5)

    # =============================
    # EXTRAIR DADOS
    # =============================

    nome_produto = driver.find_element(
        By.XPATH,
        "//div[contains(@class,'font-weight-bold')]//div"
    ).text.strip()

    # print("Texto bruto:", nome_produto)

    # Captura TODOS os padrões numero/numero
    matches = re.findall(r"(\d+)\s*/\s*(\d+)", nome_produto)

    if matches:
        # Pega o ÚLTIMO par encontrado
        numero1, numero2 = matches[-1]

        # Remove TODOS os padrões numero/numero do texto
        descricao_produto = re.sub(r"\s*\d+\s*/\s*\d+", "", nome_produto).strip()

        if numero1 == codigo:
            codigo_barra = numero2
        elif numero2 == codigo:
            codigo_barra = numero1
        else:
            codigo_barra = max(numero1, numero2)

    else:
        descricao_produto = nome_produto
        codigo_barra = None

    print("Descrição limpa:", descricao_produto)
    # print("Código interno:", codigo_barra)

    # =============================
    # EXTRAIR PREÇOS
    # =============================

    precos = driver.find_elements(
        By.XPATH,
        "//div[contains(text(),'R$')]"
    )

    preco_clube = None
    limitacao_clube = None
    preco_max = None
    limitacao_max = None
    preco_varejo = None
    limitacao_varejo = None

    for p in precos:
        texto = p.text.strip()
        texto_upper = texto.upper()

        if "CLUBE MAX" in texto_upper and preco_max is None:
            m_preco = re.search(r"R\$\s?([\d,]+)", texto)
            m_limit = re.search(r"Limitação\s+(\d+)", texto)
            preco_max     = m_preco.group(1) if m_preco else None
            limitacao_max = m_limit.group(1) if m_limit else None

        elif "CLUBE" in texto_upper and preco_clube is None:
            m_preco = re.search(r"R\$\s?([\d,]+)", texto)
            m_limit = re.search(r"Limitação\s+(\d+)", texto)
            preco_clube     = m_preco.group(1) if m_preco else None
            limitacao_clube = m_limit.group(1) if m_limit else None

        elif "VAREJO" in texto_upper and preco_varejo is None:
            m_preco = re.search(r"R\$\s?([\d,]+)", texto)
            m_limit = re.search(r"Limitação\s+(\d+)", texto)
            preco_varejo     = m_preco.group(1) if m_preco else None
            limitacao_varejo = m_limit.group(1) if m_limit else None

        if preco_clube is not None and preco_max is not None and preco_varejo is not None:
            break
    resultado = {
        "sku": sku,
        "codigo_barra_consultado": codigo,
        "descricao_produto": descricao_produto,
        "preco_clube": preco_clube,
        "limitacao_clube": limitacao_clube,
        "preco_max": preco_max,
        "limitacao_max": limitacao_max,
        "preco_varejo": preco_varejo,
        "limitacao_varejo": limitacao_varejo
    }
    
    lista_resultados.append(resultado)

    status_produto = verificar_produto(resultado)

    if status_produto == "NOVO":
        salvar_preco_mysql(resultado)
        salvar_historico(resultado)
        print("Produto novo -> salvo em precos e historico")
        # atualizar_status_codigo(codigo, 1)

    elif status_produto == "ALTERADO":
        salvar_preco_mysql(resultado)
        salvar_historico(resultado)
        print("#======> Alteracao detectada -> Historico atualizado")
        # atualizar_status_codigo(codigo, 1)

    else:
        print("Sem alteracao")    

    # print("Dados extraídos:", resultado)
    time.sleep(0.5)
    # =============================
    # EXCLUIR ÚLTIMA LINHA DA TABELA
    # =============================

    try:
        # Espera existir pelo menos um botão delete
        wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "//div[@class='v-data-table__wrapper']//button[.//i[contains(@class,'mdi-delete')]]")
            )
        )

        # Pega o ÚLTIMO botão delete da tabela
        botao_excluir = driver.find_element(
            By.XPATH,
            "(//div[@class='v-data-table__wrapper']//button[.//i[contains(@class,'mdi-delete')]])[last()]"
        )

        # Scroll até o botão (importante porque tem height fixa)
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", botao_excluir)

        time.sleep(0.3)

        # Clique via JS (mais estável no Vue)
        driver.execute_script("arguments[0].click();", botao_excluir)

        # print("Produto removido com sucesso")

        # Espera a quantidade de deletes diminuir
        time.sleep(0.5)

    except Exception as e:
        print("Erro ao excluir:", e)

    pos += 1

# Marca fim da execução
fim_execucao = datetime.now()

tempo_formatado, total_segundos = calcular_tempo_execucao(
    inicio_execucao,
    fim_execucao
)
print("+---------------------------------------------------------------------------------+")
print("\nFim da execução:", fim_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print(f"Tempo total de execução: {tempo_formatado} ({total_segundos} segundos)")

# Fecha conexão global antes de encerrar
try:
    cursor.close()
except:
    pass
try:
    conn.close()
except:
    pass

print("\nExecução finalizada com sucesso!")

try:
    driver.quit()
except:
    pass

sys.exit(0)
