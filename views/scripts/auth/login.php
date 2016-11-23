<?php
$providers = ['Google', 'Twitter'];

foreach ($providers as $provider): ?>

    <?php
    $url = $this->url([
        'controller' => 'auth',
        'action'     => 'external',
        'provider'   => $provider
    ]);
    ?>

    <a href="<?= $url ?>"><?= $provider ?></a><br>

<?php endforeach; ?>


