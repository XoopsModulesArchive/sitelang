<?php
// $Id: xoopsmodulelang.php,v 1.2 2005/05/20 06:51:54 rowd Exp $
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
//  Module        : sitelang (File: xoopsmodulelang.php)                     //
//  Creation date : 12-May-2005                                              //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH . '/kernel/module.php';

class XoopsModuleLang extends XoopsModule
{
    public function __construct()
    {
        $this->initVar('modname', XOBJ_DTYPE_TXTBOX, null, true, 150);

        parent::XoopsModule();
    }
}

class SitelangXoopsModuleLangHandler extends XoopsModuleHandler
{
    /**
     * Retrieve module records, joined with lang_module data
     *
     * @param null $criteria  {@link CriteriaElement}
     * @param bool $id_as_key Use the ID as key into the array
     * @return  array
     */

    public function &getObjects($criteria = null, $id_as_key = false)
    {
        global $xoopsConfig;

        $ret = [];

        $dirname = $xoopsConfig['language'];

        $limit = $start = 0;

        $sql = 'SELECT m.*, ml.modname FROM ' . $this->db->prefix('modules') . ' m, ' . $this->db->prefix('lang_modules') . ' ml, ' . $this->db->prefix('lang') . ' l';

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();

            $sql .= ' AND m.mid=ml.mid AND ml.langid=l.langid AND l.langdirname=\'' . $dirname . '\' ';

            $tempSort = $criteria->getSort();

            if (!empty($tempSort)) {
                $sql .= ' ' . $criteria->getSort();

                $sql .= ' ' . $criteria->getOrder(); // ASC or DESC
            } else {
                $sql .= ' ORDER BY weight, mid ASC';
            }

            $limit = $criteria->getLimit();

            $start = $criteria->getStart();
        } else {
            $sql .= ' ORDER BY weight, mid ASC';
        }

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $module = new XoopsModuleLang();

            $module->assignVars($myrow);

            if (!$id_as_key) {
                $ret[] = &$module;
            } else {
                $ret[$myrow['mid']] = &$module;
            }

            unset($module);
        }

        return $ret;
    }
}
