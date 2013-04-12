<?php

namespace Bwaine\FacebookTestUserClient\Filter;

class BooleanBodyParameterFilter {
    
    public static function filterBodyForTrueFalse($value) {
        
        // Standard casting wont work here as error codes or other body 
        // content would be cast as true.
        
        $strValue = (string)$value;
        
        return ($strValue == "true"); 
    }
    
}

