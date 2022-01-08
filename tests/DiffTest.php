<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function App\Diff\gendiff;

use App\Acl\{
    ResourceUndefined,
};


require_once 'src/Acl/ResourceUndefined.php';

class DiffTest extends TestCase
{
    protected $fileFirst;
    protected $fileSecond;
    protected $fileFirstYaml;
    protected $fileSecondYaml;
    protected $result;

    protected function setUp(): Void
    {
        $this->fileFirst = file_get_contents(__DIR__ . "/fixtures/flat-list/json/var1/file1.json");
        $this->fileSecond = file_get_contents(__DIR__ . "/fixtures/flat-list/json/var1/file2.json");
        $this->result = file_get_contents(__DIR__ . "/fixtures/flat-list/json/var1/result.json");
    }

    public function testGendiff(): void
    {
        $this->assertEquals($this->result, gendiff($this->fileFirst, $this->fileSecond));
    }

    public function testResourceUndefined()
    {
        $this->expectException(ResourceUndefined::class);
        gendiff(null, null);
        gendiff();
    }
}
