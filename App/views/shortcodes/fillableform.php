<form class="pdfform-form" data-form-id="<?php echo $__data['id'] ?>" action="<?php echo admin_url('admin-ajax.php') ?>">

    <input type="hidden" name="pdfform-form-id" value="<?php echo $__data['id'] ?>"/>
    <input type="hidden" name="action" value="pdfformsave"/>

    <div class="pdfform-messages"></div>
    <?php if($__data['submitLocation'] === 'top'): ?>
        <input type="submit" value="<?php echo $__data['submitMessage'] ?>">
    <?php endif; ?>

    <?php echo $__data['content'] ?>

    <?php if($__data['submitLocation'] === 'bottom'): ?>
        <input type="submit" value="<?php echo $__data['submitMessage'] ?>"/>
    <?php endif; ?>
</form>
