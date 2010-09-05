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
  protected $element0;
  protected $element1;
  protected $elements;

  protected $callback = array();

  protected $value = false;

  public function setUp()
  {
    $this->element0 = new Comment();
    $this->element0['name'] = 'Element0';
    $this->element1 = new Comment();
    $this->element1['name'] = 'Element0';
    $this->elements = array($this->element0, $this->element1);

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

    $group->setElements($elements = array(new Comment(), new Comment()));
    $this->assertSame($elements, $group->getElements());
  }

  public function testOriginalElements()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertSame(array(), $group->getOriginalElements());
    $group->saveOriginalElements();
    $this->assertSame($originalElements = array(
      $this->element0,
      $this->element1,
    ), $group->getOriginalElements());
    $group->add(new Comment());
    $this->assertSame($originalElements, $group->getOriginalElements());
  }

  public function testCallback()
  {
    $group = new MondongoGroup();

    $group->setCallback('foobar');
    $this->assertEquals('foobar', $group->getCallback());
  }

  public function testAdd()
  {
    $elements = $this->elements;
    $group    = new MondongoGroup($elements);

    $group->add($comment = new Comment());
    array_push($elements, $comment);
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->add($comment);
    $this->assertTrue($this->value);
  }

  public function testSet()
  {
    $elements = $this->elements;
    $group    = new MondongoGroup($elements);

    $group->set(20, $comment = new Comment());
    $elements[20] = $comment;
    $this->assertSame($elements, $group->getElements());

    $group->setCallback($this->callback);
    $group->set('foobar', new Comment);
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

    $this->assertSame($this->element1, $group->remove(1));

    $this->assertTrue($group->exists(0));
    $this->assertFalse($group->exists(1));

    $group->setCallback($this->callback);

    $this->assertNull($group->remove(10));
    $this->assertFalse($this->value);

    $group->remove(0);
    $this->assertTrue($this->value);
  }

  public function testRemoveElement()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertTrue($group->removeElement($this->element1));

    $this->assertTrue($group->existsElement($this->element0));
    $this->assertFalse($group->existsElement($this->element1));

    $group->setCallback($this->callback);

    $this->assertFalse($group->removeElement(new Comment()));
    $this->assertFalse($this->value);


    $group->removeElement($this->element0);
    $this->assertTrue($this->value);
  }

  public function testClear()
  {
    $group = new MondongoGroup($this->elements);

    $group->clear();
    $this->assertSame(array(), $group->getElements());

    $group = new MondongoGroup($this->elements);
    $group->setCallback($this->callback);
    $group->clear();
    $this->assertTrue($this->value);
  }

  public function testArrayAccess()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertTrue(isset($group[0]));
    $this->assertFalse(isset($group[10]));

    $this->assertSame($this->element0, $group[0]);
    $this->assertNull($group['no']);

    $group[10] = $comment = new Comment();
    $this->assertSame($comment, $group[10]);

    unset($group[10]);
    $this->assertNull($group[10]);
  }

  public function testCountable()
  {
    $group = new MondongoGroup($this->elements);

    $this->assertSame(2, $group->count());
    $this->assertSame(2, count($group));
  }
}
