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
        if (empty($_FILES['fileToUpload']['name'])) {
            $infoMessage = $messages['fileNotChosen'];
        } elseif ($_FILES['fileToUpload']['size'] > 100 * 1024 * 1024) { // 100MB in bytes
            $errorMessage = $messages['fileTooLarge'];
        } elseif (pathinfo($_FILES['fileToUpload']['name'], PATHINFO_EXTENSION) != 'pdf' || mime_content_type($_FILES['fileToUpload']['tmp_name']) != 'application/pdf') {
            $errorMessage = $messages['uploadNoPdf'];
        } else {
            $newFileName =  $processor->renameFile(basename($_FILES['fileToUpload']['name']), '.pdf');
            
            if (!$processor->saveFile($_FILES['fileToUpload'], $newFileName)) {
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
    if (!empty($_POST['pdfa_validate'])) {
        $args = $processor->createPdfaValidateArgs($_POST['pdfa_level'], $lang);

    } elseif (!empty($_POST['pdfa_convert'])) {
        $processor->createAndSaveProcessedFileName('.pdf');
        $metadataArray = $processor->createMetadataArray();
        if (!empty($metadataArray)) {
            $fileContent = $xmpCreator->createXmp($metadataArray);
            if (!empty($fileContent)) {
                $processor->saveXmpFile($fileContent);
            }
        }
        $args = $processor->createPdfaArgs( 
            $_POST['pdfa_convlevel'], $_POST['pdfa_mode'], $lang);
        
    }
    if (!empty($args)) {
        $processingReturnValue = $processor->executePdfProcessing($args);
        $processingReturnValue = $processor->filterReturnValue($processingReturnValue);
    }
      
    if (!empty($_POST['delete_file'])) {
        $sessionControl->clearSession();
        $infoMessage = $messages['fileDeletedMessage'];
    }

