<?php
// $Id: blocksmgnt.php,v 1.5 2005/05/18 22:07:54 rowd Exp $
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
//  Module        : sitelang (File: blocksmgnt.php)                          //
//  Creation date : 06-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
require_once '../include/adminheader.php';

$op = $_REQUEST['op'] ?? 'list';

$moduleid = 1;  // system module as default
if (isset($_REQUEST['mid'])) {
    $moduleid = (int)$_REQUEST['mid'];
}

$blockid = -1;
if (isset($_REQUEST['bid'])) {
    $blockid = (int)$_REQUEST['bid'];
}

// get all installed modules
$moduleHandler = xoops_getHandler('module');
$custom_mods = [0 => _AM_LANG_CUSTOMBLOCKS];
$modulelist = $moduleHandler->getList(null);
$installed_mods = $custom_mods + $modulelist;
$installed_modscount = count($installed_mods);

// get all installed module languages
$modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');
$modlang = $modlangHandler->getObjects(null, true, false); // $criteria, $id_as_key, $as_objects
$modlangcount = count($modlang);

// get all installed languages
$sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
$sitelang = $sitelangHandler->getObjects(null, false, true); // $criteria, $id_as_key, $as_objects
$sitelangcount = count($sitelang);

$blocklangHandler = xoops_getModuleHandler('blocklang', 'sitelang');
$blocklang = $blocklangHandler->getMidList(true); //$id_as_key

adminLangMenu(4, _AM_LANG_TAB5);
echo "<h3 style='text-align:left'>" . _AM_LANG_BLKNAMEADMIN . '</h3>';

if (isset($op)) {
    switch ($op) {
        case 'edit':
            blockForm($moduleid, $blockid, -1, 'e');
            break;
        case 'confirmedit':
            blockForm($moduleid, $blockid, -1, 'c');
            break;
        case 'submit':
            $comment = '<br> ' . _AM_LANG_CHGNOTSUCCESS . '<br><br> ';
            $mid = $_POST['mid'];
            $newname = [];
            $finalsuccess = false;

            foreach ($_POST['blckids'] as $bid => $lid) {
                foreach ($lid as $l => $v) {
                    $newname[$bid][$l] = trim($myts->addSlashes($_POST['newname'][$bid][$l]));

                    $chgblcklang = $blocklangHandler->create(false);

                    $chgblcklang->setVars(
                        [
                            'bid' => $bid,
                            'mid' => $mid,
                            'langid' => $l,
                            'langcode' => $v,
                            'blockname' => $newname[$bid][$l],
                        ]
                    );

                    $success = ($blocklangHandler->insert($chgblcklang, false));
                }

                if (!$finalsuccess && $success) {
                    $finalsuccess = true;

                    $comment = _AM_LANG_CHGSUCCESS;
                }
            }
            redirect_header('blocksmgnt.php', 2, $comment);
            break;
        case 'list':
        case 'default':
            if (isset($_POST['selected_mod'])) {
                $moduleid = (int)$_POST['selected_mod'];
            }
            if (isset($_POST['selected_blck'])) {
                $blockid = (int)$_POST['selected_blck'];
            }
            $selected_lid = 1;
            if (isset($_POST['selected_lg']) && !empty($_POST['selected_lg'])) {
                $selected_lid = (int)$_POST['selected_lg'];
            } elseif (isset($_SESSION['selected_lg']) && !empty($_SESSION['selected_lg'])) {
                $selected_lid = (int)$_SESSION['selected_lg'];
            } elseif (isset($_COOKIE['slid']) && -1 != (int)$_COOKIE['slid']) {
                $selected_lid = (int)$_COOKIE['slid'];
            }

            // Create a dropdown selection box for modules
            $mod_select = moduleSelectBox($moduleid);

            // Create a dropdown selection box for blocks
            $block_select = blockSelectBox($moduleid, $blockid);

            // Create a dropdown selection box for languages
            $lang_select = languagesSelectBox(false, 'langname', 'selected_lg', $selected_lid);

            // Create the selection form
            $selbox = languagesSelectBox(false, 'langname');
            $selform = "<form name='blocks_form' id='blocks_form' action='" . basename($_SERVER['REQUEST_URI']) . "' method='post' onchange='submit()'>\n";
            $selform .= "<input type='hidden' name='op_hidden' value='default'>";
            $selform .= _AM_LANG_BLOCKTYPE . ':&nbsp;' . $mod_select . '&nbsp;' . _AM_LANG_SELECTEDBLOCK . ':&nbsp;' . $block_select . '&nbsp;' . _AM_LANG_SELECTEDLANG . ':&nbsp;' . $lang_select;
            $selform .= '</form>';

            echo $selform;

            // Create a form with lang_block details
            blockForm($moduleid, $blockid, $selected_lid, 'e');

            break;
    }
}

xoops_cp_footer();
