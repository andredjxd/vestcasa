from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager
from datetime import datetime
import pickle
import os

# ======================================================
# CONFIGURAÇÃO DO CHROME
# ======================================================

options = webdriver.ChromeOptions()
options.add_argument("--headless=new")  # Pode ativar depois que cookies estiverem funcionando
options.add_argument("--disable-gpu")
options.add_argument("--window-size=1920,1080")
options.add_argument("--no-sandbox")
options.add_argument("--disable-dev-shm-usage")

driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()),
    options=options
)

wait = WebDriverWait(driver, 30)


# ⏱️ Marca início da execução
inicio_execucao = datetime.now()
print("Início da execução:", inicio_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print("+---------------------------------------------------------------------------------+")
# ======================================================
# CARREGA COOKIES (SE EXISTIR)
# ======================================================

driver.get("https://www.instagram.com/")

# Caminho absoluto baseado no arquivo atual
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
cookie_path = os.path.join(BASE_DIR, "cookies_instagram.pkl")

if os.path.exists(cookie_path):
    cookies = pickle.load(open(cookie_path, "rb"))
    for cookie in cookies:
        driver.add_cookie(cookie)
    driver.refresh()
    print("Cookies carregados com sucesso.")
else:
    print("Arquivo de cookies não encontrado em:", cookie_path)
    print("Faça login manualmente uma vez para gerar cookies.")

# ======================================================
# ACESSA PERFIL
# ======================================================

driver.get("https://www.instagram.com/vestcasa_palmas")

# Verifica se redirecionou para login
if "login" in driver.current_url:
    print("⚠ Redirecionado para login. Cookies expiraram.")
    driver.quit()
    exit()

# Aguarda header carregar
wait.until(EC.presence_of_element_located((By.TAG_NAME, "header")))

# ======================================================
# PEGA SEGUIDORES
# ======================================================

try:
    seguidores_element = wait.until(
        EC.presence_of_element_located(
            (By.XPATH, "//header//span[@title]")
        )
    )

    seguidores_str = seguidores_element.get_attribute("title")
    seguidores = int(seguidores_str.replace(".", ""))

    print("Seguidores:", seguidores)

except Exception as e:
    print("Erro ao pegar seguidores:", e)

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

fim_execucao = datetime.now()

tempo_formatado, total_segundos = calcular_tempo_execucao(
    inicio_execucao,
    fim_execucao
)

print("+---------------------------------------------------------------------------------+")
print("Fim da execução:", fim_execucao.strftime("%d/%m/%Y %H:%M:%S"))
print(f"Tempo total de execução: {tempo_formatado} ({total_segundos} segundos)")
print("\nExecução finalizada com sucesso!")

driver.quit()