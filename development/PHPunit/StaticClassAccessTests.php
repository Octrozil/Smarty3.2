<?php
/**
 * Smarty PHPunit tests static class access to constants, variables and methodes
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for static class access to constants, variables and methodes tests
 */
class StaticClassAccessTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        SmartyTests::init();
        $this->smarty->disableSecurity();
    }

    public static function isRunnable()
    {
        return false;
    }

    /**
     * test static class variable
     */
    public function testStaticClassVariable()
    {
        $tpl = $this->smarty->createTemplate('string:{mystaticclass::$static_var}');
        $this->assertEquals('5', $this->smarty->fetch($tpl));
    }

    /**
     * test registered static class variable
     */
    public function testStaticRegisteredClassVariable()
    {
        $this->smarty->registerClass('registeredclass', 'mystaticclass');
        $tpl = $this->smarty->createTemplate('string:{registeredclass::$static_var}');
        $this->assertEquals('5', $this->smarty->fetch($tpl));
    }

    /**
     * test static class constant
     */
    public function testStaticClassConstant()
    {
        $tpl = $this->smarty->createTemplate('string:{mystaticclass::STATIC_CONSTANT_VALUE}');
        $this->assertEquals('3', $this->smarty->fetch($tpl));
    }

    /**
     * test static class constant
     */
    public function testRegisteredStaticClassConstant()
    {
        $this->smarty->registerClass('registeredclass', 'mystaticclass');
        $tpl = $this->smarty->createTemplate('string:{registeredclass::STATIC_CONSTANT_VALUE}');
        $this->assertEquals('3', $this->smarty->fetch($tpl));
    }

    /**
     * test static class methode
     */
    public function testStaticClassMethode()
    {
        $tpl = $this->smarty->createTemplate('string:{mystaticclass::square(5)}');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
     * test static class methode
     */
    public function testRegisteredStaticClassMethode()
    {
        $this->smarty->registerClass('registeredclass', 'mystaticclass');
        $tpl = $this->smarty->createTemplate('string:{registeredclass::square(5)}');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
     * test static class variable methode
     */
    public function testStaticClassVariableMethode()
    {
        $tpl = $this->smarty->createTemplate('string:{$foo=\'square\'}{mystaticclass::$foo(5)}');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
     * test registered static class variable methode
     */
    public function testRegisteredStaticClassVariableMethode()
    {
        $this->smarty->registerClass('registeredclass', 'mystaticclass');
        $tpl = $this->smarty->createTemplate('string:{$foo=\'square\'}{registeredclass::$foo(5)}');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
     * test static class variable methode
     */
    public function testStaticClassVariableMethode2()
    {
        $tpl = $this->smarty->createTemplate('string:{mystaticclass::$foo(5)}');
        $tpl->assign('foo', 'square');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }

    /**
     * test registered static class variable methode
     */
    public function testRegisteredStaticClassVariableMethode2()
    {
        $this->smarty->registerClass('registeredclass', 'mystaticclass');
        $tpl = $this->smarty->createTemplate('string:{registeredclass::$foo(5)}');
        $tpl->assign('foo', 'square');
        $this->assertEquals('25', $this->smarty->fetch($tpl));
    }
}

class mystaticclass
{
    const STATIC_CONSTANT_VALUE = 3;
    public static $static_var = 5;

    public static function square($i)
    {
        return $i * $i;
    }
}
