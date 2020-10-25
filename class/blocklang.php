<?php
// $Id: blocklang.php,v 1.4 2005/05/17 09:16:30 rowd Exp $
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
//  Module        : sitelang (File: blocklang.php)                           //
//  Creation date : 06-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
class BlockLang extends XoopsObject
{
    public $db;

    public function __construct($bid = -1, $langid = -1)
    {
        $this->initVar('bid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('mid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('langid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('langcode', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('blockname', XOBJ_DTYPE_TXTBOX, null, false);

        $this->db = XoopsDatabaseFactory::getDatabaseConnection();

        $bid = (int)$bid;

        $langid = (int)$langid;

        if (-1 != $bid || -1 != $langid) {
            $modlangHandler = xoops_getModuleHandler('blocklang', 'sitelang');

            if (-1 != $bid) {
                $modlang = $modlangHandler->get($bid);
            }
        }
    }
}

class SitelangBlocklangHandler extends XoopsObjectHandler
{
    /**
     * create a new blocklang object
     *
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link BlockLang}
     */

    public function &create($isNew = true)
    {
        $blocklang = new BlockLang();

        if ($isNew) {
            $blocklang->setNew();
        }

        return $blocklang;
    }

    /**
     * Retrieve a blocklang object. The unique key requires two fields,
     * a block id and a language id.
     *
     * @param mixed $bid
     * @param mixed $langid
     * @param mixed $langcode
     * @return mixed reference to the {@link BlockLang} object, FALSE if failed
     */

    public function get($bid = -1, $langid = -1, $langcode = '')
    {
        $bid = (int)$bid;

        $langid = (int)$langid;

        if ($bid > 0 && ($langid > 0 || !empty($langcode))) {
            $sql = 'SELECT * FROM ' . $this->db->prefix('lang_blocks') . ' WHERE bid=' . $bid . ' AND ';

            if (!empty($langcode)) {
                $sql .= "langcode='" . $langcode . "'";
            } else {
                $sql .= 'langid=' . $langid . '';
            }

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }

            $blocklang = new BlockLang();

            $record = $this->db->fetchArray($result);

            if (!$record) {
                return false;
            }  

            $blocklang->assignVars($record);

            return $blocklang;
        }

        return false;
    }

    /**
     * retrieve block languages from the database
     *
     * @param null $criteria   {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key  use the langid as key for the array
     * @param bool $as_objects if false, return langid => blockname array
     * @return array array of {@link BlockLang} objects
     */

    public function &getObjects($criteria = null, $id_as_key = false, $as_objects = true)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('lang_blocks');

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();

            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }

            $limit = $criteria->getLimit();

            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            if ($as_objects) {
                $blocklang = new BlockLang();

                $blocklang->assignVars($myrow);

                if (!$id_as_key) {
                    $ret[] = &$blocklang;
                } else {
                    $ret[$myrow['bid']] = &$blocklang;
                }

                unset($blocklang);
            } else {
                if (!$id_as_key) {
                    $ret[] = [
                        'bid' => $myrow['bid'],
                        'mid' => $myrow['mid'],
                        'langid' => $myrow['langid'],
                        'langcode' => $myrow['langcode'],
                        'blockname' => $myrow['blockname'],
                    ];
                } else {
                    $ret[$myrow['bid']] = [
                        'bid' => $myrow['bid'],
                        'mid' => $myrow['mid'],
                        'langid' => $myrow['langid'],
                        'langcode' => $myrow['langcode'],
                        'blockname' => $myrow['blockname'],
                    ];
                }
            }
        }

        return $ret;
    }

    /**
     * returns an array of block names
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getListBlocks($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $blocks = &$this->getObjects($criteria, true);

        $j = 0;

        foreach (array_keys($blocks) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$blocks[$i]->getVar('blockname');
            } else {
                $ret[$blocks[$i]->getVar('blockname')] = &$blocks[$i]->getVar('blockname');
            }
        }

        return $ret;
    }

    /**
     * returns an array of block id=>blocknames
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getListLangs($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $blocklangs = &$this->getObjects($criteria, false);

        foreach (array_keys($blocklangs) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$blocklangs[$i]->getVar('langid');
            } else {
                $ret[$blocklangs[$i]->getVar('langid')] = &$blocklangs[$i]->getVar('blockname');
            }
        }

        return $ret;
    }

    /**
     * returns an array of block ids
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getList($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $blocklangs = &$this->getObjects($criteria, true);

        foreach (array_keys($blocklangs) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$blocklangs[$i]->getVar('blockname');
            } else {
                $ret[$blocklangs[$i]->getVar('langid')] = &$blocklangs[$i]->getVar('blockname');
            }
        }

        return $ret;
    }

    /*
    * Save block language settings in database
    * @param object $blocklang reference to the {@link BlockLang} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(XoopsObject $blocklang, $force = false, $key = 'bid')
    {
        if ('blocklang' != mb_strtolower(get_class($blocklang))) {
            return false;
        }

        if (!$blocklang->isDirty()) {
            return true;
        }

        if (!$blocklang->cleanVars()) {
            return false;
        }

        foreach ($blocklang->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $blocklang->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }

        if ($blocklang->isNew()) {
            $sql = 'INSERT INTO ' . $this->db->prefix('lang_blocks') . ' VALUES (' . $cleanvars['bid'] . ', ' . $cleanvars['mid'] . ', ' . $cleanvars['langid'] . ', ' . $cleanvars['langcode'] . ', ' . $cleanvars['blockname'] . ')';
        } else {
            $sql = 'UPDATE '
                   . $this->db->prefix('lang_blocks')
                   . ' SET bid='
                   . $cleanvars['bid']
                   . ', mid='
                   . $cleanvars['mid']
                   . ', langid='
                   . $cleanvars['langid']
                   . ', langcode='
                   . $cleanvars['langcode']
                   . ', blockname='
                   . $cleanvars['blockname']
                   . ' WHERE langid='
                   . $cleanvars['langid']
                   . ' AND bid='
                   . $cleanvars['bid']
                   . '';
        }

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete a block language link from the database
     *
     * @param \XoopsObject $blocklang reference to the {@link BlockLang} to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */

    public function delete(XoopsObject $blocklang, $force = false)
    {
        if ('blocklang' != mb_strtolower(get_class($blocklang))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('lang_blocks') . ' WHERE bid=' . $blocklang->getVar('bid') . ' AND langid=' . $blocklang->getVar('langid') . '';

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * return a list of module ids
     *
     * @param bool $id_as_key
     * @return array|false
     * @return array|false
     */

    public function getMidList($id_as_key = true)
    {
        $ret = [];

        $sql = 'SELECT DISTINCT mid FROM ' . $this->db->prefix('lang_blocks') . '';

        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            if (false === $id_as_key) {
                $ret[] = $myrow['mid'];
            } else {
                $ret[$myrow['mid']] = $myrow['mid'];
            }
        }

        return $ret;
    }
}
