<?php

namespace Naroga\JUnitParser\Tests;

use Naroga\JUnitParser\Parser;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ParserTest
 * @package Naroga\JUnitParser\Tests
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests if an exception is thrown for an invalid data format.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidFormatException
     */
    public function testThrowsExceptionOnInvalidFormat()
    {
        $parser = new Parser;
        $parser->parse('invalid-format');
    }

    /**
     * Tests if an exception is thrown if no report is passed to the parser.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnEmptyReport()
    {
        $parser = new Parser;
        $parser->parse('html');
    }

    /**
     * Tests if an exception is thrown if an invalid report (neither a XML nor a path)
     * is passed to the constructor.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnInvalidReport()
    {
        $parser = new Parser("This is an invalid string");
    }

    /**
     * Tests if the parser creates a valid DomCrawler from valid XML.
     */
    public function testConstructValidXML()
    {
        $parser = new Parser('<?xml version="1.0"?><test><name>Pedro</name></test>');
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $parser->getReport());

        $parser = new Parser(__DIR__ . '/../phpunit.xml');
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $parser->getReport());

        $parser = new Parser;
        $parser->addXmlContent('<?xml version="1.0"?><test><name>Pedro</name></test>');
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $parser->getReport());

        $parser = new Parser();
        $parser->addFileContent(__DIR__ . '/../phpunit.xml');
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Crawler', $parser->getReport());
    }

    /**
     * Tests if an exception is thrown if an invalid report path
     * is passed to the constructor.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnInvalidAddFileContent()
    {
        $parser = new Parser;
        $parser->addFileContent("Invalid XML");
    }

    /**
     * Tests if an exception is thrown if an invalid report string
     * is passed to the constructor.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnInvalidAddXmlContent()
    {
        $parser = new Parser;
        $parser->addXmlContent("Invalid XML");
    }
}
