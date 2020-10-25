<?php
// $Id:
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
//  Module        : sitelang (File: update_funcs.php)                        //
//  Creation date : 17-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
function xoops_module_update_sitelang($module, $old_version)
{
    global $xoopsConfig, $xoopsDB;

    require_once XOOPS_ROOT_PATH . '/modules/sitelang/include/functions.php';
    require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/sitelang.php';

    $result = 0;
    $errors = 0;

    switch ($old_version) {
        case 10: // versions 0.1 Beta and 0.11 Beta
            if (SitelangTableExists($xoopsDB->prefix('lang'))) {
                // create a field to hold the charset of the language
                if (!SitelangFieldExists('charset', $xoopsDB->prefix('lang'))) {
                    $result = SitelangAddField('charset VARCHAR(50) NOT NULL', $xoopsDB->prefix('lang'));
                    if (!$result) {
                        $module->setErrors('Unable to add langcharset to lang table.');
                        $errors++;
                    }
                    $result = SitelangChangeField('langname langname VARCHAR( 150 ) NOT NULL', $xoopsDB->prefix('lang'));
                    if (!$result) {
                        $module->setErrors('Unable to change langname.');
                        $errors++;
                    }
                    $result = SitelangChangeField('langdirname langdirname VARCHAR( 50 ) NOT NULL', $xoopsDB->prefix('lang'));
                    if (!$result) {
                        $module->setErrors('Unable to change langdirname.');
                        $errors++;
                    }
                }
                if (!SitelangFieldExists('langcss', $xoopsDB->prefix('lang'))) {
                    $result = SitelangAddField('langcss VARCHAR(50) NULL', $xoopsDB->prefix('lang'));
                    if (!$result) {
                        $module->setErrors('Unable to add langcss field to lang table.');
                        $errors++;
                    }
                }
                //  TODO: for each language need to add a css file??
                $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
                $languages        = $sitelangHandler->getObjects(null, false, true); // $criteria, $id_as_key, $as_objects
                $languagescount   = count($languages);

                foreach ($languages as $lang) {
                    if (empty($lang->getVar('charset')) {
                        // get charset
                        $charset = getCharsetFromFile(XOOPS_ROOT_PATH . '/language/' . $langdirname . '/global.php');
                        if (!$charset) {
                            $charset = _CHARSET; // give it a default value of the current charset, if function unsuccessful
                        }
                        $lang->setVar('charset', $charset);
                        $sitelangHandler->insert($lang, true);
                    }
                }
            }
        default :
            break;
    }

    if ($errors > 0) {
        return false;
    }

    return true;
}


