<?php

namespace Tests\Functional;

use Basko\Functional as f;

class IOTest extends BaseTest
{
    private function getFilePermission($file)
    {
        return (int)octdec(substr(sprintf('%o', fileperms($file)), -4));
    }

    private function getTargetFile()
    {
        return sys_get_temp_dir() . '/' . uniqid('test_', true) . '.php';
    }

    /**
     * @after
     */
    protected function tearDownTestFiles()
    {
        if (file_exists(sys_get_temp_dir() . '/unwritable')) {
            unlink(sys_get_temp_dir() . '/unwritable');
        }

        parent::tearDown();
    }

    public function testContentIsSavedToTheFile()
    {
        $targetFile = $this->getTargetFile();

        $fileIO666 = f\write_file(0666);
        $fileIO666AndPath = $fileIO666($targetFile);

        $io = $fileIO666AndPath('some data');
        $io()->match(
            [$this, 'assertTrue'],
            f\N
        );

        self::assertSame('some data', file_get_contents($targetFile));
    }

    public function permission()
    {
        //    chmod  umask expected
        yield [0666, 022, 0644];
        yield [0644, 0, 0644];
        yield [0600, 022, 0600];
    }

    /**
     * @requires OS Darwin|Linux
     *
     * @dataProvider permission
     */
    public function testCorrectChmodIsSet($chmod, $umask, $expectedChmod)
    {
        $targetFile = $this->getTargetFile();

        $currentUmask = umask($umask);

        $io = f\write_file($chmod, $targetFile, 'content');
        $io()->match(
            [$this, 'assertTrue'],
            f\N
        );
        umask($currentUmask);

        self::assertSame($expectedChmod, $this->getFilePermission($targetFile));
    }

    public function testUnwritableDirThrowsException()
    {
        $dir = sys_get_temp_dir() . '/unwritable';
        touch($dir);

        $io = f\write_file(0666, $dir . '/test', 'foo');
        $io()->match(
            f\N,
            function ($errorMsg) {
                $this->assertTrue(f\str_starts_with('Could not create temporary file in directory', $errorMsg));
            }
        );
    }

    public function testRelativeDirectorySaves()
    {
        $targetFile = $this->getTargetFile();
        $targetFile = dirname($targetFile) . '/../' . basename(dirname($targetFile)) . '/' . basename($targetFile);

        $io = f\write_file(0666, $targetFile, 'some data');
        $io();

        self::assertSame('some data', file_get_contents($targetFile));
    }

    public function testReadFile()
    {
        $io = f\read_file(__DIR__ . '/name.txt')->map(f\lift_m('ucfirst'));

        $nameM = $io();

        $nameM->match(
            function ($name) {
                $this->assertEquals('Slava', $name);
            },
            function () {
                $this->fail('testReadFile test failed');
            }
        );

        $this->assertEquals('Slava', $nameM->extract());
    }

    public function testReadFileFail()
    {
        $io = f\read_file('/non-existed-file.txt');

        $io()->match(
            function () {
                $this->fail('testReadFileFail test failed');
            },
            f\partial([$this, 'assertEquals'], 'File "/non-existed-file.txt" does not exist')
        );
    }
}