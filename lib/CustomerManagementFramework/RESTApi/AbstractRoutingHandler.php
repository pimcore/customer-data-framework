<?php

namespace CustomerManagementFramework\RESTApi;

use CustomerManagementFramework\RESTApi\Exception\MissingRequestBodyException;
use CustomerManagementFramework\RESTApi\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Routing handler implementation using the symfony route component to dispatch requests to actions.
 *
 * @package CustomerManagementFramework\RESTApi
 */
abstract class AbstractRoutingHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $pathPrefix;

    /**
     * Get route collection defining the routes to handle
     *
     * @return RouteCollection
     */
    abstract protected function getRoutes();

    /**
     * Handle request - match route and return Response
     *
     * @param \Zend_Controller_Request_Http $request
     * @return Response
     */
    public function handle(\Zend_Controller_Request_Http $request)
    {
        $routes = $this->getRoutes();

        $context = new RequestContext(
            $request->getBaseUrl(),
            $request->getMethod(),
            $request->getHttpHost(),
            $request->getScheme()
        );

        $matcher = new UrlMatcher($routes, $context);

        try {
            $result = $matcher->match($request->getPathInfo());
            $route  = $routes->get($result['_route']);

            // the actual action to call is set on a route option (see createRoute method)
            $action = $route->getOption('action');
            if (empty($action)) {
                throw new \RuntimeException(
                    sprintf('Route for %s does not define an action to call', $route->getPath()),
                    500
                );
            }

            if (!method_exists($this, $action)) {
                throw new \RuntimeException(
                    sprintf('Action %s defined for route %s is not callable', $action, $route->getPath()),
                    500
                );
            }

            return call_user_func_array([$this, $action], [$request, $result]);
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            throw new ResourceNotFoundException(
                sprintf('Resource %s was not found', $request->getPathInfo()),
                Response::RESPONSE_CODE_NOT_FOUND
            );
        } catch (\Exception $e) {
            return new Response([
                "success" => false,
                "msg" => $e->getMessage()
                ], Response::RESPONSE_CODE_BAD_REQUEST);
        }
    }

    /**
     * Set the route path prefix used to build the routes
     *
     * @param string|null $pathPrefix
     */
    public function setPathPrefix($pathPrefix = null)
    {
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * Get the route path prefix
     *
     * @return string|null
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * Create a route with an action option and run the path through our prefix normalization logic
     *
     * @param string $methods
     * @param string $path
     * @param $action
     * @return Route
     */
    protected function createRoute($methods = 'GET', $path = '/', $action)
    {
        $path = $this->routePath($path);

        $route = new Route($path);
        $route
            ->setMethods($methods)
            ->setOption('action', $action);

        return $route;
    }

    /**
     * Symfony routes default to / if the path is empty which prevents us from using sub-collections with a prefix to match
     * routes like '/cmf/api/customers' (with '/cmf/api/customers' being the prefix and '' being the absolute route path)
     * as the route would normalize itself to '/', ending up as '/cmf/api/customers/' with the prefix.
     *
     * Instead of using a sub-collection we add the prefix directly on the route, ending up with the desired
     * '/cmf/api/customers' as route path.
     *
     * @param string|null $path
     * @return string
     */
    protected function routePath($path = null)
    {
        if (empty($path)) {
            $path = '';
        }

        $path   = ltrim($path, '/');
        $prefix = $this->pathPrefix ?: '';

        if (empty($path)) {
            $path = $prefix;
        } else {
            $path = $prefix . '/' . $path;
        }

        // routing needs a path starting with a slash - default to / if prefix and path are empty
        if (empty($path)) {
            $path = '/';
        }

        return $path;
    }

    /**
     * Parse request body JSON
     *
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    protected function getRequestData(\Zend_Controller_Request_Http $request)
    {
        $body = $request->getRawBody();
        $data = json_decode($body, true);

        if (null === $data) {
            throw new MissingRequestBodyException(
                'Request body is no valid JSON',
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        return $data;
    }
}
