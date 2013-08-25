<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:47 compiled from ".\templates\assign.root.tpl" */
if (!class_exists('_SmartyTemplate_51db550716e8e8_88074842',false)) {
    class _SmartyTemplate_51db550716e8e8_88074842 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '8d0e795f16cf7dca37a30a29c4e07176ee9c6d20' => array(
                        0 => '.\templates\assign.root.tpl',
                        1 => 1347714118,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $_scope->root = new Smarty_Variable("root", false);
            $_ptr = $_scope->___attributes->parent_scope;
            while ($_ptr != null) {
                $_ptr->root = clone $_scope->root;
                $_ptr = $_ptr->___attributes->parent_scope;
            }
            echo " ";
            echo  (($tmp = isset($_scope->local) ? $_scope->local : $_smarty_tpl->getVariable('local', null, true, false))===null||$tmp->value==='' ? "no-local" : $tmp->value);
            echo " ";
            echo $this->_getSubTemplate ("assign.global.tpl", $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, 0, array(), 0, $_scope, null);
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
$this->template_obj = new _SmartyTemplate_51db550716e8e8_88074842($tpl_obj, $this);

