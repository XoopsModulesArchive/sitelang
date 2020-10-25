<?php
// $Id: xoopsblocklang.php,v 1.2 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: xoopsblocklang.php)                      //
//  Creation date : 10-May-2005                                              //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';

class XoopsBlockLang extends XoopsBlock
{
    /**
     * A copy of the core block function, with some changes.
     * retrieve array of {@link XoopsBlock}s meeting certain conditions
     * @param null $criteria  {@link CriteriaElement} with conditions for the blocks
     * @param bool $id_as_key should the blocks' bid be the key for the returned array?
     * @return array {@link XoopsBlock}s matching the conditions
     */

    public function &getObjects($criteria = null, $id_as_key = false)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('newblocks'); // block module link table deprecated, by the looks of it

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();

            $limit = $criteria->getLimit();

            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $block = new self();

            $block->assignVars($myrow);

            if (!$id_as_key) {
                $ret[] = &$block;
            } else {
                $ret[$myrow['bid']] = &$block;
            }

            unset($block);
        }

        return $ret;
    }

    public function buildBlock()
    {
        global $xoopsConfig, $xoopsOption;

        $block = [];

        // M for module block, S for system block C for Custom

        if ('C' != $this->getVar('block_type')) {
            // get block display function

            $show_func = $this->getVar('show_func');

            if (!$show_func) {
                return false;
            }

            // must get lang files b4 execution of the function

            if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/blocks/' . $this->getVar('func_file'))) {
                if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/blocks.php')) {
                    require_once XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/blocks.php';
                } elseif (file_exists(XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/language/english/blocks.php')) {
                    require_once XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/language/english/blocks.php';
                }

                require_once XOOPS_ROOT_PATH . '/modules/' . $this->getVar('dirname') . '/blocks/' . $this->getVar('func_file');

                $options = explode('|', $this->getVar('options'));

                if (function_exists($show_func)) {
                    // execute the function

                    $block = $show_func($options);

                    if (!$block) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // it is a custom block, so just return the contents

            $block['content'] = $this->getContent('S', $this->getVar('c_type'));

            if (empty($block['content'])) {
                return false;
            }
        }

        return $block;
    }

    public function &getAllByGroupModule($groupid, $module_id = 0, $toponlyblock = false, $visible = null, $orderby = 'b.weight,b.bid', $isactive = 1)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();

        $ret = [];

        $sql = 'SELECT DISTINCT gperm_itemid FROM ' . $db->prefix('group_permission') . " WHERE gperm_name = 'block_read' AND gperm_modid = 1";

        if (is_array($groupid)) {
            $sql .= ' AND gperm_groupid IN (' . implode(',', $groupid) . ')';
        } else {
            if ((int)$groupid > 0) {
                $sql .= ' AND gperm_groupid=' . $groupid;
            }
        }

        $result = $db->query($sql);

        $blockids = [];

        while (false !== ($myrow = $db->fetchArray($result))) {
            $blockids[] = $myrow['gperm_itemid'];
        }

        if (!empty($blockids)) {
            $sql = 'SELECT b.*, lb.blockname as langblockname FROM ' . $db->prefix('newblocks') . ' b, ' . $db->prefix('block_module_link') . ' m, ' . $db->prefix('lang_blocks') . ' lb WHERE m.block_id=b.bid AND lb.bid=b.bid AND lb.langcode=\'' . _LANGCODE . '\'';

            $sql .= ' AND b.isactive=' . $isactive;

            if (isset($visible)) {
                $sql .= ' AND b.visible=' . (int)$visible;
            }

            $module_id = (int)$module_id;

            if (!empty($module_id)) {
                $sql .= ' AND m.module_id IN (0,' . $module_id;

                if ($toponlyblock) {
                    $sql .= ',-1';
                }

                $sql .= ')';
            } else {
                if ($toponlyblock) {
                    $sql .= ' AND m.module_id IN (0,-1)';
                } else {
                    $sql .= ' AND m.module_id=0';
                }
            }

            $sql .= ' AND b.bid IN (' . implode(',', $blockids) . ')';

            $sql .= ' ORDER BY ' . $orderby;

            $result = $db->query($sql);

            $temp_btpl = '';

            while (false !== ($myrow = $db->fetchArray($result))) {
                $block = new XoopsBlock($myrow);

                $block->setVar('title', $myrow['langblockname']);

                $ret[$myrow['bid']] = &$block;

                unset($block);
            }
        }

        return $ret;
    }
}
