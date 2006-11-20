<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
 
  uses(
    'unittest.TestCase',
    'net.xp_framework.tools.vm.Parser',
    'net.xp_framework.tools.vm.Lexer'
  );

  /**
   * Tests PHP5 parseter
   *
   * @purpose  Unit Test
   */
  class DeprecatedTest extends TestCase {

    /**
     * Parses a given string source into an AST.
     *
     * @access  protected
     * @param   string source
     * @return  net.xp_framework.tools.vm.VNode[]
     * @throws  lang.FormatException in case errors occur during parse()
     */
    function &parse($source) {
      $parser= &new Parser();
      $nodes= $parser->yyparse(new Lexer($source, '(string)'));
      
      if ($parser->hasErrors()) {
        $message= 'Errors found: {';
        foreach ($parser->getErrors() as $error) {
          $message.= "\n  * ".$error->toString();
        }
        $message.= "\n}";
        return throw(new FormatException($message));
      }
      
      return $nodes;
    }

    /**
     * Tests old foreach: endforeach syntax is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedForeach() {
      $this->parse('foreach ($array as $key => $value): endforeach');
    }

    /**
     * Tests old for: endfor syntax is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedFor() {
      $this->parse('for ($i= 0; $i < 10; $i++): endfor');
    }

    /**
     * Tests old if: endif syntax is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedIf() {
      $this->parse('if ($i): endif');
    }

    /**
     * Tests old while: endwhile syntax is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedWhile() {
      $this->parse('while ($i): endwhile');
    }

    /**
     * Tests old if: endswitch syntax is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedSwitch() {
      $this->parse('switch ($foo):
        case 1:
        case 2:
        default: break;
      endswitch;');
    }


    /**
     * Tests "new Class;" - without (...) - is deprecated
     *
     * @access  public
     */
    #[@test, @expect('lang.FormatException')]
    function deprecatedConstructorWithoutBraces() {
      $this->parse('class Foo { } new Foo;');
    }
  }
?>
