<?php if ( ! empty( $__data['label'] ) ) : ?>
<label><?php echo $__data['label'] ?></label>
<?php endif; ?>
<input <?php echo $__data['attributes'] ?>
	<?php if ( ! empty( $__data['datalist'] ) ) : ?>
	list="<?php echo $__data['datalist_id'] ?>"
	<?php endif; ?>
>

<?php if ( ! empty( $__data['datalist'] ) ) : ?>
<datalist id="<?php echo $__data['datalist_id'] ?>">
	<?php foreach ( data['datalist'] as $dataitem ) : ?>
	<option><?php echo $dataitem ?></option>
	<?php endforeach; ?>
</datalist>
<?php endif; ?>