<?php
// $Id: index.php,v 1.3 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: index.php)                               //
//  Creation date : 03-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
require_once '../include/adminheader.php';
$op          = $_REQUEST['op'] ?? 'list';
$langdirname = $_REQUEST['langdirname'] ?? '';

// get all languages that are already in the database
$sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
$adminlang = $sitelangHandler->getObjects(null, false, false); // $criteria, $id_as_key, $as_objects
$adminlangcount = count($adminlang);

// Default site values and meta tags
// TODO: add lang specific footer and meta_keywords... perhaps, as it will require more core file changes to integrate :-(
$configHandler = xoops_getHandler('config');
$criteria = new CriteriaCompo(new Criteria('conf_name', 'sitename'), 'OR');
$criteria->add(new Criteria('conf_name', 'slogan'), 'OR');
$criteria->add(new Criteria('conf_name', 'language'), 'OR');
$criteria->add(new Criteria('conf_name', 'meta_keywords'), 'OR');
$criteria->add(new Criteria('conf_name', 'footer'), 'OR');

$metatag = [];
$config = &$configHandler->getConfigs($criteria, true);
foreach (array_keys($config) as $i) {
    $metatag[$config[$i]->getVar('conf_name')] = $config[$i]->getVar('conf_value');
}

// Object handlers
$moduleHandler = xoops_getHandler('module');
$modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');
$namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');
$blocklangHandler = xoops_getModuleHandler('blocklang', 'sitelang');

$langisactive = 0;
$silent = false;
$inlang = [];
foreach (array_keys($adminlang) as $key => $value) {
    $inlang[$adminlang[$key]['langdirname']] = $value;
}

// Add the lang if it is not yet installed, but chosen as default language
if (!array_key_exists($metatag['language'], $inlang)) {
    $op = 'add';

    $silent = true;

    $langdirname = $metatag['language'];

    $langisactive = 1;
} else {
    $defaultlang = $sitelangHandler->getLangByDirname($metatag['language']);

    // has been installed, but not active

    if (1 != $defaultlang->getVar('langisactive')) {
        $defaultlang->setVar('langisactive', 1);

        $sitelangHandler->insert($defaultlang, true);
    }
}

// NOTE: Whenever a module is installed or uninstalled the module_lang and block_lang (link) tables will be compromised
// The following code checks those link tables, and updates where necessary.
// Ideally these tables should be updated at the time a module is (un)installed,
// so this is really just a workaround to allow sitelanguage to be a module and not (too much of) a core hack :-)
syncLang();

adminLangMenu(0, _AM_LANG_TAB1);
echo "<h3 style='text-align:left'>" . _AM_LANG_LANGADMIN . '</h3>';

if (isset($op)) {
    switch ($op) {
        case 'delete':
            $alangid = '';
            $comment = _AM_LANG_DELNOTSUCCESS;

            if (isset($_REQUEST['ok']) && ((int)(1 == $_REQUEST['ok']))) {
                if (isset($_REQUEST['alangid']) && !empty($_REQUEST['alangid'])) {
                    $alangid = (int)$_REQUEST['alangid'];

                    $deladminlang = $sitelangHandler->get($alangid);

                    $dellangcode = $deladminlang->getVar('langcode');

                    $comment = $deladminlang->getVar('langdirname');

                    $success = ($sitelangHandler->delete($deladminlang, false));

                    // delete any lang_name records which have the deleted langid

                    $lcriteria = new CriteriaCompo();

                    $lcriteria->add(new Criteria('guilangcode', $dellangcode), 'OR');

                    $lcriteria->add(new Criteria('langcode', $dellangcode), 'OR');

                    $namelangs = $namelangHandler->getObjects($lcriteria, false, true); // $criteria, $id_as_key, $as_objects

                    if (count($namelangs) > 0) {
                        foreach ($namelangs as $nid) {
                            $namelangHandler->delete($nid, false);
                        }
                    }

                    // delete any lang_modules records which have the deleted langid

                    $mcriteria = new CriteriaCompo();

                    $mcriteria->add(new Criteria('langid', (int)$alangid));

                    $modlangs = $modlangHandler->getObjects($mcriteria, false, true); // $criteria, $id_as_key, $as_objects

                    if (count($modlangs) > 0) {
                        foreach ($modlangs as $mid) {
                            $modlangHandler->delete($mid, false);
                        }
                    }

                    // delete any lang_blocks records which have the deleted langid

                    $bcriteria = new CriteriaCompo();

                    $bcriteria->add(new Criteria('langid', (int)$alangid));

                    $blocklangs = $blocklangHandler->getObjects($bcriteria, false, true); // $criteria, $id_as_key, $as_objects

                    if (count($blocklangs) > 0) {
                        foreach ($blocklangs as $bid) {
                            $blocklangHandler->delete($bid, false);
                        }
                    }

                    if ($success) {
                        $comment .= ' ' . _AM_LANG_DELSUCCESS;
                    } else {
                        $comment .= ' ' . _AM_LANG_DELNOTSUCCESS;
                    }
                }

                redirect_header('index.php', 2, $comment);

                exit;
            }  
                xoops_confirm(['ok' => 1, 'alangid' => $_REQUEST['alangid'], 'op' => 'delete'], 'index.php', _AM_LANG_CONFIRMDEL);

            break;
        case 'submit':
            $comment = '';
            $finalsuccess = false;

            foreach ($_POST['langids'] as $lid) {
                $langcode[$lid] = trim($myts->stripSlashesGPC($_POST['langcode'][$lid]));

                $dirname[$lid] = trim($myts->stripSlashesGPC($_POST['dirname'][$lid]));

                $newstatus[$lid] = (int)$_POST['newstatus'][$lid];

                $sitename[$lid] = trim($myts->stripSlashesGPC($_POST['sitename'][$lid]));

                $slogan[$lid] = trim($myts->stripSlashesGPC($_POST['slogan'][$lid]));

                $footer[$lid] = trim($myts->stripSlashesGPC($_POST['footer'][$lid]));

                $charset[$lid] = trim($myts->stripSlashesGPC($_POST['charset'][$lid]));

                $langcss[$lid] = trim($myts->stripSlashesGPC($_POST['langcss'][$lid]));

                $chgadminlangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

                $chgadminlang = $chgadminlangHandler->create(false);

                $chgadminlang->setVars(
                    [
                        'langid' => $lid,
                        'langcode' => $langcode[$lid],
                        'langdirname' => $dirname[$lid],
                        'langisactive' => $newstatus[$lid],
                        'sitename' => $sitename[$lid],
                        'slogan' => $slogan[$lid],
                        'footer' => $footer[$lid],
                        'charset' => $charset[$lid],
                        'langcss' => $langcss[$lid],
                    ]
                );

                $success = ($chgadminlangHandler->insert($chgadminlang, false));

                if ($success) {
                    $finalsuccess = true;
                }
            }
            if ($finalsuccess) {
                $comment = _AM_LANG_CHGSUCCESS;
            } else {
                $comment = _AM_LANG_CHGNOTSUCCESS;
            }
            redirect_header('index.php', 2, $comment);
            exit;
            break;
        case 'confirmedit':
            languagesForm('c');
            break;
        case 'addcss':
            $setcss = '';
            if (isset($_REQUEST['alangid']) && !empty($_REQUEST['alangid'])) {
                $alangid = (int)$_REQUEST['alangid'];

                $csslang = $sitelangHandler->get($alangid);

                if (isset($_REQUEST['cssfile_select']) && !empty($_REQUEST['cssfile_select'])) {
                    $setcss = 'none' != $_REQUEST['cssfile_select'] ? $_REQUEST['cssfile_select'] : '';

                    $csslang->setVar('langcss', $setcss);

                    $sitelangHandler->insert($csslang, true);
                } else {
                    $setcss = $csslang->getVar('langcss');
                }

                $currentcss = $csslang->getVar('langcss');

                $css = !empty($currentcss) ? $currentcss : _NONE;

                echo '<div>' . sprintf(_AM_LANG_CURRENTCSS, $csslang->getVar('langdirname')) . ':&nbsp;' . $css . '</div>';

                $selbox = langCssSelectBox($setcss);

                $selform = "<form name='cssfile_form' id='cssfile_form' action='" . basename($_SERVER['REQUEST_URI']) . "' method='post' >\n";

                $selform .= _AM_LANG_SELECTCSS . ':&nbsp;' . $selbox;

                $selform .= "<input type='hidden' name='op' id='op' value='addcss'>";

                $selform .= "&nbsp;&nbsp;<input type='submit' value='" . _GO . "'>";

                $selform .= '</form>';

                echo '<br>' . $selform;

                echo '<br><div>' . _AM_LANG_CSSPATH_DESC . ':<br>&nbsp;&nbsp;&nbsp;' . XOOPS_URL . '/modules/sitelang/css/' . '<div>';

                echo "<div><br><br><input type='button' value='" . _AM_LANG_RETURN . "' onclick='location=\"index.php?op=default\"'></div>";
            } else {
                languagesForm('e');
            }

            break;
        case 'add':
            $comment = _AM_LANG_ADDNOTSUCCESS;
            $charset = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $langdirname . '/global.php', '_CHARSET');
            if (empty($charset)) {
                $charset = _CHARSET; // give it a default value of the current charset, if function unsuccessful
            }
            $langcode = getLangConstantFromFile(XOOPS_ROOT_PATH . '/language/' . $langdirname . '/global.php', '_LANGCODE');
            if (empty($langcode)) {
                $langcode = _LANGCODE; // give it a default value of the current langcode, if function unsuccessful
            }
            $newadminlang = $sitelangHandler->create(true);
            $newadminlang->setVars(
                [
                    'langcode' => $langcode,
                    'langdirname' => $langdirname,
                    'langisactive' => $langisactive,  // installs as INACTIVE!
                    'sitename' => $metatag['sitename'],
                    'slogan' => $metatag['slogan'],
                    'footer' => $metatag['footer'],
                    'charset' => $charset,
                    'langcss' => null,
                ]
            );
            $success = ($sitelangHandler->insert($newadminlang, true));

            if ($success) {
                $comment = $newadminlang->getVar('langdirname') . _AM_LANG_ADDSUCCESS;

                // get all installed languages

                $installed_langs = &$sitelangHandler->getAllLangs();

                foreach ($installed_langs as $lids) {
                    $newnamelang = $namelangHandler->create(true);

                    $newnamelang->setVars(
                        [
                            'guilangcode' => $langcode,
                            'langcode' => $lids['langcode'],
                            'langname' => $lids['langdirname'],
                        ]
                    );

                    $namelangHandler->insert($newnamelang, true);

                    unset($newnamelang);

                    // create new records for the already installed languages

                    $newnamelang = $namelangHandler->create(true);

                    $newnamelang->setVars(
                        [
                            'guilangcode' => $lids['langcode'],
                            'langcode' => $langcode,
                            'langname' => $langdirname,
                        ]
                    );

                    $namelangHandler->insert($newnamelang, true);

                    unset($newnamelang);
                }

                unset($newnamelang);

                $newnamelang = $namelangHandler->create(true);

                $newnamelang->setVars(
                    [
                        'guilangcode' => $langcode,
                        'langcode' => $langcode,
                        'langname' => $langdirname,
                    ]
                );

                $namelangHandler->insert($newnamelang, true);

                // get all installed modules which could be used in the main menu

                $criteria = new CriteriaCompo();

                $criteria->add(new Criteria('hasmain', '1'));

                $installed_mods = $moduleHandler->getList($criteria);

                // add lang_modules records for this langid

                foreach ($installed_mods as $mid => $modname) {
                    $newmodlang = $modlangHandler->create(true);

                    $newmodlang->setVars(
                        [
                            'mid' => $mid,
                            'langid' => $newadminlang->getVar('langid'),
                            'modname' => $modname,
                        ]
                    );

                    $modlangHandler->insert($newmodlang, true);

                    unset($newmodlang);
                }

                // get every installed module id, and add mid=0 to cater for custom blocks

                $custom_mods = [0 => _AM_LANG_CUSTOMBLOCKS];

                $modulelist = $moduleHandler->getList(null);

                $midList = $custom_mods + $modulelist;

                foreach ($midList as $key => $value) {
                    // add lang_blocks records for this langid

                    $block_arr = XoopsBlock::getByModule($key, true);

                    foreach ($block_arr as $blocks) {
                        $newblocklang = $blocklangHandler->create(true);

                        $blockname = ('C' != $blocks->getVar('block_type')) ? $blocks->getVar('name') : $blocks->getVar('title');

                        $newblocklang->setVars(
                            [
                                'bid' => $blocks->getVar('bid'),
                                'mid' => $blocks->getVar('mid'),
                                'langid' => $newadminlang->getVar('langid'),
                                'langcode' => $langcode,
                                'blockname' => $blockname,
                            ]
                        );

                        $blocklangHandler->insert($newblocklang, true);

                        unset($newblocklang);
                    }
                }
            }
            if (!$silent) {
                redirect_header('index.php', 2, $comment);

                exit;
            }
            // no break
        case 'list':
        case 'default':
            languagesForm('e');
            break;
    }
}

xoops_cp_footer();
