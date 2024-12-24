<?php
class Template {
    protected string $templateDir;
    protected array $data = [];
    protected bool $cacheEnabled = false;
    protected string $cacheDir = __DIR__ . '/cache';

    public function __construct(string $templateDir) {
        $this->templateDir = rtrim($templateDir, '/');
    }

    public function enableCache(bool $enabled = true, string $cacheDir = null): void {
        $this->cacheEnabled = $enabled;
        if ($cacheDir) {
            $this->cacheDir = rtrim($cacheDir, '/');
        }
    }

    public function assign(string $key, mixed $value): void {
        $this->data[$key] = $value;
    }

    public function render(string $template): string {
        $file = "{$this->templateDir}/{$template}.php";
        if (!file_exists($file)) {
            throw new Exception("Template file {$file} not found");
        }

        if ($this->cacheEnabled) {
            $cacheFile = $this->cacheDir . '/' . md5($file) . '.cache';
            if (file_exists($cacheFile) && filemtime($cacheFile) > filemtime($file)) {
                return file_get_contents($cacheFile);
            }
        }

        extract($this->data);
        ob_start();
        include $file;
        $content = ob_get_clean();

        if ($this->cacheEnabled) {
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0755, true);
            }
            file_put_contents($cacheFile, $content);
        }

        return $content;
    }

    public function show(string $template): void {
        echo $this->render($template);
    }
}
