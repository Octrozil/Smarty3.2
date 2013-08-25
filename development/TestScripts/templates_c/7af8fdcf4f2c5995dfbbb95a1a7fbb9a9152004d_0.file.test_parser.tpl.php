<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-13 22:08:53 compiled from ".\templates\test_parser.tpl" */
if (!class_exists('_SmartyTemplate_51e1cff56ea336_56112142',false)) {
    class _SmartyTemplate_51e1cff56ea336_56112142 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '7af8fdcf4f2c5995dfbbb95a1a7fbb9a9152004d' => array(
                        0 => '.\templates\test_parser.tpl',
                        1 => 1367535175,
                        2 => 'file'
                    )
            );
        public $required_plugins = array(
                'C:\wamp\www\Smarty3.2\lib\Smarty\Plugins\function.html_checkboxes.php' => 'smarty_function_html_checkboxes'
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            echo "Input form for parser testing <BR>\n<form name=\"Testparser\" action=\"test_parser.php";
                // line 2
            $_tmp1 = @$_REQUEST['XDEBUG_PROFILE'];
            if (isset($_tmp1)) {
                echo "?XDEBUG_PROFILE";
            }
            echo "\"\n      method=\"post\">\n    <strong>Template input</strong>\n    <textarea name=\"template\" rows=\"10\" cols=\"60\">";
            // line 5
            echo  htmlspecialchars($_scope->template->value, ENT_QUOTES, 'UTF-8', true);
            echo "</textarea><br>\n    ";
            // line 6
            echo smarty_function_html_checkboxes(array('name'=>'debug','values'=>1,'output'=>'Debug'),$_smarty_tpl);
            echo "\n    <input name=\"Update\" type=\"submit\" value=\"Update\">\n</form>\n\n";
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    19 => 1,
                    21 => 2,
                    27 => 5,
                    30 => 6
                );
        }
    }
}
$class_name = '_SmartyTemplate_51e1cff56ea336_56112142';
