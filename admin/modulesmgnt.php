<?php
// $Id: modulesmgnt.php,v 1.3 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: modulesmgnt.php)                         //
//  Creation date : 03-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
require_once '../include/adminheader.php';
$op    = $_REQUEST['op'] ?? 'list';
$modid = -1;
if (isset($_REQUEST['mid'])) {
    $modid = (int)$_REQUEST['mid'];
}

// get all installed modules which are able to be used in the main menu
$moduleHandler = xoops_getHandler('module');
$criteria = new CriteriaCompo();
$criteria->add(new Criteria('hasmain', '1'));
$installed_mods = $moduleHandler->getList($criteria);
$installed_modscount = count($installed_mods);

// get all installed languages
$adminlangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
$adminlang = $adminlangHandler->getObjects(null, false, true); // $criteria, $id_as_key, $as_objects
$adminlangcount = count($adminlang);

// get all installed module languages
$modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');
$modlang = $modlangHandler->getObjects(null, true, false); // $criteria, $id_as_key, $as_objects
$modlangcount = count($modlang);

$blocklangHandler = xoops_getModuleHandler('blocklang', 'sitelang');

adminLangMenu(3, _AM_LANG_TAB4);
echo "<h3 style='text-align:left'>" . _AM_LANG_MODNAMEADMIN . '</h3>';

if (isset($op)) {
    switch ($op) {
        case 'edit':
            moduleForm($modid, 'e');
            break;
        case 'confirmedit':
            moduleForm($modid, 'c');
            break;
        case 'submit':
            $comment = '';
            $mid = $_POST['modid'];

            foreach ($_POST['langids'] as $lid) {
                $newname[$lid] = trim($myts->stripSlashesGPC($_POST['newname'][$lid]));

                $chgmodlang = $modlangHandler->create(false);

                $chgmodlang->setVars(
                    [
                        'mid' => $mid,
                        'langid' => $lid,
                        'modname' => $newname[$lid],
                    ]
                );

                $success = ($modlangHandler->insert($chgmodlang, false));

                if ($success) {
                    $comment = _AM_LANG_CHGSUCCESS;
                } else {
                    $comment = _AM_LANG_CHGNOTSUCCESS;
                }
            }
            redirect_header('modulesmgnt.php', 2, $comment);
            break;
        case 'list':
        case 'default':
            $mod_options = '';
            $first = -1;
            if (isset($_REQUEST['current_mod']) && !empty($_REQUEST['current_mod'])) {
                $modid = $_REQUEST['current_mod'];
            }
            if ($installed_modscount > 0) {
                $mods_select = new XoopsFormSelect(_AM_LANG_MODCURRENT, 'current_mod', $modid);

                $mods_select->setExtra(" onchange='submit()' ");

                foreach ($installed_mods as $mid => $mvalue) {
                    if (-1 == $first) {
                        $first = $mid;
                    }

                    $mods_select->addOption($mid, $mvalue);
                }

                // Create a dropdown selection box for modules

                $op_hidden = new XoopsFormHidden('op', 'default');

                $form = new XoopsSimpleForm('', 'modules_form', 'modulesmgnt.php', 'post', true);

                $form->addElement($mods_select);

                $form->addElement($op_hidden);

                // gave up on the xoops form rendering :-(  I'll use it again when admin uses Smarty templates

                echo "<form name='" . $form->getName() . "' id='" . $form->getName() . "' action='" . $form->getAction() . "' method='" . $form->getMethod() . "'" . $form->getExtra() . ">\n";

                foreach ($form->getElements() as $ele) {
                    if (!$ele->isHidden()) {
                        echo $ele->getCaption() . '&nbsp;' . $ele->render() . "<br>\n";
                    } else {
                        echo $ele->render() . "\n";
                    }
                }

                echo "</form>\n";

                if ($modid > 0) {
                    moduleForm($modid, 'e');
                } else {
                    moduleForm($first, 'e');
                }
            }

            break;
    }
}

xoops_cp_footer();
