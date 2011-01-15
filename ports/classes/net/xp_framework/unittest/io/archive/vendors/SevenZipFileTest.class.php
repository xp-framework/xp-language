<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('net.xp_framework.unittest.io.archive.vendors.ZipFileVendorTest', 'io.streams.Streams');

  /**
   * Tests 7-ZIP archives
   *
   * @see   http://www.7-zip.org/
   */
  class SevenZipFileTest extends ZipFileVendorTest {
    
    /**
     * Returns vendor name
     *
     * @return  string
     */
    protected function vendorName() {
      return '7zip';
    }
    
    /**
     * Assertion helper
     *
     * @param   io.archive.zip.ZipArchiveReader reader
     * @throws  unittest.AssertionFailedError
     */
    protected function assertCompressedEntryIn($reader) {
      $entry= $reader->iterator()->next();
      $this->assertEquals('compression.txt', $entry->getName());
      $this->assertEquals(1660, $entry->getSize());
      
      with ($is= $entry->getInputStream()); {
        $this->assertEquals('This file is to be compressed', $is->read(29));
        $is->read(1630);
        $this->assertEquals('.', $is->read(1));
      }
    }

    /**
     * Tests deflate algorithm
     *
     */
    #[@test]
    public function deflate() {
      $this->assertCompressedEntryIn($this->archiveReaderFor($this->vendor, 'deflate'));
    }

    /**
     * Tests bzip2 algorithm
     *
     */
    #[@test]
    public function bzip2() {
      $this->assertCompressedEntryIn($this->archiveReaderFor($this->vendor, 'bzip2'));
    }

    /**
     * Tests deflate64 algorithm
     *
     */
    #[@test, @ignore('Not yet supported')]
    public function deflate64() {
      $this->assertCompressedEntryIn($this->archiveReaderFor($this->vendor, 'deflate64'));
    }

    /**
     * Tests lzma algorithm
     *
     */
    #[@test, @ignore('Not yet supported')]
    public function lzma() {
      $this->assertCompressedEntryIn($this->archiveReaderFor($this->vendor, 'lzma'));
    }

    /**
     * Tests ppmd algorithm
     *
     */
    #[@test, @ignore('Not yet supported')]
    public function ppmd() {
      $this->assertCompressedEntryIn($this->archiveReaderFor($this->vendor, 'ppmd'));
    }
  }
?>
