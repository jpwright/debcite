<?php

 /***************************************************
	  DocStoc Authentication Test Script : PHP

	  In order to use the authenticateUser function the username and password must first be encrypted using the RIJNDAEL format and then base64_encoded. 

	  NOTES:
	  - The default padding for CBC mode RIJNDAEL_128 encryption with PHP is 0. For the DocStoc API 
the padding is not explicitly set to 0 in order to be flexible and this means that the we have to 
match the .NET default of PKCS7 in PHP. 
	  ****************************************************/

	  /**
	  * Define Constants
	  */
	  define('APP_KEY','');
	  define('AUTH_KEY','f93e9a98e19849b9');
	  define('IV','b0e4abd99da606f2');
	  define('NUSOAP_PATH','');
	  define('USERNAME','debcite');
	  define('PASSWORD','debcite');

	  /**
	  * encrypt_string
	  * Primary function for encrypting the data to be passed.
	  * Includes the pkcs7 padding in the result value. 
	  * Also base64_encodes the value to be returned. 
	  * var $input string
	  */
	  function encrypt_string($input)
	  {
	  $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	  $input = pkcs7_pad($input, $size);
	    
	  $key = AUTH_KEY;
	  $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	  $iv = IV;
	  mcrypt_generic_init($td, $key, $iv);
	  $data = mcrypt_generic($td, $input);
	  mcrypt_generic_deinit($td);
	  mcrypt_module_close($td);
	  $data = base64_encode($data);
	  return $data;
	  }

	  /**
	  * pkcs7_pad
	  * Function for adding the pkcs7 padding to the encrypted value
	  * var $dat string
	  * var $block string
	  */
	  function pkcs7_pad($input, $size) {
	  $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	  $len = strlen($input);
	  $padding = $block - ($len % $block);
	  $input .= str_repeat(chr($padding),$padding);
	  return $input;
	  }

?>