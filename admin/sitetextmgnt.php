<?php
// $Id: sitetextmgnt.php,v 1.4 2005/05/13 23:47:15 rowd Exp $
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

$op = $_REQUEST['op'] ?? 'list';

// sitelang object handler
$adminlangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

adminLangMenu(2, _AM_LANG_TAB3);
echo "<h3 style='text-align:left'>" . _AM_LANG_SITETEXTADMIN . '</h3>';

if (isset($op)) {
    switch ($op) {
        case 'edit':
            sitetextForm('e');
            break;
        case 'confirmedit':
            sitetextForm('c');
            break;
        case 'submit':
            $comment = '';
            foreach ($_POST['langids'] as $lid) {
                $langcode[$lid] = trim($myts->stripSlashesGPC($_POST['langcode'][$lid]));

                $langdirname[$lid] = trim($myts->stripSlashesGPC($_POST['langdirname'][$lid]));

                $langstatus[$lid] = (int)$_POST['langstatus'][$lid];

                $newsitename[$lid] = trim($myts->stripSlashesGPC($_POST['newsitename'][$lid]));

                $newslogan[$lid] = trim($myts->stripSlashesGPC($_POST['newslogan'][$lid]));

                $newfooter[$lid] = trim($myts->stripSlashesGPC($_POST['footer'][$lid]));

                $charset[$lid] = trim($myts->stripSlashesGPC($_POST['charset'][$lid]));

                $langcss[$lid] = trim($myts->stripSlashesGPC($_POST['langcss'][$lid]));

                $chgadminlang = $adminlangHandler->create(false);

                $chgadminlang->setVars(
                    [
                        'langid' => $lid,
                        'langcode' => $langcode[$lid],
                        'langdirname' => $langdirname[$lid],
                        'langisactive' => $langstatus[$lid],
                        'sitename' => $newsitename[$lid],
                        'slogan' => $newslogan[$lid],
                        'footer' => $newfooter[$lid],
                        'charset' => $charset[$lid],
                        'langcss' => $langcss[$lid],
                    ]
                );

                $success = ($adminlangHandler->insert($chgadminlang, false));

                if ($success) {
                    $comment = _AM_LANG_CHGSUCCESS;
                } else {
                    $comment = _AM_LANG_CHGNOTSUCCESS;
                }
            }
            redirect_header('sitetextmgnt.php', 2, $comment);
            break;
        case 'list':
        case 'default':
            sitetextForm('e');
            break;
    }
}

xoops_cp_footer();
