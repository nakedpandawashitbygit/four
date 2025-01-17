// Scripts for the Dashboard

// Script for toggling url utm parameters -->
function toggleUTMFields() {
  const utmFields = document.getElementById('utmFields');
  utmFields.style.display = utmFields.style.display === 'none' ? 'block' : 'none';
}

// Script for toggling other url parameters -->
function toggleField(fieldId) {
	const field = document.getElementById(fieldId + 'Field');
	field.style.display = field.style.display === 'none' ? 'block' : 'none';
  }

// Function to toggle expiration date editing
function toggleExpDateEdit(id) {
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);

	expDateLink.style.display = 'none'; // Hide link
	expDateInput.style.display = 'inline'; // Show input field
	expDateIcons.style.display = 'inline'; // Show action buttons
	
	const editOverlayCard = document.getElementById('edit-overlay-card-' + id);
	const editOverlayPassword = document.getElementById('edit-overlay-password-' + id);
	
	
}

// Function to save the expiration date
function saveExpDate(id) {
	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);

	// Perform save operation (could be an AJAX request or form submission)
	const newDate = expDateInput.value;
	expDateLink.textContent = newDate || 'Add expiration date';

	// Hide input and action buttons
	expDateInput.style.display = 'none';
	expDateIcons.style.display = 'none';
	expDateLink.style.display = 'inline';
}

// Function to cancel expiration date editing
function cancelExpDate(id) {
	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);

	// Hide input and action buttons
	expDateInput.style.display = 'none';
	expDateIcons.style.display = 'none';
	expDateLink.style.display = 'inline';
}

// Function to delete the expiration date
function deleteExpDate(id) {
	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);

	// Clear expiration date
	expDateInput.value = '';
	expDateLink.textContent = 'Add expiration date';

	// Hide input and action buttons
	//expDateInput.style.display = 'none';
	//expDateIcons.style.display = 'none';
	//expDateLink.style.display = 'inline';
}

function toggleLinkPasswordVisibility() {
    const passwordInput = document.getElementById('link_password');
    const toggleButton = document.getElementById('toggle-link-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.innerHTML = '<img class="create-password-eye-icon button-icon" src="/img/buttons/eye-crossed.svg" alt="Hide Password" />'; // Changing icon to Hide
    } else {
        passwordInput.type = 'password';
        toggleButton.innerHTML = '<img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg" alt="Show Password" />'; // Changing icon to Show
    }
}

function togglePasswordEdit(id) {
    const passwordPlaceholder = document.getElementById('password-placeholder-' + id);
    const passwordInputGroup = document.getElementById('password-field-group-' + id);
    const passwordIcons = document.getElementById('password-icons-' + id);

    // Hide placeholder and show input with action icons
    passwordPlaceholder.style.display = 'none';
    passwordInputGroup.style.display = 'flex'; // Убедитесь, что input group отображается как flex
	passwordInputGroup.classList.add('d-flex');
    passwordIcons.style.display = 'flex'; // Отобразить кнопки действий
	
	const editOverlayCard = document.getElementById('edit-overlay-card-' + id);
	const editOverlayExpiration = document.getElementById('edit-overlay-expiration-' + id);
	
	editOverlayCard.style.display = 'block';
	editOverlayExpiration.style.display = 'block';
}

function togglePasswordVisibility(id) {
    const passwordInput = document.getElementById('password-input-' + id);
    const passwordVisibilityIcon = document.getElementById('toggle-password-visibility-' + id);

    // Toggle password visibility
	if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordVisibilityIcon.innerHTML = '<img class="create-password-eye-icon button-icon" src="/img/buttons/eye-crossed.svg" alt="Hide Password" />'; // Changing icon to Hide
    } else {
        passwordInput.type = 'password';
        passwordVisibilityIcon.innerHTML = '<img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg" alt="Show Password" />'; // Changing icon to Show
    }
}

// Cancel password editing
function cancelPassword(id) {
    const passwordPlaceholder = document.getElementById('password-placeholder-' + id);
    const passwordInputGroup = document.getElementById('password-field-group-' + id);
    const passwordIcons = document.getElementById('password-icons-' + id);

    // Reset the interface
    passwordPlaceholder.style.display = 'block'; // Показать плейсхолдер
    passwordInputGroup.style.display = 'none'; // Скрыть группу ввода
	passwordInputGroup.classList.remove('d-flex');
    passwordIcons.style.display = 'none'; // Скрыть кнопки действий
	
	const editOverlayCard = document.getElementById('edit-overlay-card-' + id);
	const editOverlayExpiration = document.getElementById('edit-overlay-expiration-' + id);
	
	editOverlayCard.style.display = 'none';
	editOverlayExpiration.style.display = 'none';
}


// Save the new password
function savePassword(id) {
	const passwordInput = document.getElementById('password-input-' + id).value;

	if (passwordInput) {
		// Создаем объект FormData для передачи данных
		const formData = new FormData();
		formData.append('edit_id', id);
		formData.append('new_password', passwordInput);

		// Создаем AJAX запрос для отправки данных
		const xhr = new XMLHttpRequest();
		xhr.open('POST', '/dashboard', true); // URL замените на правильный путь, если нужно

		// Отправляем запрос на сервер
		xhr.onload = function() {
			if (xhr.status === 200) {
				// Успешное сохранение
				alert('Password saved successfully for ID: ' + id);
				cancelPassword(id); // Скрыть поле и сбросить интерфейс
			} else {
				// Обработка ошибки
				alert('Error saving password: ' + xhr.responseText);
			}
		};

		xhr.onerror = function() {
			alert('An error occurred while saving the password.');
		};

		// Отправляем данные
		xhr.send(formData);
	} else {
		alert('Please enter a new password.');
	}
}

// Delete the password (reset)
function deletePassword(id) {
	if (confirm('Are you sure you want to delete the password?')) {
		// Создаем объект FormData для передачи данных
		const formData = new FormData();
		formData.append('edit_id', id);
		formData.append('delete_password', true); // Флаг, показывающий, что нужно удалить пароль

		// Создаем AJAX запрос для отправки данных
		const xhr = new XMLHttpRequest();
		xhr.open('POST', '/dashboard', true); // URL замените на правильный путь, если нужно

		// Отправляем запрос на сервер
		xhr.onload = function() {
			if (xhr.status === 200) {
				// Успешное удаление
				alert('Password deleted successfully for ID: ' + id);
				document.getElementById('password-placeholder-' + id).innerText = 'Set password';
				cancelPassword(id); // Скрыть поле и сбросить интерфейс
			} else {
				// Обработка ошибки
				alert('Error deleting password: ' + xhr.responseText);
			}
		};

		xhr.onerror = function() {
			alert('An error occurred while deleting the password.');
		};

		// Отправляем данные
		xhr.send(formData);
	}
}

function toggleEditAll(id) {
	const shortUrlText = document.getElementById('short-url-text-' + id);
	const shortUrlInput = document.getElementById('short-url-input-' + id);
	const shortUrlInputLeft = document.getElementById('short-url-input-left-' + id);
	const titleText = document.getElementById('title-text-' + id);
	const titleInput = document.getElementById('title-input-' + id);
	const longUrlText = document.getElementById('long-url-text-' + id);
	const longUrlInput = document.getElementById('long-url-input-' + id);

	const cardButtonSave = document.getElementById('card-button-save-' + id);
	const cardButtonCancel = document.getElementById('card-button-cancel-' + id);
	const cardButtonEdit = document.getElementById('card-button-edit-' + id);
	const cardButtonCopy = document.getElementById('card-button-copy-' + id);
	
	const editOverlayExpiration = document.getElementById('edit-overlay-expiration-' + id);
	const editOverlayPassword = document.getElementById('edit-overlay-password-' + id);

	// Toggle visibility of text vs input fields
	shortUrlText.style.display = shortUrlText.style.display === 'none' ? 'inline' : 'none';
	shortUrlInput.style.display = shortUrlInput.style.display === 'none' ? 'inline' : 'none';
	shortUrlInputLeft.style.display = shortUrlInputLeft.style.display === 'none' ? 'inline' : 'none';

	titleText.style.display = titleText.style.display === 'none' ? 'inline' : 'none';
	titleInput.style.display = titleInput.style.display === 'none' ? 'inline' : 'none';

	longUrlText.style.display = longUrlText.style.display === 'none' ? 'inline' : 'none';
	longUrlInput.style.display = longUrlInput.style.display === 'none' ? 'inline' : 'none';

	cardButtonSave.style.display = cardButtonSave.style.display === 'none' ? 'inline' : 'none';
	cardButtonCancel.style.display = cardButtonCancel.style.display === 'none' ? 'inline' : 'none';
	cardButtonEdit.style.display = cardButtonEdit.style.display === 'none' ? 'inline' : 'none';
	cardButtonCopy.style.display = cardButtonCopy.style.display === 'none' ? 'inline' : 'none';
	
	editOverlayExpiration.style.display = editOverlayExpiration.style.display === 'none' ? 'block' : 'none';
	editOverlayPassword.style.display = editOverlayPassword.style.display === 'none' ? 'block' : 'none';

}

function cancelEditAll(id) {
	toggleEditAll(id);
}

function copyToClipboard(text) {
	const el = document.createElement('textarea');
	el.value = text;
	document.body.appendChild(el);
	el.select();
	document.execCommand('copy');
	document.body.removeChild(el);
	//alert('Copied to clipboard');
}

/// Function to save the card edits
function saveCardEdit(id) {
	// Show the loading overlay and disable interaction
	document.getElementById('loading-overlay-global').style.display = 'block';

	// Get values from input fields
	const shortUrlInput = document.getElementById('short-url-input-' + id).value;
	const titleInput = document.getElementById('title-input-' + id).value;
	const longUrlInput = document.getElementById('long-url-input-' + id).value;
	const expirationInput = document.getElementById('expiration-input-' + id).value;
	const passwordInput = document.getElementById('password-input-' + id).value;

	// Create a FormData object to hold the data
	const formData = new FormData();
	formData.append('edit_id', id);
	formData.append('new_short_url', shortUrlInput);
	formData.append('new_title', titleInput);
	formData.append('new_long_url', longUrlInput);
	formData.append('new_expiration_date', expirationInput);
	formData.append('new_password', passwordInput);

	// Create and send the AJAX request
	const xhr = new XMLHttpRequest();
	xhr.open('POST', '/dashboard', true); // Replace '/dashboard' with the actual URL if different

	xhr.onload = function() {
			// Hide the loading overlay once the request is complete
			document.getElementById('loading-overlay-global').style.display = 'none';

			if (xhr.status === 200) {
				// Success: Handle the response (reload the page or show a success message)
				//alert('Changes saved successfully!');
				window.location.reload(); // Reload the page to reflect the saved changes
			} else {
				// Error: Show the error message
				alert('Error saving changes: ' + xhr.responseText);
			}
		};

	// Handle any errors during the request
	xhr.onerror = function() {
			document.getElementById('loading-overlay-global').style.display = 'none';
			alert('An error occurred while saving.');
		};

	// Send the form data via AJAX
	xhr.send(formData);
}

function showErrorModal(message) {
  //document.getElementById('errorMessage').innerText = message;
  document.getElementById('errorMessage').innerHTML = message;
  $('#errorModal').modal('show');
}

// Add event listener for form submission
/*document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this); // Create FormData object with form data
	
	const urlInput = document.querySelector('#long_url');
    let longUrlValue = urlInput.value.trim();

    // If the URL doesn't start with http:// or https://, prepend http://
    if (!/^https?:\/\//i.test(longUrlValue)) {
        longUrlValue = 'http://' + longUrlValue;
    }

    // Update the form data with the corrected long URL
    formData.set('long_url', longUrlValue);

    // Send data via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/dashboard', true); // Update endpoint accordingly
	
	xhr.onload = function() {
		if (xhr.status === 200) {
			try {
				const response = JSON.parse(xhr.responseText);
				
				if (response.status === 'error') {
					showErrorModal(response.message);
				} else if (response.status === 'success') {
					const formatDate = (dateString) => {
						const date = new Date(dateString);
						const year = date.getFullYear();
						const month = String(date.getMonth() + 1).padStart(2, '0');
						const day = String(date.getDate()).padStart(2, '0');
						const hours = String(date.getHours()).padStart(2, '0');
						const minutes = String(date.getMinutes()).padStart(2, '0');
						const seconds = String(date.getSeconds()).padStart(2, '0');
						return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
					};

					// Populate the template with the response data
					const newLinkHtml = createLinkCardTemplate({
						id: response.id,
						site_url: siteUrl,
						short_url: response.short_url,
						title: response.title ? response.title : (formData.get('new_title') || 'Untitled'), // Check if title is available in response or form input
						long_url: formData.get('long_url'),
						created_at: formatDate(new Date().toISOString()), // Ensure created_at has full format with seconds
						expiration_date: response.expiration_date ? formatDate(response.expiration_date) : '', // Format expiration_date similarly
						password: response.password || false,  // true if a password is set, otherwise false
						short_count: 0,
						qr_count: 0
					});
					
					// Insert the new link at the top of the list
					document.querySelector('.links-cards .container .row').insertAdjacentHTML('afterbegin', newLinkHtml);
				}
			} catch (e) {
				showErrorModal('Unexpected response format. Please try again.');
			}
		} else {
			alert('An error occurred while submitting the form.');
		}
	};
	
    xhr.onerror = function() {
        alert('An error occurred during the request.');
    };
    xhr.send(formData); // Send form data via AJAX
});*/

// Add event listener for form submission
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this); // Create FormData object with form data
    
    const urlInput = document.querySelector('#long_url');
    let longUrlValue = urlInput.value.trim();

    // If the URL doesn't start with http:// or https://, prepend http://
    if (!/^https?:\/\//i.test(longUrlValue)) {
        longUrlValue = 'http://' + longUrlValue;
    }

    // Update the form data with the corrected long URL
    formData.set('long_url', longUrlValue);

    // Send data via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/dashboard', true); // Update endpoint accordingly
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.status === 'error') {
                    showErrorModal(response.message);
                } else if (response.status === 'success') {
                    const formatDate = (dateString) => {
                        const date = new Date(dateString);
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        const seconds = String(date.getSeconds()).padStart(2, '0');
                        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                    };
					
					//console.log(response.password);
					
                    // Prepare data for template
                    const cardData = {
                        id: response.id,
                        site_url: siteUrl,
                        short_url: response.short_url,
                        title: response.title || (formData.get('new_title') || 'Untitled'),
                        long_url: formData.get('long_url'),
                        created_at: formatDate(new Date().toISOString()),
                        expiration_date: response.expiration_date ? formatDate(response.expiration_date) : '',
                        //password: response.link_password === true ? '******' : 'Set password',
						//password: response.link_password ? '******' : 'Set password',
						//password: response.password ? '******' : 'Set password',
						password: response.password, // password = true or false
                        short_count: 0,
                        qr_count: 0
                    };
					
					//console.log(cardData.password)
                    
                    // Call createLinkCardTemplate with cardData
                    createLinkCardTemplate(cardData); // This now fetches and inserts the card template
                    
                }
            } catch (e) {
                showErrorModal('Unexpected response format. Please try again.');
            }
        } else {
            alert('An error occurred while submitting the form.');
        }
    };
    
    xhr.onerror = function() {
        alert('An error occurred during the request.');
    };
    xhr.send(formData); // Send form data via AJAX
});


$('#errorModal').on('click', '.btn-secondary', function() {
    $('#errorModal').modal('hide');
});

// This code is working as of 2410312234
/*function createLinkCardTemplate(data) {
    return `
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-body position-relative">
                    <div id="edit-overlay-card-${data.id}" class="edit-overlay-card" style="display:none;"></div>
                    <form method="POST" action="/dashboard" id="edit-form-${data.id}">
                        <input type="hidden" name="edit_id" value="${data.id}">
                        <div class="card-content">
                            <!-- Short URL -->
                            <p class="card-text card-short-url d-flex align-items-center">
                                <span id="short-url-text-${data.id}">
                                    <a href="${data.site_url}/${data.short_url}" target="_blank">${data.site_url}/${data.short_url}</a>
                                </span>
                                <span id="short-url-input-left-${data.id}" style="display: none;">${data.site_url}/</span>
                                <input class="short-url-input" type="text" name="new_short_url" id="short-url-input-${data.id}" value="${data.short_url}" style="display: none;">
                                
                                <!-- Copy, QR, Edit, Save, Cancel, Delete buttons -->
                                <button type="button" id="card-button-copy-${data.id}" class="btn btn-outline-primary btn-sm ms-2" onclick="copyToClipboard('${data.site_url}/${data.short_url}')">
                                    <img id="copy-button-icon-${data.id}" class="copy-button-icon button-icon" src="/img/buttons/copy.svg">
                                </button>
                                <button type="button" id="card-button-qr-${data.id}" class="btn btn-outline-primary btn-sm ms-2" onclick="showQrCodeModal('${data.site_url}/qrcodes/${data.id}.png')">
                                    <img id="qr-button-icon-${data.id}" class="qr-button-icon button-icon" src="/img/buttons/qrcode.svg">
                                </button>
                                <button type="button" id="card-button-edit-${data.id}" class="btn btn-outline-secondary btn-sm ms-2" onclick="toggleEditAll(${data.id})">
                                    <img id="edit-button-icon-${data.id}" class="edit-button-icon button-icon" src="/img/buttons/edit.svg">
                                </button>
                                <button type="button" id="card-button-save-${data.id}" class="btn btn-outline-primary btn-sm ms-2" onclick="saveCardEdit(${data.id})" style="display: none;">
                                    <img id="save-button-icon-${data.id}" class="save-button-icon button-icon" src="/img/buttons/check.svg">
                                </button>
                                <button type="button" id="card-button-cancel-${data.id}" class="btn btn-outline-secondary btn-sm ms-2" onclick="cancelEditAll(${data.id})" style="display: none;">
                                    <img id="cancel-button-icon-${data.id}" class="cancel-button-icon button-icon" src="/img/buttons/close.svg">
                                </button>
                                <button type="button" id="card-button-delete-${data.id}" class="btn btn-outline-danger btn-sm ms-2" onclick="if(confirm('Are you sure you want to delete this link?')) { window.location.href='?delete=${data.id}&short_url=${data.short_url}'; }">
                                    <img id="delete-button-icon-${data.id}" class="delete-button-icon button-icon" src="/img/buttons/delete.svg">
                                </button>
                            </p>
                            <p class="card-subtitle">Shortened URL</p>

                            <!-- Title -->
                            <p class="card-text">
                                <span id="title-text-${data.id}">${data.title || 'Untitled'}</span>
                                <input type="text" name="new_title" class="title-input" id="title-input-${data.id}" value="${data.title}" style="display: none;">
                            </p>
                            <p class="card-subtitle">Title</p>

                            <!-- Long URL -->
                            <p class="card-text">
                                <span id="long-url-text-${data.id}"><a href="${data.long_url}" target="_blank">${data.long_url}</a></span>
                                <input type="text" name="new_long_url" class="long-url-input" id="long-url-input-${data.id}" value="${data.long_url}" style="display: none;">
                            </p>
                            <p class="card-subtitle">Destination URL</p>
                        </div>
                    </form>
                </div>
                
                <!-- Card Footer -->
                <div class="card-footer">
                    <div class="grid-container">
                        <!-- Expiration date -->
                        <div class="grid-item expiration position-relative">
                            <div id="edit-overlay-expiration-${data.id}" class="edit-overlay-expiration" style="display:none;"></div>
                            <p class="card-text d-flex align-items-center">
                                ${data.expiration_date 
                                    ? `<span class="text-primary expiration-link expiration-link-${data.id}" onclick="toggleExpDateEdit(${data.id})">${data.expiration_date}</span>`
                                    : `<span class="text-muted expiration-link expiration-link-${data.id}" onclick="toggleExpDateEdit(${data.id})">Set expiration date</span>`}
                                <input type="datetime-local" name="new_expiration_date" id="expiration-input-${data.id}" value="${data.expiration_date || ''}" style="display: none;">
                                <span id="exp-date-icons-${data.id}" style="display: none;" class="ms-2">
                                    <button type="button" id="exp-date-button-save-${data.id}" class="btn btn-outline-primary btn-sm" onclick="saveExpDate(${data.id})">
                                        <img id="save-exp-date-button-icon-${data.id}" class="save-exp-date-button-icon button-icon" src="/img/buttons/check.svg">
                                    </button>
                                    <button type="button" id="exp-date-button-cancel-${data.id}" class="btn btn-outline-secondary btn-sm" onclick="cancelExpDate(${data.id})">
                                        <img id="cancel-exp-date-button-icon-${data.id}" class="cancel-exp-date-button-icon button-icon" src="/img/buttons/close.svg">
                                    </button>
                                    <button type="button" id="exp-date-button-delete-${data.id}" class="btn btn-outline-danger btn-sm" onclick="deleteExpDate(${data.id})">
                                        <img id="delete-exp-date-button-icon-${data.id}" class="delete-exp-date-button-icon button-icon" src="/img/buttons/delete.svg">
                                    </button>
                                </span>
                            </p>
                            <p class="card-subtitle">Expiration date</p>
                        </div>

                        <!-- Password -->
                        <div class="grid-item password position-relative" style="grid-column: 2 / span 2;">
                            <div id="edit-overlay-password-${data.id}" class="edit-overlay-password" style="display:none;"></div>
                            <p class="card-text d-flex align-items-center">
                                ${data.password 
                                    ? `<span id="password-placeholder-${data.id}" class="text-primary password-link" onclick="togglePasswordEdit(${data.id})">******</span>`
                                    : `<span id="password-placeholder-${data.id}" class="text-muted password-link" onclick="togglePasswordEdit(${data.id})">Set password</span>`}
                            </p>
                            <div class="align-items-center mb-2" id="password-field-group-${data.id}" style="display: none;">
                                <div class="input-group me-2">
                                    <input type="password" name="new_password" class="form-control" id="password-input-${data.id}" placeholder="Enter new password">
                                    <button type="button" class="btn btn-outline-secondary btn-toggle-password-visibility" id="toggle-password-visibility-${data.id}" onclick="togglePasswordVisibility(${data.id})">
                                        <img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg">
                                    </button>
                                </div>
                                <div id="password-icons-${data.id}" style="display: none;">
                                    <button type="button" id="password-button-save-${data.id}" class="btn btn-outline-primary btn-sm me-1" onclick="savePassword(${data.id})">
                                        <img id="save-password-button-icon-${data.id}" class="save-password-button-icon button-icon" src="/img/buttons/check.svg">
                                    </button>
                                    <button type="button" id="password-button-cancel-${data.id}" class="btn btn-outline-secondary btn-sm me-1" onclick="cancelPassword(${data.id})">
                                        <img id="cancel-password-button-icon-${data.id}" class="cancel-password-button-icon button-icon" src="/img/buttons/close.svg">
                                    </button>
                                    <button type="button" id="password-button-delete-${data.id}" class="btn btn-outline-danger btn-sm" onclick="deletePassword(${data.id})">
                                        <img id="delete-password-button-icon-${data.id}" class="delete-password-button-icon button-icon" src="/img/buttons/delete.svg">
                                    </button>
                                </div>
                            </div>
                            <p class="card-subtitle">Password</p>
                        </div>

                        <!-- Footer -->
                        <div class="grid-item short-url-created">
                            <p class="card-text text-muted card-footer-value">${data.created_at}</p>
                            <p class="card-subtitle">Created</p>
                        </div>
                        <div class="grid-item short-url-hits">
                            <p class="card-text text-muted card-footer-value">${data.short_count}</p>
                            <p class="card-subtitle">Short URL hits</p>
                        </div>
                        <div class="grid-item qr-code-hits">
                            <p class="card-text text-muted card-footer-value">${data.qr_count}</p>
                            <p class="card-subtitle">QR code hits</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}*/

/*function createLinkCardTemplate(data) {
    $.post('/templates/card-template.php', data, function(template) {
        $('#card-container').prepend(template); // Adjust the selector as needed
    }).fail(function(xhr, status, error) {
        console.error("Error loading card template:", error);
    });
}*/
function createLinkCardTemplate(data) {
    $.ajax({
        type: 'POST',
        url: '/templates/card-template.php', // Path to your PHP template
        data: data, // Send data as POST
        success: function(response) {
            // Insert the returned template HTML into the DOM
            $('.links-cards .container .row').prepend(response); // Adjust selector as needed
        },
        error: function() {
            showErrorModal('Failed to load card template'); // Handle errors
        }
    });
}


function showQrCodeModal(qrCodeUrl) {
    // Update the QR code image source and link
    document.getElementById('qrCodeImage').src = qrCodeUrl;
    document.getElementById('qrCodeLink').href = qrCodeUrl;

    // Show the modal
    $('#qrCodeModal').modal('show');
}

$('#qrCodeModal').on('click', '.btn-secondary', function() {
    $('#qrCodeModal').modal('hide');
});