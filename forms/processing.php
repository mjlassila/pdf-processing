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
 * Form for the pdf processing settings.
 */
$simplified_conversion = $configs['simplified_conversion'];

if ($simplified_conversion) {
	$messages['pdfaLevel'] = array("2b");
	$messages['pdfaModus'] = array(" ,Vakio");
}

?>
<form method="post" action="index.php">
	<div class="container">
		<div class="row">
			<div class="col-sm-9">
				<p><?php echo htmlspecialchars($messages['uploadedFile'], ENT_QUOTES, 'UTF-8') . ' <strong>' . htmlspecialchars($_SESSION['originalFileName'], ENT_QUOTES, 'UTF-8') . '</strong>' ?></p>
			</div>
		</div>
<?php if (!$simplified_conversion):?>
		<div class="row top-buffer">
			<div class="col-sm-3"><?php echo($messages['pdfaValidateMessage']) ?></div>
			<div class="col-sm-3">
<?php
			createSelectBox('pdfa_level', $messages['pdfaLevel']);
?>							
			</div>
			<div class="col-sm-3">
				<input type="submit" class="btn btn-success" name="pdfa_validate"
						value="<?php echo htmlspecialchars($messages['validateButton'], ENT_QUOTES, 'UTF-8') ?>">
			</div>
		</div>
<?php endif; ?>

<?php include 'elements/metadata.php'; ?>
	<div class="row top-buffer">
		<div class="col-sm-3"><?php echo($messages['pdfaConvertMessage']) ?></div>
			<div class="col-sm-1">
			<?php
   				createSelectBox('pdfa_convlevel', $messages['pdfaLevel'], true);
			?>							
			</div>
			<div class="col-sm-2">
		<?php
    		createSelectBox('pdfa_mode', $messages['pdfaModus'], true);
		?>							
			</div>
			<div class="col-sm-3">
				<input type="submit" class="btn btn-info" name="pdfa_convert"
						value="<?php echo htmlspecialchars($messages['convertButton'], ENT_QUOTES, 'UTF-8') ?>">
			</div>
	</div>

		<div class="row top-buffer">
			<div class="col-sm-6">
				<p><strong><?php echo($messages['deleteMessage']) ?></strong></p>
			</div>
			<div class="col-sm-3">
				<input type="submit" class="btn btn-primary" name="delete_file"
						value="<?php echo htmlspecialchars($messages['deleteButton'], ENT_QUOTES, 'UTF-8') ?>">
			</div>
		</div>

	</div>
</form>
