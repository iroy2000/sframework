<?php

class Validate {
	/* Properties
	-----------------------------------------------*/
	protected static $error = '';
	
	/* Property Methods
	-----------------------------------------------*/
	/**
	 * Get Error
	 */
	public static function getError ()
	{
		return self::$error;
	}
		
	/* STRING VALIDATION
	-----------------------------------------------*/
	/**
	 * VALID EMAIL
	 */
	public static function validEmail( $value, $label = NULL )
	{
		$pattern = '/^([a-zA-Z0-9_\-])+(\.([a-zA-Z0-9_\-])+)*@((\[(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5])))\.(((([0-1])?([0-9])?[0-9])|(2[0-4][0-9])|(2[0-5][0-5]))\]))|((([a-zA-Z0-9])+(([\-])+([a-zA-Z0-9])+)*\.)+([a-zA-Z])+(([\-])+([a-zA-Z0-9])+)*))$/';
		if ( !preg_match( $pattern, $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is not a valid email address");
			return false;
		}
		else {	
			return true;
		}
	}

    /* STRING VALIDATION
	-----------------------------------------------*/
    /**
     * VALID login / password ...etc
     */
    public static function validAlphaNumeric( $value, $label = NULL ) {
        $pattern = '/^[a-zA-Z0-9_\-]+$/';
        if ( !preg_match( $pattern, $value ) ) {
            if ( $label )
                self::$error = self::errorString("<i>$label</i> only allows <strong>a-z A-Z 0-9 _ -</strong>");
            return false;
        }
        else {
            return true;
        }
    }
	
	public static function validTimeFormat( $value, $label = NULL ) {
	
		$pattern = '/^((0[0-9])|(1[0-2])):[0-5][0-9]\s(AM|PM)/i';
		if ( !preg_match( $pattern, $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is not a valid time format");
			return false;
		}
		else
			return true;		
	}	


	/**
	 * VALID IP
	 */
	public static function validIP( $value, $label = NULL )
	{
		$pattern = '/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/';
		if ( !preg_match( $pattern, $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is not a valid IP address.");
			return false;
		}
		else
			return true;
	}

	/**
	 * VALID PHONE NUMBER
	 */
	public static function validPhone( $value, $label = NULL )
	{
		$pattern = '/^([(][2-9]\d{2}[)][ ]?|[2-9]\d{2}[- \/.]?)[1-9]\d{2}[- .]?\d{4}$/';
		if ( !preg_match( $pattern, $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is not a valid US phone number.");
			return false;
		}
		else
			return true;
	}

	/**
	 * VALID ZIP CODE
	 */
	public static function validZip( $value, $label = NULL )
	{
		$pattern = '/^[0-9]{5}([- \/]?[0-9]{4})?$/';
		if ( !preg_match( $pattern, $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is not a valid zip code.");
			return false;
		}
		else
			return true;
	}

	/**
	 * VALID NUMBER
	 */
	public static function validNum( $value, $label = NULL )
	{
		if ( !is_numeric( $value ) )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> should be a number. You entered \"$value\".");
			return false;
		}
		else
			return true;
	}


	/**
	 * VALID DATE
	 */
	public static function validDate( $value, $label = NULL )
	{
		$value = strtotime($value);
		if ( $value == -1 || $value == false )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> should be a valid date.");
			return false;
		}
		else
			return true;
	}
	
	
	/**
	 * MIN LEN
	 */
	public static function minLen( $value, $label = NULL, $len = NULL )
	{
		if ( strlen( $value ) < $len )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> too short, must be at least $len characters. ".strlen( $value )." were entered.");

			return false;
		}
		else
			return true;
	}

	
	/**
	 * MAX LEN
	 */
	public static function maxLen( $value, $label = NULL, $len = NULL )
	{
		if ( strlen( $value ) > $len )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> too long, can't be longer than $len characters. ".strlen( $value )." were entered.");
			return false;
		}
		else
			return true;
	}

	/**
	 * NOT EMPTY
	 */
	public static function notEmpty( $value, $label = NULL )
	{
		if ( strlen( $value ) === 0 )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> can't be empty.");
			return false;
		}
		else
			return true;
	}
	
	/**
	 * REQUIRED
	 */
	public static function required( $value, $label = NULL )
	{
		if (is_array($value))
		{ // file request
			foreach ($value as $k=>$v)
			{
				if (strlen($v)===0)
				{
					self::$error = self::errorString("<i>$label</i> is required.");
					return false;
				}	
			}
		}
		else if ( strlen( $value ) === 0 )
		{
			if ( $label )
				self::$error = self::errorString("<i>$label</i> is required.");
			return false;
		}
		else
			return true;
	}	

	/**
	 * EQUAL
	 * @param array $params
	 * 		'value2' = string
	 * 		'label2' = string
	 */
	public static function equal( $value, $label = NULL, array $params = NULL )
	{
		$value2 = NULL;
		$label2 = NULL;

		if (is_array($params))
		{
			$value2 = (isset($params['value2'])) ? $params['value2'] : $value2;
			$label2 = (isset($params['label2'])) ? $params['label2'] : $label2;
		}
		
		if ( $value != $value2 )
		{
			if ( $label )
			{
				if(!$params['passwordFlag'])
				{
					self::$error = self::errorString("<i>$label</i> and <i>$label2</i> don't match.");
				} else {
					self::$error = self::errorString("<i>Passwords don't match.</i>");
				}
			}
			return false;
		}
		else
			return true;
	}

	/**
	 * CONTAINS SPACE CHARACTER
	 */
	public static function containsSpaceChar( $value, $label = NULL )
	{
		$space_chars = array( " ", "\t", "\r", "\n" );
		foreach ( $space_chars as $v )
		{
			if ( stristr( $value, $v ) )
			{
				if ( $label )
				self::$error = self::errorString("<i>$label</i> can't contain any space characters.");
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Error String
	 * @static
	 * @param void
	 * @return string
	 */	
	public static function errorString($string)
	{
		return '<span class="error">'.$string."</span>\n";
	}	
	
}
?>