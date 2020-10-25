<?php
// $Id: xoops_version.php,v 1.3 2005/05/12 18:31:53 rowd Exp $
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
//  ------------------------------------------------------------------------ //
//  Module        : sitelang (File: xoops_version.php)                       //
//  Creation date : 04-November-2004                                         //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
$modversion['name'] = _MI_LANG_NAME;
$modversion['version'] = 0.3;
$modversion['description'] = _MI_LANG_DESC;
$modversion['credits'] = 'The XOOPS Project';
$modversion['author'] = 'Rowd';
$modversion['help'] = 'help.php';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'lang_logo.png';
$modversion['dirname'] = 'sitelang';

// MySQL file
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = 'lang';
$modversion['tables'][1] = 'lang_blocks';
$modversion['tables'][2] = 'lang_modules';
$modversion['tables'][3] = 'lang_user';
$modversion['tables'][4] = 'lang_name';

$modversion['onInstall'] = 'include/install_funcs.php';

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

// Menu
$modversion['hasMain'] = 0;

// Blocks
$modversion['blocks'][1]['file'] = 'sitelang_select.php';
$modversion['blocks'][1]['name'] = _MI_LANG_BNAME1;
$modversion['blocks'][1]['description'] = _MI_LANG_BNAME1_DESC;
$modversion['blocks'][1]['show_func'] = 'b_sitelang_lang_show';
$modversion['blocks'][1]['template'] = 'lang_block_select.html';
