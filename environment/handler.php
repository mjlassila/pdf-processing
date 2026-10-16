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
?>
<?php
/**
 * This file handles the request input and possibly executes processing.
 *
 */

// If there is a file upload, save it and set the session variables
if (!empty($_FILES)) {
    $file = $_FILES['fileToUpload'] ?? null;

    if (!$file || !isset($file['error']) || is_array($file['error'])) {
        http_response_code(400);
        $errorMessage = $messages['uploadNoPdf'];
    } elseif (
        $file['error'] === UPLOAD_ERR_INI_SIZE ||
        $file['error'] === UPLOAD_ERR_FORM_SIZE
    ) {
        http_response_code(413);
        $errorMessage = $messages['fileTooLarge'];
    } elseif ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $infoMessage = $messages['fileNotChosen'];
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(
            $file['error'] === UPLOAD_ERR_PARTIAL ? 400 : 500
        );
        $errorMessage = $messages['fileNotSaved'];
    } elseif ($file['size'] > $configs['max_filesize_in_mb'] * 1024 * 1024) {
        http_response_code(413);
        $errorMessage = $messages['fileTooLarge'];
    } elseif (
        pathinfo($file['name'], PATHINFO_EXTENSION) !== 'pdf' ||
        mime_content_type($file['tmp_name']) !== 'application/pdf'
    ) {
        $errorMessage = $messages['uploadNoPdf'];
    } else {
        $newFileName = $processor->renameFile(
            basename($file['name']),
            '.pdf'
        );

        if (!$processor->saveFile($file, $newFileName)) {
            $errorMessage = $messages['fileNotSaved'];
        }
    }
}

// Check that the uploaded file exists
if (!empty($_SESSION['uploadFile']) && !file_exists($_SESSION['uploadFile'])) {
    $sessionControl->clearSession();
    $errorMessage = $messages['fileNotFound'];
}

// If a process button was pushed, perform processing
if (!empty($_POST)) {
    $processor->createAndSaveProcessedFileName('.pdf');
    $metadataArray = $processor->createMetadataArray();

    if (!empty($metadataArray)) {
        $fileContent = $xmpCreator->createXmp($metadataArray);

        if (!empty($fileContent)) {
            $processor->saveXmpFile($fileContent);
        }
    }

    $args = $processor->createPdfaArgs($lang);
}

if (!empty($args)) {
    $processingReturnValue = $processor->executePdfProcessing($args);
    $processingReturnValue = $processor->filterReturnValue($processingReturnValue);
}

if (!empty($_POST['delete_file'])) {
    $sessionControl->clearSession();
    $infoMessage = $messages['fileDeletedMessage'];
}
