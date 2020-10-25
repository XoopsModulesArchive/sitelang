<?php
// $Id: langnamemgnt.php,v 1.2 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: langnamemgnt.php)                        //
//  Creation date : 30-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //

require_once '../include/adminheader.php';

$op = $_REQUEST['op'] ?? 'list';

// object handlers
$sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');
$namelangHandler = xoops_getModuleHandler('namelang', 'sitelang');

adminLangMenu(1, _AM_LANG_TAB2);
echo "<h3 style='text-align:left'>" . _AM_LANG_NAMELANGADMIN . '</h3>';

if (isset($op)) {
    switch ($op) {
        case 'edit':
            languageNamesForm('e');
            break;
        case 'confirmedit':
            languageNamesForm('c');
            break;
        case 'submit':
            $comment = '';
            foreach ($_POST['langids'] as $lid) {
                $guilangcode[$lid] = trim($myts->stripSlashesGPC($_POST['guilangcode'][$lid]));

                $langcode[$lid] = trim($myts->stripSlashesGPC($_POST['langcode'][$lid]));

                $newname[$lid] = trim($myts->stripSlashesGPC($_POST['newname'][$lid]));

                $chgnamelang = $namelangHandler->create(false);

                $chgnamelang->setVars(
                    [
                        'guilangcode' => $guilangcode[$lid],
                        'langcode' => $langcode[$lid],
                        'langname' => $newname[$lid],
                    ]
                );

                $success = ($namelangHandler->insert($chgnamelang, false));

                if ($success) {
                    $comment = _AM_LANG_CHGSUCCESS;
                } else {
                    $comment = _AM_LANG_CHGNOTSUCCESS;
                }
            }
            redirect_header('langnamemgnt.php', 2, $comment);
            break;
        case 'list':
        case 'default':
            languageNamesForm('e');
            break;
    }
}

xoops_cp_footer();
