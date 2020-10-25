<?php
// $Id: userlang.php,v 1.5 2005/05/17 09:16:40 rowd Exp $
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
//  Module        : sitelang (File: userlang.php)                            //
//  Creation date : 03-January-2005                                          //
//  Author        : Rowd ( http://keybased.net/dev/ )                        //
//  ------------------------------------------------------------------------ //
class UserLang extends XoopsObject
{
    public $db;

    public function __construct($userid = -1, $langid = -1)
    {
        $this->initVar('userid', XOBJ_DTYPE_INT, null, false);

        $this->initVar('langid', XOBJ_DTYPE_INT, null, false);

        $this->db = XoopsDatabaseFactory::getDatabaseConnection();

        $userid = (int)$userid;

        $langid = (int)$langid;

        if (-1 != $userid || -1 != $langid) {
            $userlangHandler = xoops_getModuleHandler('userlang', 'sitelang');

            if (-1 != $userid) {
                $userlang = $userlangHandler->get($userid);
            } else {
                $userlang = &$userlangHandler->getUsingLangid($langid);
            }
        }
    }
}

class SitelangUserlangHandler extends XoopsObjectHandler
{
    /**
     * create a new userlang object
     *
     * @param bool $isNew flag the new objects as "new"?
     * @param mixed $userid
     * @param mixed $langid
     * @return object {@link UserLang}
     */

    public function &create($userid = -1, $langid = -1, $isNew = true)
    {
        $userlang = new UserLang($userid, $langid);

        if ($isNew) {
            $userlang->setNew();
        }

        return $userlang;
    }

    /**
     * retrieve a userlang object
     *
     * @param int $id userid
     * @return mixed reference to the {@link UserLang} object, FALSE if failed
     */

    public function get($id)
    {
        $id = (int)$id;

        if ($id > 0) {
            $sql = 'SELECT userid, langid FROM ' . $this->db->prefix('lang_user') . ' WHERE userid=' . $id . '';

            $result = $this->db->query($sql);

            if (!$result) {
                return false;
            }

            $userlang = new UserLang();

            $record = $this->db->fetchArray($result);

            if (!$record) {
                return false;
            }  

            $userlang->assignVars($record);

            return $userlang;
        }

        return false;
    }

    /**
     * retrieve languages from the database
     *
     * @param null $criteria   {@link CriteriaElement} conditions to be met
     * @param bool $id_as_key  use the langid as key for the array
     * @param bool $as_objects if false, return langid => langcode array
     * @return array array of {@link xoopserver} objects
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
                    ];
                } else {
                    $ret[$myrow['langid']] = [
                        'langid' => $myrow['langid'],
                        'langcode' => $myrow['langcode'],
                        'langdirname' => $myrow['langdirname'],
                        'langisactive' => $myrow['langisactive'],
                        'sitename' => $myrow['sitename'],
                        'slogan' => $myrow['slogan'],
                    ];
                }
            }
        }

        return $ret;
    }

    /*
    * Save user language settings in database
    * @param object $userlang reference to the {@link UserLang} object
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(XoopsObject $userlang, $force = false, $key = 'userid')
    {
        if ('userlang' != mb_strtolower(get_class($userlang))) {
            return false;
        }

        if (!$userlang->isDirty()) {
            return true;
        }

        if (!$userlang->cleanVars()) {
            return false;
        }

        foreach ($userlang->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $userlang->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }

        if ($userlang->isNew()) {
            $sql = 'INSERT INTO ' . $this->db->prefix('lang_user') . ' (userid, langid) VALUES (' . $cleanvars['userid'] . ', ' . $cleanvars['langid'] . ')';
        } else {
            if ('userid' == mb_strtolower($key)) {
                $sql = 'UPDATE ' . $this->db->prefix('lang_user') . ' SET userid=' . $cleanvars['userid'] . ', langid=' . $cleanvars['langid'] . ' WHERE userid=' . $cleanvars['userid'];
            } else {
                $sql = 'UPDATE ' . $this->db->prefix('lang_user') . ' SET userid=' . $cleanvars['userid'] . ', langid=' . $cleanvars['langid'] . ' WHERE langid=' . $cleanvars['langid'];
            }
        }

        if ($force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        if (!$result) {
            return false;
        }

        if ($userlang->isNew()) {
            $userlang->setVar('langid', $this->db->getInsertId());
        }

        return true;
    }

    /**
     * delete a user language link from the database
     *
     * @param \XoopsObject $userlang reference to the {@link UserLang} to delete
     * @param bool         $force
     * @return bool FALSE if failed.
     */

    public function delete(XoopsObject $userlang, $force = false)
    {
        if ('userlang' != mb_strtolower(get_class($userlang))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('lang_user') . ' WHERE userid=' . $userlang->getVar('userid') . '';

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
}
