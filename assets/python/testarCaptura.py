"""Testa capturar_numero_chamado_recente sem criar chamado novo."""

from criarChamadoFreelancer import criar_driver, fazer_login, capturar_numero_chamado_recente
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from dotenv import load_dotenv
import os

load_dotenv()
usuario = os.getenv("VEST_USER_CHAMADOS")
senha = os.getenv("VEST_PASS_CHAMADOS")

driver = criar_driver()

try:
    fazer_login(driver, usuario, senha)

    driver.get("https://chamados.grupovestcasa.com.br/")

    wait = WebDriverWait(driver, 30)
    link_meus_chamados = wait.until(
        EC.element_to_be_clickable((By.CSS_SELECTOR, "a[href*='front/issue.php']"))
    )
    driver.execute_script("arguments[0].click();", link_meus_chamados)

    numero = capturar_numero_chamado_recente(driver)
    print(f"NUMERO CAPTURADO: t_{numero}")
finally:
    driver.quit()
