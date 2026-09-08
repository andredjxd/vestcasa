"""
Abre chamado GLPI "SOLICITACAO FREELANCER" (categoria CONSULTORIA, form id=22)
de forma automatica.

Uso:
    python criarChamadoFreelancer.py <nome_solicitante> <loja> <data:AAAA-MM-DD> <hora_inicio:HH:MM:SS> <hora_fim:HH:MM:SS> <motivo:organizacao|limpeza> <qtd_freelancers> <valor_por_freelancer> [--enviar]

Sem --enviar, o formulario e preenchido e a execucao para antes de clicar
"Enviar" (modo seguro, nao cria chamado de verdade).
"""

import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from dotenv import load_dotenv
from datetime import datetime
import logging
import time
import sys
import os
import re

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s => %(message)s',
    datefmt='%d/%m/%Y %H:%M:%S'
)

URL_LOGIN = "https://chamados.grupovestcasa.com.br/"
URL_FORM = "https://chamados.grupovestcasa.com.br/marketplace/formcreator/front/formdisplay.php?id=22"

MOTIVOS = {
    "organizacao": "Organização",
    "limpeza": "Limpeza de loja",
}

PASTA_SCREENSHOTS = os.path.join(os.path.dirname(__file__), "screenshots")
os.makedirs(PASTA_SCREENSHOTS, exist_ok=True)


def salvar_screenshot(driver, nome):
    caminho = os.path.join(
        PASTA_SCREENSHOTS,
        f"{nome}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
    )
    driver.save_screenshot(caminho)
    logging.info(f"Screenshot salva: {caminho}")


def definir_valor_input(driver, seletor_css, valor):
    """Seta o value via JS e dispara input/change (necessario pros campos
    de data/hora do flatpickr, que sao readonly pro Selenium)."""
    el = driver.find_element(By.CSS_SELECTOR, seletor_css)
    driver.execute_script(
        """
        const el = arguments[0];
        const valor = arguments[1];
        const setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        setter.call(el, valor);
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
        """,
        el,
        valor
    )


def formatar_moeda(valor):
    return str(int(round(valor)))


def definir_data_flatpickr(driver, seletor_css_input, data_iso):
    """O campo de data usa flatpickr com altInput: setar o value cru nao
    funciona pois o onClose do picker sobrescreve com o valor do altInput.
    Usa a API do proprio flatpickr (setDate) pra atualizar os dois lados."""
    el = driver.find_element(By.CSS_SELECTOR, seletor_css_input)
    driver.execute_script(
        """
        const el = arguments[0];
        const dataIso = arguments[1];
        const wrapper = el.closest('.flatpickr');
        wrapper._flatpickr.setDate(dataIso, true, 'Y-m-d');
        """,
        el,
        data_iso
    )


PASTA_PERFIL_CHROME = os.path.join(os.path.dirname(__file__), "chrome_profile_chamados")


def criar_driver():
    options = uc.ChromeOptions()
    options.add_argument("--start-maximized")

    # perfil persistente: guarda o cookie de verificacao do Cloudflare (cf_clearance)
    # entre execucoes, evitando pedir o desafio "confirme que e humano" toda vez
    options.add_argument(f"--user-data-dir={PASTA_PERFIL_CHROME}")

    # ponytail: version_main fixo pois o auto-detect do uc pegou a versao errada do driver;
    # ajustar aqui se o Chrome instalado atualizar de versao
    return uc.Chrome(options=options, version_main=150)


def fazer_login(driver, usuario, senha):
    logging.info("Abrindo tela de login...")
    driver.get(URL_LOGIN)

    wait = WebDriverWait(driver, 60)

    campos_login = driver.find_elements(By.ID, "login_name")

    if not campos_login:
        logging.info("Campo de login nao apareceu, sessao ja deve estar ativa (perfil persistente).")
        return

    campo_usuario = campos_login[0]

    # via JS (nao send_keys) pra evitar problema de tecla morta (~, ^) no layout ABNT
    definir_valor_input(driver, "#login_name", usuario)
    definir_valor_input(driver, "#login_password", senha)

    botao_entrar = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, 'button[name="submit"]')))
    driver.execute_script("arguments[0].click();", botao_entrar)

    # a url nao muda entre a tela de login e a home (mesmo endereco), entao
    # o sinal confiavel de que saiu da tela de login e o campo #login_name sumir
    wait.until(EC.staleness_of(campo_usuario))
    time.sleep(2)

    erro = driver.find_elements(By.XPATH, "//*[contains(text(),'inválidos') or contains(text(),'invalidos')]")
    if erro:
        raise Exception("Login falhou: usuario ou senha invalidos.")

    logging.info("Login realizado com sucesso.")


def preencher_formulario(driver, nome_solicitante, loja, data, hora_inicio, hora_fim, motivo, qtd, valor_unitario, valor_total):
    logging.info(f"Abrindo formulario: {URL_FORM}")
    driver.get(URL_FORM)

    wait = WebDriverWait(driver, 30)

    campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, "formcreator_field_173")))
    campo_nome.clear()
    campo_nome.send_keys(nome_solicitante)

    radio_tipo = wait.until(EC.presence_of_element_located(
        (By.CSS_SELECTOR, 'input[name="formcreator_field_174"][value="SOLICITAÇÃO FREELANCER"]')
    ))
    driver.execute_script("arguments[0].click();", radio_tipo)
    logging.info("Tipo 'SOLICITAÇÃO FREELANCER' selecionado.")

    campo_loja = wait.until(EC.visibility_of_element_located((By.NAME, "formcreator_field_3728")))
    campo_loja.clear()
    campo_loja.send_keys(loja)

    definir_data_flatpickr(driver, 'input[name="formcreator_field_3729"]', data)
    definir_valor_input(driver, 'input[name="formcreator_field_3908"]', hora_inicio)
    definir_valor_input(driver, 'input[name="formcreator_field_3909"]', hora_fim)
    logging.info(f"Data/horario preenchidos: {data} {hora_inicio}-{hora_fim}")

    radio_motivo = driver.find_element(
        By.CSS_SELECTOR, f'input[name="formcreator_field_3731"][value="{motivo}"]'
    )
    driver.execute_script("arguments[0].click();", radio_motivo)

    campo_qtd = driver.find_element(By.NAME, "formcreator_field_3732")
    campo_qtd.clear()
    campo_qtd.send_keys(str(qtd))

    campo_valor_unit = driver.find_element(By.NAME, "formcreator_field_3733")
    campo_valor_unit.clear()
    campo_valor_unit.send_keys(formatar_moeda(valor_unitario))

    campo_valor_total = driver.find_element(By.NAME, "formcreator_field_3734")
    campo_valor_total.clear()
    campo_valor_total.send_keys(formatar_moeda(valor_total))

    logging.info("Formulario preenchido.")
    salvar_screenshot(driver, "formulario_preenchido")


def enviar_formulario(driver):
    wait = WebDriverWait(driver, 30)
    botao_enviar = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, 'button[name="add"]')))
    driver.execute_script("arguments[0].click();", botao_enviar)
    logging.info("Botao Enviar clicado.")

    wait.until(lambda d: "formdisplay.php" not in d.current_url)
    logging.info(f"Chamado enviado. URL apos envio: {driver.current_url}")
    salvar_screenshot(driver, "chamado_enviado")

    numero_chamado = capturar_numero_chamado_recente(driver)

    if numero_chamado:
        logging.info(f"Numero do chamado: t_{numero_chamado}")
    else:
        logging.warning("Nao consegui identificar o numero do chamado na lista.")

    return numero_chamado


def capturar_numero_chamado_recente(driver):
    """O envio redireciona pra lista 'Meus chamados' (issue.php). O numero
    (tickets_id) fica no href de cada linha; o maior da pagina e o
    recem-criado (nao navega de novo pra nao esbarrar no CSRF do GLPI)."""
    try:
        wait = WebDriverWait(driver, 30)
        wait.until(lambda d: "tickets_id=" in d.page_source)

        numeros = [int(n) for n in re.findall(r"tickets_id=(\d+)", driver.page_source)]

        return str(max(numeros)) if numeros else None

    except Exception as e:
        logging.warning(f"Erro ao capturar numero do chamado na lista: {e}")
        salvar_screenshot(driver, "erro_captura_numero")
        return None


def main():
    if len(sys.argv) < 9:
        logging.error(
            "Uso: python criarChamadoFreelancer.py <nome_solicitante> <loja> <data:AAAA-MM-DD> "
            "<hora_inicio:HH:MM:SS> <hora_fim:HH:MM:SS> <motivo:organizacao|limpeza> "
            "<qtd_freelancers> <valor_por_freelancer> [--enviar]"
        )
        sys.exit(1)

    nome_solicitante = sys.argv[1]
    loja = sys.argv[2]
    data = sys.argv[3]
    hora_inicio = sys.argv[4]
    hora_fim = sys.argv[5]
    motivo_chave = sys.argv[6].lower()
    qtd = int(sys.argv[7])
    valor_unitario = float(sys.argv[8])
    valor_total = qtd * valor_unitario
    enviar = "--enviar" in sys.argv

    motivo = MOTIVOS.get(motivo_chave)
    if not motivo:
        logging.error(f"Motivo invalido: {motivo_chave}. Use 'organizacao' ou 'limpeza'.")
        sys.exit(1)

    load_dotenv()
    usuario = os.getenv("VEST_USER_CHAMADOS")
    senha = os.getenv("VEST_PASS_CHAMADOS")

    if not usuario or not senha:
        logging.error("VEST_USER_CHAMADOS / VEST_PASS_CHAMADOS nao configurados no .env")
        sys.exit(1)

    driver = criar_driver()

    try:
        fazer_login(driver, usuario, senha)

        preencher_formulario(
            driver,
            nome_solicitante=nome_solicitante,
            loja=loja,
            data=data,
            hora_inicio=hora_inicio,
            hora_fim=hora_fim,
            motivo=motivo,
            qtd=qtd,
            valor_unitario=valor_unitario,
            valor_total=valor_total
        )

        if enviar:
            numero_chamado = enviar_formulario(driver)
            print(f"CHAMADO_CRIADO=t_{numero_chamado}")
        else:
            logging.info(
                "Modo seguro (sem --enviar): formulario preenchido mas NAO enviado. "
                "Confira o screenshot e rode de novo com --enviar para criar o chamado de verdade."
            )
            input("Pressione ENTER para fechar o navegador...")

    except Exception as e:
        logging.error(f"Erro: {e}", exc_info=True)
        salvar_screenshot(driver, "erro")

    finally:
        driver.quit()


if __name__ == "__main__":
    main()
