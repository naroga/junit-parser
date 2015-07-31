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
     * Returns a \Twig_Environment stub.
     *
     * @return mixed
     */
    protected function createTwig()
    {
        $twig = $this->getMockBuilder('Twig_Environment')->getMock();
        $twig
            ->method('render')
            ->will($this->returnCallback(function ($template, $stats) {
                $data =
"Report results:<br />
<br />
Errors: " . $stats['errors'] . "<br />
Failures: " . $stats['failures'] . "<br />
Skipped: " . $stats['skipped'] . "<br />
Tests: " . $stats['tests'] . "<br />
Time: " . $stats['time'];
                return $data;
            }));

        return $twig;
    }

    /**
     * Tests if an exception is thrown for an invalid data format.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidFormatException
     */
    public function testThrowsExceptionOnInvalidFormat()
    {
        $parser = new Parser($this->createTwig());
        $parser->parse('invalid-format');
    }

    /**
     * Tests if an exception is thrown if no report is passed to the parser.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnEmptyReport()
    {
        $parser = new Parser($this->createTwig());
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
        $parser = new Parser($this->createTwig(), "This is an invalid string");
    }

    /**
     * Tests if the parser creates a valid DomCrawler from valid XML.
     */
    public function testConstructValidXML()
    {
        $parser = new Parser($this->createTwig(), '<?xml version="1.0"?><test><name>Pedro</name></test>');
        $this->assertArrayHasKey('0', $parser->getReports());

        $parser = new Parser($this->createTwig(), __DIR__ . '/../phpunit.xml');
        $this->assertArrayHasKey('0', $parser->getReports());

        $parser = new Parser($this->createTwig());
        $parser->addXmlContent('<?xml version="1.0"?><test><name>Pedro</name></test>');
        $this->assertArrayHasKey('0', $parser->getReports());

        $parser = new Parser($this->createTwig());
        $parser->addFileContent(__DIR__ . '/../phpunit.xml');
        $this->assertArrayHasKey('0', $parser->getReports());
    }

    /**
     * Tests if an exception is thrown if an invalid report path
     * is passed to the constructor.
     *
     * @expectedException \Naroga\JUnitParser\Exception\InvalidReportException
     */
    public function testThrowsExceptionOnInvalidAddFileContent()
    {
        $parser = new Parser($this->createTwig());
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
        $parser = new Parser($this->createTwig());
        $parser->addXmlContent("Invalid XML");
    }

    public function testValidParsing()
    {
        $parser = new Parser($this->createTwig(), __DIR__ . '/valid-junit-sample1.xml');
        $result = $parser->parse();
        $expectedResult = <<<EOF
Report results:<br />
<br />
Errors: 0<br />
Failures: 1<br />
Skipped: 1<br />
Tests: 3<br />
Time: 0.006
EOF;
        $this->assertEquals($expectedResult, $result);

    }
}
