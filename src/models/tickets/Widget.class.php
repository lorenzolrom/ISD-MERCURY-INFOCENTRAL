<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/15/2019
 * Time: 8:41 PM
 */


namespace models\tickets;


use models\Model;

class Widget extends Model
{
    private $id;
    private $user;
    private $workspace;
    private $search;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getWorkspace(): int
    {
        return $this->workspace;
    }

    /**
     * @return int
     */
    public function getSearch(): int
    {
        return $this->search;
    }


}