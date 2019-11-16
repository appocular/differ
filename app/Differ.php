<?php

declare(strict_types=1);

namespace Appocular\Differ;

use Appocular\Clients\Contracts\Keeper;
use RuntimeException;
use Symfony\Component\Process\Process;

class Differ
{
    /**
     * Keeper.
     *
     * @var \Appocular\Clients\Contracts\Keeper
     */
    protected $keeper;

    public function __construct(Keeper $keeper)
    {
        $this->keeper = $keeper;
    }

    public function diff(string $imageUrl, string $baselineUrl): Diff
    {
        $different = false;
        $diffUrl = '';
        $image = $this->keeper->get($imageUrl);
        $baseline = $this->keeper->get($baselineUrl);

        // Temporary work directory. Stackoverflow has a lot of complicated
        // solutions on how to create a temporary directory, all with
        // problems. But as we're not aiming for thread safety, we can rely on
        // constructing a temp dir using the process id, and be reasonably
        // safe.
        $dir = \sys_get_temp_dir() . '/differ-' . \getmypid();

        if (!\mkdir($dir)) {
            throw new RuntimeException("Could not create temporary directory.");
        }

        $oldCwd = \getcwd();
        \chdir($dir);

        try {
            $this->writeFile('image.png', $image);
            $this->writeFile('baseline.png', $baseline);

            $process = new Process([
                'compare',
                '-dissimilarity-threshold',
                '1',
                '-fuzz',
                '4%',
                '-metric',
                'AE',
                '-highlight-color',
                'blue',
                'image.png',
                'baseline.png',
                'diff.png',
            ]);

            $process->setTimeout(600);
            $process->run();

            $pixelCount = \trim($process->getErrorOutput());
            // compare might return a number in scientific notation.

            if (!\preg_match('/^[\d.e+]+$/', $pixelCount)) {
                throw new RuntimeException('Unexpected output from compare: ' . $pixelCount);
            }

            $different = $pixelCount > 0;

            // Extract only diff from diff image.
            (new Process([
                'convert',
                'diff.png',
                '-matte',
                '(',
                '+clone',
                '-fuzz',
                '1%',
                '-transparent',
                'blue',
                ')',
                '-compose',
                'DstOut',
                '-composite',
                'diff.png',
            ]))->setTimeout(600)->mustRun();

            $diffUrl = $this->keeper->store(\file_get_contents('diff.png'));
        } finally {
            \chdir($oldCwd);
            // This wont clean up in all error cases, but /tmp will be cleaned
            // from time to time.
            $this->removeDir($dir);
        }

        return new Diff($imageUrl, $baselineUrl, $diffUrl, $different);
    }

    /**
     * Remove temporary directory.
     *
     * No error handling, as if we're at the point where we can't delete our
     * own files or directories, things are really in a bad shape.
     */
    protected function removeDir(string $dir): void
    {
        $files = \array_diff(\scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            if (\is_dir("$dir/$file")) {
                $this->removeDir("$dir/$file");
            } else {
                \unlink("$dir/$file");
            }
        }

        \rmdir($dir);
    }

    /**
     * Write file.
     *
     * Throws an exception in case of error.
     */
    protected function writeFile(string $filename, string $data): void
    {
        if (\file_put_contents($filename, $data) === false) {
            throw new RuntimeException('Could not write ' . $filename);
        }
    }
}
