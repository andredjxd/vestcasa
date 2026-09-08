from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from dotenv import load_dotenv
from datetime import datetime

import mysql.connector
import os
import time
import xml.etree.ElementTree as ET
import re
import shutil
from datetime import datetime
import sys
import unicodedata

fila_id = int(sys.argv[1])
data_str = sys.argv[2]           # NÃO usar int aqui!

# Converte string para datetime
data_obj = datetime.strptime(data_str, "%Y-%m-%d")

# Formata para padrão BR
data = data_obj.strftime("%d/%m/%Y")
# print(data)

# VERIFICAR E CRIAR PASTAS DOS XML'S
def obter_pasta_data(base_dir, data_str):
    # Converte "12/12/2025" → datetime
    data = datetime.strptime(data_str, "%d/%m/%Y")

    ano = data.strftime("%Y")
    mes = data.strftime("%m")
    dia = data.strftime("%d")

    # hoje = datetime.now()

    pasta = os.path.join(base_dir, ano, mes, dia)

    # Cria a estrutura automaticamente
    os.makedirs(pasta, exist_ok=True)

    return pasta

# Remove todos os arquivos .xml da pasta baseada na data.
def remover_xml_da_pasta_data(base_dir, data_str):
    data = datetime.strptime(data_str, "%d/%m/%Y")

    pasta = os.path.join(
        base_dir,
        data.strftime("%Y"),
        data.strftime("%m"),
        data.strftime("%d")
    )

    if not os.path.exists(pasta):
        print("Pasta não existe.")
        return True

    arquivos_removidos = 0

    for arquivo in os.listdir(pasta):
        if arquivo.lower().endswith(".xml"):
            caminho_arquivo = os.path.join(pasta, arquivo)

            try:
                # chmod 777
                os.chmod(caminho_arquivo, 0o777)

                # remover
                os.remove(caminho_arquivo)

                arquivos_removidos += 1
                print(f"Removido: {arquivo}")

            except Exception as e:
                print(f"Erro ao remover {arquivo}: {e}")

    print(f"Total removido: {arquivos_removidos}")
    return True

# verificar qua sistema esta
def obter_diretorio_download():
    if os.name == "nt":
        return r"C:\dell\vest\xml"
    elif os.name == "posix":
        return r"/vestcasa/xml/"
    else:
        raise OSError("Sistema não suportado")

# ======================================================
# CARREGA VARIÁVEIS DO .ENV
# ======================================================

load_dotenv()

DB_HOST = os.getenv("DB_HOST")
DB_USER = os.getenv("DB_USER")
DB_PASS = os.getenv("DB_PASS")
DB_BASE = os.getenv("DB_BASE")

if not DB_HOST or not DB_USER or not DB_BASE:
    raise Exception("Variáveis de banco não encontradas no .env")

USUARIO = os.getenv("VEST_USER")
SENHA   = os.getenv("VEST_PASS")

DATA_INICIO = data
DATA_FIM    = data
HORA_INICIO = "06:00"
HORA_FIM    = "23:59"

if not USUARIO or not SENHA:
    raise Exception("Arquivo .env não carregado ou variáveis não encontradas.")

# ======================================================
# CONFIGURAÇÃO DO CHROME
# ======================================================

DOWNLOAD_DIR = obter_diretorio_download()
os.makedirs(DOWNLOAD_DIR, exist_ok=True)
# Cria pasta por data
PASTA_XML_DIA = obter_pasta_data(DOWNLOAD_DIR, DATA_INICIO)
print("XMLs serão salvos em:", PASTA_XML_DIA)
remover_xml_da_pasta_data(DOWNLOAD_DIR, DATA_INICIO)


options = webdriver.ChromeOptions()
options.add_argument("--start-maximized")

# Não abre janela do navegador
options.add_argument("--headless=new")   # Chrome moderno
options.add_argument("--disable-gpu")
options.add_argument("--window-size=1920,1080")

# (opcional) evita alguns bugs em servidores / docker
options.add_argument("--no-sandbox")
options.add_argument("--disable-dev-shm-usage")

prefs = {
    "download.default_directory": PASTA_XML_DIA,
    "download.prompt_for_download": False,
    "download.directory_upgrade": True,
    "safebrowsing.enabled": True,

    # PERMITE DOWNLOADS MÚLTIPLOS AUTOMATICAMENTE
    "profile.default_content_setting_values.automatic_downloads": 1
}

options.add_experimental_option("prefs", prefs)

driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()),
    options=options
)

# Garante que o Chrome realmente usa essa pasta
driver.execute_cdp_cmd(
    "Page.setDownloadBehavior",
    {
        "behavior": "allow",
        "downloadPath": PASTA_XML_DIA
    }
)

wait = WebDriverWait(driver, 25)

# Marca início da execução
inicio_execucao = datetime.now()
print("Início da execução:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))

# ======================================================
# FUNÇÕES AUXILIARES
# ======================================================

PASTA_PROCESSADOS = os.path.join(PASTA_XML_DIA, "processados")
os.makedirs(PASTA_PROCESSADOS, exist_ok=True)

def atualizar_progresso(cursor, conn, fila_id, processados, total):
    progresso = (processados / total) * 100

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET processados=%s, total=%s, progresso=%s
        WHERE id=%s
    """, (processados, total, progresso, fila_id))
    conn.commit()

def contar_vendas_antes_de_processar(driver):
    print("\nContando vendas antes de iniciar processamento...")

    pagina = 1
    total_vendas = 0

    while True:
        time.sleep(2)

        linhas = driver.execute_script("""
            const rows = document.querySelectorAll(".v-data-table__wrapper tbody tr");
            return rows.length;
        """)

        print(f"Página {pagina} -> {linhas} vendas encontradas")

        total_vendas += linhas

        # Tenta ir para próxima página
        try:
            botao_proximo = driver.find_element(
                By.XPATH,
                "//button[.//i[contains(@class,'mdi-chevron-right')]]"
            )

            if botao_proximo.get_attribute("disabled"):
                print("Última página alcançada.")
                break

            driver.execute_script("arguments[0].click();", botao_proximo)
            pagina += 1

        except:
            print("Não foi possível avançar página.")
            break

    print(f"\nTOTAL GERAL DE VENDAS: {total_vendas}")

    # VOLTAR PARA PRIMEIRA PÁGINA
    print("Retornando para página 1...")

    try:
        while True:
            botao_anterior = driver.find_element(
                By.XPATH,
                "//button[.//i[contains(@class,'mdi-chevron-left')]]"
            )

            if botao_anterior.get_attribute("disabled"):
                break

            driver.execute_script("arguments[0].click();", botao_anterior)
            time.sleep(1)

    except:
        pass

    print("Retornado para página inicial.\n")

    return total_vendas

def extrair_pdv_inf_ad_fisco(texto):
    """
    Ex:
    'Ponto de Venda: PALMAS_PDV02' → '02'
    """
    if not texto:
        return None

    # Captura os últimos dígitos após PDV
    match = re.search(r"PDV\s*(\d+)", texto.upper())
    if match:
        return match.group(1).zfill(2)   # garante 2 dígitos
    return None

def mover_xml_processado(caminho_xml):
    destino = os.path.join(
        PASTA_PROCESSADOS,
        os.path.basename(caminho_xml)
    )

    shutil.move(caminho_xml, destino)
    print(f"XML movido para processados: {destino}")

def importar_xmls_da_pasta(pasta_xml,venda):
    print("Lendo XMLs da pasta:", pasta_xml)

    arquivos = os.listdir(pasta_xml)
    print(f"{len(arquivos)} arquivos encontrados na pasta.")

    for arquivo in arquivos:
        nome = arquivo.lower()
        caminho_xml = os.path.join(pasta_xml, arquivo)

        # ==================================================
        # PROCESSA SOMENTE _nfe_env.xml
        # ==================================================
        if nome.endswith("_nfe_env.xml"):
            print("\nProcessando XML ENV:", arquivo)

            serie_nf, num_nf = extrair_serie_numero_arquivo(arquivo)

            if not serie_nf or not num_nf:
                print("Nome de arquivo inválido, pulando:", arquivo)
                continue

            # Lê itens
            itens = ler_itens_xml(caminho_xml)

            # Lê dados da nota
            dados_nota = ler_dados_nota_xml(caminho_xml)

            # Lê pagamentos
            pagamentos = ler_pagamentos_xml(caminho_xml)

            identificador = f"{serie_nf}_{num_nf}"

            # venda["identificador"]

            # Salva itens
            salvar_itens_mysql(
                identificador=identificador,
                identificadornf=venda["identificador"],
                serie_nf=serie_nf,
                num_nf=num_nf,
                itens=itens
            )

            # Salva nota
            salvar_nota_mysql(
                identificador=identificador,
                identificadornf=venda["identificador"],
                dados=dados_nota
            )

            # Salva pagamentos
            salvar_pagamentos_mysql(
                identificador=identificador,
                identificadornf=venda["identificador"],
                serie_nf=serie_nf,
                nNF=num_nf,
                pagamentos=pagamentos
            )
            # NOVO BLOCO dados["natOp"]
            atualizar_estoque_venda(
                serie_nf=serie_nf,
                identificador=identificador,
                itens=itens,
                dados=venda,
                natop=dados_nota["natOp"],
                tpnf=dados_nota["tpNF"],
                finnfe=dados_nota["finNFe"]
            )

            # Move ENV após processar
            mover_xml_processado(caminho_xml)
            continue

        # ==================================================
        # MOVE DIRETO _nfe_ret.xml (SEM PROCESSAR)
        # ==================================================
        if nome.endswith("_nfe_ret.xml"):
            print("\nMovendo XML RET:", arquivo)
            mover_xml_processado(caminho_xml)
            continue

        # ==================================================
        # Ignora qualquer outro arquivo
        # ==================================================
        # print("Ignorando arquivo:", arquivo)

def extrair_serie_numero_arquivo(nome_arquivo):
    """
    Ex: 10_610_2025-12-31T12-47-52_nfe_env.xml
    Retorna: (10, 610)
    """
    try:
        base = os.path.basename(nome_arquivo)
        partes = base.split("_")

        serie  = int(partes[0])
        numero = int(partes[1])

        return serie, numero

    except Exception as e:
        print("Erro ao extrair série/número do arquivo:", nome_arquivo, e)
        return None, None

def salvar_vendas_mysql(dados):
    print("Salvando vendas no MySQL...")

    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
            INSERT INTO vest_relatorio_vendas
            (
                identificador,
                status_nf,
                loja,
                pdv,
                data_hora,
                tipo_nota,
                num_nf,
                serie_nf,
                total,
                ultimo_status
            )
            VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
            ON DUPLICATE KEY UPDATE
                status_nf    = VALUES(status_nf),
                loja         = VALUES(loja),
                pdv          = VALUES(pdv),
                data_hora    = VALUES(data_hora),
                tipo_nota    = VALUES(tipo_nota),
                num_nf       = VALUES(num_nf),
                serie_nf     = VALUES(serie_nf),
                total        = VALUES(total),
                ultimo_status= VALUES(ultimo_status)
        """

    for venda in dados:

        # PDV: PALMAS_PDV10 → 10
        pdv_numerico = re.sub(r"\D", "", venda["pdv"])

        # Data MySQL
        data_mysql = converter_data_mysql(venda["data_hora"])

        # Total decimal
        total_float = converter_valor_decimal(venda["total"])

        # NF separada
        tipo_nota, num_nf, serie_nf = separar_nf_serie(venda["nf_serie"])

        # Status separado
        status_nf, ultimo_status = separar_status_nf(venda["ultimo_status"])

        valores = (
            venda["identificador"],
            status_nf,
            venda["loja"],
            pdv_numerico,
            data_mysql,
            tipo_nota,
            num_nf,
            serie_nf,
            total_float,
            ultimo_status,
        )

        cursor.execute(sql, valores)


    cursor.close()
    conn.close()

    print("Vendas gravadas com sucesso no banco!")
    print("+----------------------------------------------------------------------------------------------------+")

def salvar_nota_mysql(identificador, identificadornf, dados):
    if not dados:
        print("Dados da nota vazios.")
        return

    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
        INSERT INTO vest_relatorio_vendas_nota
        (
            identificador,
            natOp,
            modelo,
            serie_nf,
            nNF,
            dhEmi,
            tpNF,
            cpf_cliente,
            nome_cliente,
            vOutro,
            vNF,
            vTroco,
            infAdFisco,
            identificadornf
        )
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        ON DUPLICATE KEY UPDATE
            natOp        = VALUES(natOp),
            modelo       = VALUES(modelo),
            serie_nf     = VALUES(serie_nf),
            nNF          = VALUES(nNF),
            dhEmi        = VALUES(dhEmi),
            tpNF         = VALUES(tpNF),
            cpf_cliente  = VALUES(cpf_cliente),
            nome_cliente = VALUES(nome_cliente),
            vOutro       = VALUES(vOutro),
            vNF          = VALUES(vNF),
            vTroco          = VALUES(vTroco),
            infAdFisco   = VALUES(infAdFisco),
            identificadornf = VALUES(identificadornf)
    """

    # Converte data ISO -> MySQL
    dhEmi = None
    if dados["dhEmi"]:
        dhEmi = dados["dhEmi"].replace("T", " ").split("-03:00")[0]

    if int(dados["serie"]) == 800:
        identificadornf = identificadornf
    else:
        identificadornf = dados["infCpl"]

    valores = (
        identificador,
        dados["natOp"],
        dados["mod"],
        int(dados["serie"]) if dados["serie"] else None,
        int(dados["nNF"]) if dados["nNF"] else None,
        dhEmi,
        int(dados["tpNF"]) if dados["tpNF"] else None,
        dados["CPF"],
        dados["xNome"],
        float(dados["vOutro"] or 0),
        float(dados["vNF"] or 0),
        float(dados["vTroco"] or 0),
        dados["infAdFisco"],
        identificadornf,
    )

    cursor.execute(sql, valores)

    conn.commit()
    cursor.close()
    conn.close()

    print("Nota gravada no banco.")

def salvar_itens_mysql(identificador, identificadornf, serie_nf, num_nf, itens):
    if not itens:
        print("Nenhum item para salvar.")
        return

    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
        INSERT INTO vest_relatorio_vendas_itens
        (
            identificador,
            serie_nf,
            num_nf,
            num_item,
            codigo_produto,
            descricao,
            quantidade,
            valor_unitario,
            valor_total,
            identificadornf
        )
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
    """

    for item in itens:
        if serie_nf == 800:
            identificadornf = identificadornf
        else:
            identificadornf = item["identificadornf"]

        valores = (
            identificador,
            serie_nf,
            num_nf,
            item["num_item"],
            item["codigo"],
            item["descricao"],
            item["quantidade"],
            item["valor_unitario"],
            item["valor_total"],
            identificadornf,
        )

        cursor.execute(sql, valores)

    conn.commit()
    cursor.close()
    conn.close()

    print(f"{len(itens)} itens gravados no banco.")

def salvar_pagamentos_mysql(identificador, identificadornf, serie_nf, nNF, pagamentos):
    if not pagamentos:
        print("Nenhum pagamento encontrado.")
        return

    conn = conectar_mysql()
    cursor = conn.cursor()

    # 🧹 Remove pagamentos anteriores para evitar duplicidade
    cursor.execute("""
        DELETE FROM vest_relatorio_vendas_pagamentos
        WHERE identificador = %s
    """, (identificador,))

    sql = """
        INSERT INTO vest_relatorio_vendas_pagamentos
        (
            identificador,
            serie_nf,
            nNF,
            tPag,
            vPag,
            tpIntegra,
            identificadornf
        )
        VALUES (%s,%s,%s,%s,%s,%s,%s)
    """

    for pag in pagamentos:
        if serie_nf == 800:
            identificadornf = identificadornf
        else:
            identificadornf = pag["identificadornf"]
        valores = (
            identificador,
            serie_nf,
            nNF,
            pag["tPag"],
            pag["vPag"],
            pag["tpIntegra"],
            identificadornf,
        )
        cursor.execute(sql, valores)

    conn.commit()
    cursor.close()
    conn.close()

    print(f"{len(pagamentos)} pagamentos gravados.")

def extrair_identificador(texto):
    """
    Ex:
    '... Identificador: l7ll-7jqy-grym'
    -> 'l7ll-7jqy-grym'
    """

    if not texto:
        return None

    match = re.search(r"Identificador:\s*([a-z0-9\-]+)", texto, re.IGNORECASE)
    
    if match:
        return match.group(1).strip()

    return None

def ler_dados_nota_xml(caminho_xml):
    """
    Lê dados principais da NFC-e no XML
    """
    try:
        tree = ET.parse(caminho_xml)
        root = tree.getroot()

        ns = {"nfe": root.tag.split("}")[0].replace("{", "")}

        def texto(xpath):
            el = root.find(xpath, ns)
            return el.text.strip() if el is not None and el.text else None
        
        texto_fisco = texto(".//nfe:infAdFisco")
        pdv = extrair_pdv_inf_ad_fisco(texto_fisco)

        texto_identificador = texto(".//nfe:infCpl")
        identificadornfe = extrair_identificador(texto_identificador)

        cpf = texto(".//nfe:dest/nfe:CPF")
        cnpj = texto(".//nfe:dest/nfe:CNPJ")

        documento = cpf or cnpj
        # tipo_doc = "CPF" if cpf else "CNPJ"

        dados = {
            "natOp": texto(".//nfe:natOp"),
            "mod": texto(".//nfe:mod"),
            "serie": texto(".//nfe:serie"),
            "nNF": texto(".//nfe:nNF"),
            "dhEmi": texto(".//nfe:dhEmi"),
            "tpNF": texto(".//nfe:tpNF"),
            "finNFe": texto(".//nfe:finNFe"),

            # Destinatário
            "CPF": documento,
            "xNome": texto(".//nfe:dest/nfe:xNome"),

            # Totais
            "vOutro": texto(".//nfe:total/nfe:ICMSTot/nfe:vOutro"),
            "vNF":    texto(".//nfe:total/nfe:ICMSTot/nfe:vNF"),
            "vTroco": texto(".//nfe:pag/nfe:vTroco"),

            # Extras
            "infAdFisco": pdv,
            "infCpl": identificadornfe,
        }

        return dados

    except Exception as e:
        print("Erro ao ler dados da nota:", caminho_xml, e)
        return None

def ler_itens_xml(caminho_xml):
    """
    Lê XML NFe e retorna lista de itens
    """
    itens = []

    try:
        tree = ET.parse(caminho_xml)
        root = tree.getroot()

        # Namespace padrão da NFe
        ns = {"nfe": root.tag.split("}")[0].replace("{", "")}

        infNFe = root.find(".//nfe:infNFe", ns)

        texto_identificador = infNFe.findtext(".//nfe:infCpl", default="", namespaces=ns)
        identificadornfe = extrair_identificador(texto_identificador)

        # Localiza todos os <det>
        for det in root.findall(".//nfe:det", ns):

            # Número do item (atributo nItem)
            num_item = det.attrib.get("nItem")

            prod = det.find("nfe:prod", ns)
            if prod is None:
                continue

            codigo      = prod.findtext("nfe:cProd", default="", namespaces=ns)
            descricao   = prod.findtext("nfe:xProd", default="", namespaces=ns)
            quantidade  = prod.findtext("nfe:qCom", default="0", namespaces=ns)
            valor_unit  = prod.findtext("nfe:vUnCom", default="0", namespaces=ns)
            valor_total = prod.findtext("nfe:vProd", default="0", namespaces=ns)

            itens.append({
                "num_item": int(num_item) if num_item else None,  # agora não fica NULL
                "codigo": codigo,
                "descricao": descricao,
                "quantidade": float(quantidade),
                "valor_unitario": float(valor_unit),
                "valor_total": float(valor_total),
                "identificadornf": identificadornfe,
            })

    except Exception as e:
        print("Erro ao ler XML:", caminho_xml, e)

    return itens

def ler_pagamentos_xml(caminho_xml):
    """
    Lê todas as formas de pagamento (<detPag>) do XML
    """
    pagamentos = []

    try:
        tree = ET.parse(caminho_xml)
        root = tree.getroot()

        ns = {"nfe": root.tag.split("}")[0].replace("{", "")}

        infNFe = root.find(".//nfe:infNFe", ns)

        texto_identificador = infNFe.findtext(".//nfe:infCpl", default="", namespaces=ns)
        identificadornfe = extrair_identificador(texto_identificador)

        for detpag in root.findall(".//nfe:detPag", ns):

            def texto(el, tag):
                node = el.find(f"nfe:{tag}", ns)
                return node.text.strip() if node is not None and node.text else None

            tPag = texto(detpag, "tPag")
            vPag = texto(detpag, "vPag")

            # Dados de cartão (opcional)
            card = detpag.find("nfe:card", ns)
            tpIntegra = None
            if card is not None:
                tpIntegra = texto(card, "tpIntegra")

            pagamentos.append({
                "tPag": tPag,
                "vPag": float(vPag or 0),
                "tpIntegra": int(tpIntegra) if tpIntegra else None,
                "identificadornf": identificadornfe,
            })

    except Exception as e:
        print("Erro ao ler pagamentos XML:", caminho_xml, e)

    return pagamentos

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

def venda_existe_no_banco(identificador):
    conn = conectar_mysql()
    cursor = conn.cursor()
    
    # vest_relatorio_vendas
    sql = """
        SELECT 1
        FROM vest_relatorio_vendas
        WHERE identificador = %s
        AND status_nf IS NOT NULL
        LIMIT 1
    """
    cursor.execute(sql, (identificador,))
    existe = cursor.fetchone() is not None
    
    if not existe:
        cursor.close()
        conn.close()
        return False

    # vest_relatorio_vendas_nota
    sql = """
        SELECT 1
        FROM vest_relatorio_vendas_nota
        WHERE identificadornf = %s
        LIMIT 1
    """
    cursor.execute(sql, (identificador,))
    existe = cursor.fetchone() is not None
    
    if not existe:
        cursor.close()
        conn.close()
        return False

    # vest_relatorio_vendas_itens
    sql = """
        SELECT 1
        FROM vest_relatorio_vendas_itens
        WHERE identificadornf = %s
        LIMIT 1
    """
    cursor.execute(sql, (identificador,))
    existe = cursor.fetchone() is not None
    
    if not existe:
        cursor.close()
        conn.close()
        return False

    # vest_relatorio_vendas_pagamentos
    sql = """
        SELECT 1
        FROM vest_relatorio_vendas_pagamentos
        WHERE identificadornf = %s
        LIMIT 1
    """
    cursor.execute(sql, (identificador,))
    existe = cursor.fetchone() is not None

    cursor.close()
    conn.close()

    return existe

def separar_status_nf(texto):
    """
    Ex: '100 - Autorizado o uso da NF-e'
    Retorna: ('100', 'Autorizado o uso da NF-e')
    """
    if not texto:
        return None, None

    texto = texto.strip()

    # Tenta padrão: NUMERO - TEXTO
    match = re.match(r"(\d+)\s*-\s*(.+)", texto)
    if match:
        return match.group(1), match.group(2).strip()

    # Se não bater padrão, grava tudo como descrição
    return None, texto

def separar_nf_serie(nf_serie):
    """
    Entrada:  'NFC-e 83 / 12'
    Saída:    ('NFC-e', 83, 12)
    """
    try:
        if not nf_serie:
            return None, None, None

        # Remove espaços duplicados
        texto = nf_serie.strip()

        # Captura tipo, número e série
        match = re.search(r"(.+?)\s+(\d+)\s*/\s*(\d+)", texto)

        if not match:
            print("Formato NF inválido:", nf_serie)
            return None, None, None

        tipo_nota = match.group(1).strip()
        num_nf    = int(match.group(2))
        serie_nf  = int(match.group(3))

        return tipo_nota, num_nf, serie_nf

    except Exception as e:
        print("Erro ao separar NF:", nf_serie, e)
        return None, None, None

def converter_data_mysql(data_str):
    """
    Converte: '12/01/2026 às 19:10:43'
    Para:      '2026-01-12 19:10:43'
    """
    try:
        data_str = data_str.replace(" às ", " ")
        dt = datetime.strptime(data_str, "%d/%m/%Y %H:%M:%S")
        return dt.strftime("%Y-%m-%d %H:%M:%S")
    except Exception as e:
        print("Erro ao converter data:", data_str, e)
        return None
    
def converter_valor_decimal(valor_str):
    """
    Converte: 'R$ 1.234,56'
    Para:      1234.56 (float)
    """
    try:
        if not valor_str:
            return 0.0

        valor_str = (
            valor_str
            .replace("R$", "")
            .replace(".", "")
            .replace(",", ".")
            .strip()
        )

        return float(valor_str)
    except Exception as e:
        print("Erro ao converter valor:", valor_str, e)
        return 0.0
 
def conectar_mysql():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_BASE,
        autocommit=True
    )

def abrir_modal_detalhes(linha, driver):
    print("Tentando abrir modal de detalhes...")

    botoes = linha.find_elements(By.CSS_SELECTOR, "td:last-child button")

    if not botoes:
        raise Exception("Nenhum botão encontrado na coluna ações")

    botao_detalhes = botoes[0]  # 👁 primeiro botão

    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});", botao_detalhes
    )
    time.sleep(0.3)

    driver.execute_script("""
        const btn = arguments[0];
        btn.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        btn.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
        btn.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    """, botao_detalhes)

    print("Aguardando modal abrir...")

    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located(
            (By.CSS_SELECTOR, ".v-dialog.v-dialog--active, .v-dialog--active")
        )
    )

    print("Modal aberto com sucesso")

def clicar_botao_download(driver):
    print("Localizando botão de download no modal...")

    wait = WebDriverWait(driver, 3)

    try:
        # Primeira tentativa normal
        botao = wait.until(
            EC.presence_of_element_located((
                By.XPATH,
                "//div[contains(@class,'v-dialog--active')]//button[.//i[contains(@class,'mdi-download')]]"
            ))
        )

    except:

        try:
            botao = wait.until(
                EC.presence_of_element_located((
                    By.XPATH,
                    "//div[contains(@class,'v-dialog--active')]//button[.//i[contains(@class,'mdi-download')]]"
                ))
            )
        except:
            print("Botão DOWNLOAD ainda não disponível após finalizar venda.")
            return False

    # Centraliza
    driver.execute_script(
        "arguments[0].scrollIntoView({block:'center'});", botao
    )
    time.sleep(0.3)

    # Clique real (Vue)
    driver.execute_script("""
        const btn = arguments[0];
        btn.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        btn.dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
        btn.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    """, botao)

    print("Clique no botão DOWNLOAD executado")
    time.sleep(3)

    return True

def atualizar_estoque_venda(serie_nf, identificador, itens, dados, natop, tpnf, finnfe):
    conn = conectar_mysql()
    cursor = conn.cursor()


    # Data MySQL
    data_mysql = converter_data_mysql(dados["data_hora"])

    natop_limpo = (natop or "").strip().lower()

    # normaliza acentos corretamente
    natop_limpo = ''.join(
        c for c in unicodedata.normalize('NFD', natop_limpo)
        if unicodedata.category(c) != 'Mn'
    )

    # valor padrão
    tipo = "SAIDA"

    # REGRA PRINCIPAL (SEMPRE prioridade)
    if tpnf and int(tpnf) == 0:
        tipo = "ENTRADA"

    # REGRA SECUNDÁRIA (devolução oficial)
    elif finnfe and int(finnfe) == 4:
        tipo = "ENTRADA"

    # REGRA DE APOIO (texto)
    elif "devolucao" in natop_limpo:
        tipo = "ENTRADA"
    
    print(f"Atualizando estoque ({tipo})...")

    for item in itens:

        sku = item["codigo"].strip()
        qtd = int(float(item["quantidade"]))

        print(f"[ESTOQUE] SKU: {sku} | QTD: {qtd}")

        # UPDATE SEGURO
        if tipo == "ENTRADA":
            cursor.execute("""
                UPDATE vest_produto_estoque
                SET quantidade = quantidade + %s
                WHERE sku = %s
            """, (qtd, sku))
        else:
            cursor.execute("""
                UPDATE vest_produto_estoque
                SET quantidade = quantidade - %s
                WHERE sku = %s
            """, (qtd, sku))

        # SE NÃO EXISTE
        if cursor.rowcount == 0:

            valor = qtd if tipo == "ENTRADA" else -qtd

            print(f"SKU não existe, criando: {sku} ({valor})")

            cursor.execute("""
                INSERT INTO vest_produto_estoque (sku, quantidade)
                VALUES (%s, %s)
            """, (sku, valor))

        # EXTRATO identificador
        cursor.execute("""
            INSERT INTO vest_produto_extrato
            (sku, tipo_movimento, documento, qtde, data_movimento, usuario, identificador)
            VALUES (%s, %s, %s, %s, %s, %s, %s)
        """, (sku, tipo, identificador, qtd, data_mysql, "ROBO_VENDA", dados["identificador"]))

        # LOG
        cursor.execute("""
            INSERT INTO vest_produto_movimentacao_log
            (sku, tipo, usuario, documento, descricao, identificador)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (
            sku,
            tipo,
            "ROBO_VENDA",
            identificador,
            "Devolução" if tipo == "ENTRADA" else "Venda via integração XML",
            dados["identificador"]
        ))

    conn.commit()
    cursor.close()
    conn.close()

    print("Estoque atualizado com sucesso!")

def xml_ja_baixado(pasta, identificador):
    for nome in os.listdir(pasta):
        if identificador in nome and nome.endswith(".xml"):
            return True
    return False

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
# MENU VENDAS
# ======================================================

vendas_btn = wait.until(
    EC.element_to_be_clickable((By.XPATH, "//a[contains(., 'Vendas')]"))
)

driver.execute_script("arguments[0].scrollIntoView(true);", vendas_btn)
ActionChains(driver).move_to_element(vendas_btn).click().perform()

print("Clique em VENDAS realizado!")
time.sleep(2)
# ======================================================
# SELECIONAR LOJA
# ======================================================

label_loja = wait.until(
    EC.presence_of_element_located((By.XPATH, "//label[contains(., 'Loja')]"))
)

driver.execute_script("arguments[0].click();", label_loja)
time.sleep(2)

campo_loja = driver.switch_to.active_element
campo_loja.send_keys(Keys.CONTROL, "a")
campo_loja.send_keys(Keys.DELETE)

LOJA = "MEGAVEST - PALMAS"
campo_loja.send_keys(LOJA)
time.sleep(3)
campo_loja.send_keys(Keys.ENTER)

print("Loja selecionada com sucesso")

# ======================================================
# BUSCAR
# ======================================================

botao_buscar = wait.until(
    EC.presence_of_element_located((
        By.XPATH, "//button[.//span[contains(text(),'Buscar')]]"
    ))
)

driver.execute_script("arguments[0].scrollIntoView({block:'center'});", botao_buscar)
time.sleep(1)
driver.execute_script("arguments[0].click();", botao_buscar)

print("Busca executada")
time.sleep(2)

# ======================================================
# ABRIR FILTROS
# ======================================================

botao_filtros = wait.until(
    EC.presence_of_element_located((
        By.XPATH, "//button[.//span[contains(text(),'Filtros')]]"
    ))
)

driver.execute_script("arguments[0].scrollIntoView({block:'center'});", botao_filtros)
time.sleep(1)
driver.execute_script("arguments[0].click();", botao_filtros)

print("Filtros abertos")
time.sleep(2)

# ======================================================
# PREENCHER DATAS E HORAS (JS)
# ======================================================

driver.execute_script("""
function setCampo(labelText, valor) {
    const labels = [...document.querySelectorAll("label")];
    const label = labels.find(l => l.innerText.trim() === labelText);
    if (!label) return;

    const container = label.closest(".v-text-field") || label.parentElement;
    const input = container.querySelector("input");
    if (!input) return;

    input.removeAttribute("readonly");
    input.removeAttribute("disabled");
    input.focus();
    input.value = valor;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    input.dispatchEvent(new Event('blur', { bubbles: true }));
}

setCampo("Data Inicio", arguments[0]);
setCampo("Data Fim", arguments[1]);
setCampo("Inicio", arguments[2]);
setCampo("Fim", arguments[3]);
""", DATA_INICIO, DATA_FIM, HORA_INICIO, HORA_FIM)

print("Datas e horas preenchidas")

botao_aplicar = wait.until(
    EC.presence_of_element_located((By.XPATH, "//span[contains(., 'Aplicar')]/ancestor::button"))
)
driver.execute_script("arguments[0].click();", botao_aplicar)

print("Filtros aplicados")

wait.until(
    EC.presence_of_element_located((By.CSS_SELECTOR, ".v-data-table__wrapper tbody tr"))
)

time.sleep(3)

totalvendas = contar_vendas_antes_de_processar(driver)
print(f"Total que será processado: {totalvendas}")

conn = conectar_mysql()
cursor = conn.cursor()

# Atualiza total inicial (IMPORTANTE para barra SSE)
cursor.execute("""
    UPDATE vest_relatorio_fila_execucao
    SET total = %s,
        processados = 0,
        progresso = 0
    WHERE id = %s
""", (totalvendas, fila_id))
conn.commit()  # <- salva imediatamente

processados = 0
pos = 1


print("\nIniciando coleta + download + gravação...")

# conexao = conectar_mysql()
pagina = 1

while True:
    print(f"\nPágina {pagina}")

    linhas = driver.execute_script("""
        const rows = document.querySelectorAll(".v-data-table__wrapper tbody tr");
        return Array.from(rows).map(tr => {
            const tds = tr.querySelectorAll("td");
            return Array.from(tds).map(td => td.innerText.trim());
        });
    """)

    if not linhas:
        break

    for idx, cols in enumerate(linhas):

        processados += 1
        atualizar_progresso(cursor, conn, fila_id, processados, totalvendas)

        # Segurança
        if len(cols) < 9:
            print("Linha incompleta:", cols)
            continue

        identificador = cols[1].strip()

        if not identificador:
            print("Identificador vazio, pulando linha.")
            continue

        # # 🔎 Verifica se já existe no banco
        # if venda_existe_no_banco(identificador):
        #     print(f"Já existe no banco, pulando: {identificador}")
        #     continue

        xml_existe = xml_ja_baixado(PASTA_XML_DIA, identificador)
        existe_banco = venda_existe_no_banco(identificador)

        if existe_banco:
            print(f"Já está no banco, pulando tudo: {identificador}")
            continue

        print(f"Processando: {identificador}")

        # Abre modal e baixa XML (usa o DOM real)
        linhas_dom = driver.find_elements(
            By.CSS_SELECTOR,
            ".v-data-table__wrapper tbody tr"
        )
        linha_dom = linhas_dom[idx]

         # Monta registro para salvar
        venda = {
            "status": cols[0],
            "identificador": identificador,
            "loja": cols[2],
            "pdv": cols[3],
            "data_hora": cols[4],
            "cliente": cols[5],
            "nf_serie": cols[6],
            "ultimo_status": cols[7],
            "total": cols[8],
        }

        try:
            if not xml_existe:
                print("Baixando XML...")
                abrir_modal_detalhes(linha_dom, driver)
                clicar_botao_download(driver)
                time.sleep(3)
                ActionChains(driver).send_keys(Keys.ESCAPE).perform()
                time.sleep(1)
            else:
                print("XML já existe, pulando download.")

            # 🔥 IMPORTA SEMPRE (fora do else)
            print("\nImportando XMLs para o banco...")
            importar_xmls_da_pasta(PASTA_XML_DIA, venda)

        except Exception as e:
            print("Erro ao baixar XML:", e)

        salvar_vendas_mysql([venda])

    # Próxima página
    try:
        botao_proximo = driver.find_element(
            By.XPATH, "//button[.//i[contains(@class,'mdi-chevron-right')]]"
        )

        if botao_proximo.get_attribute("disabled"):
            print("Última página.")
            break

        driver.execute_script("arguments[0].click();", botao_proximo)
        time.sleep(3)
        pagina += 1

    except:
        print(" Não foi possível avançar.")
        break

# conexao.close()
print("\nProcesso finalizado com sucesso!")

# Marca fim da execução
fim_execucao = datetime.now()

tempo_formatado, total_segundos = calcular_tempo_execucao(
    inicio_execucao,
    fim_execucao
)

print("\nFim da execução:", fim_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print(f"Tempo total de execução: {tempo_formatado} ({total_segundos} segundos)")

# input("\nPressione ENTER para finalizar...")
print("\nExecução finalizada com sucesso!")
driver.quit()
