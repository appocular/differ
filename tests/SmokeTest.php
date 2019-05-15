<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class SmokeTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAppReportsVersion()
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );
    }
}
