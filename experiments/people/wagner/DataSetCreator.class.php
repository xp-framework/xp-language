<?php
/* This class is part of the XP framework
 *
 * $Id: DataSetCreator.class.php 55729 2007-03-07 09:55:09Z wagner $ 
 */

  uses(
    'util.cmd.Command',
    'rdbms.util.DBXmlGenerator', 
    'rdbms.DBTable',
    'rdbms.DSN',
    'rdbms.DriverManager',
    'util.log.Logger',
    'util.log.FileAppender',
    'util.cmd.ParamString',
    'io.File',
    'io.FileUtil',
    'io.Folder',
    'util.Properties',
    'xml.DomXSLProcessor'
  );

  /**
   * DB-XML-file and DB-PHP class generator
   *
   * Supports the following database drivers:
   * <ul>
   *   <li>mysql</li>
   *   <li>sybase</li>
   * </ul>
   *
   * @test     xp://de.schlund.unittest.cmd.DataSetCreatorTest
   * @purpose  Infrastructure
   */
  class DataSetCreator extends Command {
    public static $adapters= array(
      'mysql'   => 'rdbms.mysql.MySQLDBAdapter',
      'sybase'  => 'rdbms.sybase.SybaseDBAdapter'
    );
    
    const GENERATE_XML= 'generateTables';
    const GENERATE_SRC= 'xsltproc';
    
    protected 
      $mode= self::GENERATE_XML,
      $xmltarget,
      $dsntemp,
      $prefix,
      $prefixRemove,
      $incprefix,
      $exprefix,
      $connection,
      $package,
      $xmlfile,
      $outputdir,
      $ignore;
    
    /**
     * Get prefixed classname
     *
     * @param   string tname table name
     * @param   string prefix default ''
     * @param   string[] include default array()
     * @param   string[] exclude default array()
     * @return  string
     * @throws  lang.IllegalArgumentException
     */
    public function prefixedClassName($tname, $prefix= '', $include= array(), $exclude= array(), $remove= '') {
      $p= ''; $r= '';
      if (!empty($exclude) && !empty($include)) {
        throw new IllegalArgumentException('Unknown use-case');
      } else if (!empty($exclude) && empty($include) && !in_array($tname, $exclude)) {
        $r= $remove;
        $p= $prefix;
      } else if (empty($exclude) && !empty($include) && in_array($tname, $include)) {
        $r= $remove;
        $p= $prefix;
      }

      // Perform removal, if wanted
      if (strlen($r) && 0 === strpos($tname, $r)) $tname= substr($tname, strlen($r));
      return $p.ucfirst(strtolower($tname));
    }

    /**
     * Sets up connection to the database
     *
     * @param   string dsntemp
     * @return  rdbms.DBAdapter
     * @throws  lang.IllegalArgumentException if the driver is not supported
     */
    public function getAdapter($dsntemp) {
      $dsn= new DSN($dsntemp);
      if (!isset(self::$adapters[$dsn->getDriver()])) {
        throw new IllegalArgumentException('Unsupported driver "'.$dsn->getDriver().'"');
      }

      // Check whether host is connection or connection is set in .ini
      if (empty($this->connection)) {
        $this->connection= $dsn->getHost();
      }

      // Get connection
      return XPClass::forName(self::$adapters[$dsn->getDriver()])->newInstance(
        DriverManager::getInstance()->getConnection($dsntemp)
      );
    }
    
    /**
     * generates .xml documents from tables 
     *
     */
    public function generateTables() {
      $adapter= $this->getAdapter($this->dsntemp);
      $adapter->conn->connect();

      // Create new Folder Object and new Folder(s) if necessary
      $fold=    new Folder($this->xmltarget);
      $relfold->exists() || $relfold->create(0755);

      $tables= DBTable::getByDatabase($adapter, $adapter->conn->dsn->getDatabase());
      foreach ($tables as $t) {
        if (!in_array(strtolower($t->name), $this->ignore)) {

          // Generate XML
          $gen= DBXmlGenerator::createFromTable(
            $t, 
            $this->connection,          
            $adapter->conn->dsn->getDatabase()
          ); 

          // Determine whether filename needs prefix
          $classname= $this->prefixedClassName(
            $t->name, 
            $this->prefix, 
            $this->incprefix, 
            $this->exprefix,
            $this->prefixRemove
          );
          $filename= ucfirst($t->name);

          // Create table node...
          with ($node= $gen->doc->root->children[0]); {
            $node->setAttribute('dbtype', $adapter->conn->dsn->getDriver());
            $node->setAttribute('class', $classname);
            $node->setAttribute('package', $this->package);
          }

          // ...and finally, write to a file
          $f= new File($fold->getURI().ucfirst($t->name).'.xml');
          $written= FileUtil::setContents($f, $gen->getSource());
          $this->out->writeLinef(
            '===> Output written to %s (%.2f kB)', 
            $f->getURI(),
            $written / 1024
          );
        }
      }
    }
    
    /**
     * Uses xsltProc to convert xml files to php code
     *
     */
    public function xsltproc() {
      $directory= str_replace('.', DIRECTORY_SEPARATOR, $this->package);    

      preg_match('/[0-9a-z_-]+\.xml/i', $this->xmlfile, $matches);
      $name= strtolower(str_replace('.xml', '', $matches[0]));
      $proc= new DomXSLProcessor();
      
      // Using override XSL-File
      if (array_key_exists($name, $this->overrides)) {
        $proc->setXSLFile(str_replace('config.ini', $this->overrides[$name], $this->inifile));
        $this->out->writeLinef('!!! Using override xslfile: %s', $this->overrides[$name]);
      } else {
        $proc->setXSLFile($this->xslsheet);
      }
      
      $proc->setXMLFile($this->xmlfile);
      $proc->setParam('path',         dirname($this->xmlfile));
      $proc->setParam('package',      $this->package);
      $proc->setParam('prefix',       $this->prefix);
      $proc->setParam('incprefix',    implode(',', $this->incprefix));
      $proc->setParam('exprefix',     implode(',', $this->exprefix));
      $proc->setParam('prefixRemove', $this->prefixRemove);
      $proc->run();

      $fold= new Folder($this->outputdir.DIRECTORY_SEPARATOR.$directory);
      $fold->exists() || $fold->create(0755);
      
      $filename= $this->prefixedClassName($name, $this->prefix, $this->incprefix, $this->exprefix, $this->prefixRemove);
      
      $f= new File($fold->getURI().DIRECTORY_SEPARATOR.$filename.'.class.php');
      $written= FileUtil::setContents($f, $proc->output());    
      $this->out->writeLinef('===> Writing to %s (%.2f kB)', $f->getURI(), $written / 1024);
    }
    
    /**
     * Set config.ini filename
     *
     * @param   string filename default 'config.ini'
     */
    #[@arg]
    public function setConfig($filename) {
      $ini= new Properties($filename);
      if (!$ini->exists()) {
        throw new FileNotFoundException('No config file found. Use --help for more details');
      }

      $this->xmltarget    = str_replace('config.ini', 'tables', $ini->getFilename());
      $this->dsntemp      = $ini->readString('connection', 'dsn');
      $this->prefix       = $ini->readString('prefix', 'value');
      $this->prefixRemove = $ini->readString('prefix', 'remove');
      $this->incprefix    = $ini->readArray ('prefix', 'include');
      $this->exprefix     = $ini->readArray ('prefix', 'exclude');
      $this->connection   = $ini->readString('connection', 'name');
      $this->package      = $ini->readString('mapping', 'package');
      $this->overrides    = $ini->readSection('overrides', FALSE);
      $this->inifile      = $ini->getFilename();
      $this->ignore       = $ini->readArray('ignore', 'tables');  
      if (!empty($this->incprefix) && !empty($this->exprefix)) {
        throw new IllegalArgumentException(
          '==> exclude-prefix AND include-prefix are set. This is invalid <=='."\n".
          '==> and will probably cause a rift in the space/time continuum!<=='
        );
      }
    }
    
    /**
     * Supply whether to generate sourcecode from the XML files
     *
     * @param   string xml default NULL
     */
    #[@arg(name= 'xmlgen', short= 'X')]
    public function doGenerateSource($xml= NULL) {
      if (empty($xml)) {
        $this->mode= self::GENERATE_XML;
      } else {
        $this->mode= self::GENERATE_SRC;
        $this->xmlfile= $xml;
      }
    }
    
    /**
     * Supply stylesheet to use for sourcecode generation
     *
     * @param   string xsl default NULL the stylesheet to use
     */
    #[@arg(name= 'xslsheet', short= 'S')]
    public function setStylesheet($xsl= NULL) {
      if (self::GENERATE_SRC == $this->mode && empty($xsl)) {
        throw new IllegalArgumentException('No stylesheet supplied');
      }
      $this->xslsheet= $xsl;
    }

    /**
     * Sets the output directory for the php classes.
     *
     * @param   string out default . the directory to use
     */
    #[@arg(name= 'output', short= 'O')]
    public function setOutputdir($out= ".") {
      $this->outputdir=rtrim($out, '/');
    }

    /**
     * Run this command
     *
     */
    public function run() {
      $this->getClass()->getMethod($this->mode)->invoke($this);
    }   
  }
?>
