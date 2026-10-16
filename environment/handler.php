<?php
/**
 * (c) 2017 Technische Universität Berlin
 *
 * This software is licensed under GNU General Public License version 3 or later.
 *
 * For the full copyright and license information,
 * please see https://www.gnu.org/licenses/gpl-3.0.html or read
 * the LICENSE.txt file that was distributed with this source code.
 */

/**
 * Handles file uploads, missing uploaded files, and file deletion.
 */

// PHP empties $_POST and $_FILES when post_max_size is exceeded.
$postMaxSetting = trim(ini_get('post_max_size'));
$postMaxBytes = (float) $postMaxSetting;

switch (strtolower(substr($postMaxSetting, -1))) {
    case 'g':
        $postMaxBytes *= 1024 * 1024 * 1024;
        break;
    case 'm':
        $postMaxBytes *= 1024 * 1024;
        break;
    case 'k':
        $postMaxBytes *= 1024;
        break;
}

$contentLength = (float) ($_SERVER['CONTENT_LENGTH'] ?? 0);

if (
    ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' &&
    $postMaxBytes > 0 &&
    $contentLength > $postMaxBytes
) {
    http_response_code(413);
    $errorMessage = $messages['fileTooLarge'];
    return;
}

// Validate and save the uploaded file.
if (!empty($_FILES)) {
    $file = $_FILES['fileToUpload'] ?? null;

    if (
        !is_array($file) ||
        !isset($file['error']) ||
        !is_int($file['error'])
    ) {
        http_response_code(400);
        $errorMessage = $messages['uploadNoPdf'];
        return;
    }

    if (
        $file['error'] === UPLOAD_ERR_INI_SIZE ||
        $file['error'] === UPLOAD_ERR_FORM_SIZE
    ) {
        http_response_code(413);
        $errorMessage = $messages['fileTooLarge'];
        return;
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $infoMessage = $messages['fileNotChosen'];
        return;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(
            $file['error'] === UPLOAD_ERR_PARTIAL ? 400 : 500
        );
        $errorMessage = $messages['fileNotSaved'];
        return;
    }

    if (
        !isset($file['name'], $file['tmp_name'], $file['size']) ||
        !is_string($file['name']) ||
        !is_string($file['tmp_name']) ||
        !is_int($file['size']) ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        http_response_code(400);
        $errorMessage = $messages['fileNotSaved'];
        return;
    }

    $maxFileBytes = $configs['max_filesize_in_mb'] * 1024 * 1024;
    $actualFileBytes = filesize($file['tmp_name']);

    if ($actualFileBytes === false) {
        http_response_code(500);
        $errorMessage = $messages['fileNotSaved'];
        return;
    }

    if (
        $file['size'] > $maxFileBytes ||
        $actualFileBytes > $maxFileBytes
    ) {
        http_response_code(413);
        $errorMessage = $messages['fileTooLarge'];
        return;
    }

    if (
        strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf' ||
        mime_content_type($file['tmp_name']) !== 'application/pdf'
    ) {
        http_response_code(400);
        $errorMessage = $messages['uploadNoPdf'];
        return;
    }

    $newFileName = $processor->renameFile(
        basename($file['name']),
        '.pdf'
    );

    if (!$processor->saveFile($file, $newFileName)) {
        http_response_code(500);
        $errorMessage = $messages['fileNotSaved'];
        return;
    }
}

// Check that the uploaded file still exists.
if (
    !empty($_SESSION['uploadFile']) &&
    !file_exists($_SESSION['uploadFile'])
) {
    $sessionControl->clearSession();
    $errorMessage = $messages['fileNotFound'];
    return;
}

// Delete the current upload and associated files.
if (!empty($_POST['delete_file'])) {
    $sessionControl->clearSession();
    $infoMessage = $messages['fileDeletedMessage'];
}