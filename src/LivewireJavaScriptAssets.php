<?php

namespace Livewire;

class LivewireJavaScriptAssets
{
    public function unminified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.js');
    }

    public function minified()
    {
        return $this->pretendResponseIsFile(__DIR__.'/../dist/livewire.min.js');
    }

    public function pretendResponseIsFile($file)
    {
        $lastModified = filemtime($file);
        $expires = strtotime('+1 year');

        return response()->file($file, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => sprintf('%s GMT', gmdate('D, d M Y H:i:s', $expires)),
            'Last-Modified' => sprintf('%s GMT', gmdate('D, d M Y H:i:s', $lastModified)),
        ]);
    }
}
