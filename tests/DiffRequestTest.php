<?php

use Appocular\Clients\Contracts\Assessor;
use Appocular\Differ\Diff;
use Appocular\Differ\Differ;
use Appocular\Differ\Jobs\DiffRequest;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class DiffRequestTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test that known diffs are dispatched immediately.
     */
    public function testReportKnown()
    {
        $diff = new Diff('image id', 'baseline id', 'diff id', true);
        $assessor = $this->prophesize(Assessor::class);
        $assessor->reportDiff($diff)->shouldBeCalled();

        app()->instance(Assessor::class, $assessor->reveal());
        DB::table('diffs')->insert([
            'image_kid' => 'image id',
            'baseline_kid' => 'baseline id',
            'diff_kid' => 'diff id',
            'different' => true,
        ]);

        $job = new DiffRequest('image id', 'baseline id');
        $job->handle();
    }

    /**
     * Test that new diffs are processed and dispatched.
     */
    public function testGenerating()
    {
        $diff = new Diff('image id', 'baseline id', 'diff id', true);
        $differ = $this->prophesize(Differ::class);
        $differ->diff('image id', 'baseline id')->willReturn($diff)->shouldBeCalled();
        app()->instance(Differ::class, $differ->reveal());

        $job = new DiffRequest('image id', 'baseline id');
        $job->handle();

        $this->seeInDatabase('diffs', [
            'image_kid' => 'image id',
            'baseline_kid' => 'baseline id',
            'diff_kid' => 'diff id',
            'different' => true,
        ]);
    }
}
