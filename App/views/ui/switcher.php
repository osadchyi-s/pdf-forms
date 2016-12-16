<?php
/**
 * Description: Fox ui-elements
 * Version: 0.1.0
 * Author: Osadchyi Serhii
 * Author URI: https://github.com/RDSergij
 *
 * @package ui_input_fox
 *
 * @since 0.1.0
 */
?>
<?php if (!empty($__data['label'])): ?>
<label>
	<?php echo $__data['label']; ?>
</label>
<?php endif; ?>

<div <?php echo $__data['attributes'] ?>>
	<input type="radio" name="<?php echo $__data['name'] ?>"
		   id="<?php echo $__data['name'] ?>-<?php echo  $__data['value_first']['key'] ?>"
		   value="<?php echo $__data['value_first']['key'] ?>"
		<?php if ( $__data['value_first']['key'] ==  $__data['default'] ) : ?>
		checked="checked"
		<?php endif; ?>
	>
	<label for="<?php echo  $__data['name'] ?>-<?php echo  $__data['value_second']['key'] ?>" class="on">
		<?php echo  $__data['value_first']['value'] ?>
	</label>

	<input type="radio" name="<?php echo  $__data['name'] ?>" id="<?php echo  $__data['name'] ?>-<?php echo  $__data['value_second']['key'] ?>"
		   value="<?php echo  $__data['value_second']['key'] ?>"
		<?php if (  $__data['value_second']['key'] ==  $__data['default'] ) : ?>
		checked="checked"
		<?php endif; ?>
	>
	<label for="<?php echo  $__data['name'] ?>-<?php echo  $__data['value_first']['key'] ?>" class="off">
		<?php echo  $__data['value_second']['value'] ?>
	</label>
</div>
