<?php namespace xp\compiler\checks;

/**
 * Emits a warning when global constants are used
 *
 * @test    xp://tests.checks.ConstantsAreDiscouragedTest
 */
class ConstantsAreDiscouraged extends \lang\Object implements Check {

  /**
   * Return node this check works on
   *
   * @return  lang.XPClass<? extends xp.compiler.ast.Node>
   */
  public function node() {
    return \lang\XPClass::forName('xp.compiler.ast.ConstantNode');
  }

  /**
   * Return whether this check is to be run deferred
   *
   * @return  bool
   */
  public function defer() {
    return false;
  }
  
  /**
   * Executes this check
   *
   * @param   xp.compiler.ast.Node node
   * @param   xp.compiler.types.Scope scope
   * @return  bool
   */
  public function verify(\xp\compiler\ast\Node $node, \xp\compiler\types\Scope $scope) {
    return ['T203', 'Global constants ('.\cast($node, 'xp.compiler.ast.ConstantNode')->name.') are discouraged'];
  }
}
