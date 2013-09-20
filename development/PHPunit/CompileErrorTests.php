<?php
/**
 * Smarty PHPunit tests compiler errors
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for compiler tests
 */
class CompileErrorTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
    }

    public static function isRunnable()
    {
        return true;
    }

    /**
     * test none existing template file error
     */
    public function testNoneExistingTemplateError()
    {
        try {
            $this->smarty->fetch('eval:{include file=\'no.tpl\'}');
        } catch (Exception $e) {
            $this->assertContains("Can not find source 'file:no.tpl'" , $e->getMessage());
            return;
        }
        $this->fail('Exception for none existing template has not been raised.');
    }

    /**
     * test unkown tag error
     */
    public function testUnknownTagError()
    {
        try {
            $this->smarty->fetch('eval:{unknown}');
        } catch (Exception $e) {
            $this->assertContains('unknown tag \'{unknown...}\'', $e->getMessage());

            return;
        }
        $this->fail('Exception for unknown Smarty tag has not been raised.');
    }

    /**
     * test unclosed tag error
     */
    public function testUnclosedTagError()
    {
        try {
            $this->smarty->fetch('eval:{if true}');
        } catch (Exception $e) {
            $this->assertContains('unclosed {if} tag', $e->getMessage());

            return;
        }
        $this->fail('Exception for unclosed Smarty tags has not been raised.');
    }

    /**
     * test syntax error
     */
    public function testSyntaxError()
    {
        try {
            $this->smarty->fetch('eval:{assign var=}');
        } catch (Exception $e) {
            $this->assertContains('at line 1 "{assign var=}" ', $e->getMessage());
            $this->assertContains("Syntax error :Unexpected '<b>}</b>' in ", $e->getMessage());
            return;
        }
        $this->fail('Exception for syntax error has not been raised.');
    }

    /**
     * test empty templates
     */
    public function testEmptyTemplate()
    {
        $tpl = $this->smarty->createTemplate('eval:');
        $this->assertEquals('', $this->smarty->fetch($tpl));
    }

}
