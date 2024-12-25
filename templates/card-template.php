<?php
// Capture POST data safely with defaults
$id = htmlspecialchars($_POST['id'] ?? '');
$site_url = htmlspecialchars($_POST['site_url'] ?? '');
$short_url = htmlspecialchars($_POST['short_url'] ?? '');
$title = htmlspecialchars($_POST['title'] ?? 'Untitled');
$long_url = htmlspecialchars($_POST['long_url'] ?? '');
$created_at = htmlspecialchars($_POST['created_at'] ?? '');
$expiration_date = htmlspecialchars($_POST['expiration_date'] ?? '');
$password = (!empty($_POST['password']) && $_POST['password'] !== 'false') ? '******' : 'Set password';
$short_count = htmlspecialchars($_POST['short_count'] ?? 0);
$qr_count = htmlspecialchars($_POST['qr_count'] ?? 0);

//var_dump($_POST['password']);
//var_dump($password);

?>

<div class="col-md-12">
    <div class="card mb-3">
        <div class="card-body position-relative">
            <div id="edit-overlay-card-<?= $id ?>" class="edit-overlay-card" style="display:none;"></div>
            <form method="POST" action="/dashboard" id="edit-form-<?= $id ?>">
                <input type="hidden" name="edit_id" value="<?= $id ?>">

                <!-- Card Content -->
                <div class="card-content">
                    <!-- Short URL -->
                    <p class="card-text card-short-url d-flex align-items-center">
                        <span id="short-url-text-<?= $id ?>">
                            <a href="<?= $site_url ?>/<?= $short_url ?>" target="_blank"><?= $site_url ?>/<?= $short_url ?></a>
                        </span>
                        <span id="short-url-input-left-<?= $id ?>" style="display: none;"><?= $site_url ?>/</span>
                        <input class="short-url-input" type="text" name="new_short_url" id="short-url-input-<?= $id ?>" value="<?= $short_url ?>" style="display: none;">
                        
                        <!-- Buttons: Copy, QR, Edit, Save, Cancel, Delete -->
                        <button type="button" id="card-button-copy-<?= $id ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="copyToClipboard('<?= $site_url ?>/<?= $short_url ?>')">
                            <img class="copy-button-icon button-icon" src="/img/buttons/copy.svg">
                        </button>
                        <button type="button" id="card-button-qr-<?= $id ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="showQrCodeModal('<?= $site_url ?>/qrcodes/<?= $id ?>.png')">
                            <img class="qr-button-icon button-icon" src="/img/buttons/qrcode.svg">
                        </button>
                        <button type="button" id="card-button-edit-<?= $id ?>" class="btn btn-outline-secondary btn-sm ms-2" onclick="toggleEditAll(<?= $id ?>)">
                            <img class="edit-button-icon button-icon" src="/img/buttons/edit.svg">
                        </button>
                        <button type="button" id="card-button-save-<?= $id ?>" class="btn btn-outline-primary btn-sm ms-2" onclick="saveCardEdit(<?= $id ?>)" style="display: none;">
                            <img class="save-button-icon button-icon" src="/img/buttons/check.svg">
                        </button>
                        <button type="button" id="card-button-cancel-<?= $id ?>" class="btn btn-outline-secondary btn-sm ms-2" onclick="cancelEditAll(<?= $id ?>)" style="display: none;">
                            <img class="cancel-button-icon button-icon" src="/img/buttons/close.svg">
                        </button>
                        <button type="button" id="card-button-delete-<?= $id ?>" class="btn btn-outline-danger btn-sm ms-2" onclick="if(confirm('Are you sure you want to delete this link?')) { window.location.href='?delete=<?= $id ?>&short_url=<?= $short_url ?>'; }">
                            <img class="delete-button-icon button-icon" src="/img/buttons/delete.svg">
                        </button>
                    </p>
                    <p class="card-subtitle">Shortened URL</p>

                    <!-- Title -->
                    <p class="card-text">
                        <span id="title-text-<?= $id ?>"><?= $title ?></span>
                        <input type="text" name="new_title" class="title-input" id="title-input-<?= $id ?>" value="<?= $title ?>" style="display: none;">
                    </p>
                    <p class="card-subtitle">Title</p>

                    <!-- Long URL -->
                    <p class="card-text">
                        <span id="long-url-text-<?= $id ?>"><a href="<?= $long_url ?>" target="_blank"><?= $long_url ?></a></span>
                        <input type="text" name="new_long_url" class="long-url-input" id="long-url-input-<?= $id ?>" value="<?= $long_url ?>" style="display: none;">
                    </p>
                    <p class="card-subtitle">Destination URL</p>
                </div>
            </form>
        </div>

        <!-- Card Footer -->
        <div class="card-footer">
            <div class="grid-container">

                <!-- Expiration Date -->
                <div class="grid-item expiration position-relative">
                    <div id="edit-overlay-expiration-<?= $id ?>" class="edit-overlay-expiration" style="display:none;"></div>
                    <p class="card-text d-flex align-items-center">
                        <?= $expiration_date 
                            ? "<span class='text-primary expiration-link expiration-link-{$id}' onclick='toggleExpDateEdit({$id})'>{$expiration_date}</span>
                               <input type='datetime-local' name='new_expiration_date' id='expiration-input-{$id}' value='{$expiration_date}' style='display: none;'>"
                            : "<span class='text-muted expiration-link expiration-link-{$id}' onclick='toggleExpDateEdit({$id})'>Set expiration date</span>
                               <input type='datetime-local' name='new_expiration_date' id='expiration-input-{$id}' style='display: none;'>"
                        ?>
                        <span id="exp-date-icons-<?= $id ?>" style="display: none;" class="ms-2">
                            <button type="button" id="exp-date-button-save-<?= $id ?>" class="btn btn-outline-primary btn-sm" onclick="saveExpDate(<?= $id ?>)">
                                <img id="save-exp-date-button-icon-<?= $id ?>" class="save-exp-date-button-icon button-icon" src="/img/buttons/check.svg">
                            </button>
                            <button type="button" id="exp-date-button-cancel-<?= $id ?>" class="btn btn-outline-secondary btn-sm" onclick="cancelExpDate(<?= $id ?>)">
                                <img id="cancel-exp-date-button-icon-<?= $id ?>" class="cancel-exp-date-button-icon button-icon" src="/img/buttons/close.svg">
                            </button>
                            <button type="button" id="exp-date-button-delete-<?= $id ?>" class="btn btn-outline-danger btn-sm" onclick="deleteExpDate(<?= $id ?>)">
                                <img id="delete-exp-date-button-icon-<?= $id ?>" class="delete-exp-date-button-icon button-icon" src="/img/buttons/delete.svg">
                            </button>
                        </span>
                    </p>
                    <p class="card-subtitle">Expiration date</p>
                </div>
				
				<?php
				/*
                <!-- Password -->
                <div class="grid-item password position-relative" style="grid-column: 2 / span 2;">
                    <div id="edit-overlay-password-<?= $id ?>" class="edit-overlay-password" style="display:none;"></div>
					<p class="card-text d-flex align-items-center">
						<?php if ($password === '******'): ?>
							<span id="password-placeholder-<?= $id ?>" class="text-primary password-link" onclick="togglePasswordEdit(<?= $id ?>)">******</span>
						<?php else: ?>
							<span id="password-placeholder-<?= $id ?>" class="text-muted password-link" onclick="togglePasswordEdit(<?= $id ?>)">Set password</span>
						<?php endif; ?>
					</p>
                    <div class="align-items-center mb-2" id="password-field-group-<?= $id ?>" style="display: none;">
                        <div class="input-group me-2">
                            <input type="password" name="new_password" class="form-control" id="password-input-<?= $id ?>" placeholder="Enter new password">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-password-visibility" id="toggle-password-visibility-<?= $id ?>" onclick="togglePasswordVisibility(<?= $id ?>)">
                                <img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg">
                            </button>
                        </div>
                        <div id="password-icons-<?= $id ?>" style="display: none;">
                            <button type="button" id="password-button-save-<?= $id ?>" class="btn btn-outline-primary btn-sm me-1" onclick="savePassword(<?= $id ?>)">
                                <img id="save-password-button-icon-<?= $id ?>" class="save-password-button-icon button-icon" src="/img/buttons/check.svg">
                            </button>
                            <button type="button" id="password-button-cancel-<?= $id ?>" class="btn btn-outline-secondary btn-sm me-1" onclick="cancelPassword(<?= $id ?>)">
                                <img id="cancel-password-button-icon-<?= $id ?>" class="cancel-password-button-icon button-icon" src="/img/buttons/close.svg">
                            </button>
                            <button type="button" id="password-button-delete-<?= $id ?>" class="btn btn-outline-danger btn-sm" onclick="deletePassword(<?= $id ?>)">
                                <img id="delete-password-button-icon-<?= $id ?>" class="delete-password-button-icon button-icon" src="/img/buttons/delete.svg">
                            </button>
                        </div>
                    </div>
                    <p class="card-subtitle">Password</p>
                </div>
				*/
				?>
				
				<div class="grid-item password position-relative" style="grid-column: 2 / span 2;">
					<div id="edit-overlay-password-<?= $id ?>" class="edit-overlay-password" style="display:none;"></div>
					<p class="card-text d-flex align-items-center">
						<?php if ($password === '******'): ?>
							<span id="password-placeholder-<?= $id ?>" class="text-primary password-link" onclick="togglePasswordEdit(<?= $id ?>)">******</span>
						<?php else: ?>
							<span id="password-placeholder-<?= $id ?>" class="text-muted password-link" onclick="togglePasswordEdit(<?= $id ?>)">Set password</span>
						<?php endif; ?>
					</p>
					<div class="align-items-center mb-2" id="password-field-group-<?= $id ?>" style="display: none;">
						<div class="input-group me-2">
							<input type="password" name="new_password" class="form-control" id="password-input-<?= $id ?>" placeholder="Enter new password">
							<button type="button" class="btn btn-outline-secondary btn-toggle-password-visibility" id="toggle-password-visibility-<?= $id ?>" onclick="togglePasswordVisibility(<?= $id ?>)">
								<img class="create-password-eye-icon button-icon" src="/img/buttons/eye.svg">
							</button>
						</div>
						<div id="password-icons-<?= $id ?>" style="display: none;">
							<button type="button" id="password-button-save-<?= $id ?>" class="btn btn-outline-primary btn-sm me-1" onclick="savePassword(<?= $id ?>)">
								<img id="save-password-button-icon-<?= $id ?>" class="save-password-button-icon button-icon" src="/img/buttons/check.svg">
							</button>
							<button type="button" id="password-button-cancel-<?= $id ?>" class="btn btn-outline-secondary btn-sm me-1" onclick="cancelPassword(<?= $id ?>)">
								<img id="cancel-password-button-icon-<?= $id ?>" class="cancel-password-button-icon button-icon" src="/img/buttons/close.svg">
							</button>
							<button type="button" id="password-button-delete-<?= $id ?>" class="btn btn-outline-danger btn-sm" onclick="deletePassword(<?= $id ?>)">
								<img id="delete-password-button-icon-<?= $id ?>" class="delete-password-button-icon button-icon" src="/img/buttons/delete.svg">
							</button>
						</div>
					</div>
					<p class="card-subtitle">Password</p>
				</div>

                <!-- Created, Hits, and QR Code Hits -->
                <div class="grid-item short-url-created">
                    <p class="card-text text-muted card-footer-value"><?= $created_at ?></p>
                    <p class="card-subtitle">Created</p>
                </div>
                <div class="grid-item short-url-hits">
                    <p class="card-text text-muted card-footer-value"><?= $short_count ?></p>
                    <p class="card-subtitle">Short URL hits</p>
                </div>
                <div class="grid-item qr-code-hits">
                    <p class="card-text text-muted card-footer-value"><?= $qr_count ?></p>
                    <p class="card-subtitle">QR code hits</p>
                </div>
            </div>
        </div>
    </div>
</div>
