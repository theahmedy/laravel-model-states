<?php

namespace Spatie\ModelStates;

abstract class Transition
{
    protected ?array $metaData = null;

    public function canTransition(): bool
    {
        return true;
    }

    public function setMetaData(?array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function metaData(): ?array
    {
        return $this->metaData;
    }
}
