<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'rdbms.Criteria',
    'scriptlet.xml.workflow.facade.dataset.DataSetHandler'
  );
  
  /**
   * Handler for editing DataSets
   *
   * @see      xp://scriptlet.xml.workflow.facade.DataSetFacade
   * @purpose  Generic Handler
   */
  class EditDataSetHandler extends DataSetHandler {

    /**
     * Retrieve the entity by its primary key
     *
     * @param   rdbms.Peer
     * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   scriptlet.xml.Context context
     * @return  rdbms.DataSet
     */
    protected function getEntity($peer, $request, $context) {
      $criteria= new Criteria();
      foreach ($peer->primary as $field) {
        $criteria->add($field, $request->getParam($field), EQUAL);
      }

      return $peer->iteratorFor($criteria)->next();
    }
    
    /**
     * Handle the entity that has been worked on
     *
     * @param   rdbms.DataSet
     * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   scriptlet.xml.Context context
     */
    protected function handleSubmittedEntity($entity, $request, $context) {
      foreach ($this->wrapper->getParamNames() as $field) {
        $entity->set($field, $this->wrapper->getValue($field));
      }

      $entity->update();
    }
  }
?>
