<?
/** Перенаправление по страницам по списку 
 * 
 * @version 1.4
 * 
 * \spbitec\CRedirector::redirectFromFileXML('../../redirect.xml');
 * \spbitec\CRedirector::redirectFromFileXML(['../../redirect.xml','../../redirect.xml']);
 * 
 * Example: 
 * <?xml version="1.0"?>
	<data>
  		<rule from="^/tickets/abonement/(.*)/b/(.*)/" to="^/tickets/abonement/(.*)/b/(.*)/" code="301"/>
  		<rule from="^/tickets/abonement/(.*)/s/(.*)/" to="^/tickets/abonement/(.*)/small/(.*)/" code="301"/>
  		<rule from="#\/sync\/(.*)#" to="$1" code="301"/>
	</data>
 *  
 */

namespace spbitec;

class CRedirector{
   private static $debug=false;	//Тестирование
   private static $cache=true; 	//Кеширование правил в сессии

   /*----- Public --------------------- */
   
   public static function debug($value){    
      if ($value) self::$debug=true;      
   }   

   public static function debugEcho($message,$exit=null){    
      if (self::$debug){
         echo	"<pre>".$message."</pre>";    
         if ($exit) exit;
      }     
   }   

   public static function cache($value){    
      self::$cache=($value)?true:false;      
   }   

   public static function redirectFromFileXML($file){
   	$files=[];
   	if ($file && !is_array($file)){
      	$files[]=$file;
      }elseif($file && is_array($file)){
      	$files=$file;
      }else{
         throw new \Exception('CRedirector::redirectFromFileXML - File defined'."\n");
      }
      
      foreach($files as $file){
      	self::processFile($file);
      }
      
      self::debugEcho('CRedirector::redirectFromFileXML - Redirects complete',1);    
   }
   
   
 	/*----- Private --------------------- */
   
   private static function processFile($file){    
   	if (!file_exists($file)){
      	throw new \Exception('CRedirector::processFile - File not found '.$file);
      };

      self::debugEcho('CRedirector::processFile - Start redirect from file '.$file);             
      
      
      $xml=false;
      $xml_data=null;
   
      if (self::$cache){
      	if (session_status()==PHP_SESSION_DISABLED){
         	throw new \Exception('CRedirector::processFile - No session');
         }elseif (session_status()==PHP_SESSION_NONE){
         	throw new \Exception('CRedirector::processFile - No session add session_start(); before start redirect');
         }
         
         $lastCacheKey=md5('\spbitec\CRedirector::processFile'.$file);
         $cacheKey=md5('\spbitec\CRedirector::processFile'.$file.filemtime($file));
         
         if (!isset($_SESSION[$cacheKey])){          	        
         	if ($_SESSION[$_SESSION[$lastCacheKey]]){
            	unset($_SESSION[$_SESSION[$lastCacheKey]]);
            }
            $_SESSION[$lastCacheKey]=$cacheKey;
         }else{
            self::debugEcho('CRedirector::processFile - Used Cache');             
         	$xml_data = $_SESSION[$cacheKey];
         }                         
      }
      
      if ($xml_data===null){
      	$xml = new \SimpleXMLElement(file_get_contents($file)); 
			$xml_data=array();
         foreach($xml->rule as $rule){ 
         	$xml_data_item=array();
            $xml_data_item['from']=(string)$rule->attributes()['from'];
            $xml_data_item['to']=(string)$rule->attributes()['to'];               
            $xml_data_item['code']=(string)$rule->attributes()['code'];
            $xml_data[]=$xml_data_item;
         }
      }
      
       if (self::$cache){
       	$_SESSION[$cacheKey]=$xml_data;
       }      

 
      foreach($xml_data as $data){ 
         $from=$data['from'];
         $to=$data['to'];               
         $code=$data['code'];    
         $timestamp=$data['timestamp'];    

         if (!$from) {
            continue;         
         }
         
         $uri=$_SERVER['REQUEST_URI'];
         if (self::search($uri,$from)===1){	
            $replaceUri=self::replace($uri,$from,$to);
            self::redirect($replaceUri?$replaceUri:true,$code);
            break;
         }
      } 
      
      self::debugEcho("CRedirector::processFile - No redirect found in $file");    
   }   



   private static function search($uri,$from){
   	return preg_match($from,$uri);
   }
   
   private static function replace($uri,$from,$to){
   	$ret=preg_replace($from,$to,$uri);
      if ($ret && $ret!=$uri){
         return $ret;
      }
      return false;
   }

   private static function redirect($uri,$code=null){
      $code=$code?$code:'301';      
      if (self::$debug){
         self::debugEcho('CRedirector::redirect - Redirected to <b>'.$uri.'</b>; HTTP status code - <b>'.$code.'</b>');    
      }else{
         header("Status: $code Found");
         if ($uri){
            header("Location: $uri",true,$code);         
         }
      }
      exit;
   }

}
