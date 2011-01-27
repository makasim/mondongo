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

namespace Mondongo\Tests\Extension;

use Mondongo\Tests\TestCase;
use Mondongo\Extension\Core;
use Mondongo\Group;
use Mondongo\Mondator\Container;
use Mondongo\Mondongo;
use Model\Article;
use Model\Author;
use Model\AuthorTelephone;
use Model\Category;
use Model\Comment;
use Model\EmbedNot;
use Model\News;
use Model\Source;
use Model\Summary;
use Model\User;

class CoreTest extends TestCase
{
    public function testDocumentMondongoParentClass()
    {
        $r = new \ReflectionClass('Model\Article');
        $this->assertTrue($r->isSubclassOf('Mondongo\Document\Document'));

        $r = new \ReflectionClass('Model\Comment');
        $this->assertTrue($r->isSubclassOf('Mondongo\Document\EmbeddedDocument'));
        $this->assertFalse($r->isSubclassOf('Mondongo\Document\Document'));
    }

    public function testDocumentBaseGetMondongoMethodNamespaced()
    {
        $article = new Article();
        $this->assertSame($this->mondongo, $article->getMondongo());
    }

    public function testDocumentBaseGetMondongoMethodNotNamespaced()
    {
        $article = new \Article();
        $this->assertSame($this->mondongo, $article->getMondongo());
    }

    public function testDocumentBaseGetRepositoryMethodNamespaced()
    {
        $article = new Article();
        $this->assertSame($this->mondongo->getRepository('Model\Article'), $article->getRepository());

        $user = new User();
        $this->assertSame($this->mondongo->getRepository('Model\User'), $user->getRepository());
    }

    public function testDocumentBaseGetRepositoryMethodNotNamespaced()
    {
        $article = new \Article();
        $this->assertSame($this->mondongo->getRepository('Article'), $article->getRepository());
    }

    public function testEmbedNotRepository()
    {
        $this->assertFalse(class_exists('Model\Repository\EmbedNot'));
        $this->assertFalse(class_exists('Model\Repository\Base\EmbedNot'));
    }

    public function testEmbedNotDocumentGetMondongoMethod()
    {
        $embedNot = new EmbedNot();
        $this->assertFalse(method_exists($embedNot, 'getMondongo'));
    }

    public function testEmbedNotDocumentGetRepositoryMethod()
    {
        $embedNot = new EmbedNot();
        $this->assertFalse(method_exists($embedNot, 'getRepository'));
    }

    public function testDocumentDataProperty()
    {
        $article = new Article();
        $this->assertSame(array(
            'fields' => array(
                'title'        => null,
                'slug'         => null,
                'content'      => null,
                'is_active'    => null,
                'author_id'    => null,
                'category_ids' => null,
            ),
            'references' => array(
                'author'     => null,
                'categories' => null,
            ),
            'embeddeds' => array(
                'source'   => null,
                'comments' => null,
            ),
            'relations' => array(
                'summary' => null,
                'news'    => null,
            ),
        ), $article->getDocumentData());
    }

    public function testDocumentFieldsModifiedsProperty()
    {
        $user = new User();
        $this->assertSame(array('is_active' => null), $user->getFieldsModified());
    }

    public function testDocumentFieldsSettersGettersMethods()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $this->assertSame('Mondongo', $article->getTitle());
        $article->setTitle('Mondongo 1');
        $this->assertSame(array('title' => null), $article->getFieldsModified());
    }

    public function testDocumentFieldsSettersSameCurrentValue()
    {
        $article = new Article();
        $article->setTitle(null);
        $this->assertSame(array(), $article->getFieldsModified());

        $article->setTitle('Mondongo');
        $article->save();
        $article->setTitle('Mondongo');
        $this->assertSame(array(), $article->getFieldsModified());
    }

    public function testDocumentReferencesOneSettersGetters()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $article = new Article();

        $this->assertNull($article->getAuthor());

        $article->setAuthor($author);

        $this->assertSame($author->getId(), $article->getAuthorId());
        $this->assertSame($author, $article->getAuthor());

        $article->save();

        $article = $this->mondongo->getRepository('Model\Article')->findOneById($article->getId());
        $this->assertEquals($author, $a = $article->getAuthor());
        $this->assertSame($a, $article->getAuthor());
    }

    public function testDocumentReferencesOneSetterNew()
    {
        $author = new Author();

        $article = new Article();
        $article->setAuthor($author);

        $this->assertSame($author, $article->getAuthor());
        $this->assertNull($article->getAuthorId());
    }

    public function testDocumentReferencesOneSetterOverrideWithNew()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $article = new Article();
        $article->setAuthor($author);

        $authorNew = new Author();
        $authorNew->setName('pablodip');

        $article->setAuthor($authorNew);
        $this->assertNull($article->getAuthorId());
    }

    public function testDocumentReferenceOneFieldSetAnother()
    {
        $author1 = new Author();
        $author1->setName('Pablo');
        $author1->save();

        $author2 = new Author();
        $author2->setName('pablodip');
        $author2->save();

        $article = new Article();
        $article->setTitle('Mon');
        $article->setAuthor($author1);
        $article->save();

        $article->setAuthorId($author2->getId());
        $this->assertSame($author2, $article->getAuthor());
    }

    public function testDocumentReferenceOneFieldSetNull()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $article = new Article();
        $article->setTitle('Mon');
        $article->setAuthor($author);
        $article->save();

        $article->setAuthorId(null);
        $this->assertNull($article->getAuthorId());
        $this->assertNull($article->getAuthor());
    }

    public function testDocumentReferenceOneReferenceSetNull()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $article = new Article();
        $article->setTitle('Mon');
        $article->setAuthor($author);
        $article->save();

        $article->setAuthor(null);
        $this->assertNull($article->getAuthor());
        $this->assertNull($article->getAuthorId());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesOneSetterInvalidReferenceClass()
    {
        $article = new Article();
        $article->setAuthor(new Category());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDocumentReferencesOneGetterNotExists()
    {
        $article = new Article();
        $article->setAuthorId(new \MongoId('123'));
        $article->getAuthor();
    }

    public function testDocumentReferencesManySettersGetters()
    {
        $categories = array();
        $ids        = array();
        for ($i = 1; $i <= 10; $i++) {
            $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            if (5 != $i) {
                $categories[] = $category;
                $ids[]        = $category->getId();
            }
        }

        $group = new Group($categories);

        $article = new Article();
        $article->setCategories($group);

        $this->assertSame($group, $article->getCategories());
        $this->assertSame($ids, $article->getCategoryIds());
        $this->assertSame(array($article, 'updateCategories'), $group->getChangeCallback());

        $article->save();

        $article = $this->mondongo->getRepository('Model\Article')->findOneById($article->getId());
        $this->assertEquals($group, $g = $article->getCategories());
        $this->assertSame($g, $article->getCategories());
    }

    public function testDocumentReferencesManyGetterWithoutIdsDocumentNew()
    {
        $article = new Article();
        $categories = $article->getCategories();
        $this->assertInstanceOf('\Mondongo\Group', $categories);
        $this->assertSame(0, count($categories));
    }

    public function testDocumentReferencesManyGetterWithoutIdsDocumentNotNew()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $article->save();

        $categories = $article->getCategories();
        $this->assertInstanceOf('\Mondongo\Group', $categories);
        $this->assertSame(0, count($categories));
    }

    public function testDocumentReferencesManySetterNew()
    {
        $categories = new Group();
        $ids = array();
        for ($i = 1; $i <= 8; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            if ($i % 2) {
                $category->save();
                $ids[] = $category->getId();
            }
        }

        $article = new Article();
        $article->setCategories($categories);

        $this->assertSame($categories, $article->getCategories());
        $this->assertSame($ids, $article->getCategoryIds());
    }

    public function testDocumentReferencesManySetArray()
    {
        $categories = array();
        for ($i = 1; $i <= 6; $i++) {
            $categories[] = $category = new Category();
            if ($i % 2) {
                $category->setName('Category '.$i);
                $category->save();
            }
        }

        $article = new Article();
        $article->setCategories($categories);

        $this->assertInstanceof('Mondongo\Group', $article->getCategories());
        $this->assertSame($categories, $article->getCategories()->getElements());
    }

    public function testDocumentReferencesManySetterFieldNullWithNewReferences()
    {
        $categories = new Group();
        for ($i = 1; $i <= 5; $i++) {
            $categories->add(new Category());
        }

        $article = new Article();
        $article->setCategories($categories);

        $this->assertNull($article->getCategoryIds());
    }

    public function testDocumentReferenceManyFieldSetAnothers()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 8; $i++) {
            $categories[$i] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            $ids[$i] = $category->getId();
        }

        $article = new Article();
        $article->setTitle('Mon');
        $article->setCategories(array($categories[3], $categories[5]));
        $article->save();

        $article->setCategoryIds(array($categories[2]->getId(), $categories[6]->getId()));
        $this->assertEquals(array($categories[2], $categories[6]), $article->getCategories()->getElements());
    }

    public function testDocumentReferenceManyFieldSetNull()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 8; $i++) {
            $categories[$i] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            $ids[$i] = $category->getId();
        }

        $article = new Article();
        $article->setTitle('Mon');
        $article->setCategories(array($categories[3], $categories[5]));
        $article->save();

        $article->setCategoryIds(null);
        $this->assertEquals(array(), $article->getCategories()->getElements());
    }

    public function testDocumentReferenceManyReferenceSetNull()
    {
        $categories = array();
        $ids = array();
        for ($i = 1; $i <= 8; $i++) {
            $categories[$i] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            $ids[$i] = $category->getId();
        }

        $article = new Article();
        $article->setTitle('Mon');
        $article->setCategories(array($categories[3], $categories[5]));
        $article->save();

        $article->setCategories(null);
        $this->assertNull($article->getCategoryIds());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesManySetterNotGroupNorArray()
    {
        $article = new Article();
        $article->setCategories(new Category());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesManySetterInvalidReferenceClass()
    {
        $categories = array();
        for ($i = 1; $i <= 4; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
        }

        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $group = new Group($categories);
        $group->add($author);

        $article = new Article();
        $article->setCategories($group);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDocumentReferencesManyGetterNotExists()
    {
        $article = new Article();
        $article->setCategoryIds(array(new \MongoId('123')));
        $article->getCategories();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDocumentReferencesManyGetterSomeNotExists()
    {
        $categories = array();
        $ids        = array();
        for ($i = 1; $i <= 4; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
            if (3 != $i) {
                $ids[] = $category->getId();
            }
        }
        $ids[] = new \MongoId('123');

        $article = new Article();
        $article->setCategoryIds($ids);
        $article->getCategories();
    }

    public function testDocumentReferencesManyUpdate()
    {
        $categories = array();
        for ($i = 1; $i <= 4; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
        }

        $group = new Group(array($categories[0], $categories[2]));

        $article = new Article();
        $article->setCategories($group);

        $group->setChangeCallback(null);
        $group->add($categories[1]);
        $this->assertSame(array($categories[0]->getId(), $categories[2]->getId()), $article->getCategoryIds());
        $article->updateCategories();
        $this->assertSame(array($categories[0]->getId(), $categories[2]->getId(), $categories[1]->getId()), $article->getCategoryIds());
    }

    public function testDocumentReferencesManyUpdateReferenceNew()
    {
        $categories = array();
        for ($i = 1; $i <= 4; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
        }

        $group = new Group($categories);

        $article = new Article();
        $article->setCategories($group);
        $group->add(new Category());
        $article->updateCategories();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentReferencesManyUpdateReferenceClass()
    {
        $categories = array();
        for ($i = 1; $i <= 4; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
        }

        $group = new Group($categories);

        $article = new Article();
        $article->setCategories($group);

        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $group->add($author);
        $article->updateCategories();
    }

    public function testDocumentReferencesSaveReferencesNew()
    {
        $author = new Author();
        $author->setName('Pablo');

        $categories = new Group();
        for ($i = 1; $i <= 8; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            if ($i % 2) {
                $category->save();
            }
        }

        $article = new Article();
        $article->setAuthor($author);
        $article->setCategories($categories);

        $article->saveReferences();

        $this->assertTrue($article->isNew());
        $this->assertFalse($author->isNew());
        $this->assertSame($author->getId(), $article->getAuthorId());
        $ids = array();
        foreach ($categories as $category) {
            $this->assertFalse($category->isNew());
            $ids[] = $category->getId();
        }
        $this->assertSame($ids, $article->getCategoryIds());
    }

    public function testDocumentReferencesSaveReferencesNotNews()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();
        $author->setName('pablodip');

        $categories = new Group();
        for ($i = 1; $i <= 8; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            $category->save();
            $category->setName('Cat');
        }

        $article = new Article();
        $article->setAuthor($author);
        $article->setCategories($categories);

        $article->saveReferences();

        $this->assertFalse($author->isModified());
        foreach ($categories as $category) {
            $this->assertFalse($category->isModified());
        }
    }

    public function testDocumentReferencesSaveReferencesNotModified()
    {
        $author = new Author();

        $categories = new Group();
        for ($i = 1; $i <= 4; $i++) {
            $categories->add($category = new Category());
        }

        $article = new Article();
        $article->setAuthor($author);
        $article->setCategories($categories);

        $article->saveReferences();

        $this->assertTrue($article->isNew());
        $this->assertTrue($author->isNew());
        $this->assertNull($article->getAuthorId());
        foreach ($categories as $category) {
            $this->assertTrue($category->isNew());
        }
        $this->assertNull($article->getCategoryIds());
    }

    public function testEmbeddedDocumentsOne()
    {
        $article = new Article();

        $this->assertEquals(new Source(), $source = $article->getSource());
        $this->assertSame($source, $article->getSource());

        $source = new Source();
        $article->setSource($source);
        $this->assertSame($source, $article->getSource());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmbeddedDocumentsOneSetterInvalidEmbedClass()
    {
        $article = new Article();
        $article->setSource(new Comment());
    }

    public function testEmbeddedDocumentsMany()
    {
        $article = new Article();

        $group = $article->getComments();
        $this->assertInstanceOf('Mondongo\Group', $group);
        $this->assertSame(array(), $group->getElements());
        $this->assertSame($group, $article->getComments());

        $groups = new Group();
        $article->setComments($group);
        $this->assertSame($group, $article->getComments());
    }

    public function testDocumentsEmbeddedsManySetArray()
    {
        $comments = array();
        for ($i = 1; $i <= 5; $i++) {
            $comments[] = new Comment();
        }

        $article = new Article();
        $article->setComments($comments);

        $this->assertInstanceOf('Mondongo\Group', $article->getComments());
        $this->assertSame($comments, $article->getComments()->getElements());
    }

    public function testDocumentEmbeddedsManySetCombine()
    {
        $comments1 = new Group();
        for ($i = 1; $i <= 5; $i++) {
            $comments1->add($comment = new Comment());
            $comment->setName('Comments1 '.$i);
        }

        $comments2 = new Group();
        for ($i = 1; $i <= 5; $i++) {
            $comments2->add($comment = new Comment());
            $comment->setName('Comments1 '.$i);
        }

        $article = new Article();
        $article->setComments($comments1);
        $article->save();
        $article->setComments($comments2);

        $this->assertSame(spl_object_hash($comments2), spl_object_hash($article->getComments()));
        $this->assertSame($comments1->getElements(), $article->getComments()->getOriginalElements());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmbeddedDocumentsManySetterNotGroupNorArray()
    {
        $article = new Article();
        $article->setComments(new Comment());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentEmbeddedsManySetterInvalidEmbeddedClass()
    {
        $comments = array();
        $comments[] = new Comment();
        $comments[] = new Author();
        $comments[] = new Comment();

        $article = new Article();
        $article->setComments($comments);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentEmbeddedsManyGetterUpdateInvalidEmbeddedClass()
    {
        $article = new Article();
        $article->getComments()->add(new Author());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentEmbeddedsManySetterUpdateInvalidEmbeddedClass()
    {
        $comments = new Group(array(new Comment()));

        $article = new Article();
        $article->setComments($comments);

        $comments->add(new Author());
    }

    public function testDocumentRelationsOneOne()
    {
        $telephone = new AuthorTelephone();
        $telephone->setNumber('123');
        $telephone->save();

        $telephoneAuthor = array();
        for ($i = 1; $i <= 10; $i++) {
            $author = new Author();
            $author->setName('Author '.$i);
            if (3 == $i) {
                $telephoneAuthor = $author;
                $author->setTelephoneId($telephone->getId());
            }
            $author->save();
        }

        $this->assertEquals($telephoneAuthor, $result = $telephone->getAuthor());
        $this->assertSame($result, $telephone->getAuthor());
    }

    public function testDocumentRelationsOneMany()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $articles = array();
        for ($i = 1; $i <= 10; $i++) {
            $article = new Article();
            if ($i % 2) {
                $articles[] = $article;
                $article->setAuthorId($author->getId());
            }
            $article->setTitle('Article '.$i);
            $article->save();
        }

        $this->assertEquals($articles, $results = $author->getArticles());

        $this->assertSame($results, $author->getArticles());
    }

    public function testDocumentRelationsManyMany()
    {
        $category = new Category();
        $category->setName('Mondongo');
        $category->save();

        $articles = array();
        for ($i = 1; $i <= 10; $i++) {
            $article = new Article();
            if ($i % 2) {
                $articles[] = $article;
                $article->setCategoryIds(array($category->getId()));
            }
            $article->setTitle('Article '.$i);
            $article->save();
        }

        $this->assertEquals($articles, $results = $category->getArticles());

        $this->assertSame($results, $category->getArticles());
    }

    public function testDocumentSetMethodFields()
    {
        $article = new Article();
        $article->set('title', 'foo');
        $article->set('content', 'bar');

        $this->assertSame('foo', $article->getTitle());
        $this->assertSame('bar', $article->getContent());
    }

    public function testDocumentSetMethodReferences()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $categories = new Group();
        for ($i = 1; $i <= 10; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            $category->save();
        }

        $article = new Article();
        $article->set('author', $author);
        $article->set('categories', $categories);

        $this->assertSame($author, $article->getAuthor());
        $this->assertSame($categories, $article->getCategories());
    }

    public function testDocumentSetMethodEmbeds()
    {
        $source = new Source();
        $source->setName('My Source');

        $comments = new Group();
        for ($i = 1; $i <= 10; $i++) {
            $comments->add($c = new Comment());
            $c->setName('Comment '.$i);
        }

        $article = new Article();
        $article->set('source', $source);
        $article->set('comments', $comments);

        $this->assertSame($source, $article->getSource());
        $this->assertSame($comments, $article->getComments());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentSetMethodRelations()
    {
        $article = new Article();
        $article->set('news', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentSetMethodDataDoesNotExists()
    {
        $article = new Article();
        $article->set('no', 'foo');
    }

    public function testDocumentGetMethodFields()
    {
        $article = new Article();
        $article->setTitle('foo');
        $article->setContent('bar');

        $this->assertSame('foo', $article->get('title'));
        $this->assertSame('bar', $article->get('content'));
    }

    public function testDocumentGetMethodReferences()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $categories = new Group();
        for ($i = 1; $i <= 10; $i++) {
            $categories->add($category = new Category());
            $category->setName('Category '.$i);
            $category->save();
        }

        $article = new Article();
        $article->setAuthor($author);
        $article->setCategories($categories);

        $this->assertSame($author, $article->get('author'));
        $this->assertSame($categories, $article->get('categories'));
    }

    public function testDocumentGetMethodEmbeds()
    {
        $source = new Source();
        $source->setName('My Source');

        $comments = new Group();
        for ($i = 1; $i <= 10; $i++) {
            $comments->add($c = new Comment());
            $c->setName('Comment '.$i);
        }

        $article = new Article();
        $article->setSource($source);
        $article->setComments($comments);

        $this->assertSame($source, $article->get('source'));
        $this->assertSame($comments, $article->get('comments'));
    }

    public function testDocumentGetMethodRelations()
    {
        $article = new Article();
        $article->setTitle('My Article');
        $article->save();

        $summary = new Summary();
        $summary->setArticle($article);
        $summary->setText('foo');
        $summary->save();

        $news = new Group();
        for ($i = 1; $i <= 1; $i++) {
            $news->add($n = new News());
            $n->setTitle('News '.$i);
            $n->setArticle($article);
            $n->save();
        }

        $this->assertSame($summary, $article->get('summary'));
        $this->assertSame($news->getElements(), $article->get('news'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDocumentGetMethodDataDoesNotExists()
    {
        $article = new Article();
        $article->get('no');
    }

    public function testFromArray()
    {
        $article = new Article();
        $article->fromArray(array(
            'title'     => 'Mondongo',
            'content'   => 'Content',
            'is_active' => true,
            'author_id' => '123',
            'category_ids' => array(
                '234',
                '345',
            ),
            'source' => array(
                'name' => 'Mondongo',
                'url'  => 'http://mondongo.es',
            ),
            'comments' => array(
                array(
                    'name' => 'Pablo',
                    'text' => 'Wow',
                ),
                array(
                    'name' => 'Name 2',
                    'text' => 'Text 2',
                ),
            ),
        ));

        $this->assertSame('Mondongo', $article->getTitle());
        $this->assertSame('Content', $article->getContent());
        $this->assertTrue($article->getIsActive());
        $this->assertSame('123', $article->getAuthorId());
        $this->assertSame(array('234', '345'), $article->getCategoryIds());
        $this->assertSame('Mondongo', $article->getSource()->getName());
        $this->assertSame('http://mondongo.es', $article->getSource()->getUrl());
        $this->assertSame(2, $article->getComments()->count());
        $this->assertSame('Pablo', $article->getComments()->getByKey(0)->getName());
        $this->assertSame('Wow', $article->getComments()->getByKey(0)->getText());
        $this->assertSame('Name 2', $article->getComments()->getByKey(1)->getName());
        $this->assertSame('Text 2', $article->getComments()->getByKey(1)->getText());
    }

    public function testFromArrayReferencesOne()
    {
        $author = new Author();
        $author->setName('Pablo');
        $author->save();

        $article = new Article();
        $article->fromArray(array(
            'author' => $author,
        ));

        $this->assertSame($author, $article->getAuthor());
    }

    public function testFromArrayReferencesMany()
    {
        $categories = array();
        for ($i = 1; $i <= 10; $i++) {
            $categories[] = $category = new Category();
            $category->setName('Category '.$i);
            $category->save();
        }

        $article = new Article();
        $article->fromArray(array(
            'categories' => $categories,
        ));

        $this->assertSame($categories, $article->getCategories()->getElements());
    }

    public function testFromArrayEmbeddedsOne()
    {
        $source = new Source();

        $article = new Article();
        $article->fromArray(array(
            'source' => $source,
        ));

        $this->assertSame($source, $article->getSource());
    }

    public function testFromArrayEmbeddedsMany()
    {
        $comments = array();
        for ($i = 1; $i <= 10; $i++) {
            $comments[] = new Comment();
        }

        $article = new Article();
        $article->fromArray(array(
            'comments' => $comments,
        ));

        $this->assertSame($comments, $article->getComments()->getElements());
    }

    public function testToArray()
    {
        $article = new Article();
        $article->setTitle('Mondongo');
        $article->setContent('Content');
        $article->setIsActive(true);
        $article->getSource()->setName('Mondongo');
        $article->getSource()->setUrl('http://mondongo.es');
        $article->getComments()->add($comment = new Comment());
        $comment->setName('Pablo');
        $comment->setText('Wow');
        $article->getComments()->add($comment = new Comment());
        $comment->setName('Name 2');
        $comment->setText('Text 2');

        $this->assertSame(array(
            'title'     => 'Mondongo',
            'content'   => 'Content',
            'is_active' => true,
            'source'    => array(
                'name' => 'Mondongo',
                'url'  => 'http://mondongo.es',
            ),
            'comments' => array(
                array(
                    'name' => 'Pablo',
                    'text' => 'Wow',
                ),
                array(
                    'name' => 'Name 2',
                    'text' => 'Text 2',
                ),
            ),
        ), $article->toArray());

        $this->assertSame(array(
            'title'     => 'Mondongo',
            'content'   => 'Content',
            'is_active' => true,
        ), $article->toArray(false));
    }

    public function testDocumentSetDocumentDataMethod()
    {
        $user = new User();
        $user->setDocumentData(array(
            '_id'       => $id = new \MongoId('123'),
            'username'  => 123456,
            'is_active' => 1,
        ));
        $this->assertSame($id, $user->getId());
        $this->assertSame('123456', $user->getUsername());
        $this->assertSame(true, $user->getIsActive());
        $this->assertSame(array(), $user->getFieldsModified());
    }

    public function testDocumentSetDocumentDataMethodEmbeddeds()
    {
        $article = new Article();
        $article->setDocumentData(array(
            '_id' => new \MongoId('123'),
            'title' => 123456,
            'source' => array(
                'name' => 456,
                'url' => 'http://mondongo.es',
            ),
            'comments' => array(
                array(
                    'name' => 123456,
                    'text' => 789,
                ),
                array(
                    'name' => 1.23,
                    'text' => 7.89,
                ),
            ),
        ));

        $this->assertSame('123456', $article->getTitle());

        $source = $article->getSource();
        $this->assertSame('456', $source->getName());
        $this->assertSame('http://mondongo.es', $source->getUrl());

        $comments = $article->getComments();
        $this->assertInstanceOf('Mondongo\\Group', $comments);
        $this->assertSame(2, $comments->count());
        $elements = $comments->getElements();
        $this->assertSame('123456', $elements[0]->getName());
        $this->assertSame('789', $elements[0]->getText());
        $this->assertSame('1.23', $elements[1]->getName());
        $this->assertSame('7.89', $elements[1]->getText());
        $this->assertSame($elements, $comments->getOriginalElements());
    }

    public function testDocumentFieldsToMongoMethod()
    {
        $user = new User();
        $this->assertSame(array(
            'username'  => '123456',
            'is_active' => true,
        ), $user->fieldsToMongo(array('username' => 123456, 'is_active' => 1)));
    }

    public function testRepositoryDocumentClassPropertyNamespaced()
    {
        $this->assertSame('Model\Article', $this->mondongo->getRepository('Model\Article')->getDocumentClass());
    }

    public function testRepositoryDocumentClassPropertyNotNamespaced()
    {
        $this->assertSame('Article', $this->mondongo->getRepository('Article')->getDocumentClass());
    }

    public function testRepositoryConnectionNameProperty()
    {
        $this->assertNull($this->mondongo->getRepository('Model\Article')->getConnectionName());
        $this->assertSame('global', $this->mondongo->getRepository('Model\ConnectionGlobal')->getConnectionName());
    }

    public function testRepositoryCollectionNameProperty()
    {
        $this->assertSame('article', $this->mondongo->getRepository('\Model\Article')->getCollectionName());
        $this->assertSame('my_name', $this->mondongo->getRepository('Model\CollectionName')->getCollectionName());
    }

    public function testRepositoryIsFileProperty()
    {
        $this->assertTrue($this->mondongo->getRepository('Model\Image')->isFile());
        $this->assertFalse($this->mondongo->getRepository('Model\Article')->isFile());
    }

    public function testRepositoryEnsureIndexesMethod()
    {
        $this->mondongo->getRepository('Model\Article')->ensureIndexes();

        $indexInfo = $this->db->article->getIndexInfo();

        $this->assertSame(array('slug' => 1), $indexInfo[1]['key']);
        $this->assertSame(true, $indexInfo[1]['unique']);

        $this->assertSame(array('author_id' => 1, 'is_active' => 1), $indexInfo[2]['key']);
    }

    /*
     * Errors.
     */

    /**
     * @expectedException \RuntimeException
     */
    public function testDoesNotHaveOutput()
    {
        $extension = new Core(array());
        $extension->classProcess(new Container(), 'Article', new \ArrayObject(), new \ArrayObject());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testIsFileNotBoolean()
    {
        $extension = new Core();
        $extension->classProcess(new Container(), 'Article', new \ArrayObject(array(
            'is_file' => 1,
        )), new \ArrayObject());
    }

    /**
     * @expectedException \RuntimeException
     * @dataProvider      providerFieldNotStringNorArray
     */
    public function testFieldNotStringNorArray($type)
    {
        $extension = new Core();
        $extension->classProcess(new Container(), 'Article', new \ArrayObject(array(
            'fields' => array(
                'field' => $type,
            ),
        )), new \ArrayObject());
    }

    public function providerFieldNotStringNorArray()
    {
        return array(
            array(1),
            array(1,1),
            array(true),
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFieldDoesNotHaveType()
    {
        $extension = new Core();
        $extension->classProcess(new Container(), 'Article', new \ArrayObject(array(
            'fields' => array(
                'field' => array('default' => 'default'),
            ),
        )), new \ArrayObject());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFieldTypeDoesNotExists()
    {
        $extension = new Core();
        $extension->classProcess(new Container(), 'Article', new \ArrayObject(array(
            'fields' => array(
                'field' => array('type' => 'no'),
            ),
        )), new \ArrayObject());
    }
}
