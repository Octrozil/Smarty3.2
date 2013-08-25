<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:46 */
if (!class_exists('_SmartyTemplate_51db5506e5cff3_89886838',false)) {
    class _SmartyTemplate_51db5506e5cff3_89886838 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = true;
        public $is_cache = true;
        public $cache_lifetime = 20;
        public $file_dependency = array(
                'a61d24c4704b3841bb2115d86d5bbfff007fcfa8' => array(
                        0 => 'a61d24c4704b3841bb2115d86d5bbfff007fcfa8',
                        1 => 0,
                        2 => 'string'
                    )
            );

        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            $_scope->v = new Smarty_Variable (1);
            // line 1
            echo  $_scope->v->value;
            $_scope->v = new Smarty_Variable (2);
            echo  $_scope->v->value;
            $_scope->v = new Smarty_Variable (3);
            echo  $_scope->v->value;
            $_scope->v = new Smarty_Variable (4);
            echo  $_scope->v->value;
            $_scope->v = new Smarty_Variable (5);
            echo  $_scope->v->value;
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    18 => "1"
                );
        }
    }
}
$this->template_obj = new _SmartyTemplate_51db5506e5cff3_89886838($tpl_obj, $this);

