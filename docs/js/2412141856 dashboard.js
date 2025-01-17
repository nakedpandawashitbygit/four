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

// Function to save the card edits
function saveCardEdit(id) {
    const form = document.querySelector(`#edit-form-${id}`);
    const newShortUrl = form.querySelector(`#short-url-input-${id}`).value.trim();
    const newTitle = form.querySelector(`#title-input-${id}`).value.trim();
    const newLongUrl = form.querySelector(`#long-url-input-${id}`).value.trim();

    let errorMessage = '';

    // Validate long URL format
    /*const validateLongUrl = (url) => {
        const urlPattern = /^(https?:\/\/)?(([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})(\/.*)?$/;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'http://' + url;
        }
        return urlPattern.test(url) ? url : false;
    };*/
	
	const validateLongUrl = (url) => {
		// Ensure the URL starts with a scheme if missing
		if (!/^https?:\/\//i.test(url)) {
			url = 'http://' + url;
		}

		// Pattern to allow Unicode (internationalized) domain names and paths
		const urlPattern = /^(https?:\/\/)(([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}|[^\x00-\x7F]+)(\/.*)?$/u;

		// Test and return the URL if valid, otherwise return false
		return urlPattern.test(url) ? url : false;
	};

    const validatedLongUrl = validateLongUrl(newLongUrl);
    if (!validatedLongUrl) {
        errorMessage = 'Error: The new long URL is not valid.';
    }

    // Validate short URL length and characters
    if (newShortUrl.length < 4 || !/^[a-zA-Z0-9]+$/.test(newShortUrl)) {
        errorMessage = 'Error: Short URL must be at least 4 characters long and contain only letters and numbers.';
    }

    // Set default title if empty
    const validatedTitle = newTitle.length > 128 ? newTitle.substring(0, 128) : (newTitle || 'Untitled');

    if (errorMessage) {
        // Show the error in a modal or alert
        alert(errorMessage);
        return;
    }

    // Proceed with AJAX request if validation passes
    const formData = new FormData();
    formData.append('edit_id', id);
    formData.append('new_short_url', newShortUrl);
    formData.append('new_title', validatedTitle);
    formData.append('new_long_url', validatedLongUrl);

    fetch('/dashboard', {
		method: 'POST',
		body: formData,
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		}
	})
	.then(response => response.text()) // Get the raw text response
	.then(text => {
		try {
			const data = JSON.parse(text); // Try parsing JSON if it’s valid
			if (data.status === 'success') {
				alert('URL updated successfully.');
				location.reload(); // Reload or update the DOM as needed
			} else {
				alert(data.message || 'Error: Could not update the URL.');
			}
		} catch (error) {
			console.error('Parsing error:', error); // Log parsing errors
			console.log('Server response:', text); // Log the full response
			alert('Error: Invalid response from the server.');
		}
	})
	.catch(error => console.error('Fetch error:', error));
}

function showErrorModal(message) {
  //document.getElementById('errorMessage').innerText = message;
  document.getElementById('errorMessage').innerHTML = message;
  $('#errorModal').modal('show');
}

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

				// Check for error or success in the response status
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

					// Prepare cardData with appropriate formatting
					const cardData = {
						id: response.id,
						site_url: siteUrl,
						short_url: response.short_url,
						title: response.title || (formData.get('new_title') || 'Untitled'),
						//long_url: formData.get('long_url'),
						long_url: response.long_url,
						created_at: formatDate(new Date().toISOString()),
						expiration_date: response.expiration_date ? formatDate(response.expiration_date) : '',
						password: response.password ? '******' : 'Set password', // Adjust for password display
						short_count: 0,
						qr_count: 0
					};

					// Render the updated card using createLinkCardTemplate
					createLinkCardTemplate(cardData);
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