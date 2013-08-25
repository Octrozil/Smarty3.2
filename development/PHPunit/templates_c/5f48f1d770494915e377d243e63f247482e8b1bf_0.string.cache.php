<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-09 00:10:46 compiled from "5f48f1d770494915e377d243e63f247482e8b1bf" */
if (!class_exists('_SmartyTemplate_51db5506d2ee50_66610198',false)) {
    class _SmartyTemplate_51db5506d2ee50_66610198 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = true;
        public $file_dependency = array(
                '5f48f1d770494915e377d243e63f247482e8b1bf' => array(
                        0 => '5f48f1d770494915e377d243e63f247482e8b1bf',
                        1 => 0,
                        2 => 'string'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $_scope->vars = new Smarty_Variable(array(1,2,3,4,5), false);
            $_scope->var = new Smarty_Variable;
            $_scope->var->_loop = false;
            $_from = $_scope->vars->value;
            if (!is_array($_from) && !is_object($_from)) {
                settype($_from, 'array');
            }
            foreach ($_from as $_scope->var->key => $_scope->var->value) {
                $_scope->var->_loop = true;
                $_scope->v = new Smarty_Variable($_scope->var->value, false);
                echo "/*%%SmartyNocache%%*/// line 1echo  \$_scope->v->value;/*/%%SmartyNocache%%*/";
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
$this->template_obj = new _SmartyTemplate_51db5506d2ee50_66610198($tpl_obj, $this);

