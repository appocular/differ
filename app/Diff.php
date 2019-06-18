<?php

namespace Appocular\Differ;

class Diff
{
    /**
     * Image Keeper ID.
     *
     * @var string
     */
    public $image_url;

    /**
     * Baseline image Keeper ID.
     *
     * @var string
     */
    public $baseline_url;

    /**
     * Diff image Keeper ID.
     *
     * @var string|null
     */
    public $diff_url;

    /**
     * Difference detected.
     *
     * @var bool
     */
    public $different;

    /**
     * Create new diff.
     */
    public function __construct(string $image_url, string $baseline_url, $diff_url, bool $different)
    {
        $this->image_url = $image_url;
        $this->baseline_url = $baseline_url;
        $this->diff_url = $diff_url;
        $this->different = $different;
    }
}
