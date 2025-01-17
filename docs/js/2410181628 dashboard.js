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
  document.getElementById('errorMessage').innerText = message;
  $('#errorModal').modal('show');
}

// Add event listener for form submission
/*document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);
    
    // Send data via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/dashboard', true); // Update endpoint accordingly
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            
            if (response.status === 'error') {
                // Show the modal with the error message
                document.querySelector('#errorModal .modal-body').textContent = response.message;
                $('#errorModal').modal('show');
            } else if (response.status === 'success') {
                // Handle success (e.g., redirect to dashboard)
                window.location.href = '/dashboard';
            }
        } else {
            alert('An error occurred while submitting the form.');
        }
    };
    xhr.send(formData);
});*/

// Add event listener for form submission
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this); // Create FormData object with form data

    // Send data via AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/dashboard', true); // Update endpoint accordingly
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText); // Parse JSON response
                
                if (response.status === 'error') {
                    // If there's an error, show the modal with the error message
                    showErrorModal(response.message);
                } else if (response.status === 'success') {
                    // On success, redirect to the dashboard or other page
                    window.location.href = '/dashboard';
                }
            } catch (e) {
                // Handle any parsing errors
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

