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
 * SC_Helper_DB::sfCreateBasisDataCache()のテストクラス.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_DB_sfCreateBasisDataCache extends SC_Helper_DB_TestBase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new SC_Helper_DB_sfCreateBasisDataCacheMock();
        $this->cashFilePath = MASTER_DATA_REALDIR . 'dtb_baseinfo.serial';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /////////////////////////////////////////
    public function testsfCreateBasisDataCache_ファイル操作に成功した場合_TRUEを返す()
    {
        if (file_exists($this->cashFilePath)) {
            unlink($this->cashFilePath);
        }
        $arrData = array(
            'id' => '1',
            'company_name' => 'testshop'
        );
        $this->expected = true;
        $this->actual = $this->helper->sfCreateBasisDataCache();
        unlink($this->cashFilePath);
        $this->verify();
    }
}

class SC_Helper_DB_sfCreateBasisDataCacheMock extends SC_Helper_DB_Ex
{
    public static function sfGetBasisData($force = false)
    {
        $arrData = array(
            'id' => '1',
            'company_name' => 'testshop'
        );

        return $arrData;
    }
}
