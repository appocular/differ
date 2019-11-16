<?php

declare(strict_types=1);

namespace Appocular\Differ\Http\Controllers;

use Appocular\Differ\Jobs\DiffRequest;
use Appocular\Differ\TestCase;
use Illuminate\Support\Facades\Queue;

class DiffControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Set up a shared token.
        \putenv('SHARED_TOKEN=SharedToken');
    }

    /**
     * Test that a valid request without a token gets access denied.
     */
    public function testAccess(): void
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
            'baseline_url' => 'url2',
        ]);

        $this->assertResponseStatus(401);
        Queue::assertNotPushed(DiffRequest::class);
    }

    /**
     * Test that a valid request creates a job and returns success.
     */
    public function testPostingDiffs(): void
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
            'baseline_url' => 'url2',
        ], ['Authorization' => 'Bearer SharedToken']);

        $this->assertResponseStatus(200);
        Queue::assertPushed(DiffRequest::class);
    }

    /**
     * Test that a invalid request doesn't queue a job and returns error.
     */
    public function testBadRequest(): void
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
        ], ['Authorization' => 'Bearer SharedToken']);

        $this->assertResponseStatus(422);
        Queue::assertNotPushed(DiffRequest::class);
    }
}
