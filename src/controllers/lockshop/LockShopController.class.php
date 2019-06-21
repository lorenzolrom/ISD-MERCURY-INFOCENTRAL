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
use controllers\CurrentUserController;
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
        CurrentUserController::validatePermission('lockshop-r');

        $param = $this->request->next();

        if($param == 'systems')
        {
            $s = new SystemController($this->request);
            return $s->getResponse();
        }
        else if($param == 'cores')
        {
            $c = new CoreController($this->request);
            return $c->getResponse();
        }
        else if($param == 'keys')
        {
            $k = new KeyController($this->request);
            return $k->getResponse();
        }

        return NULL;
    }
}