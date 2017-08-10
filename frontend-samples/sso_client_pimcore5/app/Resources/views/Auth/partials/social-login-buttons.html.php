<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$providers = [
    'google'  => 'Google',
    'twitter' => 'Twitter'
];

$blacklist = $this->blacklist ?: [];

foreach ($providers as $providerKey => $providerName): ?>

    <?php
    if (in_array($providerKey, $blacklist)) {
        continue;
    }

    $url = $this->url('hwi_oauth_service_redirect', [
        'service' => $providerKey
    ]);
    ?>

    <a class="btn btn-info" href="<?= $url ?>">
        <i class="fa fa-<?= $providerKey ?>" aria-hidden="true"></i>
        Sign in with <?= $providerName ?>
    </a>

<?php endforeach; ?>
