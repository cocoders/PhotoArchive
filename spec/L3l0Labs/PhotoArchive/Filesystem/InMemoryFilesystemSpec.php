<?php

namespace spec\L3l0Labs\PhotoArchive\Filesystem;

use L3l0Labs\PhotoArchive\Filesystem\File\File;
use L3l0Labs\PhotoArchive\Filesystem\Filename;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InMemoryFilesystemSpec extends ObjectBehavior
{
    function it_is_filesystem()
    {
        $this->shouldHaveType('L3l0Labs\PhotoArchive\Filesystem\Filesystem');
    }

    function it_stores_files(File $file, Filename $name)
    {
        $name->path()->willReturn('/home/l3l0/test.jpg');
        $file->filename()->willReturn($name);
        $this->add($file)->shouldBe($this);
        $this->all()->shouldBe([$file]);
    }
}
