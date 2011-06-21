<?php
/**
 * User access layer class.
 *
 * This is a simple user access layer resource based on level group.
 *
 * 9/4/2008
 *
 * @copyright  2008 Tal Cohen
 * @author     Tal Cohen MSN: tal7814@hotmail.com
 * @version    CVS: $Id:$
 *
 * @example see example in external attached page example.php and example_extend.php
 *
 */

class SimpleAcl {

   /**
    * debug mode.
    *
    * @var boolean
    */
   public $debug = true;



   /**
    * Holds user access level, default=1
    *
    * 1 => Guest
    * 2 => Member
    * 3 => Staff
    * 4 => Publisher
    * 5 => Admin
    *
    * @var Integer
    */
   protected  $userLevel = 1;



   /**
    * Holds admin level number as using in your system. default=5
    *
    * @var integer admin level
    */
   public $adminLevel = 5;



   /**
    * Holds user ID number
    *
    * @var Integer
    */
   protected $userId = 0;



   /**
    * Resources array
    *
    * @var Array
    */
   protected $resources = array();



   /**
    * Set individual user access to resource
    *
    * @var Array
    */
   protected  $allowUser = array();



   /**
    * Set individual level access to resource
    *
    * @var Array
    */
   protected  $allowLevel = array();



   /**
    * Constractor
    *
    * @param Integer $userId
    * @param Integer $userLevel
    */
   function __construct($userId,$userLevel){

      $this->userId = $userId;

      $this->userLevel = $userLevel;
   }



   /**
    * Add a new resource
    *
    * @param String $resourceName
    * @param Integer $minimunAccessLevel
    */
   public function addResource($resourceName,$minimunAccessLevel = 0){
      if (is_array($resourceName)) {
         foreach ($resourceName as $key=>$value){
            //set default minimunAccessLevel if empty
            if (empty($key)) {
               $this->resources[$value] = $minimunAccessLevel;
            }
            else {
               $this->resources[$key] = $value;
            }
         }
      }
      else {
         $this->resources[$resourceName] = $minimunAccessLevel;
      }
   }



   /**
    * Allow current user access an resource not depending on $minimunAccessLevel
    *
    * @param $resourceName
    * @param Boolean $bool
    */
   public function allowUser($resourceName,$bool=true){
      /*if ($this->debug==true) {
            throw new Exception("Resource is not defined. please add this resource.");
         }*/
      $this->allowUser[$resourceName] = $bool;
   }


   /**
    * Allow user level to access an resource not depending on $minimunAccessLevel
    *
    * @param $resourceName
    * @param unknown_type $bool
    */
   public function allowLevel($resourceName,$bool=true,$userLevel=''){
      if (empty($userLevel)) {
         $userLevel = $this->userLevel;
      }
      $this->allowLevel[$resourceName] = array($bool,$userLevel);
   }



   /**
    * true if admin, false if not
    *
    * @return boolean
    */
   public function isAdmin(){
      if ($this->adminLevel == $this->userLevel) {
      	return true;
      }
      else return false;
   }




   /**
    * Check if user is allowed to use resource
    *
    * @param String $resourceName
    * @return Boolean
    */
   public function isValid($resourceName){
      if (empty($this->resources) && empty($this->allowUser) && empty($this->allowLevel)) {
         if ($this->debug==true) {
            throw new Exception("Resource is not defined. please add this resource.");
         }
      }
      if ($this->isAdmin()) {
         return true;
      }
      //check for individual allowUser() access
      if (isset($this->allowUser[$resourceName]) && $this->allowUser[$resourceName] == true) {
         return true;
      }
      //check for individual user level allowLevel() access
      if (isset($this->allowLevel[$resourceName]) && $this->allowLevel[$resourceName][0] == true && $this->allowLevel[$resourceName][1] == $this->userLevel) {
         return true;
      }
      //check for addResource() access
      if (isset($this->resources[$resourceName])) {
         if ($this->userLevel >= $this->resources[$resourceName] && $this->resources[$resourceName] != 0 ) {
            return true;
         }
         else return false;
      } else return false;
   }
}
?>