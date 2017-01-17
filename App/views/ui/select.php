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
<?php if ( ! empty( $__data['label'] ) ) : ?>
	<label><?php echo $__data['label'] ?></label>
<?php endif; ?>
<select <?php echo  $__data['attributes'] ?>>
	<?php foreach (  $__data['list'] as $value => $title ) : ?>
	<option value="<?php echo $value ?>"
		<?php if (  $__data['default'] == $value ) : ?>
		selected="selected"
		<?php endif; ?>
	>
		<?php echo $title ?>
	</option>
	<?php endforeach; ?>
</select>
