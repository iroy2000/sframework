<?php

/*

Usage:

  // Create a session object using file access
  $session = new PrivateSession()->setSavePath('/path/to/save')->set_handler();
  
  // Create a session object using db access
  $ps = new PrivateSession()->setTableName('table_name')->set_handler($pdo_object);

  // You can using session just like normal
  session_start();
  echo $_SESSION['foo'] = 'Hi there!';

Please create this table if you want to use database as session handler
CREATE TABLE PrivateSession (
      id varchar(32) NOT NULL,
      access int(10) unsigned,
      data text,
      PRIMARY KEY (id)
)	  

*/

class PrivateSession {
	
	private $_db = null;
	private $_saveToDB = false;
	private $_savePath = null;
	private $_tableName = 'HttpSession';
	
	public function __construct() {
		 $this->_savePath = session_save_path();
		 return $this;
	}
	
	public function setHandler($db = null) {
		
		if($db != null && is_a($db, "PDO")) {
				$this->_db = $db;	
				$postfix = 'DB';
				$this->_saveToDB = true;
				
		} else {
			$postfix = 'File';
			$this->_saveToDB = false;
		}
		
		session_set_save_handler(
		  array('PrivateSession', 'open'    . $postfix),
		  array('PrivateSession', 'close'   . $postfix),
		  array('PrivateSession', 'read'    . $postfix),
		  array('PrivateSession', 'write'   . $postfix),
		  array('PrivateSession', 'destroy' . $postfix),
		  array('PrivateSession', 'clean'   . $postfix)
		);		
	}
	
	public function setSavePath($path = null) {
		if($path != null)
			$this->_savePath = $path;
			
		return $this;	
	}
	
	public function setTableName($name = null) {
		if($name != null)
			$this->_tableName = $name;
			
		return $this;	
	}	
	
  private function openFile($save_path, $session_name)
  {
    if (!is_dir($this->_savePath))
    {
      mkdir($this->_savePath);
    }
    return true;
  }	
  
  private function closeFile()
  {
    return true;
  }  
	
  private function readFile($id)
  {
    $sess_file = $this->_savePath . '/sess_' . $id;
    if ($fp = @fopen($sess_file, 'r'))
    {
      $sess_data = fread($fp, filesize($sess_file) + 1000);
      fclose($fp);
      return $sess_data;
    }
    return '';
  }	
  
  private function writeFile($id, $sess_data)
  {
    $sess_file = $this->_savePath . '/sess_' . $id;
    if ($fp = @fopen($sess_file, 'w'))
    {
      $writed = fwrite($fp, $sess_data);
      fclose($fp);
      return $writed;
    }
    return false;
  }  
  
  private function destroyFile($id)
  {
    $sess_file = $this->_savePath . '/sess_' . $id;
    return @unlink($sess_file);
  }  
  
  private function cleanFile($lifetime)
  {
    if ($dir = opendir($this->_savePath))
    {
      while ($fname = readdir($dir))
      {
        if (substr($fname, 0, 1) == '.')
        {
          continue;
        }
        clearstatcache();
        $modified = filemtime($this->_savePath . '/' . $fname);
        if ((time() - $modified) > $lifetime)
        {
          @unlink($this->_savePath . $fname);
        }

      }
      closedir($dir);
    }
    return true;
  } 
  
  /*
  	This is database part, not implemented yet ~
  */
  
  private function openDB($save_path, $session_name)
  {
    // create PDO object
  }  
  
  private function closeDB()
  {
    return true;
  }   
  
  private function readDB($id)
  {
    $sql = "SELECT data FROM $this->_tableName WHERE id= ?";
	
	// return PDO object
  }  
  
  private function writeDB($id, $sess_data)
  {
    $sql = "REPLACE INTO $this->_tableName VALUES (?, ?, ?)";  // '$id', '$access', '$sess_data'
    
	// return PDO object
	
  }  
  
  private function destroyDB($id)
  {

    $sql = "DELETE FROM $this->_tableName WHERE id= ?";
	
	// return PDO object
  }  
  
  private function cleanDB($lifetime)
  {
    $old = time() - $lifetime;

    $sql = "DELETE FROM $this->_tableName WHERE access < ? ";  // old
    
	// return PDO object
  }  
}