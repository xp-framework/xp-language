<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'peer.Socket', 
    'peer.sieve.SieveScript', 
    'security.checksum.HMAC_MD5',
    'security.sasl.DigestChallenge'
  );

  // Authentication methods
  define('SIEVE_SASL_PLAIN',       'PLAIN');
  define('SIEVE_SASL_LOGIN',       'LOGIN');
  define('SIEVE_SASL_KERBEROS_V4', 'KERBEROS_V4');
  define('SIEVE_SASL_DIGEST_MD5',  'DIGEST-MD5');
  define('SIEVE_SASL_CRAM_MD5',    'CRAM-MD5');

  // Modules
  define('SIEVE_MOD_FILEINTO',     'FILEINTO');
  define('SIEVE_MOD_REJECT',       'REJECT');
  define('SIEVE_MOD_ENVELOPE',     'ENVELOPE');
  define('SIEVE_MOD_VACATION',     'VACATION');
  define('SIEVE_MOD_IMAPFLAGS',    'IMAPFLAGS');
  define('SIEVE_MOD_NOTIFY',       'NOTIFY');
  define('SIEVE_MOD_SUBADDRESS',   'SUBADDRESS');
  define('SIEVE_MOD_REGEX',        'REGEX');

  /**
   * Sieve is a mail filtering language
   *
   * Usage example [listing all available scripts]:
   * <code>
   *   uses('peer.sieve.SieveClient');
   *
   *   $s= &new SieveClient('imap.example.com');
   *   $s->connect();
   *   $s->authenticate(SIEVE_SASL_PLAIN, 'user', 'password');
   *   var_export($s->getScripts());
   *   $s->close();
   * </code>
   *
   * @see      rfc://3028 Sieve: A Mail Filtering Language
   * @see      rfc://3431 Sieve Extension: Relational Tests
   * @see      rfc://3598 Sieve Email Filtering -- Subaddress Extension
   * @see      rfc://2298 Extensible Message Format for Message Disposition Notifications (MDNs)
   * @see      http://www.cyrusoft.com/sieve/drafts/managesieve-04.txt 
   * @see      http://www.cyrusoft.com/sieve/
   * @purpose  Sieve Implementation
   */
  class SieveClient extends Object {
    var
      $cat      = NULL;

    var
      $_sock    = NULL,
      $_sinfo   = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   string host
     * @param   int port default 2000
     */  
    function __construct($host, $port= 2000) {
      $this->_sock= &new Socket($host, $port);
    }
  
    /**
     * Connect to sieve server
     *
     * @access  public
     * @return  bool success
     * @throws  io.IOException in case connecting failed
     * @throws  lang.FormatException in case the response cannot be parsed
     */  
    function connect() {
      try(); {
        $this->_sock->connect();
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      
      // Read the banner message. Example:
      //
      // "IMPLEMENTATION" "Cyrus timsieved v1.0.0"
      // "SASL" "LOGIN PLAIN KERBEROS_V4 DIGEST-MD5 CRAM-MD5"
      // "SIEVE" "fileinto reject envelope vacation imapflags notify subaddress regex"
      // OK
      do {
        if (!($line= $this->_sock->readLine())) return FALSE;
        $this->cat && $this->cat->debug('<<<', $line);

        if ('OK' == substr($line, 0, 2)) {
          break;
        } elseif ('"' == $line{0}) {
          sscanf($line, '"%[^"]" "%[^"]"', $key, $value);
          switch ($key) {
            case 'IMPLEMENTATION':
              $this->_sinfo[$key]= $value;
              break;

            case 'SASL':
            case 'SIEVE':
              $this->_sinfo[$key]= explode(' ', strtoupper($value));
              break;
            
            case 'STARTTLS':
              $this->_sinfo[$key]= TRUE;
              break;

            default:
              return throw(new FormatException('Cannot parse banner message line >'.$line.'<'));
          }
          continue;
        }

        return throw(new FormatException('Unexpected response line >'.$line.'<'));
      } while (1);
      
      $this->cat && $this->cat->debug('Server information:', $this->_sinfo);
      return TRUE;
    }
    
    /**
     * Wrapper that sends a command to the remote host.
     *
     * @access  protected  
     * @param   string format
     * @param   mixed* args
     * @return  bool success
     */
    function _sendcmd() {
      $a= func_get_args();
      $cmd= vsprintf(array_shift($a), $a);
      $this->cat && $this->cat->debug('>>>', $cmd);
      return $this->_sock->write($cmd."\r\n");
    }

    /**
     * Wrapper that reads the response from the remote host, returning
     * it into an array if not specified otherwise.
     *
     * Stops reading at one of the terminals "OK", "NO" or "BYE".
     *
     * @access  protected  
     * @param   bool discard default FALSE
     * @param   bool error default TRUE
     * @return  string[]
     * @throws  lang.FormatException in case "NO" occurs
     * @throws  peer.SocketException in case "BYE" occurs
     */
    function _response($discard= FALSE, $error= TRUE) {
      $lines= array();
      do {
        if (!($line= $this->_sock->readLine())) return FALSE;
        $this->cat && $this->cat->debug('<<<', $line);
        
        if ('OK' == substr($line, 0, 2)) {
          break;
        } elseif ('NO' == substr($line, 0, 2)) {
          return $error ? throw(new FormatException(substr($line, 3))) : FALSE;
        } elseif ('BYE' == substr($line, 0, 3)) {
          return throw(new SocketException(substr($line, 4)));
        } elseif (!$discard) {
          $lines[]= $line;
        }
      } while (!$this->_sock->eof());

      return $discard ? TRUE : $lines;
    }
    
    /**
     * Return server implementation
     *
     * @access  public
     * @return  string
     */
    function getImplementation() {
      return $this->_sinfo['IMPLEMENTATION'];
    }

    /**
     * Retrieve supported modules. Return value is an array of modules
     * reported by the server consisting of the SIEVE_MOD_* constants.
     *
     * @access  public
     * @return  string[] 
     */
    function getSupportedModules() {
      return $this->_sinfo['SIEVE'];
    }

    /**
     * Check whether a specified module is supported
     *
     * @access  public
     * @param   string method one of the SIEVE_MOD_* constants
     * @return  bool
     */
    function supportsModule($module) {
      return in_array($module, $this->_sinfo['SIEVE']);
    }
    
    /**
     * Retrieve possible authentication methods. Return value is an 
     * array of supported methods reported by the server consisting
     * of the SIEVE_SASL_* constants.
     *
     * @access  public
     * @return  string[] 
     */
    function getAuthenticationMethods() {
      return $this->_sinfo['SASL'];
    }

    /**
     * Checks whether a specied authentication is available.
     *
     * @access  public
     * @param   string method one of the SIEVE_SASL_* constants
     * @return  bool
     */
    function hasAuthenticationMethod($method) {
      return in_array($method, $this->_sinfo['SASL']);
    }
    
    /**
     * Authenticate
     *
     * @access  public
     * @param   string method one of the SIEVE_SASL_* constants
     * @param   string user
     * @param   string pass
     * @param   string auth default NULL
     * @return  bool success
     * @throws  lang.IllegalArgumentException when the specified method is not supported
     */
    function authenticate($method, $user, $pass, $auth= NULL) {
      if (!$this->hasAuthenticationMethod($method)) {
        return throw(new IllegalArgumentException('Authentication method '.$method.' not supported'));
      }
      
      // Check whether we want to impersonate
      if (NULL === $auth) $auth= $user;
      
      // Send auth request depending on specified authentication method
      switch ($method) {
        case SIEVE_SASL_PLAIN:
          $e= base64_encode($auth."\0".$user."\0".$pass);
          $this->_sendcmd('AUTHENTICATE "PLAIN" {%d+}', strlen($e));
          $this->_sendcmd($e);
          break;

        case SIEVE_SASL_LOGIN:
          $this->_sendcmd('AUTHENTICATE "LOGIN"');
          $ue= base64_encode($user);
          $this->_sendcmd('{%d+}', strlen($ue));
          $this->_sendcmd($ue);
          $pe= base64_encode($pass);
          $this->_sendcmd('{%d+}', strlen($pe));
          $this->_sendcmd($pe);
          break;

        case SIEVE_SASL_DIGEST_MD5:
          $this->_sendcmd('AUTHENTICATE "DIGEST-MD5"');
          
          // Read server challenge. Example (decoded):
          // 
          // realm="example.com",nonce="GMybUaOM4lpMlJbeRwxOLzTalYDwLAxv/sLf8de4DPA=",
          // qop="auth,auth-int,auth-conf",cipher="rc4-40,rc4-56,rc4",charset=utf-8,
          // algorithm=md5-sess
          //
          // See also xp://security.sasl.DigestChallenge
          $len= $this->_sock->readLine(0x400);
          $str= base64_decode($this->_sock->readLine());
          $this->cat && $this->cat->debug('Challenge (length '.$len.'):', $str);
          try(); {
            $challenge= &DigestChallenge::fromString($str);
          } if (catch('FormatException', $e)) {
            return throw($e);
          }
          $this->cat && $this->cat->debug($challenge);
          
          // Check for presence of quality of protection "auth"
          $qop= DC_QOP_AUTH;
          if (!$challenge->hasQop($qop)) {
            return throw(new FormatException('Challenge does not contains DC_QOP_AUTH'));
          }

          // Build the response
          $cnonce= base64_encode(bin2hex(HMAC_MD5::hash(microtime())));
          $ncount= '00000001';
          $digest_uri= 'sieve/'.$this->_sock->host;
          $a1= bin2hex(HMAC_MD5::hash(sprintf(
            '%s:%s:%s:%s',
            HMAC_MD5::hash(utf8_encode($user).':'.utf8_encode($challenge->getRealm()).':'.utf8_encode($pass)),
            $challenge->getNonce(),
            $cnonce,
            utf8_encode($auth)
          )));
          $a2= bin2hex(HMAC_MD5::hash(sprintf(
            'AUTHENTICATE:%s',
            $digest_uri
          )));
          $response= bin2hex(HMAC_MD5::hash(sprintf(
            '%s:%s:%s:%s:%s:%s',
            $a1,
            $challenge->getNonce(),
            $ncount,
            $cnonce,
            $qop,
            $a2
          )));
          
          // Send it
          $cmd= sprintf(
            'charset=utf-8,username="%s",realm="%s",nonce="%s",nc=%s,'.
            'cnonce="%s",digest-uri="%s",response=%s,qop=%s,authzid="%s"',
            utf8_encode($user),
            utf8_encode($challenge->getRealm()),
            $challenge->getNonce(),
            $ncount,
            $cnonce,
            $digest_uri,
            $response,
            $qop,
            utf8_encode($auth)
          );
          $this->cat && $this->cat->debug('Sending challenge response', $cmd);
          $this->_sendcmd('"%s"', base64_encode($cmd));

          // Finally, read the response auth
          $len= $this->_sock->readLine();
          $str= base64_decode($this->_sock->readLine());
          $this->cat && $this->cat->debug('Response auth (length '.$len.'):', $str);
          return TRUE;
        
        default:
          return throw(new IllegalArgumentException('Authentication method '.$method.' not implemented'));
      }
      
      // Read the response. Examples:
      //
      // - OK
      // - NO ("SASL" "authentication failure") "Authentication error"
      return $this->_response(TRUE);
    }
    
    /**
     * Retrieve a list of scripts stored on the server
     *
     * @access  public
     * @return  peer.sieve.SieveScript[] scripts
     */
    function getScripts() {
      foreach ($this->getScriptNames() as $name => $info) {
        with ($s= &$this->getScript($name)); {
          $s->setActive('ACTIVE' == $info);         // Only one at a time
        }
        $r[]= &$s;
      }
      return $r;
    }

    /**
     * Retrieve a list of scripts names.
     *
     * @access  public
     * @return  array
     */
    function getScriptNames() {
      $this->_sendcmd('LISTSCRIPTS');
      
      // Response is something like this:
      //
      // "bigmessages"
      // "spam" ACTIVE
      $r= array();
      foreach ($this->_response() as $line) {
        if (!sscanf($line, '"%[^"]" %s', $name, $info)) continue;
        $r[$name]= $info;
      }
      return $r;
    }

    /**
     * Retrieve a script by its name
     *
     * @access  public
     * @param   string name
     * @return  &peer.sieve.SieveScript script
     */
    function &getScript($name) {
      $this->_sendcmd('GETSCRIPT "%s"', $name);
      if (!($r= $this->_response())) return $r;
      
      // Response it something like this:
      // 
      // {59} 
      // if size :over 100K { # this is a comment 
      //   discard; 
      // } 
      //
      // The number on the first line indicates the length. We simply 
      // discard this information.
      $s= &new SieveScript($name);
      $s->setCode(implode("\n", array_slice($r, 1)));
      return $s;
    }

    /**
     * Delete a script from the server
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    function deleteScript($name) {
      $this->_sendcmd('DELETESCRIPT "%s"', $name);
      return $this->_response(TRUE);
    }

    /**
     * Upload a script to the server
     *
     * @access  public
     * @param   &peer.sieve.SieveScript script
     * @return  bool success
     */
    function putScript(&$script) {
      $this->_sendcmd('PUTSCRIPT "%s" {%d+}', $script->getName(), $script->getLength());
      $this->_sendcmd($script->getCode());
      return $this->_response(TRUE);
    }
    
    /**
     * Set a specific script as the active one on the server
     *
     * A user may have multiple Sieve scripts on the server, yet only one
     * script may be used for filtering of incoming messages. This is the
     * active script. Users may have zero or one active scripts and MUST
     * use the SETACTIVE command described below for changing the active
     * script or disabling Sieve processing. For example, a user may have
     * an everyday script they normally use and a special script they use
     * when they go on vacation. Users can change which script is being
     * used without having to download and upload a script stored somewhere
     * else.
     *
     * If the script name is the empty string (i.e. "") then any active 
     * script is disabled.
     *
     * @access  public
     * @param   string name
     * @return  bool success
     */
    function activateScript($name) {
      $this->_sendcmd('SETACTIVE "%s"', $name);
      return $this->_response(TRUE);
    }
    
    /**
     * Check whether there is enough space for a script to be uploaded
     *
     * @access  public
     * @param   &peer.sieve.SieveScript script
     * @return  bool success
     */
    function hasSpaceFor(&$script) {
      $this->_sendcmd('HAVESPACE "%s" %d', $script->getName(), $script->getLength());
      return $this->_response(TRUE, FALSE);
    }
    
    /**
     * Close connection
     *
     * @access  public
     */
    function close() {
      try(); {
        $this->_sock->write("LOGOUT\r\n"); 
        $this->_sock->close();
      } if (catch('IOException', $e)) {
        return throw($e);
      }
      
      return TRUE;      
    }

    /**
     * Set a trace for debugging
     *
     * @access  public
     * @param   &util.log.LogCategory cat
     */
    function setTrace(&$cat) { 
      $this->cat= &$cat;
    }

  } implements(__FILE__, 'util.log.Traceable');
?>
