<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$this->extend('Layout/auth.html.php');

$form = $this->form;

$this->form()->setTheme($form, [':Form/Theme/Bootstrap']);

?>


<?php $this->headTitle('Login') ?>

<div class="row">
    <div class="col-md-4 col-md-push-4">

        <h2 class="form-signin-heading">Login</h2>

        <?php if ($this->error): ?>
            <div class="alert alert-danger"><?php echo $this->error->getMessage() ?></div>
        <?php endif ?>

        <?= $this->form()->start($form); ?>
        <?= $this->form()->row($form['_username']) ?>
        <?= $this->form()->row($form['_password']) ?>
        <?= $this->form()->widget($form['_submit'], [
            'attr' => [
                'class' => 'btn btn-primary btn-lg btn-block'
            ]
        ]) ?>
        <?= $this->form()->end($form); ?>

    </div>
</div>
