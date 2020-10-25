<?php
// $Id: modulelang.php,v 1.4 2005/05/17 09:16:40 rowd Exp $
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
//  Module        : sitelang (File: modulelang.php)                          //
//  Creation date : 03-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
class ModuleLang extends XoopsObject
{
    public $db;

    public function __construct($mid = -1, $langid = -1)
    {
        $this->initVar('mid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('langid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('modname', XOBJ_DTYPE_TXTBOX, null, true, 150);

        $this->db = XoopsDatabaseFactory::getDatabaseConnection();

        $mid = (int)$mid;

        $langid = (int)$langid;

        if (-1 != $mid || -1 != $langid) {
            $modlangHandler = xoops_getModuleHandler('modlang', 'sitelang');

            if (-1 != $mid) {
                $modlang = $modlangHandler->get($mid);
            }
        }
    }
}

class SitelangModlangHandler extends XoopsObjectHandler
{
    /**
     * create a new modulelang object
     *
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link ModuleLang}
     */

    public function &create($isNew = true)
    {
        $modlang = new ModuleLang();

        if ($isNew) {
            $modlang->setNew();
        }

        return $modlang;
    }

    /**
     * Retrieve a modulelang object. The unique key requires two fields,
     * a module id and a language id.
     *
     * @param mixed $mid
     * @param mixed $langid
     * @return mixed reference to the {@link ModuleLang} object, FALSE if failed
     */

    public function get($mid = -1, $langid = -1)
    {
        $mid = (int)$mid;

        $langid = (int)$langid;

        if (($mid > 0) && ($langid > 0)) {
            $sql = 'SELECT mid, langid, modname FROM ' . $this->db->prefix('lang_modules') . ' WHERE mid=' . $mid . ' AND langid=' . $langid . '';

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }

            $modlang = new ModuleLang();

            $record = $this->db->fetchArray($result);

            if (!$record) {
                return false;
            }  

            $modlang->assignVars($record);

            return $modlang;
        }

        return false;
    }

    /**
     * retrieve module languages from the database
     *
     * @param null $criteria   {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key  use the langid as key for the array
     * @param bool $as_objects if false, return langid => modname array
     * @return array array of {@link ModuleLang} objects
     */

    public function &getObjects($criteria = null, $id_as_key = false, $as_objects = true)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('lang_modules');

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
                $modlang = new ModuleLang();

                $modlang->assignVars($myrow);

                if (!$id_as_key) {
                    $ret[] = &$modlang;
                } else {
                    $ret[$myrow['mid']] = &$modlang;
                }

                unset($modlang);
            } else {
                if (!$id_as_key) {
                    $ret[] = [
                        'mid' => $myrow['mid'],
                        'langid' => $myrow['langid'],
                        'modname' => $myrow['modname'],
                    ];
                } else {
                    $ret[$myrow['mid']] = [
                        'mid' => $myrow['mid'],
                        'langid' => $myrow['langid'],
                        'modname' => $myrow['modname'],
                    ];
                }
            }
        }

        return $ret;
    }

    /**
     * returns an array of module names
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getListMods($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $modules = &$this->getObjects($criteria, true);

        $j = 0;

        foreach (array_keys($modules) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$modules[$i]->getVar('modname');
            } else {
                $ret[$modules[$i]->getVar('modname')] = &$modules[$i]->getVar('modname');
            }
        }

        return $ret;
    }

    /**
     * returns an array of module ids
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getListLangs($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $modulelangs = &$this->getObjects($criteria, false);

        foreach (array_keys($modulelangs) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$modulelangs[$i]->getVar('langid');
            } else {
                $ret[$modulelangs[$i]->getVar('langid')] = &$modulelangs[$i]->getVar('modname');
            }
        }

        return $ret;
    }

    /**
     * returns an array of module ids
     *
     * @param null  $criteria
     * @param mixed $dirname_as_key
     * @return  array
     */

    public function &getList($criteria = null, $dirname_as_key = false)
    {
        $ret = [];

        $modulelangs = &$this->getObjects($criteria, true);

        foreach (array_keys($modulelangs) as $i) {
            if (!$dirname_as_key) {
                $ret[$i] = &$modulelangs[$i]->getVar('modname');
            } else {
                $ret[$modulelangs[$i]->getVar('langid')] = &$modulelangs[$i]->getVar('modname');
            }
        }

        return $ret;
    }

    /*
    * Save module language settings in database
    * @param object $modlang reference to the {@link ModuleLang} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(XoopsObject $modlang, $force = false, $key = 'mid')
    {
        if ('modulelang' != mb_strtolower(get_class($modlang))) {
            return false;
        }

        if (!$modlang->isDirty()) {
            return true;
        }

        if (!$modlang->cleanVars()) {
            return false;
        }

        foreach ($modlang->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $modlang->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }

        if ($modlang->isNew()) {
            $sql = 'INSERT INTO ' . $this->db->prefix('lang_modules') . ' VALUES (' . $cleanvars['mid'] . ', ' . $cleanvars['langid'] . ', ' . $cleanvars['modname'] . ')';
        } else {
            $sql = 'UPDATE ' . $this->db->prefix('lang_modules') . ' SET mid=' . $cleanvars['mid'] . ', langid=' . $cleanvars['langid'] . ', modname=' . $cleanvars['modname'] . ' WHERE langid=' . $cleanvars['langid'] . ' AND mid=' . $cleanvars['mid'] . '';
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
     * delete a module language link from the database
     *
     * @param \XoopsObject $modlang reference to the {@link ModuleLang} to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */

    public function delete(XoopsObject $modlang, $force = false)
    {
        if ('modulelang' != mb_strtolower(get_class($modlang))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('lang_modules') . ' WHERE mid=' . $modlang->getVar('mid') . ' AND langid=' . $modlang->getVar('langid') . '';

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
     * Retrieve a {@link ModuleLang} object using the directory name of the language and module id
     *
     * @param mixed $dirname
     * @param mixed $mid
     * @return object {@link ModuleLang} object
     */

    public function getModLangByDirnameMid($dirname, $mid = -1)
    {
        $dirname = trim($dirname);

        $mid = (int)$mid;

        $modulelang = new ModuleLang();

        if (!empty($dirname) && $mid > 0) {
            $sql = 'SELECT m.mid as mid, m.langid as langid, m.modname as modname 
			        FROM ' . $this->db->prefix('lang_modules') . ' as m  
					LEFT JOIN ' . $this->db->prefix('lang') . " as l ON m.langid=l.langid 
					WHERE l.langdirname='" . $dirname . "' AND m.mid=" . $mid . '';

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $modulelang->assignVars($this->db->fetchArray($result));
        }

        return $modulelang;
    }
}
