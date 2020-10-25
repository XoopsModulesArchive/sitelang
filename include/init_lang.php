<?php
// $Id: init_lang.php,v 1.4 2005/05/18 22:07:54 rowd Exp $
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
//  Module        : sitelang (File: init_lang.php)                           //
//  Creation date : 04-November-2004                                         //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //

require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/modulelang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/namelang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/class/xoopsblocklang.php';
require_once XOOPS_ROOT_PATH . '/modules/sitelang/include/functions.php';

$siteLang = $xoopsConfig['language'] ?? 'english';
$slid = -1; // site language id
$cookieLangid = -1;
$langdir = '';

if (isset($_GET['slid']) && !empty($_GET['slid'])) {
    $slid = (int)$_GET['slid'];
} elseif (isset($_GET['lg']) && !empty($_GET['lg'])) {
    $langdir = trim($_GET['lg']);
} elseif (isset($_POST['slid']) && !empty($_POST['slid'])) {
    $slid = (int)$_POST['slid'];
}

// A cookie is necessary for storing the language choice of anonymous users, and to set initial language before user logs in
if (isset($_COOKIE['cslid']) && !empty($_COOKIE['cslid'])) {
    $cookieLangid = (int)$_COOKIE['cslid'];
}

// Object handlers
$sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
$userlangHandler = xoops_getModuleHandler('userlang', 'sitelang');
$modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');

// Get a list of all active languages
$isActiveLang = 0;
$activeLangs = $sitelangHandler->getActiveLangs();

$templid = -1;
if ((-1 != $slid) || (-1 != $cookieLangid)) {
    $templid = -1 != $slid ? $slid : $cookieLangid;
}

if (isset($activeLangs) && !empty($activeLangs)) {
    if (!empty($langdir)) {
        foreach ($activeLangs as $lang) {
            if ($lang['langdirname'] == $langdir) {
                $isActiveLang = 1;

                $templid = $lang['langid'];

                break;
            }
        }
    } else {
        if (array_key_exists($templid, $activeLangs)) {
            $isActiveLang = 1;
        }
    }
}

// is the user logged in?
if (is_object($xoopsUser) && !empty($xoopsUser)) {
    $userlang = $userlangHandler->get($xoopsUser->getVar('uid'));

    // does the user have a previous language selection?

    if ($userlang) {
        $ulangid = $userlang->getVar('langid');

        if (-1 != $templid) {
            if ($ulangid != $templid) { // the chosen lang differs from the db lang
                if (1 == $isActiveLang) {
                    $userlang->setVar('langid', $templid);

                    $success = $userlangHandler->insert($userlang, true);
                } else {
                    // delete user lang if it's not an active language

                    $success = $userlangHandler->delete($userlang, true);
                }
            } else { // chosen language is the same as db lang
                if (1 != $isActiveLang) { // but is not active
                    $success = $userlangHandler->delete($userlang, true);
                }
            }
        } else {
            // only active languages are permitted

            if ((isset($activeLangs) && !empty($activeLangs)) && array_key_exists($ulangid, $activeLangs)) {
                $templid = $ulangid;

                $isActiveLang = 1;
            } else {
                $success = $userlangHandler->delete($userlang, true);
            }
        }
    } else { // there is no entry in db for userlang
        if (-1 != $templid && 1 == $isActiveLang) { // only create db entry if an active language is chosen.
            $newuserlang = $userlangHandler->create(true);

            $newuserlang->setVars(
                [
                    'userid' => $xoopsUser->getVar('uid'),
                    'langid' => $templid,
                ]
            );

            $success = ($userlangHandler->insert($newuserlang, true));
        }
    }
}

if (1 == $isActiveLang) {
    $language = $sitelangHandler->get($templid);

    if ('sitelang' != mb_strtolower(get_class($language))) {
        $dir = '';
    } else {
        $dir = $language->getVar('langdirname');
    }

    if (!empty($dir)) {
        SetXoopsConfigLang($language->getVar('langdirname'), $language->getVar('sitename'), $language->getVar('slogan'));

        $_SESSION['languagecss'] = $language->getVar('langcss');

        // set a cookie where name=cslid, value=language id, expire=1month, path=entire domain

        setcookie('cslid', $templid, time() + 3600 * 24 * 30, '/');
    }
} else {
    // set a cookie where name=cslid, value= -1, expire=1month, path=entire domain

    setcookie('cslid', -1, time() + 3600 * 24 * 30, '/');
}

// NOTE :
// The following code has to come after setting the user's site language (see code above).
// I decided that it was safer to include this here than to give instructions for how to move
// the original to the right place in common.php

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

if (!defined('XOOPS_USE_MULTIBYTES')) {
    define('XOOPS_USE_MULTIBYTES', 0);
}

// support for easiest multilanguage hack by GIJOE
global $easiestml_lang;
if (isset($easiestml_lang) && 'all' != $easiestml_lang) {
    if (!empty($_GET['easiestml_lang']) && $_GET['easiestml_lang'] == $easiestml_lang) {
        // a stupid hack, should really change the site language again to this language
    } else {
        //	$easiestml_lang = 'xlang:'._LANGCODE; // use this instead of the next line if you are using tags in the form of 'xlang:fr'

        $easiestml_lang = _LANGCODE;
    }
}
