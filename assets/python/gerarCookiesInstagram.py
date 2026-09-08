from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
import pickle
import time

options = webdriver.ChromeOptions()
options.add_argument("--start-maximized")

driver = webdriver.Chrome(
    service=Service(ChromeDriverManager().install()),
    options=options
)

driver.get("https://www.instagram.com/accounts/login/")

input("👉 Faça login manualmente e depois pressione ENTER aqui...")

pickle.dump(driver.get_cookies(), open("cookies_instagram.pkl", "wb"))

print("✅ Cookies salvos com sucesso!")

driver.quit()