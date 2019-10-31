<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace CustomerManagementFrameworkBundle\Repository\Service\Auth;

use CustomerManagementFrameworkBundle\Entity\Service\Auth\ClientEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
    {
        $clients = \Pimcore::getContainer()->getParameter("pimcore_customer_management_framework.oauth_server");

        if(!key_exists("clients", $clients)){
            throw new \Exception("AuthorizationServer ERROR: pimcore_customer_management_framework.oauth_server.clients NOT DEFINED IN config.xml");
        }

        $clients = $clients["clients"];

        $clients = array_map(function($client){
            $transformedClient = [
                'client_id' =>       $client['client_id'],
                'secret'          => password_hash($client['secret'], PASSWORD_BCRYPT),
                'name'            => $client['name'],
                'redirect_uri'    => $client['redirect_uri'],
                'is_confidential' => $client['is_confidential']
            ];
            return $transformedClient;
        },$clients);

        $currentClient = array_filter($clients, function($client) use ($clientIdentifier) {
            return $client['client_id'] == $clientIdentifier;
        });


        // Check if client is registered
        if(!count($currentClient))return;

        $currentClient = array_shift($currentClient);

        if (
            $mustValidateSecret === true
            && $currentClient['is_confidential'] === true
            && (empty($clientSecret) || password_verify($clientSecret, $currentClient['secret']) === false)

        ) {
            return;
        }

        $client = new ClientEntity();
        $client->setIdentifier($clientIdentifier);
        $client->setName($currentClient['name']);
        $client->setRedirectUri($currentClient['redirect_uri']);

        return $client;
    }

    /**
     * Validate a client's secret.
     *
     * @param string $clientIdentifier The client's identifier
     * @param null|string $clientSecret The client's secret (if sent)
     * @param null|string $grantType The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        throw new \Exception("Validate Client not implemented yet!");
        // TODO: Implement validateClient() method.
    }
}
