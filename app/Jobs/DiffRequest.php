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
    public $image_kid;

    /**
     * @var string
     */
    public $baseline_kid;

    public function __construct(string $image_kid, string $baseline_kid)
    {
        $this->image_kid = $image_kid;
        $this->baseline_kid = $baseline_kid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $diff = DB::table('diffs')
            ->where(['image_kid' => $this->image_kid, 'baseline_kid' => $this->baseline_kid])
            ->first();

        if ($diff) {
            $diff = new Diff($diff->image_kid, $diff->baseline_kid, $diff->diff_kid, $diff->different);
        } else {
            $diff = app(Differ::class)->diff($this->image_kid, $this->baseline_kid);
            DB::table('diffs')->updateOrInsert((array) $diff);
        }

        app(Assessor::class)->reportDiff($diff->image_kid, $diff->baseline_kid, $diff->diff_kid, $diff->different);
    }
}
