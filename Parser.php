<?php

namespace Naroga\JUnitParser;

use Naroga\JUnitParser\Exception\InvalidFormatException;
use Naroga\JUnitParser\Exception\InvalidReportException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Parser
 * @package Naroga\JUnitParser
 */
class Parser
{
    /**
     * @var array Supported formats.
     */
    public static $SUPPORTED_FORMATS = ['html', 'text'];

    /**
     * @var array All loaded reports.
     */
    protected $reports = [];

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * Class constructor.
     *
     * @param \Twig_Environment $twig A twig instance.
     * @param string $report The report content (XML).
     * @param string $charset The character encoding in the XML.
     * @throws InvalidReportException
     */
    public function __construct(\Twig_Environment $twig, $report = null, $charset = 'UTF-8')
    {
        $this->twig = $twig;

        if (!empty($report)) {
            if (is_readable($report)) {
                $this->addFileContent($report, $charset);
            } else {
                $this->addXmlContent($report, $charset);
            }
        }
    }

    /**
     * Adds a XML content to the report.
     *
     * @param string $xml The XML content. Use file_get_contents($path) to get the XML text from a file.
     * @param string $charset The character encoding in the XML.
     * @return Parser The parser.
     * @throws InvalidReportException
     */
    public function addXmlContent($xml, $charset = 'UTF-8')
    {
        if (self::isStringValidXml($xml)) {
            $crawler = new Crawler();
            $crawler->addXmlContent($xml, $charset);
            $this->reports[] = $crawler;
            return $this;
        } else {
            throw new InvalidReportException("The report is not a valid XML.");
        }
    }

    /**
     * Adds a XML content to the report from a file path.
     *
     * @param string $path File path.
     * @param string $charset The character encoding in the XML.
     * @return Parser The parser.
     * @throws InvalidReportException
     */
    public function addFileContent($path, $charset = 'UTF-8')
    {
        if (!is_readable($path)) {
            throw new InvalidReportException("The report is not a readable path.");
        }
        $this->addXmlContent(file_get_contents($path), $charset);
        return $this;
    }

    /**
     * Gets the report.
     *
     * @return Crawler
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Parses all JUnit reports and provides a single summarized report.
     *
     * @param string $format Format of the generated summary (html|text)
     * @return string Returns the report in the specified $format.
     * @throws InvalidFormatException
     * @throws InvalidReportException
     */
    public function parse($format = 'html')
    {

        //Exits early if the provided format is not supported by the parser.
        if (!in_array($format, self::$SUPPORTED_FORMATS)) {
            throw new InvalidFormatException(
                'The provided $format argument is not available. ' .
                'Available formats: ' . implode("|", self::$SUPPORTED_FORMATS) . '.'
            );
        }

        //Exits early if the report is not set or is not a Symfony Crawler instance.
        if (empty($this->reports)) {
            throw new InvalidReportException("Nothing to parse.");
        }

        $stats = [
            'failures' => 0,
            'errors' => 0,
            'skipped' => 0,
            'tests' => 0,
            'time' => 0
        ];

        /** @var Crawler $report */
        foreach ($this->reports as $report) {
            $report->filter('testsuite')->each(function ($node, $i) use (&$stats) {
                foreach (array_keys($stats) as $key) {
                    /** @var $node Crawler */
                    $stats[$key] += $node->attr($key);
                }
            });
        }

        return $this->twig->render(__DIR__ . '/Resources/view/report.' . $format . '.twig', $stats);
    }

    /**
     * Takes a XML string and returns a boolean result where valid XML returns true.
     *
     * @param string $xml The XML string.
     * @return bool If the XML is valid.
     */
    public static function isStringValidXml($xml)
    {
        //Exits early, if empty string.
        if (empty($xml)) {
            return false;
        }

        //Backups libxml configuration.
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        //Clear previous errors.
        libxml_clear_errors();

        //Disables libxml errors and loads the XML.
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        //Retrieves any errors in the process.
        $errors = libxml_get_errors();

        //Restores previous libxml configuration.
        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        return empty($errors);
    }
}
