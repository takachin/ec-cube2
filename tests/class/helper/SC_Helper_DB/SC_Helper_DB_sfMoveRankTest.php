<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/helper/SC_Helper_DB/SC_Helper_DB_TestBase.php");
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * SC_Helper_DB::sfMoveRank()のテストクラス.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_DB_sfMoveRank extends SC_Helper_DB_TestBase
{
    /** @var SC_Helper_DB_Ex */
    protected $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new SC_Helper_DB_Ex();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /////////////////////////////////////////
    public function testSfMoveRank_一番下に移動する場合_RANK1を返す()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '3';
        $pos = '3';
        $where = null;
        $this->expected = 1;
        $this->helper->sfMoveRank($table, $keyIdColum, $keyId, $pos, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }

    public function testSfMoveRank_一番上に移動する場合_RANKはMAXを返す()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '2';
        $pos = '1';
        $where = null;
        $this->expected = $this->objQuery->max('rank', $table);
        $this->helper->sfMoveRank($table, $keyIdColum, $keyId, $pos, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }
    
    public function testSfMoveRank_同じ位置に移動する場合_RANKは変わらない()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '3';
        $pos = '1';
        $where = null;
        $this->expected = 3;
        $this->helper->sfMoveRank($table, $keyIdColum, $keyId, $pos, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }
    
    public function testSfMoveRank_マイナスの位置に移動する場合_RANKはMAX()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '2';
        $pos = '-1';
        $where = null;
        $this->expected = $this->objQuery->max('rank', $table);
        $this->helper->sfMoveRank($table, $keyIdColum, $keyId, $pos, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }
    
    public function testSfMoveRank_最大値以上の位置を与える場合_RANKは1となる()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '2';
        $pos = '4';
        $where = null;
        $this->expected = 1;
        $this->helper->sfMoveRank($table, $keyIdColum, $keyId, $pos, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }

    public function testSfRankUp()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '2';
        $where = 'del_flg = 0';
        $this->expected = 3;
        $this->helper->sfRankUp($table, $keyIdColum, $keyId, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }

    public function testSfRankDown()
    {
        $this->setUpNews();
        $table = 'dtb_news';
        $keyIdColum = 'news_id';
        $keyId = '2';
        $where = 'del_flg = 0';
        $this->expected = 1;
        $this->helper->sfRankDown($table, $keyIdColum, $keyId, $where);
        $col = 'rank';
        $getWhere = 'news_id = ?';
        $arrWhereVal = array($keyId);
        $this->actual = $this->objQuery->get($col, $table, $getWhere, $arrWhereVal);
        $this->verify();
    }

    public function getNewRankProvider()
    {
        return [
            [3, 2, 1],
            [2, 2, 1],
            [1, 2, 2],
            [0, 2, 2]
        ];
    }

    /**
     * @dataProvider getNewRankProvider
     */
    public function testGetNewRank($position, $maxRank, $expected)
    {
        $this->expected = $expected;
        $this->actual = $this->helper->getNewRank($position, $maxRank);
        $this->verify();
    }
}

