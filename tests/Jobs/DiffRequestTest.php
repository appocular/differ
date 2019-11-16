<?php

declare(strict_types=1);

namespace Appocular\Differ\Jobs;

use Appocular\Clients\Contracts\Assessor;
use Appocular\Differ\Diff;
use Appocular\Differ\Differ;
use Appocular\Differ\TestCase;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DiffRequestTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test that known diffs are dispatched immediately.
     */
    public function testReportKnown(): void
    {
        $assessor = $this->prophesize(Assessor::class);
        $assessor->reportDiff('image id', 'baseline id', 'diff id', true)->shouldBeCalled();

        \app()->instance(Assessor::class, $assessor->reveal());
        DB::table('diffs')->insert([
            'image_url' => 'image id',
            'baseline_url' => 'baseline id',
            'diff_url' => 'diff id',
            'different' => true,
        ]);

        $job = new DiffRequest('image id', 'baseline id');
        $job->handle();
    }

    /**
     * Test that new diffs are processed and dispatched.
     */
    public function testGenerating(): void
    {
        $assessor = $this->prophesize(Assessor::class);
        $assessor->reportDiff('image id', 'baseline id', 'diff id', true)->shouldBeCalled();

        \app()->instance(Assessor::class, $assessor->reveal());

        $diff = new Diff('image id', 'baseline id', 'diff id', true);
        $differ = $this->prophesize(Differ::class);
        $differ->diff('image id', 'baseline id')->willReturn($diff)->shouldBeCalled();
        \app()->instance(Differ::class, $differ->reveal());

        $job = new DiffRequest('image id', 'baseline id');
        $job->handle();

        $this->seeInDatabase('diffs', [
            'image_url' => 'image id',
            'baseline_url' => 'baseline id',
            'diff_url' => 'diff id',
            'different' => true,
        ]);
    }
}
