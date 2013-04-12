<?php

namespace Bwaine\FacebookTestUserClient\Filter;

class AccessTokenFilter {
    
    public static function filterAccessToken($value) {
        
        $params = null;
        
        parse_str((string)$value, $params);
        
        return $params['access_token'];
    }
    
}

