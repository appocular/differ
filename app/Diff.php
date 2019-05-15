<?php

namespace Appocular\Differ;

class Diff
{
    /**
     * Image Keeper ID.
     *
     * @var string
     */
    public $image_kid;

    /**
     * Baseline image Keeper ID.
     *
     * @var string
     */
    public $baseline_kid;

    /**
     * Diff image Keeper ID.
     *
     * @var string|null
     */
    public $diff_kid;

    /**
     * Difference detected.
     *
     * @var bool
     */
    public $different;

    /**
     * Create new diff.
     */
    public function __construct(string $image_kid, string $baseline_kid, $diff_kid, bool $different)
    {
        $this->image_kid = $image_kid;
        $this->baseline_kid = $baseline_kid;
        $this->diff_kid = $diff_kid;
        $this->different = $different;
    }
}
