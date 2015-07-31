Naroga\JUnitParser
==================

1) Installing
-------------

To install this bundle, use composer:

    composer require naroga/junit-parser
    
Alternatively you can add the following to your `composer.json`:

    "require" : {
        "naroga/junit-parser": "dev-master"
    }

Remember to run `composer update` afterwards.

2) Usage
--------

Using this bundle is pretty straightforward. Get a JUnit report and tell the parser to parse it.

    use Naroga\JUnitParser\Parser;
    
    $parser = new Parser($FILE_PATH_OR_XML_CONTENT);
    $parser->parse('html'); //Returns HTML result.
    $parser->parse('text'); //Returns a plaintext result.
    
You can also parse multiple xmls to the same report:

    $parser = new Parser;
    $parser->addXmlContent($report1);
    $parser->addXmlContent($report2);
    $parser->addFileContent($file_with_report3);
    $parse->('html'); //Returns a single HTML result, with the sum of all reports.
    
3) Extending the templates
--------------------------

TBD.