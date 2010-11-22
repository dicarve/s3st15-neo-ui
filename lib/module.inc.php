<?php
/**
 * module class
 * Application modules related class
 *
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class module extends simbio
{
    private $modules_dir = 'modules';
    private $module_table = 'mst_module';
    public $module_list = array();


    /**
     * Method to set modules directory
     *
     * @param   string  $str_modules_dir
     * @return  void
     */
    public function setModulesDir($str_modules_dir)
    {
        $this->modules_dir = $str_modules_dir;
    }


    /**
     * Method to generate a list of module menu
     *
     * @param   object  $obj_db
     * @return  string
     */
    public function generateModuleMenu($obj_db)
    {
        $dbs = &$obj_db;
        // get module data from database
        $_mods_q = $obj_db->query('SELECT * FROM '.$this->module_table);

        // defaults
        $this->module_list[] = array('name' => 'home', 'path' => 'index.php', 'desc' => 'Admin Management Console');
        $this->module_list[] = array('name' => 'opac', 'path' => '../index.php', 'desc' => 'View OPAC in new window');

        while ($_mods_d = $_mods_q->fetch_assoc()) {
            $this->module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
        }

        $this->module_list[] = array('name' => 'logout', 'path' => 'logout.php', 'desc' => 'Logging out from Management Console');

        // create the HTML Hyperlinks
        $_menu = '<ul id="main-menu">';
        // sort modules
        if ($this->module_list) {
            foreach ($this->module_list as $_module) {
                $_formated_module_name = ucwords(str_replace('_', ' ', $_module['name']));
                $_mod_dir = $_module['path'];
                if (stripos($_mod_dir, '.php') !== false) {
                    if ($_module['name'] == 'home') {
                        $_menu .= '<li><a class="mod-menu home '.( !isset($_GET['mod'])?'curr-module':'' ).' notAJAX" title="'.$_module['desc'].'" href="'.$_module['path'].'">'.__($_formated_module_name).'</a>';
                        // sub-module
                        $_submenu_file = SENAYAN_BASE_DIR.'admin'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'submenu.php';
                        include $_submenu_file;
                        $_submenus = $menu;
                        $_menu .= '<ul class="mod-submenu '.$_module['name'].'-sub">';
                            foreach ($_submenus as $_list) {
                                if ($_list[0] == 'Header') {
                                    $_menu .= '<li class="mod-submenu-head"><span>'.$_list[1].'</span></li>';
                                } else {
                                    $_menu .= '<li><a class="mod-submenu-item" '
                                        .' href="'.$_list[1].'"'
                                        .' title="'.( isset($_list[2])?$_list[2]:$_list[0] ).'" href="#">'.$_list[0].'</a></li>';
                                }
                            }
                        $_menu .= '</ul>'."\n";
                        unset($menu);
                        $_menu .= '</li>';
                    } else {
                        $_menu .= '<li><a class="mod-menu '.$_module['name'].' notAJAX" title="'.$_module['desc'].'" href="'.$_module['path'].'">'.__($_formated_module_name).'</a></li>';
                    }
                } else if (isset($_SESSION['priv'][$_module['path']]['r']) && $_SESSION['priv'][$_module['path']]['r'] && file_exists($this->modules_dir.$_mod_dir)) {
                    $_menu .= '<li><a class="mod-menu '.$_module['name'].( (isset($_GET['mod']) && $_GET['mod']==$_module['path'])?' curr-module':'' ).'" title="'.$_module['desc'].'" href="'.MODULES_WEB_ROOT_DIR.$_module['path'].'/index.php">'.__($_formated_module_name).'</a>';
                    // sub-module
                    $_submenu_file = MODULES_BASE_DIR.$_module['name'].DIRECTORY_SEPARATOR.'submenu.php';
                    include $_submenu_file;
                    $_submenus = $menu;
                    $_menu .= '<ul class="mod-submenu '.$_module['name'].'-sub">';
                        foreach ($_submenus as $_list) {
                            if ($_list[0] == 'Header') {
                                $_menu .= '<li class="mod-submenu-head"><span>'.$_list[1].'</span></li>';
                            } else {
                                $_menu .= '<li><a class="mod-submenu-item" '
                                    .' href="'.$_list[1].'"'
                                    .' title="'.( isset($_list[2])?$_list[2]:$_list[0] ).'" href="#">'.$_list[0].'</a></li>';
                            }
                        }
                    $_menu .= '</ul>'."\n";
                    unset($menu);
                    $_menu .= '</li>'."\n";
                }
            }
        }

        $_menu .= '</ul>';

        return $_menu;
    }
}
?>
