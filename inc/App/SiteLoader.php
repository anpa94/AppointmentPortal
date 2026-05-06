<?php

class SiteLoader
{
    private Template $template;

    public function __construct()
    {
        $this->template = new Template();
    }

    public function render(string $site): string
    {
        $route = $this->resolveRoute();
        $site = $route['site'];
        $site = preg_replace('/[^a-zA-Z0-9_-]/', '', $site) ?: 'home';

        if (!isset($_GET['mode']) && isset($route['mode'])) {
            $_GET['mode'] = $route['mode'];
        }

        if (!isset($_GET['p']) && isset($route['p'])) {
            $_GET['p'] = $route['p'];
        }
        if (!isset($_GET['d']) && isset($route['d'])) {
            $_GET['d'] = $route['d'];
        }

        if (!$this->isAuthorized()) {
            return $this->template->renderAlert('Seite konnte nicht aufgerufen werden. Keine Berechtigung vorhanden!', 'Page cannot be loaded. No access!');
        }

        if ($site === 'home') {
            if ((isset($_GET['mode']) && $_GET['mode'] === 'ProjectBackend') || (isset($_GET['p']) && isset($_GET['mode']) && $_GET['mode'] === 'ProjectBackend')) {
                return $this->template->renderPage('project_backend.twig');
            }

            return $this->template->renderPage('home.twig');
        }

        return $this->template->renderAlert('Die angeforderte Seite gibt es nicht!', "The requested page doesn't exist!");
    }


    private function resolveRoute(): array
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($scriptDir !== '' && $scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        if (count($parts) >= 2 && $parts[0] === 'ProjectBackend') {
            return [
                'site' => 'home',
                'mode' => 'ProjectBackend',
                'p' => preg_replace('/[^a-zA-Z0-9_-]/', '', $parts[1])
            ];
        }
        if (count($parts) >= 2 && $parts[0] === 'Project') {
            $route = [
                'site' => 'home',
                'mode' => 'loadProject',
                'p' => preg_replace('/[^a-zA-Z0-9_-]/', '', $parts[1])
            ];
            if (count($parts) >= 4 && $parts[2] === 'Date') {
                $route['d'] = preg_replace('/[^0-9-]/', '', $parts[3]);
            }

            return $route;
        }

        return [
            'site' => isset($_GET['m']) ? $_GET['m'] : 'home'
        ];
    }

    private function isAuthorized(): bool
    {
        $groups = json_decode(getConfig('authorizedGroups', 'frontend'));
        return isset($groups[0]) && ($groups[0] === 'ALL' || user_authorized($groups));
    }
}
