<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('img.shapes.Arc');
  
  /**
   * Shape class representing a three-dimensional arc
   *
   * <code>
   *   $i= &new PngImage(300, 300);
   *   $i->create();
   *   $b= &$i->allocate(new Color('#0000cc'));
   *   $d= &$i->allocate(new Color('#000066'));
   *   $i->draw(new Arc3DShape(array($b, $d), 200, 100, 200, 100, 0, 320));
   *   $i->toFile(new File('out.png'));
   * </code>
   *
   * @see img.Image
   */
  class Arc3D extends Arc {
    var
      $colors= array(),
      $shadow= 0;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &img.Color[] col colors, the first for the "lid", the second for the shadow
     * @param   int cx x center of circle
     * @param   int cy y center of circle
     * @param   int w width
     * @param   int h height
     * @param   int s default 0 start
     * @param   int e default 360 end
     * @param   int fill default IMG_ARC_PIE one of
     *          IMG_ARC_PIE
     *          IMG_ARC_CHORD
     *          IMG_ARC_NOFILL
     *          IMG_ARC_EDGED
     * @param   int shadow default 10 
     */ 
    function __construct(&$colors, $cx, $cy, $w, $h, $s= 0, $e= 360, $fill= IMG_ARC_PIE, $shadow= 10) {
      $this->colors= &$colors;
      $this->shadow= $shadow;
      parent::__construct($colors[0], $cx, $cy, $w, $h, $s, $e, $fill);
    }

    /**
     * Draw function
     *
     * @access  public
     * @param   &resource hdl an image resource
     */
    function draw(&$hdl) {
      $this->col= &$this->colors[1];
      $cy= $this->cy;
      for ($i= 1; $i < $this->shadow; $i++) {
        $this->cy= $cy+ $i;
        parent::draw($hdl);
      }
      $this->cy= $cy;
      $this->col= &$this->colors[0];
      parent::draw($hdl);
    }
  }
?>
