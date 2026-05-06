<?php

class Template
{
    private Twig $twig;

    public function __construct()
    {
        $this->twig = new Twig(__DIR__ . '/../../templates');
    }

    public function renderLayout(string $content, string $pageTitle = '[IT] Terminplanungsportal'): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo $this->twig->render('layout.twig', [
            'page_title' => $pageTitle,
            'url_path' => URLpath,
            'content' => $content,
        ]);
    }

    public function renderAlert(string $de, string $en): string
    {
        return $this->twig->render('alert.twig', ['de' => $de, 'en' => $en]);
    }

    public function renderPage(string $template, array $context = []): string
    {
        return $this->twig->render($template, $context);
    }
}
