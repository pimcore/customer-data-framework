<?php
$providers = ['Google', 'Twitter'];

foreach ($providers as $provider): ?>

    <?php
    $url = $this->url([
        'controller' => 'auth',
        'action'     => 'hybridauth',
        'provider'   => $provider
    ]);
    ?>

    <a href="<?= $url ?>"><?= $provider ?></a><br>

<?php endforeach; ?>


