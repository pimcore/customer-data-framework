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
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

$this->extend('Layout/layout.html.php');

$this->headTitle('Secure Area');

/** @var CustomerInterface|\Pimcore\Model\DataObject\Customer $customer */
$customer = $app->getUser();

/** @var SsoIdentity $ssoIdentityHelper */
$ssoIdentityHelper = $this->cmfSsoIdentity();
$ssoIdentities     = $ssoIdentityHelper->getSsoIdentities($customer);

// we don' want to show social login buttons for services we're already connected to
$connectedSsoIdentities = [];
foreach ($ssoIdentities as $ssoIdentity) {
    $connectedSsoIdentities[] = $ssoIdentity->getProvider();
}

// use VarDumper component directly without writing to debug bar
$dumper = new HtmlDumper();
$cloner = new VarCloner();
?>

<div class="jumbotron">
    <h1>Very secure area</h1>
    <p>Logged in as customer <code><?= $customer->getUsername() ?></code> with ID <code><?= $customer->getId() ?></code></p>
    <hr>
    <p>
        <a class="btn btn-lg btn-primary" href="<?= $this->url('app_auth_logout') ?>">Logout</a>
    </p>

    <?= $this->template('Auth/partials/social-login-buttons.html.php', [
        'blacklist' => $connectedSsoIdentities,
        'route'     => 'app_auth_oauth_connect',
        'connect'   => true
    ]) ?>
</div>

<?php if (count($ssoIdentities) > 0): ?>
    <h2 class="mt-4 mb-3">SSO Identities</h2>

    <?php
    /** @var Encryption $encryptionHelper */
    $encryptionHelper = $this->cmfEncryption();

    /** @var \Pimcore\Model\DataObject\SsoIdentity $ssoIdentity */
    foreach ($ssoIdentities as $ssoIdentity): ?>

        <?php
        /** @var \Pimcore\Model\DataObject\SsoIdentity\Credentials $credentials */
        $credentials = $ssoIdentity->getCredentials();
        ?>

        <div class="card mt-3">
            <div class="card-header">
                <?= $ssoIdentity->getProvider() ?>: <code><?= $ssoIdentity->getIdentifier() ?></code>
            </div>
            <div class="card-block">
                <h6>Profile Data</h6>
                <?php $dumper->dump($cloner->cloneVar(json_decode($ssoIdentity->getProfileData(), true))); ?>

                <hr>

                <?php if ($credentials->getOAuth1Token()): ?>

                    <h6>OAuth 1 Token</h6>

                    <?php
                    /** @var OAuth1TokenInterface $token */
                    $token = $credentials->getOAuth1Token();
                    $dumper->dump($cloner->cloneVar([
                        'tokenRaw'       => $token->getToken(),
                        'token'          => $encryptionHelper->decrypt($token->getToken()),
                        'tokenSecretRaw' => $token->getTokenSecret(),
                        'tokenSecret'    => $encryptionHelper->decrypt($token->getTokenSecret()),
                    ]));
                    ?>

                <?php elseif ($credentials->getOAuth2Token()): ?>

                    <h6>OAuth 2 Token</h6>

                    <?php
                    /** @var OAuth2TokenInterface $token */
                    $token = $credentials->getOAuth2Token();
                    $dumper->dump($cloner->cloneVar([
                        'type'            => $token->getTokenType(),
                        'scope'           => $token->getScope(),
                        'accessTokenRaw'  => $token->getAccessToken(),
                        'accessToken'     => $encryptionHelper->decrypt($token->getAccessToken()),
                        'refreshTokenRaw' => $token->getRefreshToken(),
                        'refreshToken'    => $encryptionHelper->decrypt($token->getRefreshToken()),
                        'expiresAtRaw'    => $token->getExpiresAt(),
                        'expiresAt'       => \Carbon\Carbon::createFromTimestamp($token->getExpiresAt()),
                    ]));
                    ?>

                <?php endif; ?>
            </div>
        </div>

    <?php endforeach; ?>

<?php endif; ?>

<h2 class="mt-5 mb-3">Customer Data</h2>
<?php $dumper->dump($cloner->cloneVar($customer->cmfToArray())); ?>
