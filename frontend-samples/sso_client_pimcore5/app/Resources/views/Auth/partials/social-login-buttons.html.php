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
$route     = $this->route ?: 'app_auth_oauth_login';
$connect   = $this->connect ?? false;

$prompt = 'Sign in with';
if ($connect) {
    $prompt = 'Connect to';
}

foreach ($providers as $providerKey => $providerName): ?>

    <?php
    if (in_array($providerKey, $blacklist)) {
        continue;
    }

    $url = $this->url($route, [
        'service' => $providerKey
    ]);
    ?>

    <a class="btn btn-info btn-sm" href="<?= $url ?>">
        <i class="fa fa-<?= $providerKey ?>" aria-hidden="true"></i>
        <?= $prompt ?> <?= $providerName ?>
    </a>

<?php endforeach; ?>
