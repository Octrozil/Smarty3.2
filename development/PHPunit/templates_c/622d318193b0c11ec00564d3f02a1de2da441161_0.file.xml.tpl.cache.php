<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:47 compiled from ".\templates\xml.tpl" */
if (!class_exists('_SmartyTemplate_51db550739b395_63718154',false)) {
    class _SmartyTemplate_51db550739b395_63718154 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '622d318193b0c11ec00564d3f02a1de2da441161' => array(
                        0 => '.\templates\xml.tpl',
                        1 => 1347714121,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            echo '<?xml';
            echo " version=\"1.0\" encoding=\"UTF-8\"";
            echo '?>';
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1
                );
        }
    }
}
$this->template_obj = new _SmartyTemplate_51db550739b395_63718154($tpl_obj, $this);

