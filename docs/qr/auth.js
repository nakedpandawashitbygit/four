// Функция для регистрации пользователя
function register(username, password) {
    fetch('register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password }), // Отправляем данные в формате JSON
    })
    .then(response => {
        if (response.ok) {
            return response.json(); // Если ответ успешный, парсим JSON
        } else {
            return response.json().then(data => {
                throw new Error(data.message); // Обработка ошибки
            });
        }
    })
    .then(data => {
        alert(data.message); // Показываем сообщение об успешной регистрации
        window.location.href = 'login.html'; // Перенаправление на страницу входа
    })
    .catch(error => {
        alert(error.message); // Показываем сообщение об ошибке
    });
}

// Функция для входа пользователя
function login(username, password) {
    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password }), // Отправляем данные в формате JSON

    })
    .then(response => {
        if (response.ok) {
            return response.json(); // Если ответ успешный, парсим JSON
        } else {
            return response.json().then(data => {
                throw new Error(data.message); // Обработка ошибки
            });
        }
    })
    .then(data => {
        alert(data.message); // Показываем сообщение об успешном входе
        // Здесь вы можете сохранить информацию о пользователе в сессии или локальном хранилище
        window.location.href = 'index.php'; // Перенаправление на главную страницу
    })
    .catch(error => {
        alert(error.message); // Показываем сообщение об ошибке
    });
}
