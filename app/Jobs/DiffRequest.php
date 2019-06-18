<?php

namespace Appocular\Differ\Jobs;

use Appocular\Clients\Contracts\Assessor;
use Appocular\Differ\Diff;
use Appocular\Differ\Differ;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiffRequest extends Job
{
    /**
     * @var string
     */
    public $image_url;

    /**
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
     *
     * @return void
     */
    public function handle()
    {
        $diff = DB::table('diffs')
            ->where(['image_url' => $this->image_url, 'baseline_url' => $this->baseline_url])
            ->first();

        if ($diff) {
            $diff = new Diff($diff->image_url, $diff->baseline_url, $diff->diff_url, $diff->different);
        } else {
            $diff = app(Differ::class)->diff($this->image_url, $this->baseline_url);
            DB::table('diffs')->updateOrInsert((array) $diff);
        }

        app(Assessor::class)->reportDiff($diff->image_url, $diff->baseline_url, $diff->diff_url, $diff->different);
    }
}
