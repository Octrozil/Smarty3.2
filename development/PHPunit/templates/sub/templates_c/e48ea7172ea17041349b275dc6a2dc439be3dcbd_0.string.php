<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-14 20:24:56 compiled from "e48ea7172ea17041349b275dc6a2dc439be3dcbd" */
if (!class_exists('_SmartyTemplate_51e30918e9a7b3_82686083',false)) {
    class _SmartyTemplate_51e30918e9a7b3_82686083 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                'e48ea7172ea17041349b275dc6a2dc439be3dcbd' => array(
                        0 => 'e48ea7172ea17041349b275dc6a2dc439be3dcbd',
                        1 => 0,
                        2 => 'string'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $this->_createLocalArrayVariable('foo', $_scope, false);
            $_scope->foo->value[] = 2;
            $_ptr = $_scope->___attributes->parent_scope;
            while ($_ptr != null) {
                $_ptr->foo = clone $_scope->foo;
                $_ptr = $_ptr->___attributes->parent_scope;
            }
            $_scope->x = new Smarty_Variable;
            $_scope->x->_loop = false;
            $_from = $_scope->foo->value;
            if (!is_array($_from) && !is_object($_from)) {
                settype($_from, 'array');
            }
            foreach ($_from as $_scope->x->key => $_scope->x->value) {
                $_scope->x->_loop = true;
                echo  $_scope->x->key;
                echo  $_scope->x->value;
            }
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->class_name = '_SmartyTemplate_51e30918e9a7b3_82686083';
