<?php

declare(strict_types=1);

namespace CedricZiel\TwigLoaderFlysystem\Test;

use CedricZiel\TwigLoaderFlysystem\FlysystemLoader;
use League\Flysystem\AdapterInterface;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use League\Flysystem\Handler;
use PHPUnit\Framework\TestCase;

/**
 * @package CedricZiel\TwigLoaderFlysystem\Test
 */
class FlysystemLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function loaderCanLoadTemplatesByPath()
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $loader->getSourceContext('test/Object.twig');
    }

    /**
     * @expectedException \Twig\Error\LoaderError
     * @test
     */
    public function throwsLoaderErrorWhenTemplateNotFound()
    {
        $this->expectException('Twig\Error\LoaderError');
        $this->expectExceptionMessage('Template could not be found on the given filesystem');

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $loader = new FlysystemLoader($filesystem);

        $loader->getSourceContext('test/Object.twig');
    }

    public function testErrorCanBeExpected(): void
    {
        $this->expectError();

        // Optionally test that the message is equal to a string
        $this->expectErrorMessage('foo');

        // Or optionally test that the message matches a regular expression
        $this->expectErrorMessageMatches('/foo/');

        \trigger_error('foo', \E_USER_ERROR);
    }

    /**
     * @test
     */
    public function canCreateCacheKey()
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $cacheKey = 'test/Object.twig';
        $this->assertEquals($cacheKey, $loader->getCacheKey($cacheKey));
    }

    /**
     * @test
     */
    public function canDetermineIfATemplateIsStillFresh()
    {
        $templateFile = $this->getMockBuilder(File::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);
        $templateFile
            ->method('getTimestamp')
            ->willReturn(1233);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $templateFile = 'test/Object.twig';
        $this->assertTrue($loader->isFresh($templateFile, 1234));
    }

    /**
     * @test
     */
    public function aFilesystemPrefixCanBeUsed()
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->with('templates/test/Object.twig')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem, 'templates');

        $loader->getSourceContext('test/Object.twig');
    }
}
