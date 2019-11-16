<?php

declare(strict_types=1);

namespace Appocular\Differ\Jobs;

use Appocular\Clients\Contracts\Assessor;
use Appocular\Differ\Diff;
use Appocular\Differ\Differ;
use Illuminate\Support\Facades\DB;

class DiffRequest extends Job
{
    /**
     * Image to diff.
     *
     * @var string
     */
    public $image_url;

    /**
     * Baseline to diff.
     *
     * @var string
     */
    public $baseline_url;

    public function __construct(string $image_url, string $baseline_url)
    {
        $this->image_url = $image_url;
        $this->baseline_url = $baseline_url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $diff = DB::table('diffs')
            ->where(['image_url' => $this->image_url, 'baseline_url' => $this->baseline_url])
            ->first();

        if ($diff) {
            $diff = new Diff($diff->image_url, $diff->baseline_url, $diff->diff_url, (bool) $diff->different);
        } else {
            $diff = \app(Differ::class)->diff($this->image_url, $this->baseline_url);
            DB::table('diffs')->updateOrInsert((array) $diff);
        }

        \app(Assessor::class)->reportDiff($diff->image_url, $diff->baseline_url, $diff->diff_url, $diff->different);
    }
}
