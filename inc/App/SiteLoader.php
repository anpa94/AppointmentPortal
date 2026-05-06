<?php

class SiteLoader
{
    public function render(string $site): void
    {
        $site = preg_replace('/[^a-zA-Z0-9_-]/', '', $site) ?: 'home';

        if (!$this->isAuthorized()) {
            $this->renderAlert('Seite konnte nicht aufgerufen werden. Keine Berechtigung vorhanden!', 'Page cannot be loaded. No access!');
            return;
        }

        $path = __DIR__ . '/../../sites/' . $site . '.php';
        if (!is_file($path)) {
            $this->renderAlert('Die angeforderte Seite gibt es nicht!', "The requested page doesn't exist!");
            return;
        }

        include $path;
    }

    private function isAuthorized(): bool
    {
        $groups = json_decode(getConfig('authorizedGroups', 'frontend'));
        return isset($groups[0]) && ($groups[0] === 'ALL' || user_authorized($groups));
    }

    private function renderAlert(string $de, string $en): void
    {
        ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Fehler!</strong> <?php echo htmlspecialchars($de, ENT_QUOTES, 'UTF-8'); ?>
            <br>
            <strong>Error!</strong> <?php echo htmlspecialchars($en, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php
    }
}
