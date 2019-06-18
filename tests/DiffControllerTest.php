<?php

use Appocular\Differ\Jobs\DiffRequest;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class DiffControllerTest extends TestCase
{
    /**
     * Test that a valid request creates a job and returns success.
     */
    public function testPostingDiffs()
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
            'baseline_url' => 'url2',
        ]);

        $this->assertResponseStatus(200);
        Queue::assertPushed(DiffRequest::class);
    }

    /**
     * Test that a invalid request doesn't queue a job and returns error.
     */
    public function testBadRequest()
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
        ]);

        $this->assertResponseStatus(422);
        Queue::assertNotPushed(DiffRequest::class);
    }
}
