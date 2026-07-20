<?php
namespace Config\Core;

class MobileViewRoute {

    protected ?array $selected = [];
    protected array $routes = [];
    protected array $routesFallback = [];
    protected bool $isLoggedIn = false;

    public function __construct(bool $isLoggedIn = false) {
        $this->getRoutes();
        $this->isLoggedIn = $isLoggedIn;
    }

    public function selected() {
        return $this->selected;
    }

    private function getRoutes() {
        $routes = require MOBILE_VIEW_ROOT .  "/pages/routes.php";

        if($routes['routes']) {
            $this->routes = $routes['routes'];
        }

        if($routes['fallback']) {
            $this->routesFallback = $routes['fallback'];
        }
    }

    public function resolve(?string $url = null): ?array {
        $result = [];
        foreach ($this->routes as $route) {
            $pattern = $route['url'];

            /** ubah ":param" → "([^/]+)" */
            $regex = preg_replace('#:([a-zA-Z0-9_]+)#', '([^/]+)', $pattern);
            $regex = "#^" . $regex . "$#";
            if (!preg_match($regex, $url, $matches)) {
                continue;
            }

            /** ekstrak nama parameter dari pattern */
            preg_match_all('#:([a-zA-Z0-9_]+)#', $pattern, $paramNames);
            array_shift($matches); // buang full match

            $params = [];
            foreach ($paramNames[1] as $i => $name) {
                $params[$name] = $matches[$i] ?? null;
            }

            $result[] = array_merge($route, ['params' => $params]);
        }

        $finalResult = null;
        foreach($result as $res) {
            $requiresAuth = $res['meta']['requires_auth'] ?? false;
            if($requiresAuth !== $this->isLoggedIn) {
                continue;
            }
            
            $finalResult = $res;
        }

        $this->selected = $finalResult;
        return $this->selected;
    }

    public function isExists(?array $routes = []): bool {
        if(empty($routes)) {
            return false;
        }

        return file_exists(MOBILE_VIEW_ROOT . "/{$routes['path']}.php");
    }

    public function fallbackTo404(): array {
        return array_values(array_filter($this->routesFallback, fn($route) => (!empty($route['meta']['name']) && $route['meta']['name'] === "fallback_404")))[0];
    }

    public function fallbackTo500(): array {
        return array_values(array_filter($this->routesFallback, fn($route) => (!empty($route['meta']['name']) && $route['meta']['name'] === "fallback_500")))[0];
    }

    public function fallbackTo403(): array {
        return array_values(array_filter($this->routesFallback, fn($route) => (!empty($route['meta']['name']) && $route['meta']['name'] === "fallback_403")))[0];
    }

}