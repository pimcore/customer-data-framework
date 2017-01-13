<?php $this->headTitle('Login') ?>

<div class="row">
    <div class="col-md-4 col-md-push-4">

        <form class="form-signin" action="<?= $this->url(['action' => 'login'], 'auth', true) ?>" method="post">
            <h2 class="form-signin-heading">Login</h2>
            <?= $this->template('auth/partials/form-errors.php') ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="input-email" class="sr-only">Email address</label>
                        <input type="email" name="email" id="input-email" class="form-control" placeholder="E-Mail address" required autofocus value="<?= $this->getParam('email') ? $this->escape($this->getParam('email')) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="input-password" class="sr-only">Password</label>
                        <input type="password" name="password" id="input-password" class="form-control" placeholder="Password" required>
                    </div>

                    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
                </div>
            </div>

            <div class="row mt10">
                <div class="col-md-12 text-center">

                    <?= $this->partial('auth/partials/social-login-buttons.php') ?>

                </div>
            </div>
        </form>

    </div>
</div>
