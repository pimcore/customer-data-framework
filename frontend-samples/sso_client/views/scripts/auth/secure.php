<?php
/** @var \CustomerManagementFramework\Model\CustomerInterface|\Pimcore\Model\Object\Customer $customer */
$customer = $this->customer;

/** @var \CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface $ssoIdentityService */
$ssoIdentityService = $this->ssoIdentityService;
$ssoIdentities      = $ssoIdentityService->getSsoIdentities($customer);

// we don' want to show social login buttons for services we're already connected to
$connectedSsoIdentities = [];
foreach ($ssoIdentities as $ssoIdentity) {
    $connectedSsoIdentities[] = $ssoIdentity->getProvider();
}
?>

<div class="jumbotron">
    <h1>Very secure area</h1>
    <p>Logged in as customer <label class="label label-default"><?= $customer->getId() ?></label></p>
    <p>
        <a class="btn btn-lg btn-primary" href="<?= $this->url(['action' => 'logout'], 'auth', true) ?>">Logout</a>
    </p>

    <?= $this->partial('auth/partials/social-login-buttons.php', [
        'blacklist' => $connectedSsoIdentities
    ]) ?>
</div>

<?php if (count($ssoIdentities) > 0): ?>
    <h2>SSO Identities</h2>

    <?php
    /** @var \Pimcore\Model\Object\SsoIdentity $ssoIdentity */
    foreach ($ssoIdentities as $ssoIdentity): ?>

        <div class="panel panel-default">
            <div class="panel-heading"><?= $ssoIdentity->getProvider() ?></div>
            <div class="panel-body">
                <p>Identifier: <?= $ssoIdentity->getIdentifier() ?></p>
                <?php dump(json_decode($ssoIdentity->getProfileData())) ?>
            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

<?php dump($customer->cmfToArray()) ?>
