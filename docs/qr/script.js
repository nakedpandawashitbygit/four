// Функция для сохранения QR-кода
async function saveQRCode(text, imageData) {
    // Получаем user_id из PHP
    const userId = document.getElementById('userId').value; // Предполагаем, что вы добавили скрытое поле для user_id

    // Проверяем, что userId не равен null
    if (!userId) {
        alert('Ошибка: user_id не установлен');
        return;
    }

    const response = await fetch('save_qr.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, text: text, imageData: imageData }),
    });

    if (response.ok) {
        alert('QR-код успешно сохранён!');
    } else {
        alert('Ошибка при сохранении QR-кода');
    }
}

// Привязываем обработчик события к кнопке "Сохранить QR-код"
document.getElementById('saveQrCode').addEventListener('click', function() {
    const qrDiv = document.getElementById('qrCode');
    const imgTag = qrDiv.querySelector('img');
    if (imgTag) {
        const qrText = document.getElementById('qrText').value;
        saveQRCode(qrText, imgTag.src);
    } else {
        alert('Сначала создайте QR-код!');
    }
});

//document.getElementById('urlShortenerForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Предотвращаем стандартное поведение формы
    const urlInput = document.getElementById('urlInput').value; // Получаем значение из поля ввода
    console.log('Введённая ссылка:', urlInput); // Отладочное сообщение
    if (!urlInput) {
        alert('Пожалуйста, введите корректный URL'); // Проверка на пустое значение
        return;
    }
    shortenURL(urlInput); // Вызываем функцию для сокращения ссылки
});
//async function shortenURL(url) {
    const userId = document.getElementById('userId').value; // Получаем userId
    const response = await fetch('shorten_url.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ userId, url }) // Отправляем данные на сервер
    });
    const data = await response.json();
    if (response.ok) {
        alert(`Сокращенная ссылка: ${data.shortened_url}`);
    } else {
        alert('Ошибка при сокращении ссылки');
    }
}

// Проверка авторизации
function checkAuth() {
    const currentUser = document.getElementById('userId').value; // Получаем user_id из скрытого поля
    const saveQrCodeButton = document.getElementById('saveQrCode');
    const viewSavedCodesButton = document.getElementById('viewSavedCodes');
    
    if (currentUser) {
        saveQrCodeButton.style.display = 'inline';
        viewSavedCodesButton.style.display = 'inline';
    } else {
        saveQrCodeButton.style.display = 'none';
        viewSavedCodesButton.style.display = 'none';
    }
}

// Убедимся, что обработчик добавляется только один раз
window.addEventListener('load', checkAuth);