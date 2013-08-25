<?php /* Smarty version Smarty 3.2-DEV, created on 2013-07-13 22:08:53 compiled from "C:\wamp\www\Smarty3.2\lib\Smarty\debug.tpl" */
if (!class_exists('_SmartyTemplate_51e1cff59a0cb5_57732955',false)) {
    class _SmartyTemplate_51e1cff59a0cb5_57732955 extends Smarty_Template_Class {
        public $version = 'Smarty 3.2-DEV';
        public $has_nocache_code = false;
        public $file_dependency = array(
                '9a9f485ff6ed4f0961fc80196b7316710d191ab3' => array(
                        0 => 'C:\wamp\www\Smarty3.2\lib\Smarty\debug.tpl',
                        1 => 1316790988,
                        2 => 'file'
                    )
            );


        function _renderTemplate ($_smarty_tpl, $_scope) {
            ob_start();
            // line 1
            $this->_capture_stack[0][] = array('_smarty_debug', 'debug_output', null);
            ob_start();
            echo "\r\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\r\n<head>\r\n    <title>Smarty Debug Console</title>\r\n<style type=\"text/css\">\r\n";
            echo "\r\nbody, h1, h2, td, th, p {\r\n    font-family: sans-serif;\r\n    font-weight: normal;\r\n    font-size: 0.9em;\r\n    margin: 1px;\r\n    padding: 0;\r\n}\r\n\r\nh1 {\r\n    margin: 0;\r\n    text-align: left;\r\n    padding: 2px;\r\n    background-color: #f0c040;\r\n    color:  black;\r\n    font-weight: bold;\r\n    font-size: 1.2em;\r\n }\r\n\r\nh2 {\r\n    background-color: #9B410E;\r\n    color: white;\r\n    text-align: left;\r\n    font-weight: bold;\r\n    padding: 2px;\r\n    border-top: 1px solid black;\r\n}\r\n\r\nbody {\r\n    background: black; \r\n}\r\n\r\np, table, div {\r\n    background: #f0ead8;\r\n} \r\n\r\np {\r\n    margin: 0;\r\n    font-style: italic;\r\n    text-align: center;\r\n}\r\n\r\ntable {\r\n    width: 100%;\r\n}\r\n\r\nth, td {\r\n    font-family: monospace;\r\n    vertical-align: top;\r\n    text-align: left;\r\n    width: 50%;\r\n}\r\n\r\ntd {\r\n    color: green;\r\n}\r\n\r\n.odd {\r\n    background-color: #eeeeee;\r\n}\r\n\r\n.even {\r\n    background-color: #fafafa;\r\n}\r\n\r\n.exectime {\r\n    font-size: 0.8em;\r\n    font-style: italic;\r\n}\r\n\r\n#table_assigned_vars th {\r\n  "
                , "  color: blue;\r\n}\r\n\r\n#table_config_vars th {\r\n    color: maroon;\r\n}\r\n";
            echo "\r\n</style>\r\n</head>\r\n<body>\r\n\r\n<h1>Smarty Debug Console  -  ";
                // line 89
            $_tmp1 = $_smarty_tpl->getVariable('template_name', null, true, false);
            if (isset($_tmp1->value)) {
                echo  smarty_modifier_debug_print_var($_scope->template_name->value);
            } else {
                echo "Total Time ";
                echo  htmlspecialchars(sprintf("%.5f", $_scope->execution_time->value), ENT_QUOTES, 'UTF-8');
            }
            echo "</h1>\r\n\r\n";
                // line 91
            if (!empty($_scope->template_data->value)) {
                echo "\r\n<h2>included templates &amp; config files (load time in seconds)</h2>\r\n\r\n<div>\r\n";
                    // line 95
                $_scope->template = new Smarty_Variable;
                $_scope->template->_loop = false;
                $_from = $_scope->template_data->value;
                if (!is_array($_from) && !is_object($_from)) {
                    settype($_from, 'array');
                }
                foreach ($_from as $_scope->template->key => $_scope->template->value) {
                    $_scope->template->_loop = true;
                    echo "\r\n  <font color=brown>";
                    // line 96
                    echo  htmlspecialchars($_scope->template->value['name'], ENT_QUOTES, 'UTF-8');
                    echo "</font>\r\n  <span class=\"exectime\">\r\n   (compile ";
                    // line 98
                    echo  htmlspecialchars(sprintf("%.5f", $_scope->template->value['compile_time']), ENT_QUOTES, 'UTF-8');
                    echo ") (render ";
                    echo  htmlspecialchars(sprintf("%.5f", $_scope->template->value['render_time']), ENT_QUOTES, 'UTF-8');
                    echo ") (cache ";
                    echo  htmlspecialchars(sprintf("%.5f", $_scope->template->value['cache_time']), ENT_QUOTES, 'UTF-8');
                    echo ")\r\n  </span>\r\n  <br>\r\n";
                // line 101
                }
                echo "\r\n</div>\r\n";
            // line 103
            }
            echo "\r\n\r\n<h2>assigned template variables</h2>\r\n\r\n<table id=\"table_assigned_vars\">\r\n    ";
                // line 108
            $_scope->vars = new Smarty_Variable;
            $_scope->vars->_loop = false;
            $_from = $_scope->assigned_vars->value;
            if (!is_array($_from) && !is_object($_from)) {
                settype($_from, 'array');
            }
            $_scope->vars->iteration = 0;
            foreach ($_from as $_scope->vars->key => $_scope->vars->value) {
                $_scope->vars->_loop = true;
                $_scope->vars->iteration++;
                echo "\r\n       <tr class=\"";
                    // line 109
                if ($_scope->vars->iteration%2==0) {
                    echo "odd";
                } else {
                    echo "even";
                }
                echo "\">   \r\n       <th>\$";
                // line 110
                echo  htmlspecialchars(htmlspecialchars($_scope->vars->key, ENT_QUOTES, 'UTF-8', true), ENT_QUOTES, 'UTF-8');
                echo "</th>\r\n       <td>";
                // line 111
                echo  smarty_modifier_debug_print_var($_scope->vars->value);
                echo "</td></tr>\r\n    ";
            // line 112
            }
            echo "\r\n</table>\r\n\r\n<h2>assigned config file variables (outer template scope)</h2>\r\n\r\n<table id=\"table_config_vars\">\r\n    ";
                // line 118
            $_scope->vars = new Smarty_Variable;
            $_scope->vars->_loop = false;
            $_from = $_scope->config_vars->value;
            if (!is_array($_from) && !is_object($_from)) {
                settype($_from, 'array');
            }
            $_scope->vars->iteration = 0;
            foreach ($_from as $_scope->vars->key => $_scope->vars->value) {
                $_scope->vars->_loop = true;
                $_scope->vars->iteration++;
                echo "\r\n       <tr class=\"";
                    // line 119
                if ($_scope->vars->iteration%2==0) {
                    echo "odd";
                } else {
                    echo "even";
                }
                echo "\">   \r\n       <th>";
                // line 120
                echo  htmlspecialchars(htmlspecialchars($_scope->vars->key, ENT_QUOTES, 'UTF-8', true), ENT_QUOTES, 'UTF-8');
                echo "</th>\r\n       <td>";
                // line 121
                echo  smarty_modifier_debug_print_var($_scope->vars->value);
                echo "</td></tr>\r\n    ";
            // line 122
            }
            echo "\r\n\r\n</table>\r\n</body>\r\n</html>\r\n";
            // line 127
            list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($this->_capture_stack[0]);
            if (!empty($_capture_buffer)) {
                if (isset($_capture_assign)) {
                    $_smarty_tpl->assign($_capture_assign, ob_get_contents());
                }
                if (isset( $_capture_append)) {
                    $_smarty_tpl->append($_capture_append, ob_get_contents());
                }
                Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
            } else {
                $_smarty_tpl->_capture_error();
            }
            echo "\r\n<script type=\"text/javascript\">\r\n";
            // line 129
            if (isset($_scope->id)) {
                $_scope->id = clone $_scope->id;
                $_scope->id->value = md5((($tmp = isset($_scope->template_name) ? $_scope->template_name : $_smarty_tpl->getVariable('template_name', null, true, false))===null||$tmp->value==='' ? '' : $tmp->value));
            } else {
                $_scope->id = new Smarty_Variable(md5((($tmp = isset($_scope->template_name) ? $_scope->template_name : $_smarty_tpl->getVariable('template_name', null, true, false))===null||$tmp->value==='' ? '' : $tmp->value)), false);
            }
            echo "\r\n    _smarty_console = window.open(\"\",\"console";
            // line 130
            echo  htmlspecialchars($_scope->id->value, ENT_QUOTES, 'UTF-8');
            echo "\",\"width=680,height=600,resizable,scrollbars=yes\");\r\n    _smarty_console.document.write(\"";
            // line 131
            echo  strtr($_scope->debug_output->value, array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", "\n" => "\\n", "</" => "<\/" ));
            echo "\");\r\n    _smarty_console.document.close();\r\n</script>\r\n";
            return ob_get_clean();
        }

        function _getSourceInfo () {
            return array(
                    16 => 1,
                    23 => 89,
                    32 => 91,
                    35 => 95,
                    45 => 96,
                    48 => 98,
                    55 => 101,
                    58 => 103,
                    61 => 108,
                    73 => 109,
                    80 => 110,
                    83 => 111,
                    86 => 112,
                    89 => 118,
                    101 => 119,
                    108 => 120,
                    111 => 121,
                    114 => 122,
                    117 => 127,
                    131 => 129,
                    139 => 130,
                    142 => 131
                );
        }
    }
}
$class_name = '_SmartyTemplate_51e1cff59a0cb5_57732955';
