<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Renderer
   *
   * @see      xp://img.chart.Chart
   * @purpose  Interface
   */
  interface GraphRenderer {
  
    /**
     * Renders a chart
     *
     * @param   &img.chart.Chart chart
     * @return  &mixed
     */
    public function render($chart);
  }
?>
