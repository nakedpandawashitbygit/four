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


// Script for url card editing -->
function toggleEdit(id) {
	const titleText = document.getElementById('title-text-' + id);
	const titleInput = document.getElementById('title-input-' + id);
	const shortUrlText = document.getElementById('short-url-text-' + id);
	const shortUrlInput = document.getElementById('short-url-input-' + id);
	const longUrlText = document.getElementById('long-url-text-' + id);
	const longUrlInput = document.getElementById('long-url-input-' + id);
	//const commentText = document.getElementById('comment-text-' + id);
	//const commentInput = document.getElementById('comment-input-' + id);
	const expirationText = document.getElementById('expiration-text-' + id);
	const expirationInput = document.getElementById('expiration-input-' + id);
	const passwordText = document.getElementById('password-text-' + id);
	const passwordInput = document.getElementById('password-input-' + id);
	const editButton = document.getElementById('edit-btn-' + id);
	const saveButton = document.getElementById('save-btn-' + id);
	const cancelButton = document.getElementById('cancel-btn-' + id);

	// Toogling edit fields visibility
	titleText.style.display = titleText.style.display === 'none' ? 'block' : 'none';
	titleInput.style.display = titleInput.style.display === 'none' ? 'block' : 'none';
	shortUrlText.style.display = shortUrlText.style.display === 'none' ? 'block' : 'none';
	shortUrlInput.style.display = shortUrlInput.style.display === 'none' ? 'block' : 'none';
	longUrlText.style.display = longUrlText.style.display === 'none' ? 'block' : 'none';
	longUrlInput.style.display = longUrlInput.style.display === 'none' ? 'block' : 'none';
	//commentText.style.display = commentText.style.display === 'none' ? 'block' : 'none';
	//commentInput.style.display = commentInput.style.display === 'none' ? 'block' : 'none';
	
	if (expirationText && expirationInput) {
	  expirationText.style.display = expirationText.style.display === 'none' ? 'block' : 'none';
	  expirationInput.style.display = expirationInput.style.display === 'none' ? 'block' : 'none';
	}

	passwordText.style.display = passwordText.style.display === 'none' ? 'block' : 'none';
	passwordInput.style.display = passwordInput.style.display === 'none' ? 'block' : 'none';

	editButton.style.display = editButton.style.display === 'none' ? 'block' : 'none';
	saveButton.style.display = saveButton.style.display === 'none' ? 'block' : 'none';
	cancelButton.style.display = cancelButton.style.display === 'none' ? 'block' : 'none';
  }

function cancelEdit(id) {
	toggleEdit(id); // Hiding edit fields
}



// Function to toggle expiration date editing
function toggleExpDateEdit(id) {
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);

	expDateLink.style.display = 'none'; // Hide link
	expDateInput.style.display = 'inline'; // Show input field
	expDateIcons.style.display = 'inline'; // Show action buttons
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

// Toggle the password input field and action icons
function togglePasswordEdit(id) {
	const passwordPlaceholder = document.getElementById('password-placeholder-' + id);
	const passwordInput = document.getElementById('password-input-' + id);
	const passwordIcons = document.getElementById('password-icons-' + id);

	// Toggle the display of the password input and icons
	passwordPlaceholder.style.display = 'none';
	passwordInput.style.display = 'block';
	passwordIcons.style.display = 'flex';
}

function toggleLinkPasswordVisibility() {
    const passwordInput = document.getElementById('link_password');
    const toggleButton = document.getElementById('togglePassword');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.innerText = '🙈'; // Меняем иконку на скрытие
    } else {
        passwordInput.type = 'password';
        toggleButton.innerText = '👁️'; // Меняем иконку на отображение
    }
}

// Toggle password visibility (show/hide)
function togglePasswordVisibility(id) {
	const passwordInput = document.getElementById('password-input-' + id);
	const passwordVisibilityIcon = document.getElementById('toggle-password-visibility-' + id);

	if (passwordInput.type === 'password') {
		passwordInput.type = 'text';
		passwordVisibilityIcon.innerText = '🙈'; // Change to hide icon
	} else {
		passwordInput.type = 'password';
		passwordVisibilityIcon.innerText = '👁️'; // Change to show icon
	}
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

// Cancel password editing
function cancelPassword(id) {
	const passwordPlaceholder = document.getElementById('password-placeholder-' + id);
	const passwordInput = document.getElementById('password-input-' + id);
	const passwordIcons = document.getElementById('password-icons-' + id);

	// Reset the interface
	passwordPlaceholder.style.display = 'block';
	passwordInput.style.display = 'none';
	passwordIcons.style.display = 'none';
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

	const expDateInput = document.getElementById('expiration-input-' + id);
	const expDateLink = document.querySelector('.expiration-link-' + id);
	const expDateIcons = document.getElementById('exp-date-icons-' + id);
	const expDateButtonSave = document.getElementById('exp-date-button-save-' + id);
	const expDateButtonCancel = document.getElementById('exp-date-button-cancel-' + id);
	const expDateButtonDelete = document.getElementById('exp-date-button-delete-' + id);

	const passwordInput = document.getElementById('password-input-' + id);
	const passwordLink = document.getElementById('password-placeholder-' + id);
	const passwordIcons = document.getElementById('password-icons-' + id);
	const toggleVisibilityButton = document.getElementById('toggle-password-visibility-' + id);
	const passwordButtonSave = document.getElementById('password-button-save-' + id);
	const passwordButtonCancel = document.getElementById('password-button-cancel-' + id);
	const passwordButtonDelete = document.getElementById('password-button-delete-' + id);

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

	expDateInput.style.display = expDateInput.style.display === 'none' ? 'inline' : 'none';
	expDateLink.style.display = expDateLink.style.display === 'none' ? 'inline' : 'none';
	expDateIcons.style.display = expDateIcons.style.display === 'none' ? 'inline' : 'none';
	expDateButtonSave.style.display = expDateButtonSave.style.display === 'none' ? 'inline' : 'none';
	expDateButtonCancel.style.display = expDateButtonCancel.style.display === 'none' ? 'inline' : 'none';

	passwordInput.style.display = passwordInput.style.display === 'none' ? 'inline' : 'none';
	passwordLink.style.display = passwordLink.style.display === 'none' ? 'inline' : 'none';
	passwordIcons.style.display = passwordIcons.style.display === 'none' ? 'inline' : 'none';
	passwordButtonSave.style.display = passwordButtonSave.style.display === 'none' ? 'inline' : 'none';
	passwordButtonCancel.style.display = passwordButtonCancel.style.display === 'none' ? 'inline' : 'none';

	// Toggle buttons
	const saveButton = document.querySelector('#save-button-${id}');
	const cancelButton = document.querySelector('#cancel-button-${id}');
	const editButton = document.querySelector('#edit-button-${id}');
	const copyButton = document.querySelector('#copy-button-${id}');

	saveButton.style.display = saveButton.style.display === 'none' ? 'inline' : 'none';
	cancelButton.style.display = cancelButton.style.display === 'none' ? 'inline' : 'none';
	editButton.style.display = editButton.style.display === 'none' ? 'inline' : 'none';
	copyButton.style.display = copyButton.style.display === 'none' ? 'inline' : 'none';
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
	document.getElementById('loading-overlay').style.display = 'block';

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
		document.getElementById('loading-overlay').style.display = 'none';

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
		document.getElementById('loading-overlay').style.display = 'none';
		alert('An error occurred while saving.');
	};

	// Send the form data via AJAX
	xhr.send(formData);
}