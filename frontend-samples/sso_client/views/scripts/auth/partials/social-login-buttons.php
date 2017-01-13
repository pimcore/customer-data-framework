<?php
$providers = ['Google', 'Twitter'];
$blacklist = $this->blacklist ?: [];

foreach ($providers as $provider): ?>

    <?php
    if (in_array($provider, $blacklist)) {
        continue;
    }

    $url = $this->url([
        'action'   => 'hybridauth',
        'provider' => $provider
    ], 'auth');
    ?>

    <a class="btn btn-info" href="<?= $url ?>">
        Sign in with <?= $provider ?>
    </a>

<?php endforeach; ?>
