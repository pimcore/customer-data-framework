<?php
/**
 * Created by PhpStorm.
 * User: fbruenner
 * Date: 21.06.2018
 * Time: 07:57
 */

namespace CustomerManagementFrameworkBundle\OAuth;

use CustomerManagementFrameworkBundle\Entity\Service\Auth\AccessToken;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

class Maintenance
{
    use LoggerAware;

    /**
     * Cleans access-tokens that have been expired
     */
    public function cleanUpOldAccessTokens()
    {
        $this->getLogger()->info('Start cleanup for old access-tokens');

        /**
         * @var \Doctrine\ORM\EntityManager $entityManger
         */
        $entityManger = \Pimcore::getContainer()->get("doctrine.orm.entity_manager");

        $now = new \DateTime();

        $queryBuilder = $entityManger->createQueryBuilder();
        $queryBuilder
            ->delete(AccessToken::class, "a")
            ->where('a.expiryDateTime < :now')
            ->setParameter('now', $now->format('Y-m-d H:i:s'));

        $this->getLogger()->info('Finished cleanup AccessTokens by using query: '.$queryBuilder->getQuery()->getDQL());

        $queryBuilder->getQuery()->execute();

    }
}
