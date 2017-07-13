<?php if (count($this->errors) > 0): ?>
    <?php foreach ($this->errors as $error): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
