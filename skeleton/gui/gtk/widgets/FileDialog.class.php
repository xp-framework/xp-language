<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'gui.gtk.GtkGladeDialogWindow', 
    'gui.gtk.util.GTKPixmapLoader', 
    'gui.gtk.util.GTKWidgetUtil', 
    'io.Folder'
  );

  /**
   * File dialog 
   * <code>
   *   $dlg= &new FileDialog(posix_getcwd());
   *   if ($dlg->show()) {
   *     printf("File selected: %s%s\n", $dlg->dir, $dlg->filename);
   *   }
   * </code>
   *
   * @purpose Provide a widget for file dialogs
   */
  class FileDialog extends GtkGladeDialogWindow {
    var
      $filename = '',
      $dir      = '',
      $filter	= '';

    /**
     * Constructor
     *
     * @access  public
     */
    function __construct($dir= '.', $filter= '.*') {
      $this->dir= $dir;
      $this->filter= $filter;
      parent::__construct(dirname(__FILE__).'/filedialog.glade', 'filedialog');
    }
   
    /**
     * Set Filename
     *
     * @access  public
     * @param   string filename
     */
    function setFilename($filename) {
      $this->filename= $filename;
    }

    /**
     * Get Filename
     *
     * @access  public
     * @return  string
     */
    function getFilename() {
      return $this->filename;
    }

    /**
     * Get Dir
     *
     * @access  public
     * @return  string
     */
    function getDirectory() {
      return $this->dir;
    }

    /**
     * Set Filter
     *
     * @access  public
     * @param   string filter
     */
    function setFilter($filter) {
      $this->filter= $filter;
    }

    /**
     * Get Filter
     *
     * @access  public
     * @return  string
     */
    function getFilter() {
      return $this->filter;
    }
 
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function buttons_connect($b) {
      foreach ($b as $name=> $callback) {
        $this->buttons[$name]= &$this->widget('button_'.$name);
        $this->buttons[$name]->connect_after('clicked', array(&$this, $callback));
      }
    }
    
    /**
     * OK, cancel pressed
     *
     * @access  
     * @param   
     * @return  
     */
    function onClose(&$widget) {
      if ('button_ok' != $widget->get_name()) {
        $this->filename= NULL;
      }
      $this->close();
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onUpDirClicked(&$widget) {
      $this->setDirectory(substr(
        $this->dir, 
        0, 
        strrpos(substr($this->dir, 0, -1), '/')
      ).'/');
    }


    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onHomeClicked(&$widget) {
      $info= posix_getpwuid(posix_getuid());
      $this->setDirectory($info['dir']);
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onFavoriteClicked(&$widget) {
      $i= posix_getpwuid(posix_getuid());
      $d= strtr(substr($widget->get_name(), 11), array(
        'HOME'  => $i['dir'],
        'ROOT'  => '/',
        'TMP'   => getenv('TMP'),
        '_'     => '/'
      ));
      $this->cat->debug($d);
      $this->setDirectory($d);
    }

    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onRefreshClicked(&$widget) {
      $this->setDirectory();
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onPNClicked(&$widget) {
      $this->cat->debug($widget->get_name(), $this->history, $this->history_offset);
      $this->history_offset+= ('button_prev' == $widget->get_name()) ? -1 : 1;
      $this->cat->debug($widget->get_name(), $this->history_offset, $this->history[$this->history_offset]);
      $this->setDirectory($this->history[$this->history_offset], FALSE);
    }
    
    /**
     * Initialize application
     *
     * @access  public
     */
    function init() {
      $this->window->set_default_size(400, 420);
      
      // File list
      $this->files= &$this->widget('clist_files');
      $this->files->set_row_height(26);
      $this->files->set_sort_column(1); // Type
      $this->files->connect('select_row', array(&$this, 'onEntrySelected'));
      
      // Location
      $this->location= &$this->widget('entry_location');
      
      // Combo
      $this->combo= &$this->widget('combo_dir');
      
      // Buttons
      $this->buttons_connect(array(
        'ok'	    => 'onClose',
        'cancel'    => 'onClose',
        'up'	    => 'onUpDirClicked',
        'home'	    => 'onHomeClicked',
        'refresh'	=> 'onRefreshClicked',
        'next'      => 'onPNClicked',
        'prev'      => 'onPNClicked',
      ));
      
      // Favorites
      $this->favorites= &$this->widget('bar_favorites');
      $this->favorites->set_button_relief(GTK_RELIEF_NONE);
      $view= &$this->widget('view_favorites');
      $style= Gtk::widget_get_default_style();
      $style->base[GTK_STATE_NORMAL]= $style->mid[GTK_STATE_NORMAL];
      $view->set_style($style);
      
      GTKWidgetUtil::connectChildren($this->widget('bar_favorites'), array(
        ':clicked' => array(&$this, 'onFavoriteClicked')
      ));
      
      // History
      $this->history= array();
      $this->history_offset= 0;

      // Load pixmaps
	  $this->pixmaps= array();
      $if= &new Folder(dirname(__FILE__).'/icons/');
      $loader= &new GTKPixmapLoader($this->window->window, $if->uri);
      try(); {
        while ($entry= $if->getEntry()) {
          if ('.xpm' != substr($entry, -4)) continue;
          $entry= substr($entry, 0, -4);
          
          $this->pixmaps= array_merge(
            $this->pixmaps, 
            $loader->load($entry)
          );
        }
        $if->close();
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
      }
      $this->cat->debug(sizeof($this->pixmaps), 'pixmaps loaded');
      
      // Read files
      $this->setDirectory();
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function onEntrySelected(&$widget, $row, $data, $event) {
      $this->cat->debug($widget);
      $ftype= $widget->get_text($row, 1);
      $entry= $widget->get_pixtext($row, 0);
      
      // Check if an item was double clicked
      if ('' == $ftype && isset($event) && GDK_2BUTTON_PRESS == $event->type) {
        return $this->setDirectory($this->dir.$entry[0]);
      }
      
      $this->filename= $entry[0];
      $this->cat->debug($row, 'selected, uri is', $this->filename, 'event', $data, $event->type);
      
      // Update location entry
      $this->location->set_text($entry[0]);
      
      // Set OK button sensitive if file type is not empty (indicating a directory)
      $this->buttons['ok']->set_sensitive('' != $ftype);
    }
    
    /**
     * Format a size
     *
     * @access  private
     * @param   int s size
     * @return  string formatted output
     */
    function _size($s) {
      if ($s < 1024) return sprintf('%d Bytes', $s);
      if ($s < 1048576) return sprintf('%0.2f KB', $s / 1024);
      if ($s < 1073741824) return sprintf('%0.2f MB', $s / 1048576);
      return sprintf('%0.2f GB', $s / 1073741824);
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  
     */
    function setDirectory($dir= NULL, $update_offset= TRUE) {
      if (NULL !== $dir) $this->dir= $dir;
      $this->cat->debug('Change dir to', $this->dir);
      
      // Disable Up button if we are at top
      $this->buttons['up']->set_sensitive(strlen($this->dir) > 1);
      
      // Update combo
      $this->history[]= $this->dir;
      $size= sizeof($this->history)- 1;
      $this->combo->set_popdown_strings(array_unique($this->history));
      
      if ($update_offset) $this->history_offset= $size;
      
      // "Previous" is available if this is not the first call
      $this->buttons['prev']->set_sensitive($this->history_offset > 0);
      $this->buttons['next']->set_sensitive($this->history_offset < $size);

      $this->readFiles();
    }
            
    /**
     * (Insert method's description here)
     *
     * @access  public
     */  
    function readFiles() {
      $f= &new Folder($this->dir);

      // Disable Up button if we are at top
      $this->buttons['up']->set_sensitive(strlen($this->dir) > 1);
      
      // Update entry
      $entry= $this->combo->entry;
      $entry->set_text($f->uri);

      // Update list
      $this->files->freeze();
      $this->files->clear();
      try(); {
        while ($entry= $f->getEntry()) {
          if (!preg_match(':'.$this->filter.':i', $entry)) continue;
          
          $icon= $mask= NULL;
          if ($dir= is_dir($f->uri.$entry)) {
          
            // Set folder icon
            $icon= $this->pixmaps['p:folder'];
            $mask= $this->pixmaps['m:folder'];
          } else {
            $ext= '(n/a)';
  	        if (FALSE !== ($p= strrpos($entry, '.')) && $p > 0) $ext= substr($entry, $p+ 1);
            
            // Check for "special" files
            if (preg_match('#README|TODO|INSTALL|COPYRIGHT|NEWS#', $entry)) {
              $idx= 'special.readme';
            } else {
              $idx= isset($this->pixmaps['p:ext.'.$ext]) ? 'ext.'.$ext : 'none';
            }
            
            // Set icon
            $icon= $this->pixmaps['p:'.$idx];
            $mask= $this->pixmaps['m:'.$idx];
          }
          
          // Get file owner's name
          $owner= posix_getpwuid(fileowner($f->uri.$entry));
          
          // $this->cat->debug($f->uri.$entry, 'dir?', $dir, 'ext', $ext); $this->cat->debug($entry, $owner);
          
		  $this->files->set_pixtext(
            $this->files->append(array(
              $entry,
              $dir ? '' : $ext,
              $this->_size(filesize($f->uri.$entry)),
              date('Y-m-d H:i', filemtime($f->uri.$entry)),
              $owner['name'],
              substr(sprintf("%o", fileperms($f->uri.$entry)), 3- $dir)
            )),
		    0, 
		    $entry,
		    4,
		    $icon,
	        $mask
          );

        }
        
        // Copy folder's URI (will be full path)
        $this->dir= $f->uri;
        $f->close();
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
      }
      $this->files->sort();
      $this->files->thaw();
    }
    
    /**
     * (Insert method's description here)
     *
     * @access  
     * @param   
     * @return  bool
     */
    function show() {
      parent::show();
      return !empty($this->filename);
    }
  }
?>
