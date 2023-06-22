<?php

namespace Livewire\Features\SupportUnitTesting;

use Illuminate\Testing\Constraints\SeeInOrder;
use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Arr;

trait MakesAssertions
{
    public function assertSee($values, $escape = true, $stripInitialData = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $escape ? e($value): $value,
                $this->html($stripInitialData)
            );
        }

        return $this;
    }

    public function assertDontSee($values, $escape = true, $stripInitialData = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringNotContainsString(
                $escape ? e($value): $value,
                $this->html($stripInitialData)
            );
        }

        return $this;
    }

    public function assertSeeHtml($values)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $value,
                $this->html()
            );
        }

        return $this;
    }

    public function assertSeeHtmlInOrder($values)
    {
        PHPUnit::assertThat(
            $values,
            new SeeInOrder($this->html())
        );

        return $this;
    }

    public function assertDontSeeHtml($values)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringNotContainsString(
                $value,
                $this->html()
            );
        }

        return $this;
    }

    public function assertSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', ($value)) : $value;

        $content = $this->html();

        tap(strip_tags($content), function ($content) use ($values) {
            foreach ($values as $value) {
                PHPUnit::assertStringContainsString((string) $value, $content);
            }
        });

        return $this;
    }
}
