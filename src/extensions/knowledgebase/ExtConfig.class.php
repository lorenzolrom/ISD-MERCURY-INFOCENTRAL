<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/13/2019
 * Time: 11:07 AM
 */


namespace extensions\knowledgebase;


class ExtConfig
{
    public const ROUTES = array(
        'kbarticles' => 'extensions\knowledgebase\controllers\ArticleController',
        'kbcollections' => 'extensions\knowledgebase\controllers\CollectionController'
    );
}