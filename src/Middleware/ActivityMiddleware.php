<?php

namespace Laracroft\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use \Closure;

class ActivityMiddleware
{
    /**
     * @inheritdoc
     */
    private array $excludRoute = [
        'api/health',
        'api/ping',
        'api/*/activity*',
        'api/*/auth/logout',
        'api/status',
        'api/heartbeat',
        'api/*/auth/user',
        'api/*/client/*',
    ];

    /**
     * @inheritdoc
     */
    private array $excludMethod = [
        'HEAD',
        'OPTIONS',
    ];

    /**
     * @inheritdoc
     */
    public function handle(Request $request, Closure $next) : Response
    {
        $response = $next($request);

        if ( $this->shouldLog($request, $response) ) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    private function shouldLog(Request $request, Response $response) : bool
    {
        if ( $response->getStatusCode() < 200 || $response->getStatusCode() >= 300 ) {
            return false;
        }

        if ( in_array($request->getMethod(), $this->excludMethod) ) {
            return false;
        }

        $path = $request->path();
        foreach ($this->excludRoute as $excludedRoute) {
            if ( fnmatch($excludedRoute, $path) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    private function logActivity(Request $request, Response $response) : void
    {
        try {
            $method = $request->getMethod();
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            $specialAction = $this->getSpecialAction($request);
            if ( $specialAction ) {

                if ( $specialAction === 'search' ) {
                    return;
                }

                $this->writeSpatieActivity(
                    request: $request,
                    action: $specialAction,
                    description: $this->getSpecialMessage($request, $specialAction)
                );
                return;
            }

            $activity = $this->getActivityFromMethod($method);

            if ( $activity === 'create' ) {
                $activity = 'update';
            }

            if ( !$activity || $activity === 'view' ) {
                return;
            }

            $customMessage = $this->getCustomMessage($request, $method, $routeName, $response, $activity);

            if ( $activity ) {
                $this->writeSpatieActivity(
                    request: $request,
                    action: $activity,
                    description: $customMessage
                );
            }

        } catch (\Throwable $e) {
            Log::error('Activity middleware logging error: ' . $e->getMessage(), [
                'method' => $request->getMethod(),
                'url'    => $request->fullUrl(),
                'route'  => $request->route()?->getName()
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    private function writeSpatieActivity(Request $request, string $action, ?string $description = null) : void
    {
        $user = $request->user();

        if ( !$user ) {
            return;
        }

        activity(config('activitylog.default_log_name', 'default'))
            ->causedBy($user)
            ->event($action)
            ->withProperties([
                'method'     => $request->getMethod(),
                'path'       => $request->path(),
                'route_name' => $request->route()?->getName(),
                'ip'         => $request->ip(),
            ])
            ->log($description ?? ucfirst($action));
    }

    /**
     * @inheritdoc
     */
    private function getActivityFromMethod(string $method) : ?string
    {
        return match (strtoupper($method)) {
            'POST'         => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE'       => 'delete',
            'GET'          => 'view',
            default        => null
        };
    }

    /**
     * @inheritdoc
     */
    private function getCustomMessage(Request $request, string $method, ?string $routeName, $response, ?string $activity = null) : ?string
    {
        $resource = $this->getResourceFromRoute($request, $routeName);

        if ( !$resource ) {
            return null;
        }

        $activityType = $activity ?? strtolower($method);

        return match ($activityType) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'view'   => 'Viewed',
            default  => null
        };
    }

    /**
     * @inheritdoc
     */
    private function getResourceFromRoute(Request $request, ?string $routeName) : ?string
    {
        if ( $routeName ) {
            $parts = explode('.', $routeName);
            if ( count($parts) > 0 ) {
                $resource = ucfirst($parts[0]);

                if ( preg_match('/^V\d+$/i', $resource) ) {
                    return null;
                }

                return $resource;
            }
        }

        $route = $request->route();

        $segments = [];
        if ( $route && method_exists($route, 'uri') ) {
            $segments = collect(explode('/', trim((string)$route->uri(), '/')))
                ->filter(fn($segment) => $segment !== '')
                ->values()
                ->all();
        }

        // Fallback for edge cases where route metadata is unavailable.
        if ( empty($segments) ) {
            $segments = $request->segments();
        }

        if ( empty($segments) ) {
            return null;
        }

        // Normalize API prefix segments: api/{version}/admin
        if ( isset($segments[0]) && $segments[0] === 'api' ) {
            array_shift($segments);
        }

        if ( !empty($segments) && preg_match('/^v\d+$/', $segments[0]) ) {
            array_shift($segments);
        }

        if ( isset($segments[0]) && $segments[0] === 'admin' ) {
            array_shift($segments);
        }

        if ( empty($segments) ) {
            return null;
        }

        return ucfirst(rtrim($segments[0], 's'));
    }

    /**
     * @inheritdoc
     */
    private function getSpecialAction(Request $request) : ?string
    {
        $path = $request->path();

        if ( $request->getMethod() === 'POST' && str_ends_with($path, '/delete') ) {
            return 'delete';
        }

        if ( str_contains($path, '/search') ) {
            return 'search';
        }

        if ( str_contains($path, '/archive') ) {
            return 'archive';
        }

        if ( str_contains($path, '/enable') ) {
            return 'enable';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    private function getSpecialMessage(Request $request, string $action) : ?string
    {
        $resource = $this->getResourceFromRoute($request, null);

        if ( !$resource ) {
            return null;
        }

        return match ($action) {
            'delete'  => 'Deleted',
            'search'  => 'Searched',
            'archive' => 'Archived',
            'enable'  => 'Enabled',
            default   => null
        };
    }
}
