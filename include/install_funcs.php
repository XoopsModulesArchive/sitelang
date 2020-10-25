<?php
// $Id: install_funcs.php,v 1.4 2005/05/14 21:27:01 rowd Exp $
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
//  Module        : sitelang (File: install_funcs.php)                       //
//  Creation date : 03-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
function xoops_module_install_sitelang(&$module)
{
    global $xoopsConfig, $xoopsDB;

    $sitename = !empty($xoopsConfig['sitename']) ? addslashes($xoopsConfig['sitename']) : '';

    $slogan = !empty($xoopsConfig['slogan']) ? addslashes($xoopsConfig['slogan']) : '';

    $defaultLangDir = (isset($xoopsConfig['language']) && !empty($xoopsConfig['language'])) ? $xoopsConfig['language'] : '';

    $langdirname = 'english';

    $langcss = '';

    $success = false;

    $finalSuccess = false;

    // get footer text

    $configHandler = xoops_getHandler('config');

    $criteria = new CriteriaCompo(new Criteria('conf_name', 'footer'));

    $metatag = [];

    $config = &$configHandler->getConfigs($criteria, true);

    foreach (array_keys($config) as $i) {
        $metatag[$config[$i]->getVar('conf_name')] = $config[$i]->getVar('conf_value');
    }

    // get charset

    require_once XOOPS_ROOT_PATH . '/modules/sitelang/include/functions.php';

    $charset = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $langdirname . '/global.php', '_CHARSET');

    if (empty($charset)) {
        $charset = _CHARSET; // give it a default value of the current charset, if function unsuccessful
    }

    $langcode = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $langdirname . '/global.php', '_LANGCODE');

    if (empty($langcode)) {
        $langcode = _LANGCODE; // give it a default value of the current charset, if function unsuccessful
    }

    // insert english lang

    $sql_lang = 'INSERT INTO ' . $xoopsDB->prefix('lang') . " VALUES(1, '" . $langcode . "', '" . $langdirname . "', 1, '" . $sitename . "', '" . $slogan . "', '" . $metatag['footer'] . "', '" . $charset . "', '" . $langcss . "')";

    $success = $xoopsDB->query($sql_lang);

    if ($success) {
        $finalSuccess = true;
    }

    $sql_langname = 'INSERT INTO ' . $xoopsDB->prefix('lang_name') . " VALUES ('" . $langcode . "', '" . $langcode . "', '" . $langdirname . "')";

    $success = $xoopsDB->query($sql_langname);

    if ($success) {
        $finalSuccess = true;
    }

    // insert the default lang, if it's not english

    if (!empty($defaultLangDir) && ('english' != $defaultLangDir)) {
        // get charset

        $charset = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $defaultLangDir . '/global.php', '_CHARSET');

        if (empty($charset)) {
            $charset = _CHARSET; // give it a default value of the current charset, if function unsuccessful
        }

        // get langcode

        $defaultlangcode = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $defaultLangDir . '/global.php', '_LANGCODE');

        if (empty($defaultlangcode)) {
            $defaultlangcode = _LANGCODE; // give it a default value of the current charset, if function unsuccessful
        }

        $sql_lang2 = 'INSERT INTO ' . $xoopsDB->prefix('lang') . " VALUES(2, '" . $defaultlangcode . "', '" . $defaultLangDir . "', 1, '" . $sitename . "', '" . $slogan . "', '" . $metatag['footer'] . "', '" . $charset . "', '" . $langcss . "')";

        $success = $xoopsDB->query($sql_lang2);

        if ($success) {
            $finalSuccess = true;
        }

        $sql_langname2 = 'INSERT INTO ' . $xoopsDB->prefix('lang_name') . " VALUES ('" . $langcode . "', '" . $defaultlangcode . "', '" . $defaultLangDir . "')";

        $sql_langname3 = 'INSERT INTO ' . $xoopsDB->prefix('lang_name') . " VALUES ('" . $defaultlangcode . "', '" . $langcode . "', '" . $langdirname . "')";

        $sql_langname4 = 'INSERT INTO ' . $xoopsDB->prefix('lang_name') . " VALUES ('" . $defaultlangcode . "', '" . $defaultlangcode . "', '" . $defaultLangDir . "')";

        $success = $xoopsDB->query($sql_langname2);

        $success = $xoopsDB->query($sql_langname3);

        $success = $xoopsDB->query($sql_langname4);
    }

    // create records for lang_blocks and lang_modules, synchronised with the core tables newblocks and modules

    syncLang();

    if ($finalSuccess) {
        return true;
    }

    return false;
}
