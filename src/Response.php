<?php

namespace Livewire;

use Livewire\HydrationMiddleware\AddAttributesToRootTagOfHtml;

class Response
{
    public $request;

    public $fingerprint;
    public $effects;
    public $memo;

    public static function fromRequest($request, $html)
    {
        return new static($request, $html);
   }

    public function __construct($request, $html)
    {
        $this->request = $request;

        $this->fingerprint = $request->fingerprint;
        $this->memo = $request->memo;
        $this->effects = [
            'html' => $html,
        ];
    }

    public function id() { return $this->fingerprint['id']; }

    public function embedThyselfInHtml()
    {
        if (! $html = $this->effects['html'] ?? null) return;

        $this->effects['html'] = (new AddAttributesToRootTagOfHtml)($html, [
            'initial-data' => $this->toArrayWithoutHtml(),
        ]);
    }

    public function embedIdInHtml()
    {
        if (! $html = $this->effects['html'] ?? null) return;

        $this->effects['html'] = (new AddAttributesToRootTagOfHtml)($html, [
            'id' => $this->fingerprint['id'],
        ]);
    }

    public function html()
    {
        return $this->effects['html'] ?? null;
    }

    public function toArrayWithoutHtml()
    {
        return [
            'fingerprint' => $this->fingerprint,
            'effects' => array_diff_key($this->effects, ['html' => null]),
            'serverMemo' => $this->memo,
        ];
    }

    public function toInitialResonse()
    {
        $this->embedIdInHtml();

        return $this;
    }

    public function toSusequentLaravelResponse()
    {
        $this->embedIdInHtml();

        $dirtyMemo = [];

        // Only send along the memos that have changed to not bloat the payload.
        foreach ($this->memo as $key => $newValue) {
            if (! isset($this->request->memo[$key])) {
                $dirtyMemo[$key] = $newValue;

                continue;
            } else if ($this->request->memo[$key] !== $newValue) {
                $dirtyMemo[$key] = $newValue;
            }
        }

        return [
            'effects' => $this->effects,
            'serverMemo' => $dirtyMemo,
        ];
    }
}
