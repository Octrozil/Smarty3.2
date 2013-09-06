<?php /* Smarty version Smarty 3.2-DEV, created on 2013-09-05 23:40:19 compiled from "..\helloworld.tpl" */
if (!class_exists('_SmartyTemplate_522916632ad143_22381139',false)) {
    class _SmartyTemplate_522916632ad143_22381139 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '8920ee950c8dc35873491e54359caa80708f10d1' => array(
                        0 => '..\helloworld.tpl',
                        1 => 1378424418,
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
$this->class_name = '_SmartyTemplate_522916632ad143_22381139';
