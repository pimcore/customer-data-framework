<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$providers = ['Google', 'Twitter'];
$blacklist = $this->blacklist ?: [];

foreach ($providers as $provider): ?>

    <?php
    if (in_array($provider, $blacklist)) {
        continue;
    }

    $url = $this->url('app_auth_hybridauth', [
        'provider' => $provider
    ]);
    ?>

    <a class="btn btn-info" href="<?= $url ?>">
        Sign in with <?= $provider ?>
    </a>

<?php endforeach; ?>
