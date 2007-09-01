<?php
/* This class is part of the XP framework
 *
 * $Id: StorageActionInterceptor.class.php 9455 2007-02-14 11:43:29Z kiesel $ 
 */

  namespace peer::ftp::server::interceptor;

  /**
   * Interface class for FTP storage interceptors
   *
   * @see      peer.ftp.server.FtpConnectionListener
   * @purpose  Interceptor interface
   */
  interface StorageActionInterceptor {
  
    /**
     * Invoked when chdir'ing into a directory
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onCwd($session, $entry);
      
    /**
     * Invoked when an entry is created
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onCreate($session, $entry);
  
    /**
     * Invoked when an entry is deleted
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onDelete($session, $entry);
  
    /**
     * Invoked when an entry is read
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onRead($session, $entry);
  
    /**
     * Invoked when an entry is renamed
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onRename($session, $entry);
  
    /**
     * Invoked when permissions are changed for an entry
     * 
     * @param  peer.ftp.server.FtpSession
     * @param  peer.ftp.server.storage.StorageEntry
     * @return bool
     */
    public function onChangePermissions($session, $entry);
  }
?>
