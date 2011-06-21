<?php
/**
 * Logger
 * Logging Message
 * @author: Roy Yu
 */
class Logger {
	
	private static $_instance = null;
	protected $messages    = array();
	
	public static function getInstance() {
		if(self::$_instance != null)
			return self::$_instance;
			
		try {
			self::$_instance = new Logger();
			return self::$_instance;
		} catch (Exception $e) {
			return null;
		}	
	}
	
	public function __construct() {
		return $this;
	}

	/**
	 * log message to session or a file
	 * @param <string> $name  | name of the message
	 * @param <string> $message | contents of the message
	 * @param <string> $save | save it to file ?
	 * @return null
	 */
	public function logMessage($name = null, $message = null, $save = false) {
		
		if($name == null || $message == null) {
			return $message;
		}
		
		if(!isset($_SESSION[$name]) || !is_array($_SESSION[$name])) 
			$_SESSION[$name] = array();
		
		if(count($_SESSION[$name]) > 3) { 
			array_shift($_SESSION[$name]);
			array_push($_SESSION[$name], $message);			
		} else {
			array_push($_SESSION[$name], $message);	
		}
		
		if($save) {
			Util::write(Globals::getConfig()->save->log->fullpath, $message);
		}
	}

	// Get messages stack
	public function getMessageIterator($name) {
		if(count($_SESSION[$name]) > 0) {
			return new ArrayIterator($_SESSION[$name]);
		}
		
		return null;
	}
	
	// Fetch Previous Message, if no, show current Message
	public function getPreviousMessage($name) {
		
		$total = count($_SESSION[$name]);
		
		if($total > 0) {
			if($total == 1) 
				return $_SESSION[$name][0];

			return $_SESSION[$name][$total - 2];
		}
		
		return null;
	}	

	// delete messages
	public function destroyMessage($name) {
		unset($_SESSION[$name]);
	}

	// get most current message
	public function getCurrentMessage($name) {
		
		$total = count($_SESSION[$name]);
		
		if($total > 0) {
			return $_SESSION[$name][$total - 1];
		}
		
		return null;
	}	

	// save message to a file
	public static function logException(Exception $e) {
		try {
			Util::write(Globals::getConfig()->save->exception->fullpath, $e->getMessage());
		} catch(Exception $e) {
			// nothing
		}
	}	
}
?>