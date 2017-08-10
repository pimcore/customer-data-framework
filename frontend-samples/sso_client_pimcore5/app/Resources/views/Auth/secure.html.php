<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface;
use CustomerManagementFrameworkBundle\Model\OAuth\OAuth2TokenInterface;
use CustomerManagementFrameworkBundle\Templating\Helper\Encryption;
use CustomerManagementFrameworkBundle\Templating\Helper\SsoIdentity;

$this->extend('Layout/auth.html.php');

/** @var CustomerInterface|\Pimcore\Model\Object\Customer $customer */
$customer = $app->getUser();

/** @var SsoIdentity $ssoIdentityHelper */
$ssoIdentityHelper = $this->cmfSsoIdentity();
$ssoIdentities     = $ssoIdentityHelper->getSsoIdentities($customer);

// we don' want to show social login buttons for services we're already connected to
$connectedSsoIdentities = [];
foreach ($ssoIdentities as $ssoIdentity) {
    $connectedSsoIdentities[] = $ssoIdentity->getProvider();
}
?>

<div class="jumbotron">
    <h1>Very secure area</h1>
    <p>Logged in as customer <code><?= $customer->getUsername() ?></code> with ID <code><?= $customer->getId() ?></code></p>
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
    /** @var Encryption $encryptionHelper */
    $encryptionHelper = $this->cmfEncryption();

    /** @var \Pimcore\Model\Object\SsoIdentity $ssoIdentity */
    foreach ($ssoIdentities as $ssoIdentity): ?>

        <?php
        /** @var \Pimcore\Model\Object\SsoIdentity\Credentials $credentials */
        $credentials = $ssoIdentity->getCredentials();
        ?>

        <div class="panel panel-default">
            <div class="panel-heading"><?= $ssoIdentity->getProvider() ?></div>
            <div class="panel-body">
                <p>Identifier: <?= $ssoIdentity->getIdentifier() ?></p>
                <?php var_dump(json_decode($ssoIdentity->getProfileData())) ?>

                <?php if ($credentials->getOAuth1Token()): ?>

                    <?php
                    /** @var OAuth1TokenInterface $token */
                    $token = $credentials->getOAuth1Token();
                    var_dump([
                        'tokenRaw'       => $token->getToken(),
                        'token'          => $encryptionHelper->decrypt($token->getToken()),
                        'tokenSecretRaw' => $token->getTokenSecret(),
                        'tokenSecret'    => $encryptionHelper->decrypt($token->getTokenSecret()),
                    ])
                    ?>

                <?php elseif ($credentials->getOAuth2Token()): ?>

                    <?php
                    /** @var OAuth2TokenInterface $token */
                    $token = $credentials->getOAuth2Token();
                    var_dump([
                        'type'            => $token->getTokenType(),
                        'scope'           => $token->getScope(),
                        'accessTokenRaw'  => $token->getAccessToken(),
                        'accessToken'     => $encryptionHelper->decrypt($token->getAccessToken()),
                        'refreshTokenRaw' => $token->getRefreshToken(),
                        'refreshToken'    => $encryptionHelper->decrypt($token->getRefreshToken()),
                        'expiresAtRaw'    => $token->getExpiresAt(),
                        'expiresAt'       => \Carbon\Carbon::createFromTimestamp($token->getExpiresAt()),
                    ])
                    ?>

                <?php endif; ?>

            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

<?php var_dump($customer->cmfToArray()) ?>
