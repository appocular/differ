<?php

use Appocular\Differ\Diff;
use Appocular\Differ\Differ;
use Appocular\Clients\Contracts\Keeper;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class DifferTest extends TestCase
{
    /**
     * Test that a basic diff produces the expected result.
     */
    public function testBasicDiff()
    {
        $diff_image = null;
        $keeper = $this->prophesize(Keeper::class);
        $keeper->get('kid1')->willReturn(file_get_contents(__DIR__ . '/../fixtures/a.png'))->shouldBeCalled();
        $keeper->get('kid2')->willReturn(file_get_contents(__DIR__ . '/../fixtures/b.png'))->shouldBeCalled();
        $keeper->store(Argument::any())->will(function ($image) use (&$diff_image) {
            $diff_image = $image;
            return 'diffkid';
        })->shouldBeCalled();

        $differ = new Differ($keeper->reveal());
        $diff = $differ->diff('kid1', 'kid2');
        $expected = new Diff('kid1', 'kid2', 'diffkid', 1);
        $this->assertEquals($expected, $diff);
        $this->assertNoDifference($diff_image, __DIR__ . '/../fixtures/a-b-diff.png');
    }

    /**
     * Test that no differences are reported as such.
     */
    public function testNoDifference()
    {
        $diff_image = null;
        $keeper = $this->prophesize(Keeper::class);
        $keeper->get('kid1')->willReturn(file_get_contents(__DIR__ . '/../fixtures/a.png'))->shouldBeCalled();
        $keeper->store(Argument::any())->will(function ($image) use (&$diff_image) {
            $diff_image = $image;
            return 'diffkid';
        })->shouldBeCalled();

        $differ = new Differ($keeper->reveal());
        $diff = $differ->diff('kid1', 'kid1');
        $expected = new Diff('kid1', 'kid1', 'diffkid', 0);
        $this->assertEquals($expected, $diff);
        $this->assertNoDifference($diff_image, __DIR__ . '/../fixtures/a-a-diff.png');
    }

    protected function assertNoDifference($image, $fixture)
    {
        $imageFile = '/tmp/differ-test' . getmypid() . '.png';
        $diffFile = '/tmp/differ-test-diff' . getmypid() . '.png';
        file_put_contents($imageFile, $image);
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

        unlink($imageFile);
        unlink($diffFile);
        $pixelCount = trim($process->getErrorOutput());
        if (!preg_match('/^\d+$/', $pixelCount)) {
            throw new RuntimeException('Unexpected output from compare: ' . $pixelCount);
        }
        $this->assertFalse($pixelCount > 0, 'Difference in diff');
    }
}
