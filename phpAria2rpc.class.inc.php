<?php
                                             
  /**
  * @package   phpAria2rpc
  * @version   See VERSION
  * @author    Viharm
  * @brief     Library to communicate with aria2 using json-RPC
  * @detail    Provides a simple interface to communicate with Aria2
  *            the popular download manager, giving the flexibility to run
  *            it as a daemon and use it via the RPC interface.
  *            See README.md for more.
  * @copyright Copyright (C) 2015, Viharm
  * @licence   Modified BSD (3-clause) license
  *            (see LICENCE or http://opensource.org/licenses/BSD-3-Clause)
  **/
  
  // for debugging, set $GLOBALS['bl_DebugSwitch'] = TRUE ;
  // $GLOBALS['bl_DebugSwitch'] = FALSE ;
  
  /* Include the debugging library */
  /* This can be commented out, as only one function in this library is utilised */
  /* There is a trap for this function not being defined, in case the library is not included */
  
  @include_once ( realpath (
    dirname(__FILE__) .
    DIRECTORY_SEPARATOR .
    'Lib' .
    DIRECTORY_SEPARATOR .
    'phpKhelper' .
    DIRECTORY_SEPARATOR .
    'phpKhelper.lib.inc.php'
  ) ) ;
  
  
  class phpAria2rpc {
    
    private $server ;
    private $ch ;
    
    function __construct (
      $server = array (
        'host'      => '127.0.0.1' ,
        'port'      => '6800' ,
        'rpcsecret' => NULL ,
        'secure'    => FALSE ,
        'cacert'    => NULL ,
        'rpcuser'   => NULL ,
        'rpcpass'   => NULL ,
        'proxy'     => array (
          'type' => NULL ,
          'host' => NULL ,
          'port' => NULL ,
          'user' => NULL ,
          'pass' => NULL
        )
      )
    ) {
      if (!function_exists('fn_Debug')) { function fn_Debug(){} }  // trap calls to debug if debug library is not loaded.
      
      fn_Debug ( 'Server information' , $server , array ( 'rpcsecret' , 'rpcpass' ) ) ;
      $this->server = $server ;
      fn_Debug ( 'transferred host string to private class variable, now applying default values' , $this->server , array ( 'rpcsecret' , 'rpcpass' ) ) ;
      if ( ! @array_key_exists ( 'host' , $this->server ) | @is_null($this->server['host']) ) {
        $this->server['host'] = '127.0.0.1' ;
      }
      if ( ! @array_key_exists ( 'port' , $this->server ) | @is_null($this->server['port']) ) {
        $this->server['port'] = 6800 ;
      }
      if ( ! @array_key_exists ( 'rpcsecret' , $this->server ) | @is_null($this->server['rpcsecret']) ) {
        $this->server['rpcsecret'] = NULL ;
      }
      if ( ! @array_key_exists ( 'secure' , $this->server ) | @is_null($this->server['secure']) ) {
        $this->server['secure'] = FALSE ;
      }
      if ( ! @array_key_exists ( 'cacert' , $this->server ) | @is_null($this->server['cacert']) ) {
        $this->server['cacert'] = NULL ;
      }
      if ( ! @array_key_exists ( 'rpcuser' , $this->server ) | @is_null($this->server['rpcuser']) ) {
        $this->server['rpcuser'] = NULL ;
      }
      if ( ! @array_key_exists ( 'rpcpass' , $this->server ) | @is_null($this->server['rpcpass']) ) {
        $this->server['rpcpass'] = NULL ;
      }
      if ( ! @array_key_exists ( 'proxy' , $this->server ) | @is_null($this->server['proxy']) ) {
        $this->server['proxy'] = NULL ;
      } else {
        if ( ! @array_key_exists ( 'type' , $this->server['proxy'] ) | @is_null($this->server['proxy']['type']) ) {
          $this->server['proxy']['type'] = NULL ;
        } else {
          switch($this->server['proxy']['type']) {
            case 'http' :
              $this->server['proxy']['type'] = CURLPROXY_HTTP ;
              break ;
            case 'socks5' :
              $this->server['proxy']['type'] = CURLPROXY_SOCKS5 ;
              break ;
            default :
              $this->server['proxy']['type'] = NULL ;
          }
        }
        if ( ! @array_key_exists ( 'host' , $this->server['proxy'] ) | @is_null($this->server['proxy']['host']) ) {
          $this->server['proxy']['host'] = NULL ;
        }
        if ( ! @array_key_exists ( 'port' , $this->server['proxy'] ) | @is_null($this->server['proxy']['port']) ) {
          $this->server['proxy']['port'] = NULL ;
        }
        if ( ! @array_key_exists ( 'user' , $this->server['proxy'] ) | @is_null($this->server['proxy']['user']) ) {
          $this->server['proxy']['user'] = NULL ;
        }
        if ( ! @array_key_exists ( 'pass' , $this->server['proxy'] ) | @is_null($this->server['proxy']['pass']) ) {
          $this->server['proxy']['pass'] = NULL ;
        }
      }
      fn_Debug ( 'Default values set' , $this->server , 'rpcpass' ) ;
      fn_Debug ( 'Checking if secure RPC connection is requested' , $this->server['secure'] ) ;
      switch ($this->server['secure']) {
        case TRUE :
          fn_Debug ( 'Secure RPC connection is requested; setting prefix for connection string' ) ;
          $connprefix = 'https://' ;
          $curlproto = CURLPROTO_HTTPS ;
          break ;
        default :
          fn_Debug ( 'Secure RPC connection is not requested; setting prefix for connection string' ) ;
          $connprefix = 'http://' ;
          $curlproto = CURLPROTO_HTTP ;
      }
      fn_Debug ( 'protocol selected for Connection prefix' , $connprefix ) ;
      fn_Debug ( 'protocol selected for cURL' , $curlproto ) ;
      fn_Debug ( 'connection prefix complete. Formulating connection string' ) ;
      $connstring = $connprefix . $this->server['host'] . '/jsonrpc' ;
      fn_Debug ( 'Connection string formulated, releasing prefix memory' , $connstring ) ;
      unset($connprefix) ;
      $this->ch = curl_init() ;
      fn_Debug ( 'initiated curl; analysing errors' , $this->ch ) ;
      fn_Debug ('error code' , curl_errno($this->ch) ) ;
      fn_Debug ('error message' , curl_error($this->ch) ) ;
      fn_Debug ( 'Checking if debugging is enabled' , $GLOBALS['bl_DebugSwitch'] ) ;
      if ($GLOBALS['bl_DebugSwitch']===TRUE) {
        $logfile = fopen ( 'phpAria2rpc.curl.log' , 'a+' ) ;
        fn_Debug ( 'Attempted to open log file' , $logfile ) ;
        $result = NULL ;
        fn_Debug ( 'initialised result buffer' , $result ) ;
        curl_setopt_array (
          $this->ch ,
          array (
            CURLOPT_STDERR   => $logfile ,
            CURLOPT_VERBOSE  => TRUE ,
            CURLOPT_CERTINFO => TRUE
          )
        ) ;
        fn_Debug ( 'Verbosity for curl options set; analysing errors ' , $result ) ;
        fn_Debug ( 'error code' , curl_errno($this->ch) ) ;
        fn_Debug ( 'error message' , curl_error($this->ch) ) ;
      }
      $curlinfo = curl_version() ;
      fn_Debug ( 'Extracted cURL version info' , $curlinfo ) ;
      fn_Debug ( 'Setting primary curl options' ) ;
      $result = NULL ;
      fn_Debug ( 'initialised result buffer' , $result ) ;
      $result = curl_setopt_array (
        $this->ch ,
        array (
          CURLOPT_POST              => TRUE ,
          CURLOPT_RETURNTRANSFER    => TRUE ,
          CURLOPT_HEADER            => FALSE ,
          CURLOPT_PROTOCOLS         => $curlproto ,
          CURLOPT_UNRESTRICTED_AUTH => FALSE ,
          CURLOPT_FOLLOWLOCATION    => FALSE ,
          CURLOPT_PORT              => $this->server['port'] ,
          CURLOPT_HTTPAUTH          => CURLAUTH_BASIC ,
          CURLOPT_USERPWD           => $this->server['rpcuser'] . ':' . $this->server['rpcpass'] ,
          CURLOPT_USERAGENT         => 'Mozilla/5.0 (' . $curlinfo['host'] . ') libcURL/' . $curlinfo['version'] . ' phpAria2rpc' ,
          CURLOPT_URL               => $connstring
        )
      ) ;
      fn_Debug ( 'Curl options set; analysing errors ' , $result ) ;
      fn_Debug ('error code' , curl_errno($this->ch) ) ;
      fn_Debug ('error message' , curl_error($this->ch) ) ;
      fn_Debug ('Freeing memory used to store cURL info' ,$curlinfo) ;
      unset($curlinfo) ;
      fn_Debug ( 'Checking if CA certificate has been provided' , $this->server['cacert'] ) ;
      if (!is_null($this->server['cacert'])) {
        $result = NULL ;
        fn_Debug ( 'initialised result buffer' , $result ) ;
        curl_setopt_array (
          $this->ch ,
          array (
            CURLOPT_CAINFO         => $this->server['cacert'] ,
            // CURLOPT_SSL_VERIFYPEER => FALSE , // WARNING: THIS OPTION IS INSECURE AND FOR TESTING ONLY
            // CURLOPT_SSL_VERIFYHOST => FALSE    // WARNING: THIS OPTION IS INSECURE AND FOR TESTING ONLY
          )
        ) ;
        fn_Debug ( 'CA cert for curl set; analysing errors ' , $result ) ;
        fn_Debug ( 'error code' , curl_errno($this->ch) ) ;
        fn_Debug ( 'error message' , curl_error($this->ch) ) ;
      }
      fn_Debug ( 'Checking curl proxy options' , @$this->server['proxy'] , 'pass' ) ;
      if ( ! is_null($this->server['proxy']) ){
        if ( !is_null($this->server['proxy']['host']) && !is_null($this->server['proxy']['port']) ) {
          fn_Debug ( 'Setting curl proxy options' ) ;
          $result = NULL ;
          fn_Debug ( 'initialised result buffer' , $result ) ;
          $result = curl_setopt_array (
            $this->ch ,
            array (
              CURLOPT_PROXYTYPE       => $this->server['proxy']['type'] ,
              CURLOPT_PROXY           => $this->server['proxy']['host'] ,
              CURLOPT_PROXYPORT       => $this->server['proxy']['port'] ,
              CURLOPT_HTTPPROXYTUNNEL => FALSE ,
              CURLOPT_PROXYAUTH       => CURLAUTH_BASIC ,
              CURLOPT_PROXYUSERPWD    => $this->server['proxy']['user'] . ':' . $this->server['proxy']['pass']
            )
          ) ;
          fn_Debug ( 'Curl proxy options set; analysing errors ' , $result ) ;
          fn_Debug ('error code' , curl_errno($this->ch) ) ;
          fn_Debug ('error message' , curl_error($this->ch) ) ;
          fn_Debug ( 'Checking curl proxy user' , @$this->server['proxy']['user'] ) ;
          if ( !is_null($this->server['proxy']['user']) ) {
            fn_Debug ( 'Non-null user; setting curl proxy login string' ) ;
            $proxylogin = NULL ; //first ensure you start with an empty string
            $proxylogin = $this->server['proxy']['user'] ;
            fn_Debug ( 'curl proxy login string set' , $proxylogin ) ;
            fn_Debug ( 'checking curl proxy password' , $this->server['proxy']['pass'] , '' ) ;
            if ( !is_null($this->server['proxy']['pass']) ) {
              fn_Debug ( 'Non-null password; setting curl proxy login string' ) ;
              $proxylogin .= ':' . $this->server['proxy']['pass'] ;
              fn_Debug ( 'curl proxy login string set' , $proxylogin  , '' ) ;
            } else {
              fn_Debug ( 'Null password; ignoring password in curl proxy login setting' ) ;
            }
            fn_Debug ( 'Non-null user; setting curl proxy user' ) ;
            $result = NULL ;
            fn_Debug ( 'initialised result buffer' , $result ) ;
            $result = curl_setopt ( $this->ch , CURLOPT_PROXYUSERPWD , $proxylogin ) ;
            fn_Debug ( 'Curl proxy login set; analysing errors ' , $result ) ;
            fn_Debug ('error code' , curl_errno($this->ch) ) ;
            fn_Debug ('error message' , curl_error($this->ch) ) ;
            fn_Debug ( 'Proxy login string used, releasing variable  memory' , $proxylogin ) ;
            unset($proxylogin) ;
          } else {
            fn_Debug ( 'Null user; ignoring curl proxy login setting' ) ;
          }
        } else {
          fn_Debug ( 'Null host/port in proxy settings array' , @$this->server['proxy'] , 'pass' ) ;
        }
      } else {
        fn_Debug ( 'Null proxy settings array' , @$this->server['proxy'] , 'pass' ) ;
      }
    }
    
    function __destruct() {
      fn_Debug ( 'closing connection' , $this->ch ) ;
      curl_close($this->ch) ;
      @fn_Debug ( 'Checking if a curl log file is still open' , $logfile ) ;
      if(@$logfile) {
        fn_Debug ('curl log file is still open, now closing') ;
        if(fclose($logfile)) {
          fn_Debug ('curl log file closed successfully') ;
        } else {
          fn_Debug ('failed to close curl log file') ;
        }
      } else {
        fn_Debug ( 'Log file pointer not found, ignoring' ) ;
      }
    }
    
    private function req($data) {
      fn_Debug ( 'formulating request via postfields' , $data ) ;
      curl_setopt ( $this->ch , CURLOPT_POSTFIELDS , $data ) ;
      fn_Debug ( 'postfields formulation error code' , curl_errno($this->ch) ) ;
      fn_Debug ( 'postfields formulation error message' , curl_error($this->ch) ) ;
      $result = NULL ;
      fn_Debug ( 'initialised result buffer' , $result ) ;
      $result = curl_exec($this->ch) ;
      fn_Debug ( 'request sent, analysing errors' , $result ) ;
      fn_Debug ( 'postfields formulation error code' , curl_errno($this->ch) ) ;
      fn_Debug ( 'postfields formulation error message' , curl_error($this->ch) ) ;
      
      return $result ;
    }
    
    function __call ( $name , $arg ) {
      fn_Debug ( 'function called' , $name ) ;
      fn_Debug ( 'arguments called' , $arg ) ;
      fn_Debug ( 'checking for RPC secret' , $this->server['rpcsecret'] , '' ) ;
      if ( ! $this->server['rpcsecret'] == NULL ) {
        fn_Debug ( 'Non-null RPC secret value, pre-pending to supplied arguments' , $this->server['rpcsecret'] , '' ) ;
        array_unshift (
          $arg ,
          'token:' . $this->server['rpcsecret']
        ) ;
      }
      $data = array (
        'jsonrpc' => '2.0' ,
        'id'      => '1' ,
        'method'  => 'aria2.' . $name ,
        'params'  => $arg
      ) ;
      fn_Debug ( 'array formulated' , $data , '0' ) ;
      $data = json_encode($data) ;
      fn_Debug ( 'array encoded to json, sending request' , $data ) ;
      $result = NULL ;
      fn_Debug ( 'initialised result buffer' , $result ) ;
      $result = $this->req($data) ;
      fn_Debug ( 'response received' , $result ) ;
      fn_Debug ( 'Checking for success' ) ;
      if ( $result === FALSE ) {
        fn_Debug ( 'curl failed' , $result ) ;
      } else {
        fn_Debug ( 'curl response valid, decoding json' , $result ) ;
        $result = json_decode ( $result , 1 ) ;
        fn_Debug ( 'decoded json, returning result' , $result ) ;
      }
      return $result ;
    }
    
  }
?>