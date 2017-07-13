<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$this->extend('Layout/auth.html.php');

/** @var \CustomerManagementFrameworkBundle\Model\CustomerInterface|\Pimcore\Model\Object\Customer $customer */
$customer = $this->customer;

/** @var \CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface $ssoIdentityService */
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
        <a class="btn btn-lg btn-primary" href="<?= $this->url('app_auth_logout') ?>">Logout</a>
    </p>

    <?= $this->template('Auth/partials/social-login-buttons.html.php', [
        'blacklist' => $connectedSsoIdentities
    ]) ?>
</div>

<?php if (count($ssoIdentities) > 0): ?>
    <h2>SSO Identities</h2>

    <?php
    /** @var \CustomerManagementFrameworkBundle\Encryption\EncryptionServiceInterface $encryptionService */
    $encryptionService = Pimcore::getDiContainer()->get(\CustomerManagementFrameworkBundle\Encryption\EncryptionServiceInterface::class);

    /** @var \Pimcore\Model\Object\SsoIdentity $ssoIdentity */
    foreach ($ssoIdentities as $ssoIdentity): ?>

        <div class="panel panel-default">
            <div class="panel-heading"><?= $ssoIdentity->getProvider() ?></div>
            <div class="panel-body">
                <p>Identifier: <?= $ssoIdentity->getIdentifier() ?></p>
                <?php dump(json_decode($ssoIdentity->getProfileData())) ?>

                <?php if ($ssoIdentity->getCredentials()->getOAuth1Token()): ?>

                    <?php
                    /** @var \CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface $token */
                    $token = $ssoIdentity->getCredentials()->getOAuth1Token();
                    dump([
                        'tokenRaw'       => $token->getToken(),
                        'token'          => $encryptionService->decrypt($token->getToken()),
                        'tokenSecretRaw' => $token->getTokenSecret(),
                        'tokenSecret'    => $encryptionService->decrypt($token->getTokenSecret()),
                    ])
                    ?>

                <?php elseif ($ssoIdentity->getCredentials()->getOAuth2Token()): ?>

                    <?php
                    /** @var \CustomerManagementFrameworkBundle\Model\OAuth\OAuth2TokenInterface $token */
                    $token = $ssoIdentity->getCredentials()->getOAuth2Token();
                    dump([
                        'accessTokenRaw'  => $token->getAccessToken(),
                        'accessToken'     => $encryptionService->decrypt($token->getAccessToken()),
                        'refreshTokenRaw' => $token->getRefreshToken(),
                        'refreshToken'    => $encryptionService->decrypt($token->getRefreshToken()),
                        'expiresAtRaw'    => $token->getExpiresAt(),
                        'expiresAt'       => \Carbon\Carbon::createFromTimestamp($token->getExpiresAt())
                    ])
                    ?>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

<?php dump($customer->cmfToArray()) ?>
