<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Mondongo.
 *
 * Mondongo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mondongo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mondongo. If not, see <http://www.gnu.org/licenses/>.
 */

class MondongoGroupTest extends MondongoTestCase
{
  protected $elements = array(0 => 'foobar', 1 => 'barfoo');

  protected $callback = array();

  protected $value = false;

  public function setUp()
  {
    $this->callback = array($this, 'changeValue');
    $this->value    = false;
  }

  public function changeValue()
  {
    $this->value = true;
  }

  public function testConstructorSetElementsGetElements()
  {
    $group = new MondongoGroup($this->elements);
    $this->assertSame($this->elements, $group->getElements());

    $group->setElements($elements = array('ups', 'spu'));
    $this->assertSame($elements, $group->getElements());
  }

  public function testCallback()
  {
    $group = new MondongoGroup(array(), 'callback');

    $this->assertEquals('callback', $group->getCallback());
    $group->setCallback('foobar');
    $this->assertEquals('foobar', $group->getCallback());
  }

  public function testAdd()
  {
    $elements = $this->elements;
    $group    = new MondongoGroup($elements);

    $group->add('ups');
    array_push($elements, 'ups');
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->add('ups');
    $this->assertTrue($this->value);
  }

  public function testSet()
  {
    $elements = $this->elements;
    $group    = new MondongoGroup($elements);

    $group->set('ups', 'spu');
    $elements['ups'] = 'spu';
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->set('foobar', true);
    $this->assertTrue($this->value);
  }

  public function testExists()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertTrue($group->exists(0));
    $this->assertFalse($group->exists(10));
  }

  public function testExistsElement()
  {
    $group = new MondongoGroup(array(0 => $date = new DateTime(), 1 => 12));

    $this->assertTrue($group->existsElement($date));
    $this->assertTrue($group->existsElement(12));

    $this->assertFalse($group->indexOf(new DateTime()));
    $this->assertFalse($group->indexOf('foo'));
  }

  public function testIndexOf()
  {
    $group = new MondongoGroup(array(0 => $date = new DateTime(), 1 => 12));

    $this->assertSame(0, $group->indexOf($date));
    $this->assertSame(1, $group->indexOf(12));

    $this->assertFalse($group->indexOf(new DateTime()));
    $this->assertFalse($group->indexOf('foo'));
  }

  public function testRemove()
  {
    $group = new MondongoGroup($this->elements);

    $group->remove(1);

    $this->assertTrue($group->exists(0));
    $this->assertFalse($group->exists(1));

    $group->setCallback($this->callback);
    $group->remove(0);
    $this->assertTrue($this->value);
  }

  public function testClear()
  {
    $group = new MondongoGroup($this->elements);

    $group->clear();
    $this->assertSame(array(), $group->getElements());

    $group = new MondongoGroup($this->elements, $this->callback);
    $group->clear();
    $this->assertTrue($this->value);
  }

  public function testArrayAccess()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertTrue(isset($group[0]));
    $this->assertFalse(isset($group[10]));

    $this->assertSame('foobar', $group[0]);
    $this->assertNull($group['no']);

    $group['ups'] = 'spu';
    $this->assertSame('spu', $group['ups']);

    unset($group['ups']);
    $this->assertNull($group['ups']);
  }

  public function testCountable()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertSame(2, $group->count());
    $this->assertSame(2, count($group));
  }
}
