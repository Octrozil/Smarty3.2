<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-14 22:36:36 compiled from ".\relative.tpl" */
if (!class_exists('_SmartyTemplate_51e327f4610392_68796809',false)) {
    class _SmartyTemplate_51e327f4610392_68796809 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                'a46a3db0cd2f5d3ff7ed6ce7095ba9fe28e787ca' => array(
                        0 => '.\relative.tpl',
                        1 => 1347714119,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            echo $this->_getSubTemplate ("../helloworld.tpl", $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, 0, array(), 0, $_scope, null);
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->class_name = '_SmartyTemplate_51e327f4610392_68796809';
