<?php

namespace Appocular\Differ;

use Appocular\Differ\Diff;
use Appocular\Clients\Contracts\Keeper;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class Differ
{
    /**
     * Keeper.
     */
    protected $keeper;

    public function __construct(Keeper $keeper)
    {
        $this->keeper = $keeper;
    }

    public function diff(string $imageKid, string $baselineKid) : Diff
    {
        $different = false;
        $diffKid = '';
        $image = $this->keeper->get($imageKid);
        $baseline = $this->keeper->get($baselineKid);

        // Temporary work directory. Stackoverflow has a lot of complicated
        // solutions on how to create a temporary directory, all with
        // problems. But as we're not aiming for thread safety, we can rely on
        // constructing a temp dir using the process id, and be reasonably
        // safe.
        $dir = sys_get_temp_dir() . '/differ-' . getmypid();
        if (!mkdir($dir)) {
            throw new RuntimeException("Could not create temporary directory.");
        }

        $oldCwd = getcwd();
        chdir($dir);
        try {
            $this->writeFile('image.png', $image);
            $this->writeFile('baseline.png', $baseline);

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
                'image.png',
                'baseline.png',
                'diff.png',
            ]);

            $process->run();

            $pixelCount = trim($process->getErrorOutput());
            if (!preg_match('/^\d+$/', $pixelCount)) {
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
            ]))->mustRun();

            $diffKid = $this->keeper->store(file_get_contents('diff.png'));
        } finally {
            chdir($oldCwd);
            // This wont clean up in all error cases, but /tmp will be cleaned
            // from time to time.
            $this->removeDir($dir);
        }

        return new Diff($imageKid, $baselineKid, $diffKid, $different);
    }

    /**
     * Remove temporary directory.
     *
     * No error handling, as if we're at the point where we can't delete our
     * own files or directories, things are really in a bad shape.
     */
    protected function removeDir($dir) : void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $this->removeDir("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        rmdir($dir);
    }

    /**
     * Write file.
     *
     * Throws an exception in case of error.
     */
    protected function writeFile($filename, $data)
    {
        if (file_put_contents($filename, $data) === false) {
            throw RuntimeException('Could not write ' . $filename);
        }
    }
}
