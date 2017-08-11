<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ContentController extends FrontendController
{
    /**
     * @Route("/", name="app_index")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('app_secure');
    }

    /**
     * The action we want to open after login. The Security annotation defines that the action needs a valid user
     * to be accessible. This can either be done on a controller or action level or handled globally via access_control
     * configuration.
     *
     * @Route("/secure", name="app_secure")
     * @Security("has_role('ROLE_USER')")
     */
    public function secureAction()
    {
    }
}
