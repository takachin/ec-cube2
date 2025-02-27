<?php

$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/helper/SC_Helper_Purchase/SC_Helper_Purchase_TestBase.php");
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
 * SC_Helper_Purchase::registerOrderDetail()のテストクラス.
 *
 * @author Hiroko Tamagawa
 * @version $Id$
 */
class SC_Helper_Purchase_registerOrderDetailTest extends SC_Helper_Purchase_TestBase
{
  /** @var array */
  private $customer_ids = [];
  /** @var array */
  private $order_ids = [];
  protected function setUp(): void
  {
    parent::setUp();
    $this->customer_ids = $this->setUpCustomer();
    $this->order_ids = $this->setUpOrder($this->customer_ids);
  }

  protected function tearDown(): void
  {
    parent::tearDown();
  }

  /////////////////////////////////////////
  public function testRegisterOrderDetail_該当の受注が存在する場合_削除後に新しい情報が登録される()
  {
    $params = array(
      array(
        'order_id' => $this->order_ids[0],
        'hoge' => '999', // DBに存在しないカラム
        'product_id' => '9001',
        'product_class_id' => '9001',
        'product_name' => '製品名9001'
      )
    );
    SC_Helper_Purchase::registerOrderDetail($this->order_ids[0], $params);

    $this->expected['count'] = '1';
    $this->expected['content'] = array(
      'order_id' => $this->order_ids[0],
      'product_id' => '9001',
      'product_class_id' => '9001',
      'product_name' => '製品名9001',
      'product_code' => null // 古いデータにはあるが、deleteされたので消えている
    );

    $this->actual['count'] = $this->objQuery->count('dtb_order_detail', 'order_id = ?', [$this->order_ids[0]]);
    $this->actual['content'] = $this->objQuery->getRow(
      'order_id, product_id, product_class_id, product_name, product_code',
      'dtb_order_detail',
      'order_id = ?',
      [$this->order_ids[0]]
    );

    $this->verify();
  }

  public function testRegisterOrderDetail_該当の受注が存在しない場合_新しい情報が追加登録される()
  {
    $params = array(
      array(
        'order_id' => '1003',
        'hoge' => '999', // DBに存在しないカラム
        'product_id' => '9003',
        'product_class_id' => '9003',
        'product_name' => '製品名9003'
      )
    );
    SC_Helper_Purchase::registerOrderDetail('1003', $params);

    $this->expected['count'] = '1';
    $this->expected['content'] = array(
      'order_id' => '1003',
      'product_id' => '9003',
      'product_class_id' => '9003',
      'product_name' => '製品名9003',
      'product_code' => null
    );

    $this->actual['count'] = $this->objQuery->count('dtb_order_detail', 'order_id = ?', [1003]);
    $result = $this->objQuery->select(
      'order_id, product_id, product_class_id, product_name, product_code',
      'dtb_order_detail',
      'order_id = ?',
      array('1003')
    );
    $this->actual['content'] = $result[0];

    $this->verify();
  }
  //////////////////////////////////////////
}
