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

<select <?php echo  $__data['attributes'] ?>>
	<?php foreach (  $__data['options'] as $value => $title ) : ?>
	<option value="<?php echo $value ?>"
		<?php if (  $__data['default'] == $value ) : ?>
		selected="selected"
		<?php endif; ?>
	>
		<?php echo $title ?>
	</option>
	<?php endforeach; ?>
</select>
