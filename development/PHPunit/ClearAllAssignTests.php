<?php
/**
 * Smarty PHPunit tests clearing all assigned variables
 *
 * @package PHPunit
 * @author Uwe Tews
 */

/**
 * class for clearing all assigned variables tests
 */
class ClearAllAssignTests extends PHPUnit_Framework_TestCase
{

    protected $_data = null;
    protected $_tpl = null;
    protected $_dataBC = null;
    protected $_tplBC = null;

    public function setUp()
    {
        $this->smarty = SmartyTests::$smarty;
        $this->smartyBC = SmartyTests::$smartyBC;
        SmartyTests::init();

        $this->smarty->error_unassigned = Smarty::UNASSIGNED_IGNORE;
        $this->smartyBC->error_unassigned = Smarty::UNASSIGNED_IGNORE;
        $this->smarty->assign('foo', 'foo');
        $this->_data = $this->smarty->createData($this->smarty);
        $this->_data->assign('bar', 'bar');
        $this->_tpl = $this->smarty->createTemplate('eval:{$foo}{$bar}{$blar}', null, null, $this->_data);
        $this->_tpl->assign('blar', 'blar');

        $this->smartyBC->assign('foo', 'foo');
        $this->_dataBC = $this->smarty->createData($this->smartyBC);
        $this->_dataBC->assign('bar', 'bar');
        $this->_tplBC = $this->smartyBC->createTemplate('eval:{$foo}{$bar}{$blar}', null, null, $this->_dataBC);
        $this->_tplBC->assign('blar', 'blar');
    }

    public static function isRunnable()
    {
        return true;
    }

    /**
     * test all variables accessable
     */
    public function testAllVariablesAccessable()
    {
        $this->assertEquals('foobarblar', $this->smarty->fetch($this->_tpl));
    }

    /**
     * test clear all assign in template
     */
    public function testClearAllAssignInTemplate()
    {
        $this->_tpl->clearAllAssign();
        $this->assertEquals('foobar', $this->smarty->fetch($this->_tpl));
    }

    /**
     * test clear all assign in data
     */
    public function testClearAllAssignInData()
    {
        $this->smarty->error_unassigned = Smarty::UNASSIGNED_IGNORE;
        $this->_data->clearAllAssign();
        $this->tpl = $this->smarty->createTemplate('eval:{$foo}{$bar}{$blar}', null, null, $this->_data);
        $this->tpl->assign('blar', 'blar');
        $this->assertEquals('fooblar', $this->smarty->fetch($this->tpl));
    }

    /**
     * test clear all assign in Smarty object
     */
    public function testClearAllAssignInSmarty()
    {
        $this->smarty->clearAllAssign();
        $this->assertEquals('barblar', $this->smarty->fetch($this->_tpl));
    }

    public function testSmarty2ClearAllAssignInSmarty()
    {
        $this->smartyBC->clear_all_assign();
        $this->assertEquals('barblar', $this->smartyBC->fetch($this->_tplBC));
    }
}
