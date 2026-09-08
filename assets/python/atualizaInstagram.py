from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

from dotenv import load_dotenv
from datetime import datetime, timezone, timedelta
import mysql.connector
import pickle
import os
import sys
import time

# ======================================================
# FILA
# ======================================================

fila_id = int(sys.argv[1])

# ======================================================
# CARREGA .ENV
# ======================================================

load_dotenv()

DB_HOST = os.getenv("DB_HOST")
DB_USER = os.getenv("DB_USER")
DB_PASS = os.getenv("DB_PASS")
DB_BASE = os.getenv("DB_BASE")

# ======================================================
# MYSQL
# ======================================================

def conectar_mysql():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_BASE,
        port=3306
    )

# ======================================================
# SALVAR INSTAGRAM
# ======================================================

def salvar_instagram(seguidores, reels):

    conn = conectar_mysql()
    cursor = conn.cursor()

    sql = """
        INSERT INTO vest_instagram
        (data, seguidores, reels)
        VALUES (CURDATE(), %s, %s)
        ON DUPLICATE KEY UPDATE
            seguidores = VALUES(seguidores),
            reels = VALUES(reels)
    """

    cursor.execute(sql,(seguidores,reels))
    conn.commit()

    cursor.close()
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

    tempo_formatado = f"{horas:02d}:{minutos:02d}:{segundos:02d}"

    return tempo_formatado, total_segundos

# ======================================================
# ATUALIZAR PROGRESSO
# ======================================================

def atualizar_progresso(cursor, conn, fila_id, processados, total):
    progresso = (processados / total) * 100

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET processados=%s, total=%s, progresso=%s
        WHERE id=%s
    """, (processados, total, progresso, fila_id))
    conn.commit()

# ======================================================
# CONFIG CHROME
# ======================================================

options = webdriver.ChromeOptions()

options.add_argument("--headless=new")
options.add_argument("--start-maximized")
options.add_argument("--disable-gpu")
options.add_argument("--window-size=1920,1080")
options.add_argument("--no-sandbox")
options.add_argument("--disable-dev-shm-usage")

driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()),
    options=options
)

wait = WebDriverWait(driver, 30)

# ======================================================
# INICIO EXECUÇÃO
# ======================================================

inicio_execucao = datetime.now()

print("+---------------------------------------------------------------------------------+")
print("Inicio da execução:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))

conn = conectar_mysql()
cursor = conn.cursor()

cursor.execute("""
    UPDATE vest_relatorio_fila_execucao
    SET status=1, data_inicio=NOW()
    WHERE id=%s
""",(fila_id,))
conn.commit()

try:

    # ======================================================
    # CARREGAR COOKIES
    # ======================================================

    driver.get("https://www.instagram.com/")

    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    cookie_path = os.path.join(BASE_DIR, "cookies_instagram.pkl")

    if os.path.exists(cookie_path):

        cookies = pickle.load(open(cookie_path, "rb"))

        for cookie in cookies:
            driver.add_cookie(cookie)

        driver.refresh()

        print("Cookies carregados com sucesso")

    else:

        print("Arquivo de cookies não encontrado:", cookie_path)
        driver.quit()
        exit()

    # ======================================================
    # ABRIR PERFIL
    # ======================================================

    driver.get("https://www.instagram.com/vestcasa_palmas")

    if "login" in driver.current_url:

        print("Cookies expiraram")
        driver.quit()
        exit()

    # ======================================================
    # PEGAR SEGUIDORES
    # ======================================================

    seguidores_element = wait.until(
        EC.presence_of_element_located(
            (By.XPATH, "//header//span[@title]")
        )
    )

    seguidores_str = seguidores_element.get_attribute("title")
    seguidores = int(seguidores_str.replace('.', '').replace(',', ''))

    print("Seguidores:", seguidores)

    atualizar_progresso(cursor, conn, fila_id, 1, 2)

    # ======================================================
    # ABRIR REELS
    # ======================================================

    driver.get("https://www.instagram.com/vestcasa_palmas/reels/")

    # aguarda o grid principal de reels carregar (nao usa <time>, pois
    # os thumbnails do Instagram nao rendem <time> na grade)
    try:
        wait.until(
            EC.presence_of_element_located(
                (By.XPATH, "//div[@role='main']")
            )
        )
        print("Pagina de reels carregada.")
    except Exception:
        print("Aviso: timeout aguardando pagina de reels.")

    time.sleep(3)

    # rola para carregar mais reels na grade
    # aumentado para 10 scrolls para garantir reels recentes
    links_antes = 0
    for i in range(10):
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight)")
        time.sleep(2)
        # para cedo se nao carregou novos links nas ultimas 3 rodadas
        links_agora = len(driver.find_elements(
            By.XPATH, "//a[contains(@href,'/reel/')]"
        ))
        if i >= 2 and links_agora == links_antes:
            print(f"  Scroll {i+1}: sem novos reels carregados. Encerrando scroll.")
            break
        links_antes = links_agora
        print(f"  Scroll {i+1}: {links_agora} reels na grade")

    # coleta apenas links /reel/ — exclui /p/ que sao posts normais
    # (o XPath anterior com /p/ coletava posts comuns, como
    # https://www.instagram.com/p/DY7zThKj7TH/ que nao e um reel)
    links_reels = driver.find_elements(
        By.XPATH,
        "//a[contains(@href,'/reel/')]"
    )

    print(f"Links de reels encontrados na grade: {len(links_reels)}")

    hrefs_reels = []
    for link in links_reels:
        href = link.get_attribute("href")
        if href and href not in hrefs_reels:
            hrefs_reels.append(href)

    del links_reels

    # calcula data alvo uma unica vez fora do loop
    DIAS_ATRAS = 0
    data_alvo = (datetime.now(timezone.utc) - timedelta(days=DIAS_ATRAS)).date()
    reels = 0

    print(f"Data alvo: {data_alvo} ({DIAS_ATRAS} dia(s) atras)")

    # abre cada reel para ler a data — <time> so existe na pagina do reel
    # os reels estao listados do mais novo ao mais antigo:
    # quando 3 consecutivos sao anteriores a data_alvo, encerra cedo
    consecutivos_antigos = 0

    for href in hrefs_reels:

        try:

            driver.get(href)

            time.sleep(2)

            time_els = driver.find_elements(By.XPATH, "//time[@datetime]")

            if not time_els:
                print(f"  Sem <time> em: {href}")
                continue

            data_post = time_els[0].get_attribute("datetime")

            if not data_post:
                continue

            data_post_dt = datetime.fromisoformat(
                data_post.replace("Z", "+00:00")
            )
            data_post_date = data_post_dt.date()

            print(f"  Reel: {href} | Data: {data_post_date} | Alvo: {data_alvo}")

            if data_post_date == data_alvo:
                reels += 1
                consecutivos_antigos = 0

            elif data_post_date < data_alvo:
                consecutivos_antigos += 1
                if consecutivos_antigos >= 3:
                    print("  3 reels consecutivos anteriores a data alvo. Encerrando.")
                    break

            else:
                # reel mais novo que a data alvo — ainda nao chegou na data
                consecutivos_antigos = 0

        except Exception as e_reel:
            print(f"  Erro ao ler reel {href}: {e_reel}")
            continue

    print(f"Reels publicados {DIAS_ATRAS} dia(s) atras: {reels}")

    atualizar_progresso(cursor, conn, fila_id, 2, 2)

    salvar_instagram(seguidores, reels)

    # ======================================================
    # FINALIZAÇÃO
    # ======================================================

    fim_execucao = datetime.now()

    tempo_formatado, total_segundos = calcular_tempo_execucao(
        inicio_execucao,
        fim_execucao
    )

    print("+---------------------------------------------------------------------------------+")
    print("Fim da execução:", fim_execucao.strftime("%d/%m/%Y %H:%M:%S"))
    print(f"Tempo total de execução: {tempo_formatado} ({total_segundos} segundos)")
    print("Execução finalizada com sucesso")

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET status=2, progresso=100, data_fim=NOW()
        WHERE id=%s
    """,(fila_id,))
    conn.commit()

except Exception as e:

    print("Erro:", e)

    cursor.execute("""
        UPDATE vest_relatorio_fila_execucao
        SET status=3, log=%s, data_fim=NOW()
        WHERE id=%s
    """,(str(e),fila_id))
    conn.commit()

finally:

    cursor.close()
    conn.close()
    driver.quit()