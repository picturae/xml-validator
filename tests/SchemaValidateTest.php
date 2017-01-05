<?php

/*
 * This file is part of Picturae\Xml-Validator.
 *
 * Picturae\Xml-Validator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Picturae\Xml-Validator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Picturae\Xml-Validator.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Test\Picturae\XmlValidator;

use Picturae\XmlValidator\SchemaValidate;

class SchemaValidateTest extends \PHPUnit_Framework_TestCase
{
    public function testValidXml()
    {
        $document = new \DOMDocument();
        $document->loadXML(
            '
                <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
                    http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd
                    ">
                  <responseDate>2017-01-05T11:09:01Z</responseDate>
                  <request verb="Identify"/>
                  <Identify>
                    <repositoryName>testRepo</repositoryName>
                    <baseURL/>
                    <protocolVersion>2.0</protocolVersion>
                    <adminEmail>email@example.com</adminEmail>
                    <earliestDatestamp>2017-01-05T11:09:01Z</earliestDatestamp>
                    <deletedRecord>persistent</deletedRecord>
                    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
                    <compression>gzip</compression>
                    <description>
                      <eprints xmlns="http://www.openarchives.org/OAI/1.1/eprints" 
                               xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints
                               http://www.openarchives.org/OAI/1.1/eprints.xsd">
                                    <content>
                                        <URL>http://arXiv.org/arXiv_content.htm</URL>
                                    </content>
                                    <metadataPolicy>
                                        <text>Metadata can be used by commercial and non-commercial 
                                              service providers</text>
                                        <URL>http://arXiv.org/arXiv_metadata_use.htm</URL>
                                    </metadataPolicy>
                                    <dataPolicy>
                                        <text>Full content, i.e. preprints may not be harvested by robots</text>
                                    </dataPolicy>
                                    <submissionPolicy>
                                        <URL>http://arXiv.org/arXiv_submission.htm</URL>
                                    </submissionPolicy>
                                </eprints>
                    </description>
                  </Identify>
                </OAI-PMH>
            '
        );
        
        $this->assertTrue(SchemaValidate::validate($document));
    }
    
    public function testNotValidXml()
    {
        $document = new \DOMDocument();
        $document->loadXML(
            '
                <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
                    http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
                  <test>
                    <testNode>test</testNode>
                  </test>
                </OAI-PMH>
            '
        );
        
        $this->assertFalse(SchemaValidate::validate($document, $errors));
        $this->assertNotEmpty($errors);
    }
}
