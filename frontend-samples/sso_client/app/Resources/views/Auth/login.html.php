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

<?php $this->headTitle('Login') ?>

<div class="row justify-content-center">
    <div class="col-4">

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

<div class="row justify-content-center mt-4">
    <div class="col-4 text-center">

        <?= $this->template(':Auth/partials:social-login-buttons.html.php') ?>

    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-4 text-center">

        <p>No account? <a href="<?= $this->url('app_auth_register') ?>">Register now!</a></p>

    </div>
</div>

