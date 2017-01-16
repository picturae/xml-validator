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

namespace Picturae\XmlValidator;

use Picturae\XmlValidator\Exception;

/**
 * Validates dom document by using all schemas found in it.
 * While DOMDocument::schemaValidate can validate only against one of the schemas.
 * 
 * Inspired by Symfony\Component\DependencyInjection\Loader\XmlFileLoader::validateSchema
 * @example
 * <code>
 * SchemaValidate::validate($document);
 * </code>
 * @package Picturae\XmlValidator
 */
class SchemaValidate
{
    /**
     * Validates the document by using all schemas found in it.
     * @param \DOMDocument $document
     * @param array &$errors Outputs the reported errors if not valid
     */
    public static function validate(\DOMDocument $document, &$errors = null)
    {
        $allSchemas = self::extractAllSchemas($document);
        
        // @TODO By extracting all schemas we do not keep in mind the hierarchy
        // and for which specific elements the schemas are included.
        // This has to be fixed by validating element by element.
        
        if (empty($allSchemas)) {
            throw new Exception(
                'No schemas found in the xml.'
            );
        }
        
        // Create a combined schema
        $imports = [];
        foreach ($allSchemas as $namespace => $location) {
            $imports[] = sprintf('<xsd:import namespace="%s" schemaLocation="%s" />', $namespace, $location);
        }
        
        $combinedSchema = '
            <xsd:schema
                xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                elementFormDefault="qualified">
                <xsd:import namespace="http://www.w3.org/XML/1998/namespace"/>
                ' . implode(PHP_EOL, $imports) . '
            </xsd:schema>
        ';
        
        return self::safeSchemaValidateSource($document, $combinedSchema, $errors);
    }
    
    /**
     * Extract all xsd paths from all xsi:schemaLocations
     * @param \DOMDocument $document
     * @return array [[namespace1 => location1], [namespace2 => location2]]
     */
    protected static function extractAllSchemas(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $schemaLocations = $xpath->query('//*[@xsi:schemaLocation]');
        
        // "namespace1 location1 namespace2 location2"
        $locationRegex = '/'
            . '(?P<namespace>[^\s]+)'
            . '\s+'
            . '(?P<location>[^\s]+)'
            . '/';
        
        $allSchemas = [];
        foreach ($schemaLocations as $element) {
            // We have "namespace1 xsd1 namespace2 xsd2", but we want [namespace1 => xsd1, namespace2 => xsd2]
            $schemaLocation = $element->getAttribute('xsi:schemaLocation');
            
            if (preg_match_all($locationRegex, $schemaLocation, $matches)) {
                $schemas = array_combine($matches['namespace'], $matches['location']);
                $allSchemas = array_merge($allSchemas, $schemas);
            } else {
                throw new Exception(
                    'Unable to parse the value of schemaLocation. Expected "namespace1 xsd1 namespace2 xsd2" '
                    . 'but found "' . $schemaLocation . '"'
                );
            }
        }
        
        return array_unique($allSchemas);
    }
    
    /**
     * Encapsulates document schema validate to not throw error but to return errors.
     * @param \DOMDocument $document
     * @param string $schema
     * @param array $errors Outputs the reported errors and warnings
     * @return bool
     */
    protected static function safeSchemaValidateSource(\DOMDocument $document, $schema, &$errors = null)
    {
        // Enable handling of errors and remember what was the initial value of the flag.
        $initialErrorsFlag = libxml_use_internal_errors(true);
        
        $isValid = $document->schemaValidateSource($schema);
        
        // Collect the errors and warning if any.
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        // Return the use internal errors to what it was.
        libxml_use_internal_errors($initialErrorsFlag);
        
        return $isValid;
    }
}
