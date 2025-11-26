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
 * Handles asynchronous PDF/A conversion requests.
 */

include_once("environment/init.php");

header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'error',
    'message' => $messages['fileNotChosen'] ?? 'No file chosen.',
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response);
    exit;
}

if (empty($_SESSION['uploadFile']) || !file_exists($_SESSION['uploadFile'])) {
    $response['message'] = $messages['fileNotFound'] ?? 'Uploaded file not found.';
    echo json_encode($response);
    exit;
}

$_SESSION['conversionFinished'] = false;

$level = filter_input(INPUT_POST, 'pdfa_convlevel', FILTER_SANITIZE_STRING) ?? '';
$mode = filter_input(INPUT_POST, 'pdfa_mode', FILTER_SANITIZE_STRING) ?? '';

$processor->createAndSaveProcessedFileName('.pdf');
$processedFile = $_SESSION['processedFile'] ?? '';
$processedDisplayName = $_SESSION['processedDisplayName'] ?? '';
$metadataArray = $processor->createMetadataArray();

if (!empty($metadataArray)) {
    $fileContent = $xmpCreator->createXmp($metadataArray);
    if (!empty($fileContent)) {
        $processor->saveXmpFile($fileContent);
    }
}

$args = $processor->createPdfaArgs($level, $mode, $lang);
# Release the session lock before running the conversion so status requests can
# check for the result while the processor is running.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
$processingReturnValue = $processor->executePdfProcessing($args);
$processingReturnValue = $processor->filterReturnValue($processingReturnValue);

$conversionOk = $processor->returnOk($processingReturnValue)
    && !empty($processedFile)
    && file_exists($processedFile);

$response['returnValue'] = $processingReturnValue;

if ($conversionOk) {
    $response['status'] = 'success';
    $response['message'] = $messages['conversionSuccess'] ?? ($messages['downloadLabel'] ?? 'Conversion finished.');
    $response['downloadUrl'] = 'stream.php';
    $response['displayName'] = $processedDisplayName ?: basename($processedFile);
    $_SESSION['conversionFinished'] = true;
} else {
    $response['message'] = $messages['conversionFailed'] ?? ($messages['failMessage'] ?? 'Conversion failed.');
}

echo json_encode($response);
exit;
