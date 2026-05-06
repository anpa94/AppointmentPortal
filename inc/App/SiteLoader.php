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
        $site = preg_replace('/[^a-zA-Z0-9_-]/', '', $site) ?: 'home';

        if (!$this->isAuthorized()) {
            return $this->template->renderAlert('Seite konnte nicht aufgerufen werden. Keine Berechtigung vorhanden!', 'Page cannot be loaded. No access!');
        }

        if ($site === 'home') {
            return $this->template->renderPage('home.twig');
        }

        return $this->template->renderAlert('Die angeforderte Seite gibt es nicht!', "The requested page doesn't exist!");
    }

    private function isAuthorized(): bool
    {
        $groups = json_decode(getConfig('authorizedGroups', 'frontend'));
        return isset($groups[0]) && ($groups[0] === 'ALL' || user_authorized($groups));
    }
}
