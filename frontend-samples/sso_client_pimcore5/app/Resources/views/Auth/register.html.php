<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$this->extend('Layout/layout.html.php');

$form = $this->form;

$this->form()->setTheme($form, [':Form/Theme/Bootstrap']);
?>

<?php $this->headTitle('Register') ?>

<div class="row justify-content-center">
    <div class="col-4">

        <h2 class="form-signin-heading">Register</h2>

        <?php if ($this->errors): ?>
            <?php foreach($this->errors as $error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endforeach; ?>
        <?php endif ?>

        <div class="row">
            <div class="col-md-12">

                <?= $this->form()->start($form); ?>
                <?= $this->form()->row($form['email']) ?>
                <?= $this->form()->row($form['password']) ?>
                <?= $this->form()->row($form['firstname']) ?>
                <?= $this->form()->row($form['lastname']) ?>
                <?= $this->form()->widget($form['_submit'], [
                    'attr' => [
                        'class' => 'btn btn-primary btn-lg btn-block'
                    ]
                ]) ?>
                <?= $this->form()->end($form); ?>

            </div>
        </div>

    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-4 text-center">

        <p><a href="<?= $this->url('app_auth_login') ?>">&larr; Back to login</a></p>

    </div>
</div>
