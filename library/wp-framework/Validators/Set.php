<?php

/**
 * Check if input is a number
 * 
 * @author 		Sitebase (Wim Mostmans)
 * @copyright  	Copyright (c) 2011, Sitebase (http://www.sitebase.be)
 * @license    	http://www.opensource.org/licenses/bsd-license.php    BSD License
 */
class WpFramework_Validators_Set extends WpFramework_Validators_Abstract
{
			
	private $_set;
	
	/**
	 * Constructor
	 *
	 * @param string $message
	 * @param int $min
	 * @param int $max
	 */
	public function __construct($message, $set = array() ){
		$this->failMessage 		= $message;
		$this->_set 			= $set;
	}
	
	/**
	 * Validate this element
	 *
	 * @access public
	 * @param int $var
	 * @return bool
	 */
	public function validate($var){
		return in_array($var, $this->_set);
	}
	
}
