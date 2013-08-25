<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:47 compiled from ".\templates\assign.global.tpl" */
if (!class_exists('_SmartyTemplate_51db550719d624_59413432',false)) {
    class _SmartyTemplate_51db550719d624_59413432 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '645522cd57565c081dc85607a98eb6135ca7b84f' => array(
                        0 => '.\templates\assign.global.tpl',
                        1 => 1347714121,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $_scope->global = new Smarty_Variable("global", false);
            $_ptr = $_scope->___attributes->parent_scope;
            while ($_ptr != null) {
                $_ptr->global = clone $_scope->global;
                $_ptr = $_ptr->___attributes->parent_scope;
            }
            Smarty::$global_tpl_vars->global =  clone $_scope->global;
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->template_obj = new _SmartyTemplate_51db550719d624_59413432($tpl_obj, $this);

