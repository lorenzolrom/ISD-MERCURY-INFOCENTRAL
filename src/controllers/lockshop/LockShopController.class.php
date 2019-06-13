<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 6/13/2019
 * Time: 1:30 PM
 */


namespace controllers\lockshop;


use controllers\Controller;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPResponse;

class LockShopController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($param == 'systems')
        {
            $s = new SystemController($this->request);
            return $s->getResponse();
        }

        return NULL;
    }
}