<?php

declare(strict_types=1);

namespace Appocular\Differ;

use Appocular\Clients\Contracts\Keeper;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

class DifferTest extends TestCase
{
    /**
     * Test that a basic diff produces the expected result.
     */
    public function testBasicDiff(): void
    {
        $diff_image = null;
        $keeper = $this->prophesize(Keeper::class);
        $keeper->get('url1')->willReturn(\file_get_contents(__DIR__ . '/../fixtures/a.png'))->shouldBeCalled();
        $keeper->get('url2')->willReturn(\file_get_contents(__DIR__ . '/../fixtures/b.png'))->shouldBeCalled();
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        $keeper->store(Argument::any())->will(function (array $argv) use (&$diff_image): string {
            $diff_image = $argv[0];

            return 'diffurl';
        })->shouldBeCalled();

        $differ = new Differ($keeper->reveal());
        $diff = $differ->diff('url1', 'url2');
        $expected = new Diff('url1', 'url2', 'diffurl', true);
        $this->assertEquals($expected, $diff);
        $this->assertNoDifference($diff_image, __DIR__ . '/../fixtures/a-b-diff.png');
    }

    /**
     * Test that no differences are reported as such.
     */
    public function testNoDifference(): void
    {
        $diff_image = null;
        $keeper = $this->prophesize(Keeper::class);
        $keeper->get('url1')->willReturn(\file_get_contents(__DIR__ . '/../fixtures/a.png'))->shouldBeCalled();
        // phpcs:ignore SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
        $keeper->store(Argument::any())->will(function (array $argv) use (&$diff_image): string {
            $diff_image = $argv[0];

            return 'diffurl';
        })->shouldBeCalled();

        $differ = new Differ($keeper->reveal());
        $diff = $differ->diff('url1', 'url1');
        $expected = new Diff('url1', 'url1', 'diffurl', false);
        $this->assertEquals($expected, $diff);
        $this->assertNoDifference($diff_image, __DIR__ . '/../fixtures/a-a-diff.png');
    }

    protected function assertNoDifference(string $image, string $fixture): void
    {
        $imageFile = '/tmp/differ-test' . \getmypid() . '.png';
        $diffFile = '/tmp/differ-test-diff' . \getmypid() . '.png';
        \file_put_contents($imageFile, $image);
        $process = new Process([
            'compare',
            '-dissimilarity-threshold',
            '1',
            '-fuzz',
            '1',
            '-metric',
            'AE',
            '-highlight-color',
            'blue',
            $imageFile,
            $fixture,
            $diffFile,
        ]);

        $process->run();

        \unlink($imageFile);
        \unlink($diffFile);
        $pixelCount = \trim($process->getErrorOutput());

        if (! \preg_match('/^\d+$/', $pixelCount)) {
            throw new RuntimeException('Unexpected output from compare: ' . $pixelCount);
        }

        $this->assertFalse($pixelCount > 0, 'Difference in diff');
    }
}
