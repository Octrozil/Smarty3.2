<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:47 compiled from ".\templates\assign.parent.tpl" */
if (!class_exists('_SmartyTemplate_51db5507110dd6_90746766',false)) {
    class _SmartyTemplate_51db5507110dd6_90746766 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '84e26627682853100dd26c373bda360efe3dd4cb' => array(
                        0 => '.\templates\assign.parent.tpl',
                        1 => 1347714122,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $_scope->parent = new Smarty_Variable("parent", false);
            if ($_scope->___attributes->parent_scope != null) {
                $_scope->___attributes->parent_scope->parent = clone $_scope->parent;
            }
            echo " ";
            echo  (($tmp = isset($_scope->local) ? $_scope->local : $_smarty_tpl->getVariable('local', null, true, false))===null||$tmp->value==='' ? "no-local" : $tmp->value);
            echo " ";
            echo $this->_getSubTemplate ("assign.root.tpl", $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, 0, array(), 0, $_scope, null);
            echo " ";
            echo  (($tmp = isset($_scope->parent) ? $_scope->parent : $_smarty_tpl->getVariable('parent', null, true, false))===null||$tmp->value==='' ? "no-parent" : $tmp->value);
            echo " ";
            echo  (($tmp = isset($_scope->root) ? $_scope->root : $_smarty_tpl->getVariable('root', null, true, false))===null||$tmp->value==='' ? "no-root" : $tmp->value);
            echo " ";
            echo  (($tmp = isset($_scope->global) ? $_scope->global : $_smarty_tpl->getVariable('global', null, true, false))===null||$tmp->value==='' ? "no-global" : $tmp->value);
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->template_obj = new _SmartyTemplate_51db5507110dd6_90746766($tpl_obj, $this);

