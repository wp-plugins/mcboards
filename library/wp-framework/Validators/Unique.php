<?php

/**
 * Check if input is unique
 * 
 * @author 		Sitebase (Wim Mostmans)
 * @copyright  	Copyright (c) 2011, Sitebase (http://www.sitebase.be)
 * @license    	http://www.opensource.org/licenses/bsd-license.php    BSD License
 */
class WpFramework_Validators_Unique extends WpFramework_Validators_Abstract
{
	
	private $_values = null;
	
	/**
	 * Constructor
	 *
	 * @param string $message
	 */
	public function __construct($message, $values = array()){
		$this->failMessage = $message;
		$this->_values = $values;
	}
	
	/**
	 * Validate this element
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public function validate($value) {
		$exists = in_array($value, $this->_values);
		
		return !$exists;
	}
	
}