<?php

namespace Model\Document\Base;

abstract class Article extends \Mondongo\Document\Document
{

    protected $data = array (
  'fields' => 
  array (
    'title' => NULL,
    'content' => NULL,
    'is_active' => NULL,
    'author_id' => NULL,
    'category_ids' => NULL,
  ),
  'references' => 
  array (
    'author' => NULL,
    'categories' => NULL,
  ),
  'embeds' => 
  array (
    'source' => NULL,
    'comments' => NULL,
  ),
);

    protected $fieldsModified = array (
);

    public function getMondongo()
    {
        return \Mondongo\Container::getForDocumentClass('Model\Document\Article');
    }

    public function getRepository()
    {
        return $this->getMondongo()->getRepository('Model\Document\Article');
    }

    public function setDocumentData($data)
    {
        $this->id = $data['_id'];

        if (isset($data['title'])) {
            $this->data['fields']['title'] = (string) $data['title'];
        }
        if (isset($data['content'])) {
            $this->data['fields']['content'] = (string) $data['content'];
        }
        if (isset($data['is_active'])) {
            $this->data['fields']['is_active'] = (bool) $data['is_active'];
        }
        if (isset($data['author_id'])) {
            $this->data['fields']['author_id'] = $data['author_id'];
        }
        if (isset($data['category_ids'])) {
            $this->data['fields']['category_ids'] = $data['category_ids'];
        }

        if (isset($data['source'])) {
            $embed = new \Model\Document\Source();
            $embed->setDocumentData($data['source']);
            $this->setSource($embed);
        }
        if (isset($data['comments'])) {
            $elements = array();
            foreach ($data['comments'] as $datum) {
                $elements[] = $element = new \Model\Document\Comment();
                $element->setDocumentData($datum);
            }
            $group = new \Mondongo\Group($elements);
            $group->saveOriginalElements();
            $this->setComments($group);
        }

        
    }

    public function fieldsToMongo($fields)
    {
        if (isset($fields['title'])) {
            $fields['title'] = (string) $fields['title'];
        }
        if (isset($fields['content'])) {
            $fields['content'] = (string) $fields['content'];
        }
        if (isset($fields['is_active'])) {
            $fields['is_active'] = (bool) $fields['is_active'];
        }
        if (isset($fields['author_id'])) {
            $fields['author_id'] = $fields['author_id'];
        }
        if (isset($fields['category_ids'])) {
            $fields['category_ids'] = $fields['category_ids'];
        }


        return $fields;
    }

    public function setTitle($value)
    {
        if (!array_key_exists('title', $this->fieldsModified)) {
            $this->fieldsModified['title'] = $this->data['fields']['title'];
        } elseif ($value === $this->fieldsModified['title']) {
            unset($this->fieldsModified['title']);
        }

        $this->data['fields']['title'] = $value;
    }

    public function getTitle()
    {
        return $this->data['fields']['title'];
    }

    public function setContent($value)
    {
        if (!array_key_exists('content', $this->fieldsModified)) {
            $this->fieldsModified['content'] = $this->data['fields']['content'];
        } elseif ($value === $this->fieldsModified['content']) {
            unset($this->fieldsModified['content']);
        }

        $this->data['fields']['content'] = $value;
    }

    public function getContent()
    {
        return $this->data['fields']['content'];
    }

    public function setIsActive($value)
    {
        if (!array_key_exists('is_active', $this->fieldsModified)) {
            $this->fieldsModified['is_active'] = $this->data['fields']['is_active'];
        } elseif ($value === $this->fieldsModified['is_active']) {
            unset($this->fieldsModified['is_active']);
        }

        $this->data['fields']['is_active'] = $value;
    }

    public function getIsActive()
    {
        return $this->data['fields']['is_active'];
    }

    public function setAuthorId($value)
    {
        if (!array_key_exists('author_id', $this->fieldsModified)) {
            $this->fieldsModified['author_id'] = $this->data['fields']['author_id'];
        } elseif ($value === $this->fieldsModified['author_id']) {
            unset($this->fieldsModified['author_id']);
        }

        $this->data['fields']['author_id'] = $value;
    }

    public function getAuthorId()
    {
        return $this->data['fields']['author_id'];
    }

    public function setCategoryIds($value)
    {
        if (!array_key_exists('category_ids', $this->fieldsModified)) {
            $this->fieldsModified['category_ids'] = $this->data['fields']['category_ids'];
        } elseif ($value === $this->fieldsModified['category_ids']) {
            unset($this->fieldsModified['category_ids']);
        }

        $this->data['fields']['category_ids'] = $value;
    }

    public function getCategoryIds()
    {
        return $this->data['fields']['category_ids'];
    }

    public function setAuthor($value)
    {
        if (!$value instanceof \Model\Document\Author) {
            throw new \InvalidArgumentException('The reference "author" is not an instance of "Model\Document\Author".');
        }
        if ($value->isNew()) {
            throw new \InvalidArgumentException('The reference "author" is new.');
        }

        $this->setAuthorId($value->getId());
        $this->data['references']['author'] = $value;
    }

    public function getAuthor()
    {
        if (null === $this->data['references']['author']) {
            $value = \Mondongo\Container::getForDocumentClass('Model\Document\Author')->getRepository('Model\Document\Author')->get($this->getAuthorId());
            if (!$value) {
                throw new \RuntimeException('The reference "author" does not exists');
            }
            $this->data['references']['author'] = $value;
        }

        return $this->data['references']['author'];
    }

    public function setCategories($value)
    {
        if (!$value instanceof \Mondongo\Group) {
            throw new \InvalidArgumentException('The reference "categories" is not an instance of Mondongo\Group.');
        }
        $value->setChangeCallback(array($this, 'updateCategories'));

        $ids = array();
        foreach ($value as $document) {
            if (!$document instanceof \Model\Document\Category) {
                throw new \InvalidArgumentException('Some document in the reference "categories" is not an instance of "Model\Document\Category".');
            }
            if ($document->isNew()) {
                throw new \InvalidArgumentException('Some document in the reference "categories" is new.');
            }
            $ids[] = $document->getId();
        }

        $this->setCategoryIds($ids);
        $this->data['references']['categories'] = $value;
    }

    public function getCategories()
    {
        if (null === $this->data['references']['categories']) {
            $ids   = $this->getCategoryIds();
            $value = \Mondongo\Container::getForDocumentClass('Model\Document\Category')->getRepository('Model\Document\Category')->find(array(
                'query' => array('_id' => array('$in' => $ids)),
            ));
            if (!$value || count($value) != count($ids)) {
                throw new \RuntimeException('The reference "categories" does not exists');
            }

            $group = new \Mondongo\Group($value);
            $group->setChangeCallback(array($this, 'updateCategories'));

            $this->data['references']['categories'] = $group;
        }

        return $this->data['references']['categories'];
    }

    public function updateCategories()
    {
        if (null !== $this->data['references']['categories']) {
            $ids = array();
            foreach ($this->data['references']['categories'] as $document) {
                if (!$document instanceof \Model\Document\Category) {
                    throw new \RuntimeException('Some document of the "categories" reference is not an instance of "Model\Document\Category".');
                }
                if ($document->isNew()) {
                    throw new \RuntimeException('Some document of the "categories" reference is new.');
                }
                $ids[] = $document->getId();
            }

            if ($ids !== $this->getCategoryIds()) {
                $this->setCategoryIds($ids);
            }
        }
    }

    public function setSource($value)
    {
        if (!$value instanceof \Model\Document\Source) {
            throw new \InvalidArgumentException('The embed "source" is not an instance of "Model\Document\Source".');
        }

        $this->data['embeds']['source'] = $value;
    }

    public function getSource()
    {
        if (null === $this->data['embeds']['source']) {
            $this->data['embeds']['source'] = new \Model\Document\Source();
        }

        return $this->data['embeds']['source'];
    }

    public function setComments($value)
    {
        if (!$value instanceof \Mondongo\Group) {
            throw new \InvalidArgumentException('The embed "comments" is not an instance of "Mondongo\Group".');
        }

        $this->data['embeds']['comments'] = $value;
    }

    public function getComments()
    {
        if (null === $this->data['embeds']['comments']) {
            $this->data['embeds']['comments'] = new \Mondongo\Group();
        }

        return $this->data['embeds']['comments'];
    }
}