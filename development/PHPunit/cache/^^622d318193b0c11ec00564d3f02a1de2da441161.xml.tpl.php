<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:47 */
if (!class_exists('_SmartyTemplate_51db55073b8942_76328851',false)) {
    class _SmartyTemplate_51db55073b8942_76328851 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $is_cache = true;
        public $cache_lifetime = 1000;
        public $file_dependency = array(
                '622d318193b0c11ec00564d3f02a1de2da441161' => array(
                        0 => '.\templates\xml.tpl',
                        1 => 1347714121,
                        2 => 'file'
                    )
            );

        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    
                );
        }
    }
}
$this->template_obj = new _SmartyTemplate_51db55073b8942_76328851($tpl_obj, $this);

