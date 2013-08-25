<?php /* Smarty version Smarty 3.2-DEV, created on 2013-08-25 22:27:44 compiled from "..\helloworld.tpl" */
if (!class_exists('_SmartyTemplate_521a84e09861b8_94654601',false)) {
    class _SmartyTemplate_521a84e09861b8_94654601 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '8920ee950c8dc35873491e54359caa80708f10d1' => array(
                        0 => '..\helloworld.tpl',
                        1 => 1377469663,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            echo "hello world";
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->class_name = '_SmartyTemplate_521a84e09861b8_94654601';
