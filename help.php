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
 * Prints a static help page.
 */

include_once("environment/init.php");
include_once("elements/header.php");

?>
<div class="container helppage">
    <div class="row">
        <div class="col-sm-9">

            <h3><?php echo($messages['helpHeadline']) ?></h3>
            <p><?php echo($messages['helpFileBrowse']) ?></p>
            <p><?php echo($messages['helpIntroAlert']) ?>
            <div class="alert alert-warning"><?php echo($messages['helpIntroAlert']) ?></div>
            <p><?php echo($messages['helpMetadataIntro']) ?>
            <p><?php echo($messages['helpMetadataPurpose']) ?>
            <p><?php echo($messages['helpMetadataProcess']) ?>
            <p><?php echo($messages['helpConversionReview']) ?>
            <p><?php echo($messages['helpRememberToDetele']) ?>
        </div>
    </div>

</div>
<?php
include_once("elements/footer.php");

