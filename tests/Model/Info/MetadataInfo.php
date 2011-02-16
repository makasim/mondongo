<?php

namespace Model\Info;

class MetadataInfo
{
    public function getModelAuthorClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_author',
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(
                'telephone' => array(
                    'class' => 'Model\\AuthorTelephone',
                    'field' => 'telephone_id',
                ),
            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(
                'articles' => array(
                    'class' => 'Model\\Article',
                    'field' => 'author_id',
                ),
            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelAuthorTelephoneClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_author_telephone',
            'fields' => array(
                'number' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(
                'author' => array(
                    'class' => 'Model\\Author',
                    'field' => 'telephone_id',
                ),
            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelCategoryClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_category',
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(
                'articles' => array(
                    'class' => 'Model\\Article',
                    'field' => 'category_ids',
                ),
            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelCommentClassInfo()
    {
        return array(
            'is_embedded' => true,
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
                'text' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
        );
    }

    public function getModelSourceClassInfo()
    {
        return array(
            'is_embedded' => true,
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
                'url' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
        );
    }

    public function getModelArticleClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'article',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                ),
                'slug' => array(
                    'type' => 'string',
                ),
                'content' => array(
                    'type' => 'string',
                ),
                'is_active' => array(
                    'type' => 'boolean',
                ),
            ),
            'references_one' => array(
                'author' => array(
                    'class' => 'Model\\Author',
                    'field' => 'author_id',
                ),
            ),
            'references_many' => array(
                'categories' => array(
                    'class' => 'Model\\Category',
                    'field' => 'category_ids',
                ),
            ),
            'embeddeds_one' => array(
                'source' => array(
                    'class' => 'Model\\Source',
                ),
            ),
            'embeddeds_many' => array(
                'comments' => array(
                    'class' => 'Model\\Comment',
                ),
            ),
            'relations_one' => array(
                'summary' => array(
                    'class' => 'Model\\Summary',
                    'field' => 'article_id',
                ),
            ),
            'relations_many_one' => array(
                'news' => array(
                    'class' => 'Model\\News',
                    'field' => 'article_id',
                ),
            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(
                'votes_users' => array(
                    'class' => 'Model\\User',
                    'through' => 'Model\\ArticleVote',
                    'local' => 'article_id',
                    'foreign' => 'user_id',
                ),
            ),
            'indexes' => array(
                0 => array(
                    'keys' => array(
                        'slug' => 1,
                    ),
                    'options' => array(
                        'unique' => true,
                    ),
                ),
                1 => array(
                    'keys' => array(
                        'author_id' => 1,
                        'is_active' => 1,
                    ),
                ),
            ),
        );
    }

    public function getModelArticleVoteClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_article_vote',
            'fields' => array(

            ),
            'references_one' => array(
                'article' => array(
                    'class' => 'Model\\Article',
                    'field' => 'article_id',
                ),
                'user' => array(
                    'class' => 'Model\\User',
                    'field' => 'user_id',
                ),
            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelNewsClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_news',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(
                'article' => array(
                    'class' => 'Model\\Article',
                    'field' => 'article_id',
                ),
            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelSummaryClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_summary',
            'fields' => array(
                'text' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(
                'article' => array(
                    'class' => 'Model\\Article',
                    'field' => 'article_id',
                ),
            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelUserClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_user',
            'fields' => array(
                'username' => array(
                    'type' => 'string',
                ),
                'is_active' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelMessageClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_message',
            'fields' => array(
                'author' => array(
                    'type' => 'string',
                ),
                'text' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(
                'reply_to' => array(
                    'class' => 'Model\\Message',
                    'field' => 'reply_to_id',
                ),
            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelImageClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'image',
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
                'description' => array(
                    'type' => 'string',
                ),
                'file' => array(
                    'type' => 'raw',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelConnectionGlobalClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => 'global',
            'collection' => 'model_connection_global',
            'fields' => array(

            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelCollectionNameClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'my_name',
            'fields' => array(

            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelEventsClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_events',
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelEmbedNotClassInfo()
    {
        return array(
            'is_embedded' => true,
            'fields' => array(

            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
        );
    }

    public function getModelMultipleEmbedsClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_multiple_embeds',
            'fields' => array(
                'title' => array(
                    'type' => 'string',
                ),
                'content' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(
                'embeddeds1' => array(
                    'class' => 'Model\\MultipleEmbedsEmbedded1',
                ),
            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelMultipleEmbedsEmbedded1ClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_multiple_embeds_embedded1',
            'fields' => array(
                'name' => array(
                    'type' => 'string',
                ),
                'surname' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(
                'embeddeds2' => array(
                    'class' => 'Model\\MultipleEmbedsEmbedded2',
                ),
            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelMultipleEmbedsEmbedded2ClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => null,
            'connection' => null,
            'collection' => 'model_multiple_embeds_embedded2',
            'fields' => array(
                'field1' => array(
                    'type' => 'string',
                ),
                'field2' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }

    public function getModelCustomMondongoClassInfo()
    {
        return array(
            'is_embedded' => false,
            'mondongo' => 'foobar',
            'connection' => null,
            'collection' => 'model_custom_mondongo',
            'fields' => array(
                'field' => array(
                    'type' => 'string',
                ),
            ),
            'references_one' => array(

            ),
            'references_many' => array(

            ),
            'embeddeds_one' => array(

            ),
            'embeddeds_many' => array(

            ),
            'relations_one' => array(

            ),
            'relations_many_one' => array(

            ),
            'relations_many_many' => array(

            ),
            'relations_many_through' => array(

            ),
            'indexes' => array(

            ),
        );
    }
}