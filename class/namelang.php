<?php
// $Id: namelang.php,v 1.2 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: namelang.php)                            //
//  Creation date : 29-April-2005                                            //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
class NameLang extends XoopsObject
{
    public $db;

    public function __construct($langcode = '')
    {
        $this->initVar('guilangcode', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('langcode', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('langname', XOBJ_DTYPE_TXTBOX, null, false);

        $this->db = XoopsDatabaseFactory::getDatabaseConnection();
    }
}

class SitelangNamelangHandler extends XoopsObjectHandler
{
    /**
     * create a new namelang object
     *
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link NameLang}
     */

    public function &create($isNew = true)
    {
        $namelang = new NameLang();

        if ($isNew) {
            $namelang->setNew();
        }

        return $namelang;
    }

    /**
     * retrieve a namelang object
     *
     * @param string $guilangcode langcode of the GUI i.e. the page being viewed
     * @param string $langcode    langcode for this language name
     * @return mixed reference to the {@link NameLang} object, FALSE if failed
     */

    public function get($guilangcode = '', $langcode = '')
    {
        if (!empty($guilangcode) && !empty($langcode)) {
            $sql = 'SELECT * FROM ' . $this->db->prefix('lang_name') . " WHERE guilangcode='" . $guilangcode . "' AND langcode='" . $langcode . "'";

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }

            $namelang = new NameLang();

            $record = $this->db->fetchArray($result);

            if (!$record) {
                return false;
            }  

            $namelang->assignVars($record);

            return $namelang;
        }

        return false;
    }

    /**
     * Retrieve language names from the database
     *
     * @param null $criteria   {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key  use the langcode as key for the array
     * @param bool $as_objects if false, return langcode => langname array
     * @return array array of {@link NameLang} objects
     */

    public function &getObjects($criteria = null, $id_as_key = false, $as_objects = false)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('lang_name');

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
                $namelang = new NameLang();

                $namelang->assignVars($myrow);

                if (!$id_as_key) {
                    $ret[] = &$namelang;
                } else {
                    $ret[$myrow['langcode']] = &$namelang;
                }

                unset($namelang);
            } else {
                if (!$id_as_key) {
                    $ret[] = [
                        'guilangcode' => $myrow['guilangcode'],
                        'langcode' => $myrow['langcode'],
                        'langname' => $myrow['langname'],
                    ];
                } else {
                    $ret[$myrow['guilangcode']] = [
                        'guilangcode' => $myrow['guilangcode'],
                        'langcode' => $myrow['langcode'],
                        'langname' => $myrow['langname'],
                    ];
                }
            }
        }

        return $ret;
    }

    /*
    * Save language name settings in database
    * @param object $namelang reference to the {@link NameLang} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(XoopsObject $namelang, $force = false)
    {
        if ('namelang' != mb_strtolower(get_class($namelang))) {
            return false;
        }

        if (!$namelang->isDirty()) {
            return true;
        }

        if (!$namelang->cleanVars()) {
            return false;
        }

        foreach ($namelang->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $namelang->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }

        if ($namelang->isNew()) {
            $sql = 'INSERT INTO ' . $this->db->prefix('lang_name') . ' VALUES (' . $cleanvars['guilangcode'] . ', ' . $cleanvars['langcode'] . ', ' . $cleanvars['langname'] . ')';
        } else {
            $sql = 'UPDATE ' . $this->db->prefix('lang_name') . ' SET langname=' . $cleanvars['langname'] . ' WHERE guilangcode=' . $cleanvars['guilangcode'] . ' AND langcode=' . $cleanvars['langcode'] . ' ';
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
     * Delete language name records from the database
     *
     * @param \XoopsObject $namelang reference to the {@link NameLang} to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */

    public function delete(XoopsObject $namelang, $force = false)
    {
        if ('namelang' != mb_strtolower(get_class($namelang))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('lang_name') . " WHERE guilangcode='" . $namelang->getVar('guilangcode') . "' AND langcode='" . $namelang->getVar('langcode') . "'";

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
     * Retrieve an array of {@link NameLang} objects which are 'active'
     *
     * @staticvar array $activelangs is returned
     * @return array array of {@link SiteLang} objects
     */

    public function &getActiveLangsNames()
    {
        static $activelangs = null;

        if (empty($activelangs)) {
            $criteria = new CriteriaCompo();

            $criteria->add(new Criteria('langisactive', '1'));

            $activelangs = $this->getObjects($criteria, true, false); // $criteria, $id_as_key, $as_objects
        }

        return $activelangs;
    }

    /**
     * Retrieve an array of all {@link SiteLang} objects
     *
     * @staticvar array $langs_arr is returned
     * @return array array of {@link SiteLang} objects
     */

    public function &getAllLangsNames()
    {
        static $langs_arr = null;

        if (empty($langs_arr)) {
            $langs_arr = $this->getObjects(null, true, false); // $criteria, $id_as_key, $as_objects
        }

        return $langs_arr;
    }

    /**
     * Retrieve a {@link SiteLang} object using the directory name of the language
     *
     * @param mixed $dirname
     * @return object {@link SiteLang} object
     */

    public function getLangByLangcode($dirname)
    {
        $dirname = trim($dirname);

        $sitelang = new SiteLang();

        if (!empty($dirname)) {
            $sql = 'SELECT langid, langname, langdirname, langisactive, sitename, slogan, footer, charset, langcss FROM ' . $this->db->prefix('lang') . " WHERE langdirname='" . $dirname . "'";

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $sitelang->assignVars($this->db->fetchArray($result));
        }

        return $sitelang;
    }
}
