<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:38 PM
 */


namespace models\bookworm;


use models\Model;

class Volume extends Model
{
    private $volumeId;

    /**
     * @return string
     */
    public function getVolumeId(): string
    {
        return $this->volumeId;
    }
}