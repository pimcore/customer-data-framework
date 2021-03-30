<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


namespace CustomerManagementFrameworkBundle\Twig\Extension;


use CustomerManagementFrameworkBundle\Templating\Helper\JsConfig;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CmfFrontendUtilsExtension extends AbstractExtension
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var JsConfig
     */
    private $jsConfigService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * CmfFrontendUtilsExtension constructor.
     * @param Packages $packages
     * @param JsConfig $jsConfigService
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     */
    public function __construct(Packages $packages, JsConfig $jsConfigService, RouterInterface $router, RequestStack $requestStack)
    {
        $this->packages = $packages;
        $this->jsConfigService = $jsConfigService;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cmf_minifiedAssetUrl', [$this, 'minifiedAssetUrl']),
            new TwigFunction('cmf_jsConfig', [$this, 'getJsConfig']),
            new TwigFunction('cmf_inDebug', [$this, 'inDebug']),
            new TwigFunction('cmf_filterFormAction', [$this, 'filterFormAction'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('cmf_print_field_combinations', [$this, 'printFieldCombinations'])
        ];
    }

    public function minifiedAssetUrl($url, $packageName = null, $condition = null, $minifiedExtension = 'min'): string
    {
        if (null === $condition) {
            $condition = !\Pimcore::inDebugMode();
        } else {
            if (is_callable($condition)) {
                $condition = call_user_func($condition, $url, $minifiedExtension);
            }
        }

        if (!$condition) {
            return $this->packages->getUrl($url, $packageName);
        }

        $parts = explode('.', $url);

        $extension = array_pop($parts);
        $extension = $minifiedExtension.'.'.$extension;

        $parts[] = $extension;

        return $this->packages->getUrl(implode('.', $parts), $packageName);
    }

    public function getJsConfig(): JsConfig {
        return $this->jsConfigService;
    }

    public function inDebug(): bool {
        return \Pimcore::inDebugMode();
    }

    public function printFieldCombinations($fieldCombinations): string {
        if($fieldCombinations) {
            foreach ($fieldCombinations as $key => $combination) {
                $fieldCombinations[$key] = implode(', ', $combination);
            }

            return implode('<br>', $fieldCombinations);
        }
        return '';
    }

    public function filterFormAction(PaginationInterface $paginator): string
    {
        // reset page when changing filters
        $formActionParams = [
            'page' => null,
            'perPage' => null,
        ];

        if (null !== $paginator && $paginator->getItemNumberPerPage() !== 25) {
            $formActionParams['perPage'] = $paginator->getItemNumberPerPage();
        }

        $request = $this->requestStack->getMasterRequest();

        return $this->router->generate($request->get('_route'), $formActionParams);
    }

}
