<?php

use Appocular\Differ\Jobs\DiffRequest;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class DiffControllerTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();
        // Set up a shared token.
        \putenv('SHARED_TOKEN=SharedToken');
    }

    /**
     * Test that a valid request without a token gets access denied.
     */
    public function testAccess()
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
    public function testPostingDiffs()
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
    public function testBadRequest()
    {
        Queue::fake();
        $this->json('POST', '/diff', [
            'image_url' => 'url1',
        ], ['Authorization' => 'Bearer SharedToken']);

        $this->assertResponseStatus(422);
        Queue::assertNotPushed(DiffRequest::class);
    }
}
