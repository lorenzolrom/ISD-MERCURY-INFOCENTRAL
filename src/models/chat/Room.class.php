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


namespace models\chat;


use models\Model;

class Room extends Model
{
    private $id;
    private $title;
    private $private;
    private $archived;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getPrivate(): int
    {
        return $this->private;
    }

    /**
     * @return int
     */
    public function getArchived(): int
    {
        return $this->archived;
    }


}