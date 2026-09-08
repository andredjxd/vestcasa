"""
Abre 'Meus chamados' pra inspecionar como o numero/protocolo do
chamado e exibido (usado pra descobrir como capturar o numero
depois que um chamado novo e enviado).
"""

from criarChamadoFreelancer import criar_driver, fazer_login, salvar_screenshot
from dotenv import load_dotenv
from datetime import datetime
import os

URL_MEUS_CHAMADOS = "https://chamados.grupovestcasa.com.br/marketplace/formcreator/front/issue.php"
PASTA_SAIDA = os.path.join(os.path.dirname(__file__), "screenshots")

load_dotenv()
usuario = os.getenv("VEST_USER_CHAMADOS")
senha = os.getenv("VEST_PASS_CHAMADOS")

driver = criar_driver()

try:
    fazer_login(driver, usuario, senha)

    driver.get(URL_MEUS_CHAMADOS)

    marca = datetime.now().strftime("%Y%m%d_%H%M%S")
    caminho_html = os.path.join(PASTA_SAIDA, f"meus_chamados_{marca}.html")

    with open(caminho_html, "w", encoding="utf-8") as f:
        f.write(driver.page_source)

    salvar_screenshot(driver, "meus_chamados")
    print(f"HTML salvo em: {caminho_html}")

finally:
    driver.quit()
