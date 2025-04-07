import requests
import tkinter as tk
from tkinter import messagebox, scrolledtext

# Tokens das empresas
TOKENS = {
    "SHOPPARTNER": "APP_USR-1579081530370380-040706-0e8f72a911fe4fb2305d91c17f55f761-462843421",
    "MAQLIDER": "APP_USR-8697275498508193-040709-cba0730f9c6befee1fca248f3f58b25b-755771763"
}

def get_ad_details(ad_id, token):
    url = f'https://api.mercadolibre.com/items/{ad_id}?include_attributes=all'
    headers = {'Authorization': f'Bearer {token}'}
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        return response.json()
    else:
        print(f"Erro ao obter detalhes do anúncio {ad_id}: {response.status_code} - {response.text}")
        messagebox.showerror("Erro", f"Erro {response.status_code} ao buscar o anúncio.\n{response.text}")
        return None

def buscar_variacoes():
    output_box.delete('1.0', tk.END)
    ad_id_raw = ad_entry.get().strip()
    if not ad_id_raw:
        messagebox.showwarning("Campo vazio", "Digite um ID de anúncio.")
        return

    empresa = empresa_var.get()
    access_token = TOKENS.get(empresa)
    if not access_token:
        messagebox.showerror("Erro", "Empresa selecionada inválida.")
        return

    ad_id = f"MLB{ad_id_raw}" if not ad_id_raw.upper().startswith("MLB") else ad_id_raw.upper()

    ad_details = get_ad_details(ad_id, access_token)
    if not ad_details:
        return

    output_box.insert(tk.END, f"🟦 ID PAI: {ad_details.get('id')} - {ad_details.get('title')}\n")

    variations = ad_details.get('variations', [])
    if variations:
        for v in variations:
            var_id = v.get('id')
            sku = next((attr.get('value_name') for attr in v.get('attributes', []) if attr.get('id') == 'SELLER_SKU'), 'SKU não encontrado')
            output_box.insert(tk.END, f"   └ VARIAÇÃO → ID: {var_id} / SKU: {sku}\n")
    else:
        output_box.insert(tk.END, "   (Sem variações encontradas)\n")

# === Interface Gráfica ===
app = tk.Tk()
app.title("Buscar Variações por ID - Mercado Livre - Desenvolvido por Higor Silva")
app.geometry("700x520")
app.resizable(False, False)

tk.Label(app, text="Escolha a empresa:").pack(pady=(10, 2))
empresa_var = tk.StringVar(value="SHOPPARTNER")
empresa_menu = tk.OptionMenu(app, empresa_var, *TOKENS.keys())
empresa_menu.pack()

tk.Label(app, text="Digite o número do anúncio ou o ID completo (MLB...):").pack(pady=(10, 5))
ad_entry = tk.Entry(app, width=40)
ad_entry.pack()

tk.Button(app, text="Buscar Variações", command=buscar_variacoes, bg="#4CAF50", fg="white", padx=10, pady=5).pack(pady=10)

output_box = scrolledtext.ScrolledText(app, width=80, height=25, font=("Courier", 10))
output_box.pack(pady=10)

# Créditos no rodapé
footer_label = tk.Label(app, text="Desenvolvido por Higor Silva", font=("Arial", 8), fg="gray")
footer_label.pack(side="bottom", pady=5)

app.mainloop()
