<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/13/2019
 * Time: 11:08 AM
 */


namespace extensions\knowledgebase\models;


use models\Model;

class Collection extends Model
{
    public const DRAFT = 'Draft';
    public const REVIEW = 'Review';
    public const PUBLISHED = 'Published';
    public const DISCARDED = 'Discarded';

    // All valid statuses
    public const STATUSES = array(
        self::DRAFT, self::REVIEW, self::PUBLISHED, self::DISCARDED
    );

    private $id;
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


}