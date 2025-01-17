from flask import Flask, request, redirect, render_template
import sqlite3
import string
import random

app = Flask(__name__)
DATABASE = 'database.db'

# Функция для генерации короткого кода
def generate_short_code():
    characters = string.ascii_letters + string.digits
    return ''.join(random.choice(characters) for _ in range(6))

# Функция для получения соединения с базой данных
def get_db_connection():
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row
    return conn

# Главная страница с формой для ввода URL
@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        original_url = request.form['url']
        short_code = generate_short_code()

        conn = get_db_connection()
        conn.execute('INSERT INTO links (code, original_url) VALUES (?, ?)',
                     (short_code, original_url))
        conn.commit()
        conn.close()

        short_url = request.host_url + short_code
        return render_template('index.html', short_url=short_url)

    return render_template('index.html')

# Перенаправление по короткой ссылке
@app.route('/<code>')
def redirect_url(code):
    conn = get_db_connection()
    link = conn.execute('SELECT original_url FROM links WHERE code = ?', (code,)).fetchone()
    conn.close()

    if link is None:
        return "Link not found", 404
    else:
        return redirect(link['original_url'])

if __name__ == '__main__':
    app.run(debug=True)
