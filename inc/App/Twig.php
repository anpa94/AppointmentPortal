<?php

class Twig
{
    private string $templatePath;

    public function __construct(string $templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/');
    }

    public function render(string $template, array $context = []): string
    {
        $file = $this->templatePath . '/' . $template;
        if (!is_file($file)) {
            throw new RuntimeException('Template not found: ' . $template);
        }

        $content = file_get_contents($file);
        $content = preg_replace_callback('/\{\%\s*include\s+\'([^\']+)\'\s*\%\}/', function ($m) use ($context) {
            return $this->render($m[1], $context);
        }, $content);

        $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)(\|e)?\s*\}\}/', function ($m) use ($context) {
            $value = $this->resolveContext($context, $m[1]);
            $value = is_scalar($value) ? (string) $value : '';
            if (isset($m[2]) && $m[2] === '|e') {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $content);

        return $content;
    }

    private function resolveContext(array $context, string $key)
    {
        $segments = explode('.', $key);
        $value = $context;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return '';
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
