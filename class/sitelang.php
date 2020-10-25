<?php
// $Id: sitelang.php,v 1.4 2005/05/13 23:47:15 rowd Exp $
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
//  Module        : sitelang (File: sitelang.php)                            //
//  Creation date : 03-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
class SiteLang extends XoopsObject
{
    public $db;

    public function __construct($langid = -1)
    {
        $this->initVar('langid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('langcode', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('langdirname', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('langisactive', XOBJ_DTYPE_INT, null, false);

        $this->initVar('sitename', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('slogan', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('footer', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('charset', XOBJ_DTYPE_TXTBOX, null, false);

        $this->initVar('langcss', XOBJ_DTYPE_TXTBOX, null, false);

        $this->db = XoopsDatabaseFactory::getDatabaseConnection();

        if (is_array($langid)) {
            $this->assignVars($langid);
        } elseif (-1 != $langid) {
            $sitelangHandler = xoops_getModuleHandler('sitelang', 'sitelang');

            $sitelang = $sitelangHandler->get($langid);

            foreach ($sitelang->vars as $k => $v) {
                $this->assignVar($k, $v['value']);
            }
        }
    }
}

class SitelangSitelangHandler extends XoopsObjectHandler
{
    /**
     * create a new sitelang object
     *
     * @param bool $isNew flag the new objects as "new"?
     * @return object {@link SiteLang}
     */

    public function &create($isNew = true)
    {
        $sitelang = new SiteLang();

        if ($isNew) {
            $sitelang->setNew();
        }

        return $sitelang;
    }

    /**
     * retrieve a sitelang object
     *
     * @param int $id langid
     * @return mixed reference to the {@link SiteLang} object, FALSE if failed
     */

    public function get($id)
    {
        $id = (int)$id;

        $sitelang = new SiteLang();

        if ($id > 0) {
            $sql = 'SELECT langid, langcode, langdirname, langisactive, sitename, slogan, footer, charset, langcss FROM ' . $this->db->prefix('lang') . " WHERE langid=$id";

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $sitelang->assignVars($this->db->fetchArray($result));
        }

        return $sitelang;
    }

    /**
     * Retrieve languages from the database
     *
     * @param null $criteria   {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key  use the langid as key for the array
     * @param bool $as_objects if false, return langid => langcode array
     * @return array array of {@link SiteLang} objects
     */

    public function &getObjects($criteria = null, $id_as_key = false, $as_objects = true)
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix('lang');

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
                $sitelang = new SiteLang();

                $sitelang->assignVars($myrow);

                if (!$id_as_key) {
                    $ret[] = &$sitelang;
                } else {
                    $ret[$myrow['langid']] = &$sitelang;
                }

                unset($sitelang);
            } else {
                if (!$id_as_key) {
                    $ret[] = [
                        'langid' => $myrow['langid'],
                        'langcode' => $myrow['langcode'],
                        'langdirname' => $myrow['langdirname'],
                        'langisactive' => $myrow['langisactive'],
                        'sitename' => $myrow['sitename'],
                        'slogan' => $myrow['slogan'],
                        'footer' => $myrow['footer'],
                        'charset' => $myrow['charset'],
                        'langcss' => $myrow['langcss'],
                    ];
                } else {
                    $ret[$myrow['langid']] = [
                        'langid' => $myrow['langid'],
                        'langcode' => $myrow['langcode'],
                        'langdirname' => $myrow['langdirname'],
                        'langisactive' => $myrow['langisactive'],
                        'sitename' => $myrow['sitename'],
                        'slogan' => $myrow['slogan'],
                        'footer' => $myrow['footer'],
                        'charset' => $myrow['charset'],
                        'langcss' => $myrow['langcss'],
                    ];
                }
            }
        }

        return $ret;
    }

    /*
    * Save language settings in database
    * @param object $sitelang reference to the {@link SiteLang} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(XoopsObject $sitelang, $force = false)
    {
        if ('sitelang' != mb_strtolower(get_class($sitelang))) {
            return false;
        }

        if (!$sitelang->isDirty()) {
            return true;
        }

        if (!$sitelang->cleanVars()) {
            return false;
        }

        foreach ($sitelang->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $sitelang->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }

        if ($sitelang->isNew()) {
            $sql = 'INSERT INTO '
                   . $this->db->prefix('lang')
                   . ' VALUES (null, '
                   . $cleanvars['langcode']
                   . ', '
                   . $cleanvars['langdirname']
                   . ', '
                   . $cleanvars['langisactive']
                   . ', '
                   . $cleanvars['sitename']
                   . ', '
                   . $cleanvars['slogan']
                   . ', '
                   . $cleanvars['footer']
                   . ', '
                   . $cleanvars['charset']
                   . ', '
                   . $cleanvars['langcss']
                   . ')';
        } else {
            $sql = 'UPDATE '
                   . $this->db->prefix('lang')
                   . ' SET langcode='
                   . $cleanvars['langcode']
                   . ', langdirname='
                   . $cleanvars['langdirname']
                   . ', langisactive='
                   . $cleanvars['langisactive']
                   . ', sitename='
                   . $cleanvars['sitename']
                   . ', slogan='
                   . $cleanvars['slogan']
                   . ', footer='
                   . $cleanvars['footer']
                   . ', charset='
                   . $cleanvars['charset']
                   . ', langcss='
                   . $cleanvars['langcss']
                   . ' WHERE langid='
                   . $cleanvars['langid'];
        }

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        if ($sitelang->isNew()) {
            $sitelang->setVar('langid', $this->db->getInsertId());
        }

        return true;
    }

    /**
     * Delete a language from the database
     *
     * @param \XoopsObject $sitelang reference to the {@link SiteLang} to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */

    public function delete(XoopsObject $sitelang, $force = false)
    {
        if ('sitelang' != mb_strtolower(get_class($sitelang))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('lang') . ' WHERE langid=' . $sitelang->getVar('langid') . '';

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
     * Retrieve an array of {@link SiteLang} objects which are 'active'
     *
     * @staticvar array $activelangs is returned
     * @param mixed $guilangcode
     * @return string array of <a href='psi_element://SiteLang'>SiteLang</a> objects
     */

    public function &getActiveLangs($guilangcode = 'en')
    {
        //		static $activelangs = null;  TODO: make this static again for each language...

        $activelangs = '';

        if (empty($activelangs)) {
            $sql = 'SELECT sl.*, ln.langname 
		            FROM ' . $this->db->prefix('lang') . ' sl, ' . $this->db->prefix('lang_name') . " ln 
				    WHERE ln.guilangcode='" . $guilangcode . "' AND ln.langcode=sl.langcode AND sl.langisactive=1";

            $result = $this->db->query($sql);

            if (!$result) {
                return $activelangs;
            }

            while (false !== ($myrow = $this->db->fetchArray($result))) {
                $activelangs[$myrow['langid']] = [
                    'langid' => $myrow['langid'],
                    'langcode' => $myrow['langcode'],
                    'langdirname' => $myrow['langdirname'],
                    'langisactive' => $myrow['langisactive'],
                    'sitename' => $myrow['sitename'],
                    'slogan' => $myrow['slogan'],
                    'footer' => $myrow['footer'],
                    'charset' => $myrow['charset'],
                    'langcss' => $myrow['langcss'],
                    'langname' => $myrow['langname'],
                ];

                /*                $langs_arr[$myrow['langcode']] = array( 'langid' => $myrow['langid'],
                                                                      'langcode' => $myrow['langcode'],
                                                                      'langdirname' => $myrow['langdirname'],
                                                                      'langisactive' => $myrow['langisactive'],
                                                                      'sitename' => $myrow['sitename'],
                                                                      'slogan' => $myrow['slogan'],
                                                                      'footer' => $myrow['footer'],
                                                                      'charset' => $myrow['charset'],
                                                                      'langcss' => $myrow['langcss'],
                                                                      'langname' => $myrow['langname']); */
            }
        }

        return $activelangs;
    }

    /**
     * Retrieve an array of all {@link SiteLang} objects
     *
     * @staticvar array $langs_arr is returned
     * @param mixed $guilangcode
     * @return string array of <a href='psi_element://SiteLang'>SiteLang</a> objects
     */

    public function &getAllLangs($guilangcode = 'en')
    {
        //		static $langs_arr = null; TODO: make this static again for each language...

        $langs_arr = '';

        if (empty($langs_arr)) {
            $sql = 'SELECT sl.langid, sl.langcode, sl.langdirname, sl.langisactive, sl.sitename, sl.slogan, sl.footer, sl.charset, sl.langcss, ln.langname 
		            FROM ' . $this->db->prefix('lang') . ' sl, ' . $this->db->prefix('lang_name') . " ln 
				    WHERE ln.guilangcode='" . $guilangcode . "' AND ln.langcode=sl.langcode";

            $result = $this->db->query($sql);

            if (!$result) {
                return $langs_arr;
            }

            while (false !== ($myrow = $this->db->fetchArray($result))) {
                $langs_arr[$myrow['langid']] = [
                    'langid' => $myrow['langid'],
                    'langcode' => $myrow['langcode'],
                    'langdirname' => $myrow['langdirname'],
                    'langisactive' => $myrow['langisactive'],
                    'sitename' => $myrow['sitename'],
                    'slogan' => $myrow['slogan'],
                    'footer' => $myrow['footer'],
                    'charset' => $myrow['charset'],
                    'langcss' => $myrow['langcss'],
                    'langname' => $myrow['langname'],
                ];

                /*                $langs_arr[$myrow['langcode']] = array( 'langid' => $myrow['langid'],
                                                                      'langcode' => $myrow['langcode'],
                                                                      'langdirname' => $myrow['langdirname'],
                                                                      'langisactive' => $myrow['langisactive'],
                                                                      'sitename' => $myrow['sitename'],
                                                                      'slogan' => $myrow['slogan'],
                                                                      'footer' => $myrow['footer'],
                                                                      'charset' => $myrow['charset'],
                                                                      'langcss' => $myrow['langcss'],
                                                                      'langname' => $myrow['langname']); */
            }
        }

        return $langs_arr;
    }

    /**
     * Retrieve a {@link SiteLang} object using the directory name of the language
     *
     * @param mixed $dirname
     * @return object {@link SiteLang} object
     */

    public function getLangByDirname($dirname)
    {
        $dirname = trim($dirname);

        $sitelang = new SiteLang();

        if (!empty($dirname)) {
            $sql = 'SELECT langid, langcode, langdirname, langisactive, sitename, slogan, footer, charset, langcss FROM ' . $this->db->prefix('lang') . " WHERE langdirname='" . $dirname . "'";

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $sitelang->assignVars($this->db->fetchArray($result));
        }

        return $sitelang;
    }
}
