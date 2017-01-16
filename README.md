# xml-validator
Allows validation of DOMDocument against multiple schemas contained in it.

## Known issues
* The schemas are extracted from all around the document and than combined as one to validate.
This way the hierarchy and where the actual schema is included is lost.
Can be fixed by validating each element which includes schema as a separate document.