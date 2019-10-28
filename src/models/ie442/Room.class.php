<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/21/2019
 * Time: 4:18 PM
 */


namespace models\ie442;


use models\Model;

class Room extends Model
{
    private $id;
    private $user1;
    private $user2;

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
    public function getUser1(): int
    {
        return $this->user1;
    }

    /**
     * @return int
     */
    public function getUser2(): int
    {
        return $this->user2;
    }


}