<?php

declare(strict_types=1);

namespace Appocular\Differ;

class SmokeTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAppReportsVersion(): void
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(),
            $this->response->getContent(),
        );
    }
}
