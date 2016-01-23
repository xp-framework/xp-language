<?php namespace net\xp_lang\tests\syntax\xp;

use xp\compiler\syntax\xp\Parser;

/**
 * TestCase
 *
 * @see      xp://xp.compiler.syntax.xp.Lexer
 */
abstract class LexerTest extends \unittest\TestCase {

  /**
   * Creates a lexer instance
   *
   * @param   string in
   * @return  xp.compiler.syntax.xp.Lexer
   */
  protected abstract function newLexer($in);

  /**
   * Returns an array of tokens for a given input string
   *
   * @param   string in
   * @return  array<int, string>[] tokens
   */
  protected function tokensOf($in) {
    $l= $this->newLexer($in);
    $tokens= [];
    do {
      try {
        if ($r= $l->advance()) {
          $tokens[]= [$l->token, $l->value, $l->position];
        }
      } catch (\lang\Throwable $e) {
        $tokens[]= [nameof($e), $e->getMessage()];
        $r= false;
      }
    } while ($r);
    return $tokens;
  }

  /**
   * Test parsing a class declaration
   *
   */
  #[@test]
  public function classDeclaration() {
    $t= $this->tokensOf('public class Point { }');
    $this->assertEquals([Parser::T_PUBLIC, 'public', [1, 1]], $t[0]);
    $this->assertEquals([Parser::T_CLASS, 'class', [1, 8]], $t[1]);
    $this->assertEquals([Parser::T_WORD, 'Point', [1, 14]], $t[2]);
    $this->assertEquals([123, '{', [1, 20]], $t[3]);
    $this->assertEquals([125, '}', [1, 22]], $t[4]);
  }

  /**
   * Test parsing a one-line comment at the end of a line
   *
   */
  #[@test]
  public function commentAtEnd() {
    $t= $this->tokensOf('$a++; // HACK');
    $this->assertEquals([Parser::T_VARIABLE, 'a', [1, 1]], $t[0]);
    $this->assertEquals([Parser::T_INC, '++', [1, 3]], $t[1]);
    $this->assertEquals([59, ';', [1, 5]], $t[2]);
  }

  /**
   * Test parsing a doc-comment
   *
   */
  #[@test]
  public function docComment() {
    $t= $this->tokensOf('
      /**
       * Doc-Comment
       *
       * @see http://example.com
       */  
      public void init() { }
    ');
    $this->assertEquals([Parser::T_PUBLIC, 'public', [7, 7]], $t[0]);
    $this->assertEquals([Parser::T_WORD, 'void', [7, 14]], $t[1]);
    $this->assertEquals([Parser::T_WORD, 'init', [7, 19]], $t[2]);
    $this->assertEquals([40, '(', [7, 23]], $t[3]);
    $this->assertEquals([41, ')', [7, 24]], $t[4]);
    $this->assertEquals([123, '{', [7, 26]], $t[5]);
    $this->assertEquals([125, '}', [7, 28]], $t[6]);
  }

  /**
   * Test parsing a double-quoted string
   *
   */
  #[@test]
  public function dqString() {
    $t= $this->tokensOf('$s= "Hello World";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, 'Hello World', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 18]], $t[3]);
  }

  /**
   * Test parsing an empty double-quoted string
   *
   */
  #[@test]
  public function emptyDqString() {
    $t= $this->tokensOf('$s= "";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 7]], $t[3]);
  }

  /**
   * Test parsing an multi-line double-quoted string
   *
   */
  #[@test]
  public function multiLineDqString() {
    $t= $this->tokensOf('$s= "'."\n\n".'";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, "\n\n", [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [3, 2]], $t[3]);
  }

  /**
   * Test parsing a double-quoted string with escapes
   *
   */
  #[@test]
  public function dqStringWithEscapes() {
    $t= $this->tokensOf('$s= "\"Hello\", he said";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '"Hello", he said', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 25]], $t[3]);
  }

  /**
   * Provides values for escape_expansion test
   */
  public function escapes() {
    return [
      ['r', "\x0d"],
      ['n', "\x0a"],
      ['t', "\x09"],
      ['b', "\x08"],
      ['f', "\x0c"],
      ['\\', "\x5c"]
    ];
  }

  /**
   * Test escape sequences
   */
  #[@test, @values('escapes')]
  public function escape_expansion($escape, $expanded) {
    $this->assertEquals(
      [
        [Parser::T_VARIABLE, 's', [1, 1]],
        [61, '=', [1, 3]],
        [Parser::T_STRING, '{'.$expanded.'}', [1, 5]],
        [59, ';', [1, 11]]
      ],
      $this->tokensOf('$s= "{\\'.$escape.'}";')
    );
  }

  /**
   * Test illegal escape sequence
   *
   */
  #[@test]
  public function illegalEscapeSequence() {
    $t= $this->tokensOf('$s= "Hell\�";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals(['lang.FormatException', 'Illegal escape sequence \\� in Hell\\� starting at line 1, offset 5'], $t[2]);
  }

  /**
   * Test parsing a single-quoted string
   *
   */
  #[@test]
  public function sqString() {
    $t= $this->tokensOf('$s= \'Hello World\';');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, 'Hello World', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 18]], $t[3]);
  }

  /**
   * Test parsing an empty single-quoted string
   *
   */
  #[@test]
  public function emptySqString() {
    $t= $this->tokensOf('$s= \'\';');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 7]], $t[3]);
  }

  /**
   * Test parsing an multi-line single-quoted string
   *
   */
  #[@test]
  public function multiLineSqString() {
    $t= $this->tokensOf('$s= \''."\n\n".'\';');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, "\n\n", [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [3, 2]], $t[3]);
  }

  /**
   * Test parsing a single-quoted string with escapes
   *
   */
  #[@test]
  public function sqStringWithEscapes() {
    $t= $this->tokensOf('$s= \'\\\'Hello\\\', he said\';');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '\'Hello\', he said', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 25]], $t[3]);
  }

  /**
   * Test string at end
   *
   */
  #[@test]
  public function stringAsLastToken() {
    $t= $this->tokensOf('"Hello World"');
    $this->assertEquals(1, sizeof($t));
    $this->assertEquals([Parser::T_STRING, 'Hello World', [1, 1]], $t[0]);
  }

  /**
   * Test parsing an unterminated string
   *
   */
  #[@test]
  public function unterminatedString() {
    $t= $this->tokensOf('$s= "The end');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals(['lang.IllegalStateException', 'Unterminated string literal starting at line 1, offset 5'], $t[2]);
  }

  /**
   * Test a backslash inside a double quoted string ("\\\\")
   *
   */
  #[@test]
  public function dqBackslash() {
    $t= $this->tokensOf('$s= "\\\\";');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '\\', [1, 5]], $t[2]);
  }

  /**
   * Test a backslash inside a single quoted string ('\\')
   *
   */
  #[@test]
  public function sqBackslash() {
    $t= $this->tokensOf('$s= \'\\\\\';');
    $this->assertEquals([Parser::T_VARIABLE, 's', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_STRING, '\\', [1, 5]], $t[2]);
  }

  /**
   * Test decimal number
   *
   */
  #[@test]
  public function decimalNumber() {
    $t= $this->tokensOf('$i= 1.0;');
    $this->assertEquals([Parser::T_VARIABLE, 'i', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_DECIMAL, '1.0', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 8]], $t[3]);
  }

  /**
   * Test illegal decimal number
   *
   */
  #[@test]
  public function illegalDecimalNumber() {
    $t= $this->tokensOf('$i= 1.a;');
    $this->assertEquals([Parser::T_VARIABLE, 'i', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals(['lang.FormatException', 'Illegal decimal number <1.a> starting at line 1, offset 5'], $t[2]);
  }

  /**
   * Test hex number
   *
   */
  #[@test]
  public function hexNumber() {
    $t= $this->tokensOf('$i= 0xFF;');
    $this->assertEquals([Parser::T_VARIABLE, 'i', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals([Parser::T_HEX, '0xFF', [1, 5]], $t[2]);
    $this->assertEquals([59, ';', [1, 9]], $t[3]);
  }

  /**
   * Test illegal decimal number
   *
   */
  #[@test]
  public function illegalHexNumber() {
    $t= $this->tokensOf('$i= 0xZ;');
    $this->assertEquals([Parser::T_VARIABLE, 'i', [1, 1]], $t[0]);
    $this->assertEquals([61, '=', [1, 3]], $t[1]);
    $this->assertEquals(['lang.FormatException', 'Illegal hex number <0xZ> starting at line 1, offset 5'], $t[2]);
  }
}
