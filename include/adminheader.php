<?php
// $Id: adminheader.php,v 1.4 2005/05/18 22:07:54 rowd Exp $
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
// ------------------------------------------------------------------------ //
//  Module        : sitelang (File: adminheader.php)                        //
//  Creation date : 18-April-2005                                           //
//  Author        : Rowd ( http://keybased.net/dev/ )                       //
//  ----------------------------------------------------------------------- //

// This is instead of calling cp_header.php, which can't be used because it includes the language files for $xoopsConfig['language']
$xoopsOption['nositelang'] = 1;
require_once dirname(__DIR__, 3) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/include/cp_functions.php';
$modulepermHandler = xoops_getHandler('groupperm');
if ($xoopsUser) {
    $url_arr = explode('/', mb_strstr($xoopsRequestUri, '/modules/'));

    $moduleHandler = xoops_getHandler('module');

    $xoopsModule = $moduleHandler->getByDirname($url_arr[2]);

    unset($url_arr);

    if (!$modulepermHandler->checkRight('module_admin', $xoopsModule->getVar('mid'), $xoopsUser->getGroups())) {
        redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

        exit();
    }
} else {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);

    exit();
}
// set config values for this module
if (1 == $xoopsModule->getVar('hasconfig') || 1 == $xoopsModule->getVar('hascomments')) {
    $configHandler = xoops_getHandler('config');

    $xoopsModuleConfig = &$configHandler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
}

require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/sitelang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/include/functions.php';
require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsform/tableform.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/include/sl_forms.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/namelang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/modulelang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/blocklang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/xoopsblocklang.php';
$myts = MyTextSanitizer::getInstance();

// ################ Language handling ################
$sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
// get current language
if (isset($_COOKIE['cslid']) && -1 != (int)$_COOKIE['cslid']) {
    $current_lang = $sitelangHandler->get((int)$_COOKIE['cslid']);
} else {
    $current_lang = $sitelangHandler->getLangByDirname($xoopsConfig['language']);
}

// get selected language
$selected_langid = '';
if (isset($_REQUEST['selected_lg']) && !empty($_REQUEST['selected_lg'])) {
    $selected_langid = $_REQUEST['selected_lg'];
} elseif (isset($_SESSION['selected_lg']) && !empty($_SESSION['selected_lg'])) {
    $selected_langid = $_SESSION['selected_lg'];
}
if (!empty($selected_langid)) {
    // create session with this id

    unset($current_lang);

    $current_lang = $sitelangHandler->get($selected_langid);

    $current_langid = $current_lang->getVar('langid');
}
SetXoopsConfigLang($current_lang->getVar('langdirname'), $current_lang->getVar('sitename'), $current_lang->getVar('slogan'));
$_SESSION['selected_lg'] = $current_lang->getVar('langid');

// #################### Include site-wide lang file ##################
if (file_exists(XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/global.php')) {
    require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/global.php';
} else {
    require_once XOOPS_ROOT_PATH . '/language/english/global.php';
}

// ################ Include page-specific lang file ################
if (isset($xoopsOption['pagetype']) && false === mb_strpos($xoopsOption['pagetype'], '.')) {
    if (file_exists(XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/' . $xoopsOption['pagetype'] . '.php')) {
        require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/' . $xoopsOption['pagetype'] . '.php';
    } else {
        require_once XOOPS_ROOT_PATH . '/language/english/' . $xoopsOption['pagetype'] . '.php';
    }
}
$xoopsOption = [];

if (!defined('XOOPS_USE_MULTIBYTES')) {
    define('XOOPS_USE_MULTIBYTES', 0);
}

// ################ Include admin interface lang file ################
if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/admin.php')) {
    require XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/admin.php';
} elseif (file_exists(XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/english/admin.php')) {
    require XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/language/english/admin.php';
}

$selbox = languagesSelectBox(false, 'langdirname');
$selform = '';
$selform .= "<form name='languages_form' id='languages_form' action='" . basename($_SERVER['REQUEST_URI']) . "' method='post' onchange='submit()'>\n";
$selform .= $selbox . '</form>';

xoops_cp_header();
