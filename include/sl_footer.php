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
//  ------------------------------------------------------------------------ //

// assign the language css file to the template, if required for current site langauge
$langcss = '';
if (isset($_SESSION['languagecss']) && !empty($_SESSION['languagecss'])) {
    $langcss_sess = $_SESSION['languagecss'];

    $langcss_href = XOOPS_URL . '/modules/sitelang/css/' . $langcss_sess;

    $langcss = '<link rel="stylesheet" type="text/css" media="screen" href="' . $langcss_href . '">';
}

if (!empty($langcss)) {
    if ($xoopsTpl->get_template_vars('xoops_module_header')) {
        $langcss = $xoopsTpl->get_template_vars('xoops_module_header') . $langcss;

        $xoopsTpl->assign('xoops_module_header', $langcss);
    } else {
        $xoopsTpl->assign('xoops_module_header', $langcss);
    }
}
