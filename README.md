# phpAria2rpc

[![Version][mg_BadgeVersion]][ln_ReleaseLatest]
[![Issues][mg_BadgeIssues]][ln_Issues]
[![Language][mg_BadgeCodeLang]][ln_CodeLang]
[![License][mg_BadgeLicense]][ln_License]

Library to communicate with aria2 using json-RPC.

Based on *[shiny](https://github.com/shiny)*'s *[php-aria2](https://github.com/shiny/php-aria2)*.


## Install


### Pre-requisites

* PHP 5+ with cURL support
* Standard web framework (web server, etc.)
* Aria2 running in daemon mode either on the same host or on a different known host


### Download


#### Archive

Get the release archives from [downloads][ln_ReleaseLatest]


#### Clone

Clone repository.

```
git clone --recurse-submodules \
https://github.com/viharm/phpAria2rpc.git
```

Remember to clone recursively (`--recurse-submodules`) to ensure cloning the submodules.


### Deploy

Save the downloaded directory structure in your choice of path within your application (plugins, includes, etc.)


## Configure

No specific configuration required yet.


## Usage


### Run Aria2 in daemon mode

There are several recommended options for configuring *Aria2* however the minimum necessary to get started are...

* `--enable-rpc`
* `--rpc-allow-origin-all`
* `-c`
* `-D`

Assuming that the *Aria2* binary is in your execution path, run it with the above options...

```
aria2c --enable-rpc --rpc-allow-origin-all -c -D
```

To enable auto-start on most *Linux* systems, add the following in `/etc/rc.local` before the `exit` line...

```
/usr/local/bin/aria2c --enable-rpc --rpc-allow-origin-all -c -D
```


### Use


#### Include the library in your application code

```
include 'phpAria2rpc.class.inc.php';
```

#### Create an instance

```
$aria2 = new phpAria2rpc() ;
```

*phpAria2rpc* uses safe defaults for *Aria2* running on the same host as the script is running on. See advanced usage for custom configuration


#### Call Aria2 methods

```
var_dump($aria2->getGlobalStat());
var_dump($aria2->tellActive());
var_dump($aria2->tellWaiting(0,1000));
var_dump($aria2->tellStopped(0,1000));
var_dump($aria2->addUri(array('https://www.google.com.hk/images/srpr/logo3w.png'),array(
	    'dir'=>'/tmp',
)));
var_dump($aria2->tellStatus('1'));
var_dump($aria2->removeDownloadResult('1'));
//and more ...
```

### Advanced usage

Advanced configuration allows custom settings. Formulate a server configuration array

```
$server = array (
  'host'      => 'aria2.host.local' ,
  'port'      => '6800' ,
  'rpcsecret' => 'aria2rpcsecrettoken' ,
  'secure'    => TRUE ,
  'cacert'    => '/path/to/ca-cert/on/host/running/phpAria2.pem' ,
  'rpcuser'   => 'legacyrpcusername' ,
  'rpcpass'   => 'legacyrpcpassword' ,
  'proxy'     => array (
    'type' => 'socks5' ,
    'host' => 'localhost' ,
    'port' => 9050 ,
    'user' => 'proxyuser' ,
    'pass' => 'proxypassword
  )
) ;
```

This array is passed as a parameter only once, whilst creating an instance of the class.

```
$aria2 = new phpAria2rpc($server) ;
```

*phpAria2rpc* inserts the correct value as per *Aria2*'s requirement.

Further usage is normal, by calling *Aria2* methods against  *phpAria2rpc*.


#### Secure RPC

If *Aria2* is configured for secure RPC, then set the `secure` key value in the `$server` array to `TRUE`.

If self-signed certificates are used, then the appropriate CA certificate will have to be copied over to the host running this library and its path specified in the `cacert` key value in the `$server` array.


#### RPC secret token

If *Aria2* has been configured with a secret token on the RPC interface then this should be specified in the `rpcsecret` key value in the `$server` array.


#### Legacy RPC authentication

*phpAria2rpc* can also be configured to connect to *Aria2* daemons with the legacy username/password authentication for the RPC interface.

Please note that the use of this method of authentication is deprecated by the *Aria2* authors, however still supported by *phpAria2rpc* for compatibility with older versions of *Aria2* without the newer secret token authentication.


#### RPC username (legacy)

Specify the RPC username in the `rpcuser` key value of the `$server` array.


#### RPC password (legacy)

Specify the RPC password in the `rpcpass` key value of the `$server` array.


#### Connecting through proxy

*phpAria2rpc* allows connecting to the desired instance of *Aria2* via proxy. This can be accomplished by adding an sub-array item `proxy` to the array parameter passed to the class.

This sub-array associative and is used for setting the proxy configuration.

Please refer to the proxy configuration or documentation of your proxy service for information on values specific to the application environment.

It has the following elements.


##### Type

Use the value for the key `type` to specify the type of proxy to use. Currently only `http` and `socks5` types are supported.

There is no default value for this parameter.


##### Host

Use the value for the key `host` to specify the proxy host to use. Both IP addresses and resolvable host names are allowed.

There is no default value for this parameter.


##### Port

Use the value for the key `port` to specify the port of the proxy service. This is the port that the proxy service is listening for connections on.

There is no default value for this parameter.


###### Proxy authentication

If the proxy server requires authentication, then this can be achieved by two more items in the `proxy` sub-array.

Remember that this is different from the RPC authentication for *Aria2*. 

* Use the value for the key `user` to specify the username to authenticate with the proxy service.

  If `user` is not provided or is `NULL` then *phpAria2* does not attempt to authenticate with the proxy service.
  
* Use the value for the key `pass` to specify the password to authenticate with the proxy service.

  If `pass` is not provided or is `NULL`, *phpAria2* will use only the username to authenticate with the proxy service. While this scenario is unlikely, it allows connecting to proxy servers with non-standard setups.
  
  Proxy authentication with only username and without password depends on the proxy service and may fail if not configured properly.


### Examples


#### Download a File

```
	$addresult = $aria2->addUri ( array (
	  'https://www.google.com.hk/images/srpr/logo3w.png'
	) ,
  array('dir'=>'/tmp')
  ) ) ;
```


#### Returned Data


##### Can't Download

```
	array(3) {
	  ["id"]=>
	  string(1) "1"
	  ["jsonrpc"]=>
	  string(3) "2.0"
	  ["result"]=>
	  array(13) {
	    ["completedLength"]=>
	    string(1) "0"
	    ["connections"]=>
	    string(1) "0"
	    ["dir"]=>
	    string(4) "/tmp"
	    ["downloadSpeed"]=>
	    string(1) "0"
	    ["errorCode"]=>
	    string(1) "1"
	    ["files"]=>
	    array(1) {
	      [0]=>
	      array(6) {
	        ["completedLength"]=>
	        string(1) "0"
	        ["index"]=>
	        string(1) "1"
	        ["length"]=>
	        string(1) "0"
	        ["path"]=>
	        string(0) ""
	        ["selected"]=>
	        string(4) "true"
	        ["uris"]=>
	        array(1) {
	          [0]=>
	          array(2) {
	            ["status"]=>
	            string(4) "used"
	            ["uri"]=>
	            string(48) "https://www.google.com.hk/images/srpr/logo3w.png"
	          }
	        }
	      }
	    }
	    ["gid"]=>
	    string(1) "2"
	    ["numPieces"]=>
	    string(1) "0"
	    ["pieceLength"]=>
	    string(7) "1048576"
	    ["status"]=>
	    string(5) "error"
	    ["totalLength"]=>
	    string(1) "0"
	    ["uploadLength"]=>
	    string(1) "0"
	    ["uploadSpeed"]=>
	    string(1) "0"
	  }
	}
```


##### Downloading (Active)

```
	array(3) {
	  ["id"]=>
	  string(1) "1"
	  ["jsonrpc"]=>
	  string(3) "2.0"
	  ["result"]=>
	  array(13) {
	    ["bitfield"]=>
	    string(8) "e0000000"
	    ["completedLength"]=>
	    string(7) "3932160"
	    ["connections"]=>
	    string(1) "1"
	    ["dir"]=>
	    string(18) "/data/files/lixian"
	    ["downloadSpeed"]=>
	    string(5) "75972"
	    ["files"]=>
	    array(1) {
	      [0]=>
	      array(6) {
	        ["completedLength"]=>
	        string(7) "3145728"
	        ["index"]=>
	        string(1) "1"
	        ["length"]=>
	        string(8) "31550548"
	        ["path"]=>
	        string(48) "/data/files/lixian/[茶经].陆羽.扫描版.pdf"
	        ["selected"]=>
	        string(4) "true"
	        ["uris"]=>
	        array(5) {
	          [0]=>
	          array(2) {
	            ["status"]=>
	            string(4) "used"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [1]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [2]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [3]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [4]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	        }
	      }
	    }
	    ["gid"]=>
	    string(1) "3"
	    ["numPieces"]=>
	    string(2) "31"
	    ["pieceLength"]=>
	    string(7) "1048576"
	    ["status"]=>
	    string(6) "active"
	    ["totalLength"]=>
	    string(8) "31550548"
	    ["uploadLength"]=>
	    string(1) "0"
	    ["uploadSpeed"]=>
	    string(1) "0"
	  }
	}
```


##### Downloaded

```
	array(3) {
	  ["id"]=>
	  string(1) "1"
	  ["jsonrpc"]=>
	  string(3) "2.0"
	  ["result"]=>
	  array(14) {
	    ["bitfield"]=>
	    string(8) "fffffffe"
	    ["completedLength"]=>
	    string(8) "31550548"
	    ["connections"]=>
	    string(1) "0"
	    ["dir"]=>
	    string(18) "/data/files/lixian"
	    ["downloadSpeed"]=>
	    string(1) "0"
	    ["errorCode"]=>
	    string(1) "0"
	    ["files"]=>
	    array(1) {
	      [0]=>
	      array(6) {
	        ["completedLength"]=>
	        string(8) "31550548"
	        ["index"]=>
	        string(1) "1"
	        ["length"]=>
	        string(8) "31550548"
	        ["path"]=>
	        string(48) "/data/files/lixian/[茶经].陆羽.扫描版.pdf"
	        ["selected"]=>
	        string(4) "true"
	        ["uris"]=>
	        array(6) {
	          [0]=>
	          array(2) {
	            ["status"]=>
	            string(4) "used"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [1]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [2]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [3]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [4]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	          [5]=>
	          array(2) {
	            ["status"]=>
	            string(7) "waiting"
	            ["uri"]=>
	            string(417) "http://gdl.lixian.vip.xunlei.com/download?fid=zKHWI/O2IbQ07pi/0hPYP1OLwrBUbOEBAAAAACaqKvQbmfR7K7JcbWGT3XQBlDzs&mid=666&threshold=150&tid=3018BA81C31480902DC937770AC2734F&srcid=4&verno=1&g=26AA2AF41B99F47B2BB25C6D6193DD7401943CEC&scn=c7&i=0D2B59F64D6CCBB5A1507A03C3B685BC&t=4&ui=222151634&ti=106821253185&s=31550548&m=0&n=013A830CE1AD5D2EC2DCE21471C9A8C3E8D1D7CA2F64660000&ff=0&co=33BB9833AB0EE7AAEA94105B64C8013F&cm=1"
	          }
	        }
	      }
	    }
	    ["gid"]=>
	    string(1) "3"
	    ["numPieces"]=>
	    string(2) "31"
	    ["pieceLength"]=>
	    string(7) "1048576"
	    ["status"]=>
	    string(8) "complete"
	    ["totalLength"]=>
	    string(8) "31550548"
	    ["uploadLength"]=>
	    string(1) "0"
	    ["uploadSpeed"]=>
	    string(1) "0"
	  }
	}
```

## Known limitations

* *Aria2* does not set CA certificates, so for self-signed certificates, the CA cert file will have to be copied to the host running *phpAria2rpc*.


## Support

Please refer to the documentation of *Aria2*

* http://aria2.sourceforge.net/manual/en/html/aria2c.html#rpc-interface for RPC interface.
* http://aria2.sourceforge.net/manual/en/html/aria2c.html for general options.

Debugging can be enabled by setting boolean `$GLOBALS['bl_DebugSwitch']` to `TRUE`.

```
$GLOBALS['bl_DebugSwitch'] = TRUE ;
```

For issues, queries, suggestions and comments please create an [issue/ticket][ln_Issues].


## Contribute

Please feel free to clone/fork and contribute via pull requests. Donations also welcome, simply create an [issue/ticket][ln_Issues].

Please make contact for more information.


## Development environment ##
Developed on..

* *Debian Wheezy*
* *Apache* 2.2
* *PHP* 5.4
* *Aria2* 1.15.1 and 1.18.8


## Licence

Licensed under the modified BSD (3-clause) license.

A copy of the license is available...

* in the enclosed [`LICENSE`][ln_License] file.
* at http://opensource.org/licenses/BSD-3-Clause


## References


### php-aria2

* `aria2.class.php` (2013-11-22)
* Available in the public domain at https://github.com/shiny/php-aria2
* Author daijie (shiny)


## Credits


### Tools


#### Kint

*Kint* debugging library (http://raveren.github.io/kint/), used under the MIT license

Copyright (c) 2013 Rokas Å leinius (raveren at gmail dot com)


### Utilities


#### Codiad

*Codiad* web based IDE (https://github.com/Codiad/Codiad), used under a MIT-style license.

Copyright (c) Codiad & Kent Safranski (codiad.com)


#### jEdit

*jEdit* text editor (http://www.jedit.org/), used under the GNU GPL v2.

Copyright (C) jEdit authors.


### GitHub

Hosted by *GitHub* code repository (github.com).



[mg_BadgeLicense]: https://img.shields.io/github/license/viharm/phpAria2rpc.svg?style=flat-square
[mg_BadgeVersion]: https://img.shields.io/github/release/viharm/phpAria2rpc.svg?style=flat-square
[mg_BadgeIssues]: https://img.shields.io/github/issues/viharm/phpAria2rpc.svg?style=flat-square
[mg_BadgeCodeLang]: https://img.shields.io/badge/language-php-yellowgreen.svg?style=flat-square
[ln_ReleaseLatest]: https://github.com/viharm/phpAria2rpc/releases/latest
[ln_License]: LICENCE?at=master
[ln_Issues]: https://github.com/viharm/phpAria2rpc/issues
[ln_CodeLang]: https://www.python.org/
